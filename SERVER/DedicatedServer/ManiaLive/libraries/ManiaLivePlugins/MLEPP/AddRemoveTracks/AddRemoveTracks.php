<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Add/Remove Tracks
 * @date 26-06-2011
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP team
 * @copyright 2010 - 2011
 *
 * ---------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will b e useful,
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

namespace ManiaLivePlugins\MLEPP\AddRemoveTracks;

use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Console;
use ManiaLive\PluginHandler\Dependency;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Event\Dispatcher;
use ManiaLive\Gui\Windowing\WindowHandler;
use ManiaLive\Gui\Windowing\Windows\Info;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLivePlugins\MLEPP\AddRemoveTracks\Events\onTrackAdded;
use ManiaLivePlugins\MLEPP\AddRemoveTracks\Events\onTrackRemoved;
use ManiaLivePlugins\MLEPP\AddRemoveTracks\Gui\Windows\AddLocalWindow;
use ManiaLivePlugins\MLEPP\AddRemoveTracks\Gui\Windows\RemoveWindow;

class AddRemoveTracks extends \ManiaLive\PluginHandler\Plugin {

	private $mlepp;
	public static $mxLocation = 'tm.mania-exchange.com';

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	 
	function onInit() {
		$this->setVersion(1050);
		$this->setPublicMethod('getVersion');
	}

	 /**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	 
	function onLoad() {
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: Add/Remove Tracks r' . $this->getVersion());
		$this->mlepp = Mlepp::getInstance();

		if ($this->isPluginLoaded('MLEPP\Admin', 251)) {
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'addLocalWin'), array("add", "track", "local"), true, false, false);
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'RemoveWindow'), array("remove", "track"), true, false, false);
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'addmx'), array("add", "track", "mx"), true, false, false);
		} else {
			Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] Disabled admin commands, Admin is not loaded, define admin plugin before this!');
		}
	}
    /**
	* onUnload()
	* Function called on unloading of the plugin
	*	
	*/
	
