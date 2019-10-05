<?php
/**
 * Chat plugin.
 * Displays help for public chat commands.
 */

include_once('includes/rasp.funcs.php');

//Aseco::registerEvent('onPlayerServerMessageAnswer', 'event_multi_message');		// RASP .3
Aseco::addChatCommand('admin', 'provides powerful admin commands. (see: /admin help)');
//Aseco::addChatCommand('setservername', 'changes the name of the server', true);
Aseco::addChatCommand('setmaxplayers', 'sets a new maximum of players', true);
Aseco::addChatCommand('next', 'forces server to load next map', true);
Aseco::addChatCommand('restart', 'restarts currently running map', true);
//Aseco::addChatCommand('ircrestart', 'restarts the IRC bot', true);						// RASP .3
Aseco::addChatCommand('clearjukebox', 'clears the jukebox', true);							// RASP .3
//Aseco::addChatCommand('remove', 'remove track from rotation', true);						// RASP .3
Aseco::addChatCommand('erase', 'remove track from rotation & delete track file', true);		// RASP .3
Aseco::addChatCommand('undo', 'remove last track added', true);		// RASP .3
Aseco::addChatCommand('add', 'adds a track from TMX, (usage: /admin add <tmx_id> {TMO/TMS/TMN/SM})', true);	// RASP .3
//Aseco::addChatCommand('writetracklist', 'save current track list to rasp_tracks.txt', true);// RASP .4
//Aseco::addChatCommand('debug', 'toggle debugging output', true);							// RASP 1.0
Aseco::addChatCommand('warn', 'sends a kick/ban warning to a player', true);				// RASP 1.1
Aseco::addChatCommand('kick', 'kicks a player from server', true);                          // RASP .2
Aseco::addChatCommand('ban', 'ban a player from server', true);								// RASP 1.1
Aseco::addChatCommand('black', 'blacklists a player from server', true);					// RASP 1.1
Aseco::addChatCommand('cancel', 'cancels a running vote', true);							// RASP 1.1
//Aseco::addChatCommand('match', 'followed by begin/end will start/stop match tracking', true); // RASP 1.3
//Aseco::addChatCommand('acdl', 'yes/no - AllowChallengeDownload setting on/off', true);		// RASP 1.4

$undoadd = false;

