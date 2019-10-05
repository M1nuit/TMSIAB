g<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Projectname: Aseco
 *
 * PHP version 5 (4.1 is coming soon!)
 *
 * LICENSE: This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA
 *
 * @author		Florian Schnell <floschnell@gmail.com>
 * @copyright	2006 by Florian Schnell
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version		TMU 1.1
 * @link		http://www.tmbase.de
 * @since		File available since Release 0.5.0
 *
 * Heavily modified by AssemblerManiac for TMU support & RASP support
 */

/**
 * Include required classes ...
 */
require_once('includes/basic.inc.php'); // contains standard functions
require_once('includes/xmlparser.inc.php'); // provides a xml parser
require_once('includes/GbxRemote.inc.php'); // needed for dedicated server connections
require_once('includes/types.inc.php'); // contains classes to store information
require_once('includes/rasp_settings.php'); //specific to the Rasp plugin

/**
 * Error function
 * Report errors on a regular way.
 */
set_error_handler('displayError');
function displayError($errno, $errstr, $errfile, $errline)
{
	switch ($errno) {
	case E_USER_ERROR:
		$message = "[ASECO Error] $errstr on line $errline\r\n";
		//doLog($message);
		echo $message;
		die();
		break;
	case E_ERROR:
		$message = "[PHP Error] $errstr on line $errline in file $errfile\r\n";
		//doLog($message);
		echo $message;
		mistral_checklog();
		break;

	case E_USER_WARNING:
		$message = "[ASECO Warning] $errstr\r\n";
		//doLog($message);
		echo $message;
		mistral_checklog();
		break;
	default:
		// do nothing...
		// only treat known errors ...
	}
}

// Current project version.
define('ASECO_VERSION', '1.1b TMU');
define('CRLF', "\r\n");

/**
 * Here Aseco actually starts.
 * Advanced coders can use this class
 * by including the document.
 * Make sure you cut out the lines below the class
 * and create your own instance.
 */
class Aseco {

	/**
	 * Public fields
	 */
	var $lastloginid;
	var $login;
	var $pass;
	var $admin_list;
	var $script_timeout;
	var $debug;
	var $command;
	var $dataexchanger;
	var $xml_parser;
	var $events;
	var $server;
	var $rpc_calls;
	var $rpc_responses;
	var $chat_commands;
	var $chat_admin_cmd_count;
	var $chat_colors;
	var $chat_messages;
	var $welcome_msgs;
	var $masterserver;
	var $settings;

	/**
	 * Initializes the server.
	 */
	function Aseco($debug, $config_file) {
		echo '# initialize ASECO ############################################################' . CRLF;

		// send php & mysql version info
		$this->console_text('[Aseco] PHP Version is ' . phpversion());

		// initialize ...
		$this->chat_commands = array();
		$this->debug = $debug;
		$this->client = new IXR_ClientMulticall_Gbx;
		$this->xml_parser = new Examsly();
		$this->server = new Server('127.0.0.1', 5000, 'SuperAdmin', 'SuperAdmin');
		$this->server->challenge = new Challenge();
		$this->server->players = new PlayerList();
		$this->server->records = new RecordList($maxrecs);
		$this->admin_list = array();
		$this->masterserver = new Masterserver();

		// load new settings, if available ...
		$this->console_text('[Aseco] Load settings [{1}]', $config_file);
		$this->loadSettings($config_file);

		$this->chat_admin_cmd_count = 0;

		// load plugins and register chat commands ...
		$this->loadPlugins();
	}


	/**
	 * Loads files in the plugins directory ...
	 */
	function loadPlugins() {

		// load and parse the plugins file ...
		if ($plugins = $this->xml_parser->parseXml('plugins.xml')) {

			// take each plugin tag ...
			foreach ($plugins['ASECO_PLUGINS']['PLUGIN'] as $plugin) {

				// showup message
				$this->console_text('[Aseco] Load plugin [' . $plugin . ']');

				// and include the value between ...
				require_once('plugins/' . $plugin);
			}
		}
	}