	function onUnLoad() {
		Console::println('[' . date('H:i:s') . '] [UNLOAD] Add/Remove Tracks r' . $this->getVersion() . '');
		if ($this->isPluginLoaded('MLEPP\Admin', 251)) {
			$this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'add', 'track', 'mx');   //remove full add mx command structure
			$this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'add', 'track', 'local');   //remove full add local command structure
			$this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'remove', 'track'); // remove full remove command structure
			Console::println('[' . date('H:i:s') . '] [UNLOAD] [AddRemoveTracks] Removed all dependend add/rmove commands from admin.');
		}
		parent::onUnload();
	}

	 /**
	 * addlocal()
	 * Function adding track in tracklist from local source.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	 
	function addlocal($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, 'addLocalTrack')) {
			$this->mlepp->sendChat($this->mlepp->AdminGroups->noPermissionMsg, $fromLogin);
			return;
		}
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$login = $admin->login;

		if (!is_string($param1)) {
			$this->mlepp->sendChat(' %adminerror%/admin add local takes a filename as a parameter.', $admin);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $admin->login . '] Missing parameter . . .');
			return;
		}

		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);

		$challengeDir = $dataDir . "Maps/";
		$mapExtensions = array("map.gbx", "map.Gbx", "Map.gbx", "Map.Gbx");


		$cpt = 0;
		$targetFile = false;
		while ($cpt < sizeof($mapExtensions) && $targetFile == false) {
			//echo $challengeDir . $param1 . "." . $mapExtensions[$cpt] . "\n";
			if (is_file($challengeDir . $param1 . "." . $mapExtensions[$cpt])) {
				$targetFile = $challengeDir . $param1 . "." . $mapExtensions[$cpt];
			}else
				$cpt++;
		}

		$isTmx = false;
		if ($targetFile !== false) {
			try {
				$this->connection->insertChallenge($targetFile);
				$this->mlepp->sendChat('%adminaction%Admin ' . $admin->nickName . '$z$s%adminaction% added new local track %variable%' . $param1);
				Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $admin->login . '] Added new local track :' . $param1);
				$eventTargetFile = $targetFile;
				Dispatcher::dispatch(new onTrackAdded($login, $eventTargetFile, $isTmx));
				$this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $fromLogin, NULL, NULL, true);
			} catch (\Exception $e) {
				$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			}
		} else {
			$this->mlepp->sendChat(' %adminerror%File %variable%' . $param1 . '.' . $mapExtensions[0] . ' %adminerror% at location %variable%' . $challengeDir . ' %adminerror%doesn\'t exist.', $admin);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $admin->login . '] Tried to add new local track :' . $param1 . ', but it doesn\'t exist.');
		}
	}

	 /**
	 * addmx()
	 * Handles the /addmx command.
	 *
	 * @param mixed $login
	 * @param string $mxid
	 * @return void
	 */
	 
	function addmx($login, $mxid = '') {
		$loginObj = $this->storage->getPlayerObject($login);

		if (!$this->mlepp->AdminGroup->hasPermission($login, 'addLocalTrack')) {
			$this->mlepp->sendChat($this->mlepp->AdminGroups->noPermissionMsg, $login);
			return;
		}

		if (!is_numeric($mxid)) {
			$this->mlepp->sendChat('%adminerror%You have entered a non-numeric value for mx track. All mx tracks are numerical.', $login);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $loginObj->login . '] Use of non-numeric value for TMX track.');
			return;
		}

		$trackinfo = $this->getDatas('http://' . self::$mxLocation . '/api/tracks/get_track_info/id/' . $mxid . '?format=json');
		if (is_int($trackinfo)) {
			$this->mlepp->sendChat('%adminerror%Adding track from MX failed with http error %variable%' . $trackinfo . '%adminerror%.', $login);
			return;
		} else {
			$trackinfo = json_decode($trackinfo);
		}

		if (!is_null($trackinfo)) {
			$trackdata = $this->getDatas('http://' . self::$mxLocation . '/tracks/download/' . $mxid);

			$dataDir = $this->connection->gameDataDirectory();
			$dataDir = str_replace('\\', '/', $dataDir);
			$challengeDir = $dataDir . "Maps/Downloaded/MX/";
			if (!is_dir($challengeDir)) {
				mkdir($challengeDir, 0777, true);
			}

			if (strlen($trackdata) >= 1024 * 1024) {
				$size = round(strlen($trackdata) / 1024);
				$this->mlepp->sendChat('%adminerror%The track you\'re trying to download is too large (' . $size . 'Kb > 1024 Kb).', $loginObj);
				Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Trackfile is too large (' . $size . 'Kb > 1024 Kb).');
				return;
			}

			$targetFile = $challengeDir . $this->filterName($trackinfo->Name) . '-' . $mxid . '.Map.Gbx';
			$eventTargetFile = "Maps/Downloaded/MX/" . $this->filterName($trackinfo->Name) . '-' . $mxid . '.Map.Gbx';

			if (file_put_contents($targetFile, $trackdata) === false) {
				$this->mlepp->sendChat('%adminerror% Couldn\'t write trackdata. Check directory & file permissions at dedicated tracks folder!', $loginObj);
				Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Trackdata couldn\'t been written. Check directory- and filepermissions!.');
				return;
			}

			$newChallenge = $this->connection->getChallengeInfo($targetFile);
			foreach ($this->storage->challenges as $chal) {
				if ($chal->uId == $newChallenge->uId) {
					$this->mlepp->sendChat('%adminerror%The track you tried to add is already in serverlist.', $loginObj);
					Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Track already in the tracklist.');
					return;
				}
			}
			try {
				$this->connection->insertChallenge($targetFile);
				$this->mlepp->sendChat('%adminaction%Admin ' . $loginObj->nickName . '$z$s%adminaction% added track %variable%' . $trackinfo->Name . '$z$s%adminaction% from $fffM$5DFX$0ae!');
				//Dispatcher::dispatch(new onTrackAdded($login,$eventTargetFile,$isTmx,$param2));
				$this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $login, NULL, NULL, true);

				Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Succesfully added track ' . $trackinfo->Name . '.');
			} catch (\Exception $e) {
				$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $login);
			}
		} else {
			// track unknown
			$this->mlepp->sendChat('%adminerror%The track you\'re trying to download doesn\'t exist.', $loginObj);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Unknown track.');
		}
	}
	
	function getDatas($url) {
		$options = array('http' => array(
				'user_agent' => 'manialive tmx-getter', // who am i
				'max_redirects' => 1, // stop after 10 redirects
				'timeout' => 3, // timeout on response
				));
		$context = stream_context_create($options);
		return @file_get_contents($url, null, $context);
	}

	 /**
	 * removethis()
	 * Function removes current track from tracklist.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	 
	function removethis($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, 'removeTrack')) {
			$this->mlepp->sendChat($this->mlepp->AdminGroups->noPermissionMsg, $fromLogin);
			return;
		}
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$login = $admin->login;
		$challenge = $this->connection->getCurrentChallengeInfo();
		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$file = $challenge->fileName;
		$challengeFile = $dataDir . "Maps/" . $file;

		$this->connection->removeChallenge($challengeFile);
		$this->mlepp->sendChat('%adminaction%Admin ' . $admin->nickName . '$z$s%adminaction% removed this track from playlist.');
		Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $admin->login . '] Removed current track from the tracklist.');
		Dispatcher::dispatch(new onTrackRemoved($login, $challengeFile));
		$this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $fromLogin, NULL, NULL, true);
	}

	 /**
	 * remove()
	 * Function removes track from the tracklist.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	 
	function remove($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, 'removeTrack')) {
			$this->mlepp->sendChat($this->mlepp->AdminGroups->noPermissionMsg, $fromLogin);
			return;
		}
		if ($param1 == 'this') {
			$this->removethis($fromLogin);
			return;
		}

		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$login = $admin->login;
		$data = false;

		$param1 = (int) $param1;
		if ($param1 == null || !\is_numerick($param1) || $param1 < 0) {

			$info = Info::Create($login);
			$info->setSize(100, 30);
			$info->setTitle('Wrong use of /admin remove #');
			$text = "You need to use a valid number";
			$info->setText($text);
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
			Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $admin->login . '] Wrong use of /admin remove (use valid number).');
		}

		if ($this->isPluginLoaded("MLEPP\Jukebox")) {
			$data = $this->callPublicMethod("MLEPP\Jukebox", "getJukeboxTrack", $login, $param1);
			if ($data != false) {
				$file = $data["challenge_file"];
				$name = $data["challenge_name"];
			}
		}

		if ($data == false) {
			$challenges = $this->connection->getChallengeList(-1, 0);
			$file = "";
			$name = "";
			foreach ($challenges as $key => $data) {
				if (($key + 1) == $param1) {
					$file = $data->fileName;
					$name = $data->name;
					break;
				}
			}
		}

		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$challengeFile = $dataDir . "Maps/" . $file;


		if (!is_file($challengeFile)) {
			$this->mlepp->sendChat('%adminerror%Target trackfile not found in filesystem. Check, that you have entered correct track id!', $admin);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $admin->login . '] Target trackfile not found in filesystem.');
			return;
		}
		$this->connection->removeChallenge($challengeFile);
		$this->mlepp->sendChat('%adminaction%Admin ' . $admin->nickName . '$z$s%adminaction% removed track %variable%' . $name . '$z$s%adminaction% from playlist.');
		Dispatcher::dispatch(new onTrackRemoved($login, $challengeFile));
		$this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $fromLogin, NULL, NULL, true);
	}

	 /**
	 * getData()
	 * Function called for getting TMX data.
	 *
	 * @param mixed $url
	 * @return mixed $content
	 */
	 
	function getData($url) {
		$options = array('http' => array(
				'user_agent' => 'manialive tmx-getter', // who am i
				'max_redirects' => 1, // stop after 10 redirects
				'timeout' => 3, // timeout on response
				));
		$context = stream_context_create($options);
		return @file_get_contents($url, null, $context);
	}

	 /**
	 * filterName()
	 * Function used to filter the tracks filename.
	 *
	 * @param mixed $text
	 * @return string $output
	 */
	 
	function filterName($text) {
		$str = trim(utf8_decode($text));
		$output = "";
		for ($i = 0; $i < strlen($str); $i++) {
			$c = ord($str[$i]);
			if ($c == 32) {
				$output .= "_";
				continue;
			} // space
			if ($c >= 48 && $c <= 57) {
				$output .= chr($c);
				continue;
			}// 0-9
			if ($c >= 65 && $c <= 90) {
				$output .= chr($c);
				continue;
			}// A-Z
			if ($c >= 97 && $c <= 122) {
				$output .= chr($c);
				continue;
			}// a-z
			$output .= "_";
		}
		return utf8_encode($output);
	}

	function RemoveWindow($login, $overrideDir = NULL) {

		if (!$this->mlepp->AdminGroup->hasPermission($login, 'removeTrack')) {
			$this->mlepp->sendChat($this->mlepp->AdminGroups->noPermissionMsg, $login);
			return;
		}

		if ($overrideDir == 'this') {
			$this->removethis($login);
			return;
		}
		if (\is_numeric($overrideDir) && (int)$overrideDir > 0) {
			$this->remove($login, $overrideDir);
			return;
		}


		$window = RemoveWindow::Create($login);
		$window->setSize(200, 110);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Filename', 0.6);
		$window->addColumn('Action', 0.2);

		// refresh records for this window ...
		$window->clearItems();


		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$challengeDir = $dataDir . "Maps/";

		foreach ($this->connection->getChallengeList(-1, 0) as $challenge) {
			$entry = array
				(
				'Filename' => array($challenge->name, NULL, false),
				'Action' => array("Remove", array(($challengeDir . "/" . $challenge->fileName), $challenge->name), false)
			);
			$window->addAdminItem($entry, array($this, 'onClicks'));
		}

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	function addLocalWin($login, $overrideDir = false) {
		if (!$this->mlepp->AdminGroup->hasPermission($login, 'addLocalTrack')) {
			$this->mlepp->sendChat($this->mlepp->AdminGroups->noPermissionMsg, $login);
			return;
		}
		$window = AddLocalWindow::Create($login);
		$window->setSize(200, 110);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Filename', 0.6);
		$window->addColumn('Action', 0.2);

		// refresh records for this window ...
		$window->clearItems();


		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$challengeDir = $dataDir . "Maps/";

		if ($overrideDir == false || empty($overrideDir)) {
			$overrideDir = $challengeDir;
		} else {
			if (!is_array($overrideDir) && $overrideDir !== false) {
				$this->addlocal($login, $overrideDir);
			} else {
				$overrideDir = $overrideDir[0];
			}
		}


		$localFiles = scandir($overrideDir);

		foreach ($localFiles as $file) {
			if (is_dir($overrideDir . $file)) {
				if ($file == "." || $file == "MatchSettings")
					continue;
				if ($file == "..") {
					$tempdir = explode('/', $overrideDir);
					$newDir = "";
					for ($x = 0; $x < count($tempdir) - 1; $x++) {
						$newDir .= $tempdir[$x] . "/";
					}
					$file = "";
					$label = "..";
				} else {
					$newDir = $overrideDir;
					$label = $file;
				}

				$entry = array
					(
					'Filename' => array(utf8_encode($label), array("changeDir", $newDir . $file), true),
					'Action' => array("", NULL, false)
				);
			} else {
				if (!stristr($file, ".map.gbx") && !stristr($file, ".challenge.gbx"))
					continue;

				$newDir = $overrideDir;
				$entry = array
					(
					'Filename' => array(utf8_encode($file), NULL, false),
					'Action' => array("Add", array(($newDir . "/" . $file), $file), false)
				);
			}
			$window->addAdminItem($entry, array($this, 'onClick'));
		}

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	function onClicks($login, $action, $target) {
		if ($action == "Remove") {
			try {
				$this->connection->removeChallenge($target[0]);
				$file = str_replace(".Map.Gbx", "", $target[1]);
				$admin = $this->storage->getPlayerObject($login);
				$this->mlepp->sendChat('%adminaction%Admin ' . $admin->nickName . '$z$s%adminaction% removed local track %variable%' . $file);
				Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $admin->login . '] removed local track :' . $file);
				$eventTargetFile = $target[0];
				Dispatcher::dispatch(new onTrackRemoved($login, $eventTargetFile, false));
				$this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $login, NULL, NULL, true);
			} catch (\Exception $e) {
				$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $login);
			}
		}
		if (is_array($target) && $target[0] == "changeDir") {
			$this->RemoveWindow($login, $target[1]);
		}
	}

	function onClick($login, $action, $target) {
		if ($action == "Add") {
			try {
				$this->connection->insertChallenge($target[0]);
				$file = str_replace(".Map.Gbx", "", $target[1]);
				$admin = $this->storage->getPlayerObject($login);
				$this->mlepp->sendChat('%adminaction%Admin ' . $admin->nickName . '$z$s%adminaction% added new local track %variable%' . $file);
				Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $admin->login . '] Added new local track :' . $file);
				$eventTargetFile = $target[0];
				Dispatcher::dispatch(new onTrackAdded($login, $eventTargetFile, false));
				$this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $login, NULL, NULL, true);
			} catch (\Exception $e) {
				$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $login);
			}
		}
		if (is_array($target) && $target[0] == "changeDir") {
			$this->addLocalWin($login, array($target[1]));
		}
	}
	
}

?>