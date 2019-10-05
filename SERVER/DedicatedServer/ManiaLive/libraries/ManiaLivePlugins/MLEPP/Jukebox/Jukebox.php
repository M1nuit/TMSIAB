<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Jukebox
 * @date 02-07-2011
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP Team
 * @copyright 2010 - 2011
 *
 * ---------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * You are allowed to change things or use this in other projects, as
 * long as you leave the information at the top (name, date, version,
 * website, package, author, copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */

namespace ManiaLivePlugins\MLEPP\Jukebox;

use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Utilities\Time;
use ManiaLib\Utils\TMStrings as String;
use ManiaLive\Event\Dispatcher;

use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows\trackList;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\Controls;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows\AskConfirmation_jbDrop;

use ManiaLivePlugins\MLEPP\Jukebox\Structures\Columns;
use ManiaLivePlugins\MLEPP\Jukebox\Adapter\oliverde8HudMenu;
use ManiaLivePlugins\MLEPP\Jukebox\Handler\CooperBill\CooperBill as Handler_CooperBill;
use ManiaLivePlugins\MLEPP\Jukebox\Handler\CooperBill\Bill as Handler_CooperBillBill;

class Jukebox extends \ManiaLive\PluginHandler\Plugin {

	public static $Jukebox;
	// adminQueuerestart
	private $queueRestart = false;
	// helpText
	private $descList = "Usage: /list (\$i/list help\$i to see all options)";
	private $descJuke = "Usage: /jukebox <track ID>(\$i/jukebox help\$i to see all options)";
	private $helpList = "This function opens a window with all tracks on this server.
It is also possible to add criteria, to be able to find tracks with a specific attribute.

\$wUsage\$z:
\$o/list\$z - Show all tracks on this server.
\$o/list <name or part name>\$z - Show all tracks with a certain (part of a) name on this server.
\$o/list <criterium>\$z - Show all tracks on this server corresponding to the chosen criterium.
   Possible criteria are:
   nofinish, nofirst, authortime, noauthortime, goldtime, nogoldtime, silvertime, nosilvertime,
   bronzetime, nobronzetime.
\$o/list <criterium> <variable>\$z
   Possible criteria with their variables are:
   shorter (seconds), longer (seconds), rank (number), first (number),
   tmx_type (Race|Puzzle|Platform|Stunts|Shortcut),
   tmx_dif (Beginner|Intermediate|Expert|Lunatic),
   tmx_style (Normal|Stunt|Maze|Offroad|Laps|Fullspeed|Lol|Tech|Speedtech|RPG|Pressforward).";
	private $helpJuke = "This function lets you add a track to the jukebox, and see the tracks that are already in there.

\$wUsage\$z:
\$o/jukebox <ID>\$z - Add a track to the jukebox.
(Find track ID with the /list function, \$i/list help\$z for more info)
\$o/jukebox list\$z - See the tracks that are already queued in the jukebox.";
	//Settings
	public static $jbAdminOnly = "false";
	public static $set_jbAddPrice = array();
	//Different lists
	public static $list_default = array();
	public static $list_noFinish = array();
	public static $list_noAuthorTime = array();
	public static $list_noGoldTime = array();
	public static $list_noSilverTime = array();
	public static $list_noBronzeTime = array();
	public static $list_authorTime = array();
	public static $list_goldTime = array();
	public static $list_silverTime = array();
	public static $list_bronzeTime = array();
	public static $list_jb = array();

	//All the Messages
	public static $msg_jbNextChallange = null;
	public static $msg_jbInvalidNum = null;
	public static $msg_jbAlreadyInJb = null;
	public static $msg_jbadded = null;
	public static $msg_jbdroped = null;
	public static $msg_jbEmpty = null;
	public static $msg_jbYouAlreadyAdded = null;
	public static $msg_jbRecentylPlayed = null;
	public static $msg_jbAdminOnly = null;
	public static $BufferSize = 0;
	protected $JukeboxList = array();
	protected $jukeboxedList = array();
	protected $playedBuffer = array();
	protected $queueLogin = NULL;
	//Database
	private $useDatabase;
	private $allocatedWindows = array();  // $allocatedWindows[$login] = trackList::Create($login);
	//private $mlepp_db;
//  private $dbtype;
	public $mlepp;
	private $db_refreshTrackList;
	private $searchHandler;
	//Handlers
	private $handler_CooperBill;

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	public function onInit() {
		// this needs to be set in the init section
		$this->setVersion(1050);
		$this->setPublicMethod('getVersion');
		$this->setPublicMethod("getJukeboxTrack");
		$this->setPublicMethod('adminQueueRestart');
		$this->setPublicMethod('playerQueueRestart');
		
		//trackList::$plugin_jb = $this;
		Controls::$plugin_jb = $this;
		//Oliverde8 Menu
		if ($this->isPluginLoaded('oliverde8\HudMenu')) {
			Dispatcher::register(\ManiaLivePlugins\oliverde8\HudMenu\onOliverde8HudMenuReady::getClass(), $this);
		}
		//InÃ„Â±t of Handlers
		$this->handler_CooperBill = new Handler_CooperBill();
		$this->mlepp = Mlepp::getInstance();
	}

	 /**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	public function getInstance() {
		return $this;
	}
	public function onLoad() {
		$this->enableDedicatedEvents();
		$this->enableWindowingEvents();

		$command = $this->registerChatCommand("list", "trackList", 0, true);
		$command->help = $this->descList;
		$command = $this->registerChatCommand("list", "trackList", 1, true);
		$command->help = $this->descList;
		$command = $this->registerChatCommand("list", "trackList", 2, true);
		$command->help = $this->descList;


		if ($this->isPluginLoaded('MLEPP\Database')) {
			$this->useDatabase = true;
			$this->searchHandler = new DbSearch($this);
		} else {
			$this->searchHandler = new DirectSearch($this);
		}
		$command = $this->registerChatCommand("jukebox", "juke", 0, true);
		$command->help = $this->descJuke;
		$command = $this->registerChatCommand("jukebox", "juke", 1, true);
		$command->help = $this->descJuke;

		$this->loadSettings();
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: Jukebox v' . $this->getVersion() );
	}

	 /**
	 * loadSettings()
	 * Function used for loading the settings.
	 *
	 * @return void
	 */
	private function loadSettings() {

		if (self::$msg_jbNextChallange == null)
			self::$msg_jbNextChallange = '%server%Jukebox $fff»%jukebox% Next challenge will be %variable%%challenge_name% %jukebox%by %variable%%challenge_author%$z$s%jukebox% as requested by %variable%%player_nick%.';

		if (self::$msg_jbInvalidNum == null)
			self::$msg_jbInvalidNum = '%server%Jukebox $fff» %error%Invalid tracknumber!';

		if (self::$msg_jbAlreadyInJb == null)
			self::$msg_jbAlreadyInJb = '%server%Jukebox $fff» %error%Track %variable%%challenge_name%$z$s%error% is already in the Jukebox!';

		if (self::$msg_jbadded == null)
			self::$msg_jbadded = '%server%Jukebox $fff» %variable%%challenge_name% %jukebox%by %variable%%challenge_author%$z$s%jukebox% is added to the jukebox by %variable%%player_nick%.';

		if (self::$msg_jbdroped == null)
			self::$msg_jbdroped = '%server%Jukebox $fff» %variable%%challenge_name%$z$s%jukebox% has been dropped the Jukebox by Admin %variable%%player_nick%.';

		if (self::$msg_jbEmpty == null)
			self::$msg_jbEmpty = '%server%Jukebox $fff» $i%error%No tracks in jukebox!';

		if (self::$msg_jbYouAlreadyAdded == null) {
			self::$msg_jbYouAlreadyAdded = '%server%Jukebox $fff» $i%error% You already added tracks to JB. You have reached your limit';
		}

		if (self::$msg_jbRecentylPlayed == null) {
			self::$msg_jbRecentylPlayed = '%server%Jukebox $fff» $i%error% This track has been played recently. Plz wait before adding it again';
		}

		if (self::$msg_jbAdminOnly == null) {
			self::$msg_jbAdminOnly = '%server%Jukebox $fff» $i%error% On this server only Admins can juke tracks. Sorry...';
		}

		//Loading settings
		self::$jbAdminOnly = $this->stringToBool(self::$jbAdminOnly);

		if (empty(self::$set_jbAddPrice))
			self::$set_jbAddPrice = array(0);
		else {
			//Check for integers
			$i = 0;
			while (isset(self::$set_jbAddPrice[$i])) {
				if (!is_integer(self::$set_jbAddPrice[$i]))
					if (is_numeric(self::$set_jbAddPrice[$i]))
						self::$set_jbAddPrice[$i] = intval(self::$set_jbAddPrice[$i]);
					else
						self::$set_jbAddPrice[$i] = 0;
				$i++;
			}
		}

		if (!empty(self::$list_default)) {
			$columns = new Columns();
			$columns->generateFromSetting(self::$list_default);
			$this->searchHandler->setColumns("list_default", $columns);
		} else {
			$columns = new Columns();
			$columns->addColumn('challenge_id', 0.1, 'Id');
			$columns->addColumn('challenge_name', 0.4, 'Name');
			//$columns->addColumn('challenge_environment', 0.08, 'Env');
			$columns->addColumn('challenge_author', 0.25, 'Author');

			if ($this->isPluginLoaded('MLEPP\LocalRecords'))
				$columns->addColumn('player_rank', 0.12, 'My Rank');

			$columns->addColumn('challenge_mood', 0.18, 'Mood');
			$columns->addColumn('challenge_authorTime', 0.2, 'Author Time');
			$columns->addColumn('tmx_awards', 0.2, 'Awards');

			$this->searchHandler->setColumns("list_default", $columns);
		}

		if (!empty(self::$list_authorTime)) {
			$columns = new Columns();
			$columns->generateFromSetting(self::$list_authorTime);
			$this->searchHandler->setColumns("list_authortime", $columns);
		} else {
			$columns = new Columns();
			$columns->addColumn('challenge_id', 0.1, 'Id');
			$columns->addColumn('challenge_name', 0.4, 'Name');
			$columns->addColumn('challenge_environment', 0.08, 'Env');
			$columns->addColumn('challenge_author', 0.25, 'Author');

			if ($this->isPluginLoaded('MLEPP\LocalRecords'))
				$columns->addColumn('player_rank', 0.12, 'My Rank');

			$columns->addColumn('challenge_mood', 0.18, 'Mood');
			$columns->addColumn('challenge_authorTime', 0.2, 'Author Time');
			$columns->addColumn('tmx_awards', 0.2, 'Awards');

			$this->searchHandler->setColumns("list_authortime", $columns);
		}

		if (!empty(self::$list_goldTime)) {
			$columns = new Columns();
			$columns->generateFromSetting(self::$list_goldTime);
			$this->searchHandler->setColumns("list_goldtime", $columns);
		} else {
			$columns = new Columns();
			$columns->addColumn('challenge_id', 0.1, 'Id');
			$columns->addColumn('challenge_name', 0.4, 'Name');
			$columns->addColumn('challenge_environment', 0.08, 'Env');
			$columns->addColumn('challenge_author', 0.25, 'Author');

			if ($this->isPluginLoaded('MLEPP\LocalRecords'))
				$columns->addColumn('player_rank', 0.12, 'My Rank');

			$columns->addColumn('challenge_mood', 0.18, 'Mood');
			$columns->addColumn('challenge_goldTime', 0.2, 'Gold Time');
			$columns->addColumn('tmx_awards', 0.2, 'Awards');

			$this->searchHandler->setColumns("list_goldtime", $columns);
		}

		if (!empty(self::$list_silverTime)) {
			$columns = new Columns();
			$columns->generateFromSetting(self::$list_silverTime);
			$this->searchHandler->setColumns("list_silvertime", $columns);
		} else {
			$columns = new Columns();
			$columns->addColumn('challenge_id', 0.1, 'Id');
			$columns->addColumn('challenge_name', 0.4, 'Name');
			$columns->addColumn('challenge_environment', 0.08, 'Env');
			$columns->addColumn('challenge_author', 0.25, 'Author');

			if ($this->isPluginLoaded('MLEPP\LocalRecords'))
				$columns->addColumn('player_rank', 0.12, 'My Rank');

			$columns->addColumn('challenge_mood', 0.18, 'Mood');
			$columns->addColumn('challenge_silverTime', 0.2, 'Silver Time');
			$columns->addColumn('tmx_awards', 0.2, 'Awards');

			$this->searchHandler->setColumns("list_silvertime", $columns);
		}

		if (!empty(self::$list_bronzeTime)) {
			$columns = new Columns();
			$columns->generateFromSetting(self::$list_bronzeTime);
			$this->searchHandler->setColumns("list_bronzetime", $columns);
		} else {
			$columns = new Columns();
			$columns->addColumn('challenge_id', 0.1, 'Id');
			$columns->addColumn('challenge_name', 0.4, 'Name');
			$columns->addColumn('challenge_environment', 0.08, 'Env');
			$columns->addColumn('challenge_author', 0.25, 'Author');

			if ($this->isPluginLoaded('MLEPP\LocalRecords'))
				$columns->addColumn('player_rank', 0.12, 'My Rank');

			$columns->addColumn('challenge_mood', 0.18, 'Mood');
			$columns->addColumn('challenge_bronzeTime', 0.2, 'Bronze Time');
			$columns->addColumn('tmx_awards', 0.2, 'Awards');

			$this->searchHandler->setColumns("list_bronzetime", $columns);
		}

		if (!empty(self::$list_noAuthorTime)) {
			$columns = new Columns();
			$columns->generateFromSetting(self::$list_noAuthorTime);
			$this->searchHandler->setColumns("list_noauthortime", $columns);
		} else {
			$columns = new Columns();
			$columns->addColumn('challenge_id', 0.1, 'Id');
			$columns->addColumn('challenge_name', 0.4, 'Name');
			$columns->addColumn('challenge_environment', 0.08, 'Env');
			$columns->addColumn('challenge_author', 0.25, 'Author');

			if ($this->isPluginLoaded('MLEPP\LocalRecords'))
				$columns->addColumn('player_rank', 0.12, 'My Rank');

			$columns->addColumn('challenge_mood', 0.18, 'Mood');
			$columns->addColumn('challenge_authorTime', 0.2, 'Author Time');
			$columns->addColumn('tmx_awards', 0.2, 'Awards');

			$this->searchHandler->setColumns("list_noauthortime", $columns);
		}

		if (!empty(self::$list_noGoldTime)) {
			$columns = new Columns();
			$columns->generateFromSetting(self::$list_noGoldTime);
			$this->searchHandler->setColumns("list_nogoldtime", $columns);
		} else {
			$columns = new Columns();
			$columns->addColumn('challenge_id', 0.1, 'Id');
			$columns->addColumn('challenge_name', 0.4, 'Name');
			$columns->addColumn('challenge_environment', 0.08, 'Env');
			$columns->addColumn('challenge_author', 0.25, 'Author');

			if ($this->isPluginLoaded('MLEPP\LocalRecords'))
				$columns->addColumn('player_rank', 0.12, 'My Rank');

			$columns->addColumn('challenge_mood', 0.18, 'Mood');
			$columns->addColumn('challenge_goldTime', 0.2, 'Gold Time');
			$columns->addColumn('tmx_awards', 0.2, 'Awards');

			$this->searchHandler->setColumns("list_nogoldtime", $columns);
		}

		if (!empty(self::$list_noSilverTime)) {
			$columns = new Columns();
			$columns->generateFromSetting(self::$list_noSilverTime);
			$this->searchHandler->setColumns("list_nosilvertime", $columns);
		} else {
			$columns = new Columns();
			$columns->addColumn('challenge_id', 0.1, 'Id');
			$columns->addColumn('challenge_name', 0.4, 'Name');
			$columns->addColumn('challenge_environment', 0.08, 'Env');
			$columns->addColumn('challenge_author', 0.25, 'Author');

			if ($this->isPluginLoaded('MLEPP\LocalRecords'))
				$columns->addColumn('player_rank', 0.12, 'My Rank');

			$columns->addColumn('challenge_mood', 0.18, 'Mood');
			$columns->addColumn('challenge_silverTime', 0.2, 'Silver Time');
			$columns->addColumn('tmx_awards', 0.2, 'Awards');

			$this->searchHandler->setColumns("list_nosilvertime", $columns);
		}

		if (!empty(self::$list_noBronzeTime)) {
			$columns = new Columns();
			$columns->generateFromSetting(self::$list_noBronzeTime);
			$this->searchHandler->setColumns("list_nobronzetime", $columns);
		} else {
			$columns = new Columns();
			$columns->addColumn('challenge_id', 0.1, 'Id');
			$columns->addColumn('challenge_name', 0.4, 'Name');
			$columns->addColumn('challenge_environment', 0.08, 'Env');
			$columns->addColumn('challenge_author', 0.25, 'Author');

			if ($this->isPluginLoaded('MLEPP\LocalRecords'))
				$columns->addColumn('player_rank', 0.12, 'My Rank');

			$columns->addColumn('challenge_mood', 0.18, 'Mood');
			$columns->addColumn('challenge_bronzeTime', 0.2, 'Bronze Time');
			$columns->addColumn('tmx_awards', 0.2, 'Awards');

			$this->searchHandler->setColumns("list_nobronzetime", $columns);
		}

		if (!empty(self::$list_jb)) {
			$columns = new Columns();
			$columns->generateFromSetting(self::$list_jb);
			$this->searchHandler->setColumns("jukebox", $columns);
		} else {
			$columns = new Columns();
			$columns->addColumn('challenge_name', 0.4, 'Name');
			$columns->addColumn('challenge_environment', 0.08, 'Env');
			$columns->addColumn('challenge_author', 0.25, 'Author');
			$columns->addColumn('challenge_mood', 0.1, 'Mood');
			$columns->addColumn('Requestedby', 0.3, 'Requested by');
			$columns->addColumn('jblist_remove', 0.08, 'D');

			$this->searchHandler->setColumns("jukebox", $columns);
		}

		$columns = new Columns();
		$columns->addColumn('Help_Command', 0.2, 'Command');
		$columns->addColumn('Help_Description', 0.8, 'Description');
		$this->searchHandler->setColumns("list_helper", $columns);
	}

	 /**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */
	public function onReady() {
		if ($this->useDatabase) {
			/*trackList::$mlepp_db = $this->mlepp->db;
			trackList::$dbtype = 'MySQL';
			DbSearch::$mlepp_db = $this->mlepp->db;
			*/

			//Lets look to the Server Challanges now
			if (!$this->mlepp->db->tableExists('serverchallengelist'))
				$this->db_createDatabaseChallengeList();

			$this->db_populateDatabaseChallengeList();
		}
	}

	 /**
	 * onOliverde8HudMenuReady()
	 * Function used for adding buttons to Olivers Hud Menu.
	 *
	 * @param mixed $menu
	 * @return void
	 */
	public function onOliverde8HudMenuReady($menu) {
		new oliverde8HudMenu($this, $menu);
	}

	     /**
     * stringToBool()
     * Sets string into boolean.
     *
     * @param string $string
     * @return bool $bool
     */
    private function stringToBool($string) {
        if(strtoupper($string) == "FALSE" || $string == "0" || strtoupper($string) == "NO" || empty($string))
            return false;
        return true;
    }

	 /**
	 * onBeginChallenge()
	 * Function called on begin of challenge.
	 *
	 * @param mixed $challenge
	 * @param mixed $warmUp
	 * @param mixed $matchContinuation
	 * @return void
	 */
	public function onBeginChallenge($challenge, $warmUp, $matchContinuation) {
		if ($this->useDatabase) {
			$this->db_checkNewTrack($challenge);
		}
		//Handling The track buffer
		$this->playedBuffer[] = array('challenge' => array('challenge_file' => $challenge['FileName'],
				'challenge_name' => $challenge['Name'],
				'challenge_uid' => $challenge['UId'],
				'challenge_author' => $challenge['Author'],
				'challenge_environnement' => $challenge['Environnement']));
		if (sizeof($this->playedBuffer) > self::$BufferSize) {
			array_shift($this->playedBuffer);
		}
	}

	 /**
	 * onEndChallenge()
	 * Function called on the end of challenge.
	 *
	 * @param mixed $rankings
	 * @param mixed $challenge
	 * @param mixed $wasWarmUp
	 * @param mixed $matchContinuesOnNextChallenge
	 * @param mixed $restartChallenge
	 * @return void
	 */
	public function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge) {
		if ($this->queueRestart == true) {
			$this->connection->restartChallenge();
			$this->queueRestart = false;
			if (!empty($this->queueLogin)) {
					$admin = Storage::GetInstance()->getPlayerObject($this->adminLogin);
					$this->mlepp->sendChat('%server%Jukebox $fff» %adminaction%Admin %variable%'.$admin->nickName.'$z$s%adminaction% restarted the challenge!');
					return;
				}
			else {
				$this->mlepp->sendChat('%server%Jukebox $fff» %jukebox% Challenge is restarted due callVotes');
					return;
				}
		}

		if ($restartChallenge == false) {
			$jblist = $this->JukeboxList;
			if (count($jblist) == 0) {
				return;
			}
			unset($this->JukeboxList);
			$this->JukeboxList = array();

			$nextmap = $jblist[0];

			$nextMapFile = $nextmap["challenge"]['challenge_file'];
			$nextChallenge = $this->connection->getChallengeInfo($nextMapFile);
			try {
			$this->connection->chooseNextChallenge($nextMapFile);
			} catch (\Exception $e) {
				$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $admin->login);
			}
			$this->console('Next challenge is ' . String::stripAllTmStyle($nextChallenge->name) . ' as requested by ' . $nextmap['request'] . '.');

			$text = self::$msg_jbNextChallange;
			$text = str_replace("%challenge_name%", String::stripAllTmStyle($nextChallenge->name), $text);
			$text = str_replace("%challenge_author%", String::stripAllTmStyle($nextChallenge->author), $text);
			$text = str_replace("%player_nick%", String::stripAllTmStyle($nextmap['request_nickName']), $text);


			$this->mlepp->sendChat($text);

			//Handling The track buffer
			$this->jukeboxedList[] = $jblist[0];
			if (sizeof($this->jukeboxedList) > self::$BufferSize) {
				array_shift($this->jukeboxedList);
			}

			unset($jblist[0]);
			$x = 1;
			foreach ($jblist as $list) {
				$this->JukeboxList[] = array('jbid' => $x,
					'challenge' => $list['challenge'],
					'request' => $list['request'],
					'request_login' => $list['request_login'],
					'request_nickName' => $list['request_nickName']);
				$x++;
			}
		}
	}

	public function onChallengeListModified($curChallengeIndex, $nextChallengeIndex, $isListModified){
		if($this->useDatabase && $isListModified)
			$this->db_populateDatabaseChallengeList();
	}

	 /**
	 * onPlayerDisconnect()
	 * Function called when a player disconnects.
	 *
	 * @param mixed $login
	 * @return void
	 */
	public function onPlayerDisconnect($login) {
		unset($this->allocatedWindows[$login]);
		$this->searchHandler->playerDisconnected($login);
	}

	 /**
	 * onBillUpdated()
	 * Function called when a bill is updated.
	 *
	 * @param mixed $billId
	 * @param mixed $state
	 * @param mixed $stateName
	 * @param mixed $transactionId
	 * @return void
	 */
	function onBillUpdated($billId, $state, $stateName, $transactionId) {
		$this->handler_CooperBill->doBill($billId, $state);
	}

	 /**
	 * juke()
	 * Function providing the /jukebox command.
	 *
	 * @param mixed $login
	 * @param mixed $tracknumber
	 * @return
	 */
	public function juke($login, $tracknumber = NULL) {
		if (\is_array($tracknumber)) {
			$tracknumber = $tracknumber[0];
		}

		if ($tracknumber == 'list' || $tracknumber == 'display') {
			$this->jukeList($login);
			return;
		}

		if ($tracknumber == NULL || $tracknumber == 'help' || !is_numeric($tracknumber)) {
			$this->showHelp($login, $this->helpJuke);
			return;
		}

		$dbhallenge = $this->searchHandler->getChallangeFromNum($tracknumber, $login);
		$source_player = Storage::GetInstance()->getPlayerObject($login);

		if ($dbhallenge) {
			foreach ($dbhallenge as $nam => $val) {
				$nam = str_replace("c.", "", $nam);
				$challenge[$nam] = $val;
			}
		}

		$nbPlayerC = 0;

		//Checking all the track list
		if (!$dbhallenge) {
			$text = self::$msg_jbInvalidNum;
			$this->mlepp->sendChat($text, $login);
		} elseif (!empty($this->JukeboxList)) {
			foreach ($this->JukeboxList as $track) {
				if ($track["challenge"]['challenge_file'] == $challenge["challenge_file"]) {
					$text = self::$msg_jbAlreadyInJb;
					$nextChallenge = $this->connection->getChallengeInfo($track["challenge"]['challenge_file']);
					$text = str_replace("%challenge_name%", String::stripAllTmStyle($nextChallenge->name), $text);
					$this->mlepp->sendChat($text, $source_player);
					return;
				} elseif ($track['request_login'] == $login) {
					$nbPlayerC++;
				}
			}
		}

		if (in_array($login,$this->mlepp->AdminGroup->getAdminsByPermission('admin'))) {
			$isAdmin = true;
		} else {
			$isAdmin = false;
		}

		if(self::$jbAdminOnly && !$isAdmin){
			$text = self::$msg_jbAdminOnly;
			$this->mlepp->sendChat($text, $source_player);
			return;
		}

		//Checking The Track BUffer if not Admin
		if(!$isAdmin){
			foreach ($this->playedBuffer as $jbb) {
				if ($jbb["challenge"]['challenge_file'] == $challenge["challenge_file"]) {
					$text = self::$msg_jbRecentylPlayed;
					$nextChallenge = $this->connection->getChallengeInfo($jbb["challenge"]['challenge_file']);
					$text = str_replace("%challenge_name%", String::stripAllTmStyle($nextChallenge->name), $text);
					$this->mlepp->sendChat($text, $source_player);
					return;
				}
			}
		}

		if (isset(self::$set_jbAddPrice[$nbPlayerC]) || $isAdmin) {
			if (!$isAdmin && self::$set_jbAddPrice[$nbPlayerC] > 0) {
				$storage = Storage::getInstance();
				$connection = Connection::getInstance();
				$toPlayer = new \ManiaLive\DedicatedApi\Structures\Player();
				$toPlayer->login = $storage->serverLogin;
				$fromPlayer = $storage->getPlayerObject($login);
				$billId = $connection->sendBill($fromPlayer, self::$set_jbAddPrice[$nbPlayerC], 'Adding Track to Jukebox', $toPlayer);

				$this->handler_CooperBill->addBill(new Handler_CooperBillBill($billId, "addToJukebox", $this, $challenge, $login));
			} else {
				$this->addToJukebox($login, $challenge);
			}
		} else {
			$text = self::$msg_jbYouAlreadyAdded;
			$this->mlepp->sendChat($text, $source_player);
			return;
		}
	}

	 /**
	 * addToJukebox()
	 * Function used for adding a track into the jukebox.
	 *
	 * @param mixed $login
	 * @param mixed $challenge
	 * @return void
	 */
	public function addToJukebox($login, $challenge) {

		$source_player = Storage::GetInstance()->getPlayerObject($login);

		$jbnum = count($this->JukeboxList);
		$this->JukeboxList[$jbnum] = array('jbid' => $jbnum,
			'challenge' => $challenge,
			'request' => $login,
			'request_login' => $login,
			'request_nickName' => $source_player->nickName);

		$text = self::$msg_jbadded;
		$text = str_replace("%challenge_name%", String::stripAllTmStyle($challenge["challenge_name"]), $text);
		$text = str_replace("%challenge_author%", String::stripAllTmStyle($challenge["challenge_author"]), $text);
		$text = str_replace("%player_nick%", String::stripAllTmStyle($source_player->nickName), $text);

		$this->mlepp->sendChat($text);
	}

	public function dropFromJukebox($login, $id, $check = false) {
		if ($this->mlepp->AdminGroup->hasPermission($login, 'jbdrop')) {

			$i = -1;
			foreach ($this->JukeboxList as $jbnum => $track) {
				if ($track['challenge']['challenge_id'] == $id) {
					$i = $jbnum;
					break;
				}
			}


			if ($i != -1) {

				if (!$check) {
					new AskConfirmation_jbDrop($this, $login, $id);
				} else {
					$removedChallange = $this->JukeboxList[$i];

					while ($i < (\sizeof($this->JukeboxList) - 1)) {
						$this->JukeboxList[$i] = $this->JukeboxList[$i + 1];
						$i++;
					}
					unset($this->JukeboxList[$i]);

					$source_player = $this->storage->getPlayerObject($login);

					$text = self::$msg_jbdroped;
					$text = str_replace("%challenge_name%", String::stripAllTmStyle($removedChallange['challenge']['challenge_name']), $text);
					$text = str_replace("%player_nick%", String::stripAllTmStyle($source_player->nickName), $text);
					$this->mlepp->sendChat($text);

					$wnd = trackList::Get($login);
					foreach ($wnd as $win) {
						if ($win->getRecipient() == $login) {
							$win->Redraw();
						}
					}
					if(!empty($this->JukeboxList));
						$this->jukeList($login);
				}
			} else {
				$text = self::$msg_jbInvalidNum;
				$this->mlepp->sendChat($text, $login);
			}
		} else {
			$this->mlepp->sendChat(Core::$adminPermissionError, $fromLogin);
		}
	}

	 /**
	 * trackList()
	 * Function providing the /list command.
	 *
	 * @param mixed $login
	 * @param mixed $param1
	 * @param mixed $param2
	 * @return
	 */
	public function trackList($login, $param1=null, $param2 = null) {
		if ($param1 == "help") {
			$this->showHelp($login, $this->helpList);
			return;
		}
		if (\is_array($param1)) {
			if (isset($param1[1])) {
				$param2 = $param1[1];
			}

			$param1 = $param1[0];
		}

		$this->searchHandler->tracklist($login, $param1, $param2);
	}

	 /**
	 * jukeList()
	 * Function showing the jukebox list.
	 *
	 * @param mixed $login
	 * @return
	 */
	public function jukeList($login) {
		if (empty($this->JukeboxList)) {
			$loginObj = Storage::GetInstance()->getPlayerObject($login);
			$this->mlepp->sendChat(self::$msg_jbEmpty, $loginObj);
			return;
		} else {
			$stor = Storage::getInstance();
			if (! isset($this->allocatedWindows[$login] )) {
				$this->allocatedWindows[$login] = trackList::Create($login);
			} else {
			}

			$this->allocatedWindows[$login]->setSize(210, 100);
			$this->allocatedWindows[$login]->setClicks_disabled(false);

			$this->allocatedWindows[$login]->clearRecords();
			$this->allocatedWindows[$login]->setColumns($this->searchHandler->getColumns("jukebox"));

			$this->allocatedWindows[$login]->setTitle("List of Jukebox Tracks");

			foreach ($this->JukeboxList as $key => $data) {

				$challege = $data["challenge"];
				$challege["Requestedby"] = $data["request"];

				$this->allocatedWindows[$login]->addRecord($challege);
			}

			$this->allocatedWindows[$login]->centerOnScreen();
			$this->allocatedWindows[$login]->show();
		}
	}

	 /**
	 * getJukeboxTrack()
	 * Helper function, gets information from challenge id.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @return
	 */
	public function getJukeboxTrack($login, $param) {
		return $this->searchHandler->getChallangeFromNum($param, $login);
	}

	 /**
	 * console()
	 * Helper function, addes MLEPP messages.
	 *
	 * @param mixed $text
	 * @return void
	 */
	private function console($text) {
		Console::println('[' . date('H:i:s') . '] [MLEPP] [Jukebox] ' . $text);
	}

	 /**
	 * db_createDatabaseChallengeList()
	 * Database function, ceating table serverchallengelist.
	 *
	 * @param string $name
	 * @return void
	 */
	private function db_createDatabaseChallengeList($name = "serverchallengelist") {

		$this->db_setDatabaseVersion($name, 1);

		$q->mysql = "CREATE TABLE `$name` (
                       `Challenge_id` INT( 11 ) NOT NULL ,
                       `server_login` VARCHAR( 100 ) NOT NULL ,
                       PRIMARY KEY (  `Challenge_id` ,  `server_login` )) ENGINE = MYISAM ;";

		$this->mlepp->db->query($q);
	}

	 /**
	 * db_checkNewTrack()
	 * Database function, checks the new track.
	 *
	 * @param mixed $challenge
	 * @return void
	 */
	private function db_checkNewTrack($challenge) {
		/* if(! $id = $this->db_getTrackId_from_Uid($challenge["UId"])){
		  $this->db_refreshTrackList = true;
		  return;
		  } */

		$this->db_populateDatabaseChallengeList();
	}


	 /**
	 * db_populateDatabaseChallengeList()
	 * Database function, updates the whole table.
	 *
	 * @return void
	 */
	private function db_populateDatabaseChallengeList() {

		$serverLogin = Storage::getInstance()->serverLogin;

		//First empty Tracks of this server
		$q = "DELETE FROM `serverchallengelist`
                        WHERE `server_login`='" . $serverLogin . "'";

		$this->mlepp->db->query($q);

		$serverChallenges = $this->storage->challenges;

		foreach ($serverChallenges as $challenge) {

			$this->db_addTrack($challenge);

		}
		$this->console('Added and updated ServerChallenges in the database.');
	}

	 /**
	 * Adds a track to this server's track list
	 *
	 * @param <type> $challenge The challenge you wont to add to this server
	 */
	private function db_addTrack($challenge) {

		//Get track Id from Database
		$id = $this->db_getTrackId_from_Uid($challenge->uId);

		//If couldn't get track ID
		if ($id == false) {
			//Add Track to Database using Database plugin
			$this->callPublicMethod("MLEPP\Database", "insertChallenge", $challenge, $login = 'n/a');

			//Get the ID know that the track was adeed
			$id = $this->db_getTrackId_from_Uid($challenge->uId);
		}

		//If we still can't find the ID we let go.
		if ($id) {

			$serverLogin = $this->storage->serverLogin;

			$q = "INSERT INTO `serverchallengelist`
                                VALUES(" . $id . ", " . $this->mlepp->db->quote($serverLogin) . ")";

			$this->mlepp->db->query($q);
		}
	}

	public function query($q) {
		return $this->mlepp->db->query($q);
	}

	 /**
	 * db_getTrackId_from_Uid()
	 * Database function, gets track id from uid.
	 *
	 * @param mixed $uid
	 * @return boolean $challengeid
	 */
	public function db_getTrackId_from_Uid($uid) {
		$q = "SELECT `challenge_id` FROM `challenges`
                        WHERE `challenge_uid`='" . $uid . "'";

		$response = $this->mlepp->db->query($q);

		if ($response->recordCount() > 0) {
			$response = $response->fetchAssoc();
			return $response["challenge_id"];
		} else {
			return false;
		}
	}

	 /**
	 * db_inTrackList()
	 * Database function, checks if challenge is in the database.
	 *
	 * @param mixed $id
	 * @return boolean $indatabase
	 */
	public function db_inTrackList($id) {
		$q = "SELECT `challenge_id` FROM `serverchallengelist`
                        WHERE `challenge_id`=" . $this->mlepp->db->quote($id) . "";

		$response = $this->mlepp->db->query($q);

		if ($response->recordCount() > 0) {
			return true;
		} else {
			return false;
		}
	}

	function adminQueueRestart($fromLogin, $plugin) {
		$this->adminLogin = $fromLogin;
		$this->queueRestart = true;
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$this->mlepp->sendChat('%server%Jukebox $fff» %adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets the challenge to start again after the race.');
		$this->console('[' . $fromLogin . '] Challenge to be replayed.');
	}

	function playerQueueRestart($fromLogin, $plugin) {
	        $this->queueLogin = "";
	        $this->queueRestart = true;
	        $admin = Storage::GetInstance()->getPlayerObject($fromLogin);
	        $this->mlepp->sendChat('%server%Jukebox $fff» %jukebox% Challenge will be restarted after podium.');
	        $this->console('Challenge to be replayed.');
	    }

	 /**
	 * db_getDatabaseVersion()
	 * Database function, gets table version.
	 *
	 * @param mixed $table
	 * @param mixed $fromPlugin
	 * @return boolean $dbversion
	 */
	function db_getDatabaseVersion($table, $fromPlugin = null) {
		$g = "SELECT * FROM `databaseversion` WHERE `database_table` = " . $this->mlepp->db->quote($table) . ";";
		$query = $this->mlepp->db->query($g);

		if ($query->recordCount() == 0) {
			return false;
		} else {
			$record = $query->fetchStdObject();
			return $record->database_version;
		}
	}

	 /**
	 * db_setDatabaseVersion()
	 * Database function, sets table version.
	 *
	 * @param mixed $table
	 * @param mixed $version
	 * @return void
	 */
	function db_setDatabaseVersion($table, $version) {
		$g = "SELECT * FROM `databaseversion` WHERE `database_table` = " . $this->mlepp->db->quote($table) . ";";
		$query = $this->mlepp->db->query($g);

		if ($query->recordCount() == 0) {
			$q = "INSERT INTO `databaseversion` (`database_table`,
                                                     `database_version`
                                                     ) VALUES (
                                                     " . $this->mlepp->db->quote($table) . ",
                                                     " . $this->mlepp->db->quote($version) . "
                                                     )";
			$this->mlepp->db->query($q);
		} else {
			$q = "UPDATE
            `databaseversion`
            SET
            `database_version` = " . $this->mlepp->db->quote($version) . "
            WHERE
            `database_table` = " . $this->mlepp->db->quote($table) . ";";

			$this->mlepp->db->query($q);
		}
		$this->console("set new database version: table $table -> version $version.");
	}

	 /**
	 * showHelp()
	 * Function used for showing the help window.
	 *
	 * @param mixed $login
	 * @param mixed $text
	 * @return void
	 */
	function showHelp($login, $text) {
		$this->callPublicMethod('MLEPP\Core', 'showHelp', $login, "help for plugin " . $this->getName(), $text);
	}
}

?>