	/**
	 * Runs the server.
	 */
	function run() {

		// connect to Trackmania Dedicated Server ...
		if (!$this->connect()) {

			// kill program with an error ...
			trigger_error('Connection could not be established!', E_USER_ERROR);
		}

		// send status message ...
		$this->console_text('[Aseco] Connection established successfully!');

		// throw new event ...
		$this->releaseEvent ('onStartup', array());

		// register chat commands ...
		$this->registerChatCommands();

		// synchronize information with server ...
		$this->serverSync();

		// make a visual header ...
		$this->sendHeader();

	 	mistralLoadJukebox($this);

		// get current game infos if server loaded a track yet ...
		if ($this->server->status == 100) {
			$this->console_text('[Aseco] Waiting for the server to start a challenge');
		} else {
			$this->beginRace(false);
		}
		// main loop ...
		while (true) {
			// sends calls to the server ...
			$this->executeCalls();
			if ( $this->client->error->code != 0 )
				{
				$this->console('XMLRPC Error [' . $this->client->error->code . '] - ' . $this->client->error->message);
				break;
				}

			// get callbacks from the server ...
			$this->executeCallbacks();
			if ( $this->client->error->code != 0 )
				{
				$this->console('XMLRPC Error [' . $this->client->error->code . '] - ' . $this->client->error->message);
				break;
				}

			$this->releaseEvent('onMainLoop', $nullvar);
			if ( $this->client->error->code != 0 )
				{
				$this->console('XMLRPC Error [' . $this->client->error->code . '] - ' . $this->client->error->message);
				break;
				}

			// make sure the script does not timeout ...
			set_time_limit($this->settings['script_timeout']);
			if (count($this->server->players->player_list) == 0)
				{
				mysql_query("select id from players where id=0;");
				sleep(15);
				}

		}

		// close the client connection ...
		$this->client->Terminate();
	}


	/**
	 * Authenticates Aseco at the server.
	 */
	function connect() {

		// only if logins are set ...
		if ($this->server->ip && $this->server->port && $this->server->login && $this->server->pass) {

			$this->console_text('[Aseco] Try to connect to server on {1}:{2}',
				$this->server->ip,
				$this->server->port);

			// connect to the server ...
			if (!$this->client->InitWithIp($this->server->ip, $this->server->port)) {
				trigger_error('[' . $this->client->getErrorCode() . '] ' . $this->client->getErrorMessage());
				return false;
			}

			$this->console_text('[Aseco] Authenticated with username \'{1}\' and password \'{2}\'',
				$this->server->login, $this->server->pass);

			// logon the server ...
			$this->client->addCall('Authenticate', array($this->server->login, $this->server->pass));

			// enable callback system ...
			$this->client->addCall('EnableCallbacks', array(true));

			// start query ...
			if (!$this->client->multiquery()){
				trigger_error('[' . $this->client->getErrorCode() . '] ' . $this->client->getErrorMessage());
				return false;
			}
			$this->client->getResponse();

			// connection established ...
			return true;
		} else {

			// connection failed ...
			return false;
		}
	}

	/**
	 * Initializes the player list.
	 * Reads a list of the players who are on the server already.
	 */
	function serverSync() {

		// get current players on the server ...
		$this->client->query('GetPlayerList', 100, 0);
		$response['playerlist'] = $this->client->getResponse();

		// get game version the server runs on ...
		$this->client->query('GetVersion');
		$response['version'] = $this->client->getResponse();

		$this->client->query('GetCurrentGameInfo');
		$response['gameinfo'] = $this->client->getResponse();

		$this->client->query('GetStatus');
		$response['status'] = $this->client->getResponse();

		// update player list ...
		if (!empty($response['playerlist'])) {
			foreach ($response['playerlist'] as $player) {
			 
				// PRELOAD
				// create player object of every player response ...
				$player_item = new Player($player);
				$player_item->mistral['displayPlayerInfo']=true;

				// add player object to player list ...
				$this->server->players->addPlayer($player_item);

				// GET ALL INFOS LATER - wtf no wins 
//				$this->addCall('GetPlayerInfo', array($player['Login']), '', 'initPlayer');
			 
				// NO RASPWAY
//				$this->playerConnect(array($player['Login'], ''));					// fake it into thinking it's a connecting player, it gets team & ladder info this way
			}
		}

		// get game ...
		$this->server->game = $response['version']['Name'];

		// get mode ...
		$this->server->gameinfo = new Gameinfo($response['gameinfo']);

		// get status ...
		$this->server->status = $response['getstatus']['Code'];

		// get trackdir
		$this->client->query('GetTracksDirectory');
		$this->server->trackdir = $this->client->getResponse();
		// throw new synchronisation event ...
		$this->releaseEvent('onSync', array());
	}