function chat_admin(&$aseco, &$command) {
	global $trackadmins, $con, $jukebox, $tmxdir, $trackkeep, $trackdontcare, $trackdelete, $tracknotmyenv, $undoadd;

	$admin = $command['author'];

	// check if chat command was used by an admin ...
	if (!$aseco->isAdmin($admin->login) && !in_array($admin->login, $trackadmins)) {

		// writes warning in console ...
		$aseco->console($admin->login . ' tried to use admin chat command (no permission!)');

		// sends chat message ...
		$aseco->addCall('ChatSendToLogin', array($aseco->formatColors('{#error}You have to be in admin list to do that!'), $admin->login));
		return false;
	}

	myChatAdmin($aseco, $command); 

	// split params into array
	$arglist = explode(' ', $command['params'], 2);
	$command['params'] = explode(' ', $command['params']);

	$cmdcount = count($arglist);

	if ( in_array($admin->login, $trackadmins) && $command['params'][0] != 'add' && $command['params'][0] != 'undo' && $undoadd == false)
		{			
		$aseco->addCall('ChatSendToLogin', array($aseco->formatColors('{#error}Sorry, you are trackadmin only.'), $admin->login));
		return false;
		}
	$undoadd = false;
	
	/**
	 * Display admin help.
	 */
	if ($command['params'][0] == 'help')
		{
		// show admin help
		display_help($admin, true);
		}
	/*
	 * Toggle debug on/off
	 */
	elseif ($command['params'][0] == 'debug') {
		$aseco->debug = !$aseco->debug;
		if ( $aseco->debug )
			{
			$msg = 'Debug enabled';
			}
		else
			{
			$msg = 'Debug disabled';
			}
		$aseco->addCall('ChatSendServerMessageToLogin',array($msg, $admin->login));

	/**
	 * Sets a new server name (on the fly).
	 */
	}elseif ($arglist[0] == 'setservername') {

		// tells the server to set a new servername ...
		$aseco->addCall('SetServerName', array($arglist[1]));

		// display console message ...
		$aseco->console('admin [{1}] set new server name [{2}]',
			$admin->login,
			$arglist[1]);

		// replace parameters ...
		$message = formatText('{#server}>> Admin set servername to {1}', $arglist[1]);

		// replace colors ...
		$message = $aseco->formatColors($message);

		// sends chat message ...
		$aseco->addCall('ChatSendServerMessage',array($message));

	/**
	 * Sets a new player maximum which is able to
	 * connect to the server.
	 */
	}elseif ($command['params'][0] == 'setmaxplayers' && is_numeric($command['params'][1])) {

		// tells server to set new player max ...
		$aseco->addCall('SetMaxPlayers', array((int) $command['params'][1]));

		// display console message ...
		$aseco->console('admin [{1}] set new player maximum [{2}]',
			$admin->login,
			$command['params'][1]);

		// replace parameters ...
		$message = formatText('{#server}>> Admin set new player maximum to {1}!', $command['params'][1]);

		// replace colors ...
		$message = $aseco->formatColors($message);

		// send chat message ...
		$aseco->addCall('ChatSendServerMessage', array($message));

	/**
	 * Forces the server to load next track.
	 */
	}elseif ($command['params'][0] == 'next') {
		$aseco->addCall('NextChallenge');
		$aseco->console('admin [' . $admin->login . '] skipped challenge!');

		// send chat message ...
		$aseco->addCall('ChatSendServerMessage', array($aseco->formatColors('{#server}>> Admin skipped challenge!')));

	/**
	 * Restarts the currently running map.
	 */
	}elseif ($command['params'][0] == 'restart') {

		// tells the server to restart the map ...
		$aseco->addCall('ChallengeRestart');

		// display console message ...
		$aseco->console('admin [{1}] restarted challenge!',
			$admin->login);

		// send chat message ...
		$aseco->addCall('ChatSendServerMessage', array($aseco->formatColors('{#server}>> Admin restarted challenge!')));

	/**
	 * Kicks a player with the specified login.
	 */
	}elseif ($command['params'][0] == 'kick' && $command['params'][1] != '') {

		// get player information ...
		$player = $aseco->server->players->getPlayer($command['params'][1]);

		if ( !isset($player) )
			{
			$message = '{$server}>> ' . $command['params'][1] . ' is not a valid player login. Use /players to find the correct login.';
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $admin->login));
			}
		else
			{

			// tell the server to kick the player ...
			$aseco->addCall('Kick', array($player->login));

			// display console message ...
			$aseco->console('admin [{1}] kicked player {2}!',
				$admin->login,
				stripColors($admin->nickname));

			// replace parameters ...
			$message = formatText('{#server}>> Admin kicked {1}!', $player->login);

			// replace colors ...
			$message = $aseco->formatColors($message);

			// send chat message ...
			$aseco->addCall('ChatSendServerMessage', array($message));
			}

		/**
		 * Ban a player with the specified login.
		 */
	}elseif ($command['params'][0] == 'ban' && $command['params'][1] != '') {
		// get player information ...
		$player = $aseco->server->players->getPlayer($command['params'][1]);

		if ( !isset($player) )
			{
			$message = '{$server}>> ' . $command['params'][1] . ' is not a valid player login. Use /players to find the correct login.';
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $admin->login));
			}
		else
			{
			// tell the server to blacklist the player and kick him ...
			$aseco->addCall('Ban', array($command['params'][1]));
			$aseco->addCall('Kick', array($command['params'][1]));

			// display console message ...
			$aseco->console('admin [{1}] banned player {2}!',
				$command['author']->id,
				stripColors($player->nickname));

			// replace parameters ...
			$message = formatText('{#server}>> Admin banned {1}!', $player->login);

			// replace colors ...
			$message = $aseco->formatColors($message);

			// send chat message ...
			$aseco->addCall(ChatSendServerMessage, array($message));
			}

		/**
		 * Blacklists a player with the specified login.
		 */
	}elseif ($command['params'][0] == 'black' && $command['params'][1] != '') {
		// get player information ...
		$player = $aseco->server->players->getPlayer($command['params'][1]);

		if ( !isset($player) )
			{
			$message = '{$server}>> ' . $command['params'][1] . ' is not a valid player login. Use /players to find the correct login.';
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $admin->login));
			}
		else
			{
			// tell the server to blacklist the player and kick him ...
			$aseco->addCall('BlackList', array($command['params'][1]));
			$aseco->addCall('Kick', array($command['params'][1]));

			// display console message ...
			$aseco->console('admin [{1}] blacklisted player {2}!',
				$command['author']->id,
				stripColors($player->nickname));

			// replace parameters ...
			$message = formatText('{#server}>> Admin blacklisted {1}!', $player->login);

			// replace colors ...
			$message = $aseco->formatColors($message);

			// send chat message ...
			$aseco->addCall(ChatSendServerMessage, array($message));
			}

		/**
		 * Cancels a vote.
		 */
	}elseif ($command['params'][0] == 'cancel') {
		$aseco->addCall('CancelVote');
		$aseco->console('admin [' . $command['author']->id . '] canceled vote!');

		// send chat message ...
		$aseco->addCall(ChatSendServerMessage, array($aseco->formatColors('{#server}>> Admin canceled vote!')));

		/**
		 * Warns a player with the specified login.
		 */
	}elseif ($command['params'][0] == 'warn' && $command['params'][1] != '') {
		// get player information ...
		$player = $aseco->server->players->getPlayer($command['params'][1]);

		if ( !isset($player) )
			{
			$message = '{$server}>> ' . $command['params'][1] . ' is not a valid player login. Use /players to find the correct login.';
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $admin->login));
			}
		else
			{
			$aseco->console('admin [' . $admin->login . '] warned ' . $player->login. '.');
			$aseco->addCall('ChatSendServerMessageToLogin', array('You warned ' . $player->login . '.', $admin->login));
			$cr = "\n";
			$message =	'$s$F00This is an administrative warning.' . $cr . $cr .
						'$z$sYou have done something against our server\'s policy. Not respecting other players, or using offensive language might result in a $F00kick, or ban $z$88F$sthe next time.' . $cr . $cr .
						'$z$sThe server administrators.';
			popup_msg($player->login, $message);

			// replace parameters ...
			$message = formatText('{#server}>> Admin warned {1}!', $player->login);

			// replace colors ...
			$message = $aseco->formatColors($message);

			// send chat message ...
			$aseco->addCall('ChatSendServerMessage', array($message));
			}
		}
	 /**
	  * Remove a track from the active rotation - doesn't update match settings unfortunately - command 'writetracklist' will though
	  */
	elseif (/*($command['params'][0] == 'remove') ||*/
			($command['params'][0] == 'erase') )
		{
		if(count($admin->tracklist) == 0)
			{
			$message = $aseco->formatColors('{#server}> {#emotic}Use \'/list\' first');
			$aseco->addCall('ChatSendServerMessageToLogin', array($message, $admin->login));
			return;
			}
		$tid = $command['params'][1];
		$tid--;
		if (array_key_exists($tid, $admin->tracklist)) 		// find track by given #
			{
			$filename = $aseco->server->trackdir . $admin->tracklist[$tid]['filename'];
			$set = $aseco->client->addCall('RemoveChallenge', array($filename));
			if (!$aseco->client->multiquery())
				{
				trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
				}
			else
				{
				// NEW - remember erased tracks in new table mistral_erasedtracks
				$eraseuid = $admin->tracklist[$tid]['uid'];
				$eraseid = getTrackIDfromUID($eraseuid);
				$query = "INSERT INTO mistral_erasedtracks (ChallengeUid, ChallengeId, ErasedAt, ErasedBy) VALUES (".quotedString(mysql_real_escape_string($eraseuid)).", $eraseid, NOW(), ".quotedString($admin->login).");";
				mysql_query($query);
				
				$msg = '{#server}>> Admin removed track: ' . stripcolors($admin->tracklist[$tid]['name']);
				if ( $command['params'][0] == 'erase' && is_file($filename) )
					{
					if ( unlink($filename) )
						{
						$msg = '{#server}>> Admin deleted track: ' . stripcolors($admin->tracklist[$tid]['name']);
						}
					else
						{
						$msg = $aseco->formatColors('{#server}>{#emotic} Delete file ' . $filename . ' failed');
						$aseco->addCall('ChatSendServerMessageToLogin', array($msg, $admin->login));
						$msg = '{#server}>> Admin remove track failed: ' . stripcolors($admin->tracklist[$tid]['name']);
						}
					}
				$aseco->addCall('ChatSendServerMessage', array($aseco->formatColors($msg)));
				$aseco->console_text('Admin [{1}] ' . $command['params'][0] . 'd track ' . $admin->tracklist[$tid]['name'], $admin->id);
				}
			}
		else
			{
			$message = $aseco->formatColors('{#server}>{#emotic} Track ID not found! Type {#highlite}/list {#emotic}to see all tracks.');
			$aseco->addCall('ChatSendServerMessageToLogin', array($message, $admin->login));
			return;
			}
		}

	 /**
	 * Clears the jukebox (for use with rasp jukebox plugin)
	 */
	elseif ($command['params'][0] == 'clearjukebox') {
		$jukebox = array();
		mistralSaveJukebox($aseco);
		$aseco->addCall('ChatSendServerMessage', array($aseco->formatColors('{#server}>> Admin cleared jukebox!')));

	}elseif ($command['params'][0] == 'writetracklist') {
		$filename = 'rasp-tracklist.txt';
		$aseco->addCall('SaveMatchSettings', array($filename));
		if (!$aseco->client->multiquery()) {
			trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
			return;
		} else {
			$msg = '{#server}>> Rasp-TrackList.txt written';
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($msg), $admin->login));
		}

	 /**
	  * Undo last /admin add
	  */
	}elseif ($command['params'][0] == 'undo') {
		if ( !isset($admin->mistral['lastadduid']) )
			{
			$aseco->addCall('ChatSendToLogin', array($aseco->formatColors('{#error}No Track added since last join.'), $admin->login));
			return false;			
			}
		$admin->tracklist[0]['uid'] = $admin->mistral['lastadduid'];
		unset($admin->mistral['lastadduid']);
		$admin->tracklist[0]['name'] = $admin->mistral['lastaddname'];
		$admin->tracklist[0]['filename'] = $admin->mistral['lastaddfilename'];
		$newcommand['params'] = 'erase 1';
		$newcommand['author'] = $admin;
		$undoadd = true;
		return chat_admin($aseco, $newcommand);

	 /**
	  * Add a TMX track to the track rotation
	  */
	}elseif ($command['params'][0] == 'add') {
		$tracktypes = array();
		$tracktypes['TMO'] = array(0 => 7458, 'original');
		$tracktypes['TMS'] = array(0 => 7455, 'sunrise');
		$tracktypes['TMN'] = array(0 => 7456, 'nations');
		$tracktypes['TMU'] = array(0 => 7459, 'united');
		$tracktypes['TMF'] = array(0 => 7457, 'tmnforever');
		$tracktypes['SM'] = "sharemania";
		$tmxid = $command['params'][1];
		if (is_numeric($tmxid) && $tmxid >= 0) {
			$tmxid = ltrim($tmxid, '0');
			if ( count($command['params']) > 2 )
				{
				$tracktype = strtoupper($command['params'][2]);
				if ( !isset($tracktypes[$tracktype]) )
					{
					$message = $aseco->formatColors("{#server}>{#emotic} '" . $tracktype . "' is not valid, try again with TMO/TMS/TMN/TMU/TMF/SM.");
					$aseco->addcall('ChatSendServerMessageToLogin', array($message, $admin->login));
					return;
					}
				}
			else
				{
				$tracktype = 'TMU';
				}

			if ($tracktype == 'SM')
				$remotefile = "http://sharemania.eu/download.php?id=$tmxid";
			else
				$remotefile = 'http://' . $tracktypes[$tracktype][1] . '.tm-exchange.com/get.aspx?action=trackgbx&id=' . $tmxid;
				
			if (!$stream = fopen($remotefile, 'r')) {
				$message = $aseco->formatColors('{#server}>{#emotic} Track not found, or error downloading. Please check the TrackID.');
				$aseco->addCall('ChatSendServerMessageToLogin', array($message, $admin->login));
				return;
			} else {
				if (!$file = stream_get_contents($stream)) {
					$message = $aseco->formatColors('{#server}>{#emotic} Error Saving file - unable to get data');
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $admin->login));
					return;
				}
				$sepchar = substr($aseco->server->trackdir, -1, 1);
				$partialdir = $tmxdir . $sepchar . $tracktype . $tmxid . '.challenge.gbx';
				$localfile = $aseco->server->trackdir . $partialdir;
				if ( $aseco->debug )
					{
					$aseco->console_text('/add - tmxdir=' . $tmxdir);
					$aseco->console_text('/add - path + filename=' . $partialdir);
					$aseco->console_text('/add - aseco->server->trackdir = ' . $aseco->server->trackdir);
					}
				if (file_exists($localfile)) {
					if (!unlink($localfile)) {
						$message = $aseco->formatColors('{#server}>{#emotic} Error erasing old file ' . $localfile);
						$aseco->addCall('ChatSendServerMessageToLogin', array($message, $admin->login));
						return;
					}
				}
				if (!$lfile = fopen($localfile, 'w+')) {
					$message = $aseco->formatColors('{#server}>{#emotic} Error saving file - unable to create ' . $localfile);
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $admin->login));
					return;
				}
				if (!fwrite($lfile, $file)) {
					$message = $aseco->formatColors('{#server}>{#emotic} Error saving file - unable to write data.');
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $admin->login));
					return;
				}
				fclose($lfile);		// can't do filesize while it's open, might have also caused problems with tmserver giving back info
				if (filesize($localfile) == $tracktypes[$tracktype][0]) {
					$message = $aseco->formatColors('{#server}>{#emotic} No such track on TMX');
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $admin->login));
					unlink($localfile);
					return;
				}
				$newtrk = getChallengeData($localfile, True);		// 2nd parm is whether or not to get players & votes required
				if ( $newtrk['votes'] == 500 && $newtrk['name'] == 'Not a GBX file')
					{
					$message = $aseco->formatColors('{#server}>{#emotic} No such track on TMX');
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $admin->login));
					unlink($localfile);
					return;
					}
				getAllChallenges($admin, '*', '*');		// populate players tracklist with all current tracks
				foreach($admin->tracklist as $key)
					{
					if ( $key['uid'] == $newtrk['uid'] )
						{
						$msg = '{#server}>{#highlite} File already in track list, not added';
						$message = $aseco->formatColors($msg);
						$aseco->addCall('ChatSendServerMessageToLogin', array($message, $admin->login));
						unlink($localfile);
						return;
						}
					}
				if ( getenv('windir') != '' )
					{
					$partialdir = str_replace('/', '\\', $partialdir);
					}
				$resp = $aseco->client->addCall('AddChallenge', array($partialdir));
				if (!$aseco->client->multiquery()) {
					trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
					return;
				} else {
					$track = $aseco->client->addCall('GetChallengeInfo', array($partialdir));
					if (!$aseco->client->multiquery()) {
						trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
						return;
					// SUCCESSFULLY ADDED
					} else {
						$response = $aseco->client->getResponse();
						$challenge = new Challenge($response[$track][0]);
						$msg = '{#server}>> Admin added track: ' . $challenge->name . '$z$s{#server} ('. $challenge->environment .')';
						$aseco->addCall('ChatSendServerMessage', array($aseco->formatColors($msg)));

						// Store Data for /admin undo
						$admin->mistral['lastadduid'] = $challenge->uid;
						$admin->mistral['lastaddname'] = $challenge->name;
						$admin->mistral['lastaddfilename'] = $partialdir;

						// Check if track got previously deleted
						$id = 0;
						$query = "SELECT ChallengeId, ErasedAt, ErasedBy from mistral_erasedtracks WHERE ChallengeUid=".quotedString(mysql_real_escape_string($challenge->uid)).";";
						$result = mysql_query($query);
						if ($result)
							{
							if (mysql_num_rows($result) > 0)
								{
								$row = mysql_fetch_row($result);
								$id = $row[0];
								$erasedat = $row[1];
								$erasedby = getNicknameFromLogin($row[2]);
								mysql_free_result($result);
								
								$msg = '> '.$challenge->name.'$z$s$FFF was deleted by '.$erasedby.'$z$s$FFF at '.$erasedat.'. Evaluation: $0F0'.getEvalCount($id, $trackkeep).'$FFF/$FF0'.getEvalCount($id, $trackdontcare).'$FFF/$F00'.getEvalCount($id, $trackdelete).'$FFF/$00F'.getEvalCount($id, $tracknotmyenv);
								$aseco->addCall('ChatSendServerMessageToLogin', array($msg, $admin->login));

								$query = "DELETE FROM mistral_erasedtracks where ChallengeId=$id";
								mysql_query($query);
								}
							}
						
						// New track
						if ($id == 0)
							{
						    $query = 'INSERT INTO challenges (Uid, Name, Author, Environment, TMXType, TMXId) VALUES (' . quotedString(mysql_real_escape_string($challenge->uid)) . ', ' . quotedString(stripColors($challenge->name)) . ', ' . quotedString($challenge->author) . ', ' . quotedString($challenge->environment) . ", '$tracktype', $tmxid)";
							if (!mysql_query($query))
								{
								echo "Query failed: $query";
								}
							}
						// Previous deleted track -> insert with "old" Id
						else
							{
						    $query = 'INSERT INTO challenges (Id, Uid, Name, Author, Environment, TMXType, TMXId) VALUES (' . $id . ', ' . quotedString(mysql_real_escape_string($challenge->uid)) . ', ' . quotedString(stripColors($challenge->name)) . ', ' . quotedString($challenge->author) . ', ' . quotedString($challenge->environment) . ", '$tracktype', $tmxid)";
							if (!mysql_query($query))
								{
								echo "Query failed: $query";
								}
							}
					}
				}
			}
		}
	}
	elseif ( $command['params'][0] == 'match' )
		{
		global $MatchSettings;
		if ( $command['params'][1] == 'begin' )
			{
			$msg = '{#server}>> Admin has started the match';
			$aseco->addCall('ChatSendServerMessage', array($aseco->formatColors($msg)));
			match_loadsettings();
			$MatchSettings['enable'] = true;
			}
		elseif ( $command['params'][1] == 'end' )
			{
			$MatchSettings['enable'] = false;
			$msg = '{#server}>> Admin has ended the match';
			$aseco->addCall('ChatSendServerMessage', array($aseco->formatColors($msg)));
			}
		else
			{
			$msg = '{#server}>> Match is currently ' . $MatchSettings['enable'] ? 'Running' : 'Stopped';
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($msg), $admin->login));
			}
		}
	elseif ( $command['params'][0] == 'acdl' )
		{
		$testparm = $command['params'][1];
		if ( $testparm == 'yes' || $testparm == 'no' )
			{
			$enabled = 0;
			if ( $testparm == 'yes' )
				{
				$enabled++;
				}
			$aseco->addCall('AllowChallengeDownload', array($enabled==1));
			$msg = '{#server}>> AllowChallengeDownload set to ' . ($enabled==1 ? 'Enabled' : 'Disabled');
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($msg), $admin->login));
			}
		else
			{
			$aseco->client->query('IsChallengeDownloadAllowed');
			$enabled = $aseco->client->getResponse();
			$msg = '{#server}>> AllowChallengeDownload is currently ' . ($enabled==1 ? 'Enabled' : 'Disabled');
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($msg), $admin->login));
			}
		}


}

?>