	/**
	 * Load settings and apply them on the current
	 * run.
	 */
	function loadSettings($config_file) {

		if ($settings = $this->xml_parser->parseXml($config_file)) {

			// read the xml structure into an array ...
			$aseco = $settings['SETTINGS']['ASECO'][0];

			// read settings and apply them ...
			$this->masterserver->login = $aseco['LOGIN'][0];
			$this->masterserver->pass = $aseco['PASSWORD'][0];
			$this->masterserver->ip = $aseco['SERVERIP'][0];
			$this->masterserver->port = $aseco['SERVERPORT'][0];
			$this->chat_colors = $aseco['COLORS'][0];
			$this->chat_messages = $aseco['MESSAGES'][0];
			$this->admin_list = $aseco['ADMINS'][0];

			// set script timeout ...
			$this->settings['script_timeout'] = $aseco['SCRIPT_TIMEOUT'][0];

			// display welcome message as window ?
			if (strtoupper($aseco['WELCOME_MSG_WINDOW'][0]) == 'TRUE') {
				$this->settings['welcome_msg_window'] = true;
			} else {
				$this->settings['welcome_msg_window'] = false;
			}

			// read the xml structure into an array ...
			$tmserver = $settings['SETTINGS']['TMSERVER'][0];

			// read settings and apply them ...
			$this->server->login = $tmserver['LOGIN'][0];
			$this->server->pass = $tmserver['PASSWORD'][0];
			$this->server->port = $tmserver['PORT'][0];
			$this->server->ip = $tmserver['IP'][0];

			// settings loaded ...
			return true;

		} else {

			// Could not parse XML file ...
			trigger_error('You\'ve got an XML error in you Aseco config file!');
			return false;
		}
	}

	/**
	 * Sends program header to console ...
	 * ... and ingame chat!
	 */
	function sendHeader() {
		$this->console_text('###############################################################################');
		$this->console_text('  Aseco v' . ASECO_VERSION . ' running on {1}:{2}', $this->server->ip, $this->server->port);
		$this->console_text('  Game  : {1} - {2}', $this->server->game, $this->server->gameinfo->getMode());
		$this->console_text('  Author: Florian Schnell');
		$this->console_text('###############################################################################');

		if ($this->welcome_msgs) {
			foreach ($this->welcome_msgs as $message) {
				$this->console_text('[' . $message['DATE'][0].'] ' . $message['TEXT'][0]);
			}
			$this->console_text('###############################################################################');
		}

		// format the text of the message ...
		$startup_msg = formatText($this->getChatMessage('STARTUP'),
			ASECO_VERSION,
			$this->server->ip,
			$this->server->port);

		// replace colors ...
		$startup_msg = $this->formatColors($startup_msg);

		// send the message ...
		$this->client->addCall('ChatSendServerMessage', array($startup_msg));
		$this->client->multiquery();
	}


	/**
	 * Gets callbacks from the TM Dedicated Server
	 * and reacts on them.
	 */
	function executeCallbacks() {

		// receive callbacks with a timeout of 1 second ...
		$this->client->readCB(2000);

		// now get the responses out of the 'buffer' ...
		$calls = $this->client->getCBResponses();

		if (!empty($calls)) {
			while ($call = array_shift($calls)) {
				switch ($call[0]) {
					case 'TrackMania.PlayerConnect':
						// event is released in the function!
						$this->playerConnect($call[1]);
						break;

					case 'TrackMania.PlayerDisconnect':
						// event is released in the function!
						$this->playerDisconnect($call[1]);
						break;

					case 'TrackMania.PlayerManialinkPageAnswer':
						$this->playerServerMessageAnswer($call[1]);
						break;

					case 'TrackMania.BillUpdated':
						$this->releaseEvent('onBillUpdated', $call[1]);
						break;

					case 'TrackMania.PlayerFinish':
						$this->playerFinish($call[1]);
						break;

					case 'TrackMania.PlayerChat':
						$this->playerChat($call[1]);
						$this->releaseEvent('onChat', $call[1]);
						break;

					case 'TrackMania.BeginRace':
						$this->beginRace($call[1]);
						break;

					case 'TrackMania.EndRace':
						$this->endRace($call[1]);
						break;

					case 'TrackMania.PlayerCheckpoint':
						$this->releaseEvent('onCheckpoint', $call[1]);
						break;

					case 'TrackMania.PlayerIncoherence':
						$this->releaseEvent('onPlayerIncoherence', $call[1]);
						break;

					case 'TrackMania.BeginRound':
						$this->beginRound();
						break;

					case 'TrackMania.StatusChanged':
//						$this->console_text($call[1][0]." - ".$call[1][1]);
						if ( $call[1][0] == 4 )		// Running - Play
							{
							$this->runningPlay();
							}
						if ( $call[1][0] == 3 )		// Synchronisation (always)
							{
							displayAllPlayerInfo($this, $this->server->challenge);
							}
						if ( $call[1][0] == 2 )		// Launching (new map)
							{
							;
							}
						if ( $call[1][0] == 5 )		// Finish (always)
							{
							;
							}
						break;

					default:
						// do nothing ...
				}
			}
			return $calls;
		} else {
			return false;
		}
	}


	/**
	 * Adds calls to a multiquery.
	 * It's possible to set a callback function which
	 * will be executed on incoming response.
	 * You can also set an ID to read response later on.
	 */
	function addCall($call, $params = array(), $id = 0, $callback_func = false) {
		// adds call and registers a callback if needed ...
		$index = $this->client->addCall($call, $params);
		$rpc_call = new RPCCall($id, $index, $callback_func, array($call, $params));
		$this->rpc_calls[] = $rpc_call;
	}


	/**
	 * Executes a multicall and gets responses.
	 * Saves responses in array with IDs as keys.
	 */
	function executeCalls() {

		// clear responses ...
		$this->rpc_responses = array();

		// stop if there are no rpc calls in query ...
		if (empty($this->client->calls)) {
			return true;
		}

		// sends multiquery to the server and gets the response ...
		if ($results = $this->client->multiquery()) {

			// get new response from server ...
			$responses = $this->client->getResponse();

			// handle server responses ...
			foreach ($this->rpc_calls as $call) {

				// display error message if needed ...
				$err = false;
				if (isset($responses[$call->index]['faultString'])) {
					$this->rpcErrorResponse($responses[$call->index]);
					print_r($call->call);
					$err = true;
				}

				// if an id was set, then save the response under the specified id ...
				if ($call->id) {
					$this->rpc_responses[$call->id] = $responses[$call->index][0];
				}

				// if a callback function has been set, then execute it ...
				if ($call->callback && !$err) {
					if (function_exists($call->callback)) {

						// callback the function with the response as parameter ...
						call_user_func($call->callback, $responses[$call->index][0]);

					// if a function with the name of the callback wasn't found, then
					// try to execute a method with it's name ...
					} elseif (method_exists($this, $call->callback)) {

						// callback the method with the response as parameter ...
						call_user_func(array($this, $call->callback), $responses[$call->index][0]);
					}
				}
			}
		}

		// clear calls ...
		$this->rpc_calls = array();
	}

	/**
	 * Documents RPC Errors.
	 */
	function rpcErrorResponse($response) {
		$this->console_text('[RPC Error '.$response['faultCode'].'] '.$response['faultString']);
	}


	/**
	 * Registers functions which are called on specific events!
	 */
	function registerEvent($event_type, $callback_func) {

		// registers a new event ...
		$this->events[$event_type][] = $callback_func;
	}


	/**
	 * Executes the functions which were registered for
	 * specified events.
	 */
	function releaseEvent($event_type, $func_param) {

		// executes registered event functions ...
		// if there are any events for that type ...
		if (count($this->events[$event_type])) {

			// for each registered function of this type ...
			foreach($this->events[$event_type] as $func_name) {

				// if function for the specified player connect event can be found ...
				if (function_exists($func_name)) {

					// ... execute it!
					call_user_func($func_name, $this, $func_param);
				}
			}
		}
	}


	/**
	 * Registers functions which are called on specific events!
	 */
	function addChatCommand($command_name, $command_help, $command_is_admin = false) {
		if ( $command_is_admin )
			{
			$this->chat_admin_cmd_count++;
			}
		$chat_command = new ChatCommand($command_name, $command_help, $command_is_admin);
		$this->chat_commands[] = $chat_command;
	}

	/**
	 * Registers new chat commands and
	 */
	function registerChatCommands() {
		if ($this->debug) {
			if (isset($this->chat_commands)) {
				foreach ($this->chat_commands as $command) {
					// display message if debug mode is set to true ...
					$this->console_text('register chat command: ' . $command->name);
				}
			}
		}
	}

	/**
	 * When a new round is started we have to get information
	 * about the new track and so on.
	 */
	function beginRound()
		{
		// request information about the new challenge ...
		// ... and callback to function newChallenge() ...

		}  //  beginRound

	/**
	 * When a new track is started we have to get information
	 * about the new track and so on.
	 */
	function runningPlay() {
		// request information about the new challenge ...
		// ... and callback to function newChallenge() ...
//		$this->addCall('GetCurrentChallengeInfo', array(), '', 'newChallenge');
	}



	/**
	 * When a new race is started we have to get information
	 * about the new track and so on.
	 */
	function beginRace($race) {
		// request information about the new challenge ...
		// ... and callback to function newChallenge() ...

		$this->console_text('Begin Race');

		if (!$race)
			{
//			$this->addCall('GetCurrentChallengeInfo', array(), '', 'newChallenge');
			$this->client->query('GetCurrentChallengeInfo');
			$challenge = $this->client->GetResponse();
			$this->newChallenge($challenge);
			}
		else
			{
			$this->newChallenge($race[0]);
			}
	}


	/**
	 * Reacts on new challenges.
	 * gets record to current challenge etc.
	 */
	function newChallenge(&$challenge) {
		// reset record list ...
		$this->server->records->clear();

		// reset player votings ...
		$this->server->players->resetVotings();

		// create new challenge object ...
		$challenge_item = new Challenge($challenge);

		// display console message ...
		$this->console('map changed [{1}] >> [{2}]',
			stripColors($this->server->challenge->name),
			stripColors($challenge_item->name));

		// update the field which contains current challenge ...
		$this->server->challenge = $challenge_item;

		// release a 'new challenge' event ...
		$this->releaseEvent('onNewChallenge', $challenge_item);
	}


	/**
	 * End of current race ...
	 * write records to database and/or display
	 * final statistics.
	 */
	function endRace($race) {
		$this->console_text('End Race');
		

		// get rankings and call endRaceRanking as soon as we have them ...
		// release new event ...
		$this->releaseEvent('onEndRace', $race);		// $race is actually 2 sets of data, rankings & challenge info
		$this->endRaceRanking($race[0]);
	}


	/**
	 * Check out who won the current run
	 * and increment his wins by one.
	 */
	function endRaceRanking($ranking) {
		if ($player = $this->server->players->getPlayer($ranking[0]['Login'])) {
			if ($ranking[0]['Rank'] == 1 && $ranking[0]['BestTime'] > 0 && count($ranking) > 1) {

				// increase the player's wins ...
				$player->newwins++;

				// display console message ...
				$this->console('{1} won for the {2}. time!', $player->login, $player->getWins());

				// replace parameters ...
				$message = formatText('{#server}>> Congratulations, you won your {#highlite}{1}{#server}. race!', $player->getWins());

				// replace colors ...
				$message = $this->formatColors($message);

				// send the message ...
				$this->client->addCall('ChatSendServerMessageToLogin', array($message, $player->login));
				$this->client->multiquery();

				// release a new event ...
				$this->releaseEvent('onPlayerWins', $player);
			}
		}
	}


// minimized version of newPlayer for skriptstart
	function initPlayer($player) {
		// creates player object ...
		$player_item = new Player($player);

		ldb_playerConnect($this, $player_item);

		// adds a new player to the intern player list ...
		$this->server->players->addPlayer($player_item);

		// display console message ...
		$this->console('<< player {1} online [{2}] {3} wins',
			$player_item->id,
			$player_item->login,
			$player_item->getWins());
	}

	/**
	 * Handles connections of new players.
	 */
	function playerConnect($player) {

		// request information about the new player ...
		// ... and callback to function newPlayer() ...
		$this->addCall('GetPlayerInfo', array($player[0]), 0, 'newPlayer');
	}


	/**
	 * Callback function of playerConnect().
	 * Receives information about the new player.
	 */
	function newPlayer($player) {

		// creates player object ...
		$player_item = new Player($player);
		if ($player_item->login == "")
			{
			//$this->console("New broken Player entered - ignored.");
			return;
			}
		
		if ($player_item->id == $this->lastloginid)
			{
			//$this->console("Dublicated join event - ignored.");
			return;
			}
		$this->lastloginid = $player_item->id;

		// adds a new player to the intern player list ...
		$this->server->players->addPlayer($player_item);

		// display console message ...
		$this->console('<< player {1} joined the game [{2}]',
			$player_item->id,
			$player_item->login);

		// send chat message to player ...
		// format message parameters ...
		$message = myWelcomeMessage($this, $player_item);

		// replace colors ...
		$message = $this->formatColors($message);

		// send the message ...
		$message = str_replace('{br}', "\n", $message);
		if ($this->settings['welcome_msg_window']) {
		 	if ($player_item->login)
				popup_msg($player_item->login, $message, 10000);
		} else {
			$this->client->addCall('ChatSendServerMessageToLogin', array(str_replace("\n\n", "\n", $message), $player_item->login));
			$this->client->multiquery();
		}

		// release a new player connect event ...
		$this->releaseEvent('onPlayerConnect', $player_item);
	}


	/**
	 * Handles disconnections of players.
	 */
	function playerDisconnect($player) {

		// delete player and put him into the player item ...
		$player_item = new Player();
		$player_item = $this->server->players->removePlayer($player[0]);

		// display message ...
		$this->console('>> player {1} left the game [{2}]',
		$player_item->id,
		$player_item->login);

		// release a new player disconnect event ...
		$this->releaseEvent('onPlayerDisconnect', $player_item);
	}


	/**
	 * Handles clicks on server messageboxes.
	 */
	function playerServerMessageAnswer($answer) {
		if ($answer[2]) {

			// throw new event ...
			$this->releaseEvent('onPlayerServerMessageAnswer', $answer);
		}
	}


	/**
	 * Player reaches finish.
	 */
	function playerFinish($finish) {
		// only if we have some information about the track ...
		if ($this->server->challenge->name == '') return;

		// build a record object with the current finish information ...
		$finish_item = new Record();
		$finish_item->score = $finish[2];
		$finish_item->challenge = $this->server->challenge;
		$finish_item->player = $this->server->players->getPlayer($finish[1]);		// $finish[1] is player logon
		$finish_item->date = strftime('%Y-%m-%d %H:%M:%S');

		// throw new event - player finished ...
		$this->releaseEvent('onPlayerFinish', $finish_item);
	}


	/**
	 * Receives chat messages and reacts on them.
	 * Reactions are done by the chat plugins.
	 */
	function playerChat($chat) {

		// check for chat command ...
		$command = $chat[2];
		if (substr($command, 0, 1) == '/') {

			// remove the '/' prefix ...
			$command = substr($command, 1);
			// split strings at spaces and add them into an array ...
			$params = explode(' ', $command, 2);

			$translated_name = str_replace('+', 'plus', $params[0]);
			$translated_name = str_replace('-', 'dash', $translated_name);

			// if the function and the command were registered ...
			if (function_exists('chat_' . $translated_name)) {

				// show message in console ...
				$this->console('{1} used chat command "{2} {3}"',
					$chat[1],
					$params[0],
					$params[1]);

				// save curricumstances in array ...
				$chat_command['author'] = $this->server->players->getPlayer($chat[1]);
				$chat_command['params'] = $params[1];

				// call the function which belongs to the command ...
				call_user_func('chat_' . $translated_name, $this, $chat_command);
			}
		}
	}


	/**
	 * Gets the specified chat messages out of the
	 * settings file.
	 */
	function getChatMessage($name) {
		return $this->chat_messages[$name][0];
	}


	/**
	 * Checks if the given player ID is in admin list.
	 */
	function isAdmin($login) {
		for ($i = 0; $i < count($this->admin_list['TMLOGIN']); $i++) {
			if ($this->admin_list['TMLOGIN'][$i] == $login) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Formats aseco color codes in a string,
	 * for example '{#server} hello' will end up as
	 * '$0ff hello'.
	 * It depends on what you've set in the config file.
	 */
	function formatColors ($text) {

		// replace colors ...
		for ($i = 0; $i < sizeof($this->chat_colors); $i++) {
			$key = key($this->chat_colors);
			$value = current($this->chat_colors);
			$text = str_replace('{#'.strtolower($key).'}', $value[0], $text);
			next($this->chat_colors);
		}

		// jump back to start of the array ...
		if (is_array($this->chat_colors)) {
			reset($this->chat_colors);
		}

		return $text;
	}


	/**
	 * Outputs a formatted string without datetime.
	 */
	function console_text() {
		$args = func_get_args();
		$message = call_user_func_array('formatText', $args) . CRLF;
		echo $message;
		//doLog($message);
		flush();
	}


	/**
	 * Outputs a string to console with datetime prefix.
	 */
	function console() {
		$args = func_get_args();
		$message = '['.date('m/d,H:i:s').'] '.call_user_func_array('formatText', $args) . CRLF;
		echo $message;
		//doLog($message);
		flush();
	}
}

// create an instance of Aseco and run it ...
$aseco = new Aseco(false, 'config.xml');
$aseco->run();

?>

