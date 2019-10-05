<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name MusicBox
 * @date 26-06-2011
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP team
 * @copyright 2011
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

namespace ManiaLivePlugins\MLEPP\MusicBox;

use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Config\Loader;
use ManiaLive\DedicatedApi\Structures\Music;
use ManiaLive\Utilities\Time;
use ManiaLib\Utils\TMStrings as String;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\MLEPP\MusicBox\Gui\Windows\MusicBoxWindow;
use ManiaLivePlugins\MLEPP\MusicBox\Gui\Windows\SimpleWindow;
use ManiaLivePlugins\MLEPP\MusicBox\Gui\Windows\CurrentTrackWidget;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class MusicBox extends \ManiaLive\PluginHandler\Plugin {

	private $mlepp = NULL;
	private $config;
	private $musicBox = array();
	private $enabled = true;
	private $musicMusicBox = array();
	protected $adminLogin = NULL;
	private $currentMusic = array();
	private $lastPlayedMusicNumber = 0;
	private $helpMbox = "Open a window showing all songs that are queued in the MusicBox and waiting to get played.

\$wUsage\$z:
\$o/mbox\$z - Show all songs that are queued in the MusicBox.";
	private $helpMlist = "Open a window showing all songs on this server.

\$wUsage\$z:
\$o/mlist\$z - Show all songs on this server.
\$o/mlist \"text between hyphens\"\$z - Show all songs corresponding to the text. The taxt can be (part of) artist, (part of) title and (part of) genre.";
	private $descMbox = "Usage: /mbox";
	private $descMlist = "Usage: /mlist";

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	function onInit() {
		// this needs to be set in the init section
		$this->setVersion(1050);
		$this->mlepp = Mlepp::getInstance();
		$this->config = Config::getInstance();
		$this->setPublicMethod('getVersion');
		//Oliverde8 Menu
		if ($this->isPluginLoaded('oliverde8\HudMenu')) {
			Dispatcher::register(\ManiaLivePlugins\oliverde8\HudMenu\onOliverde8HudMenuReady::getClass(), $this);
		}
	}

	 /**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	function onLoad() {
		$this->enableDedicatedEvents();

		$command = $this->registerChatCommand("mlist", "mlist", 0, true);
		$command->help = $this->descMlist;
		$command = $this->registerChatCommand("mlist", "mlist", 1, true);
		$command->help = $this->descMlist;

		$command = $this->registerChatCommand("musicbox", "mbox", 0, true);
		$command->help = $this->descMbox;
		$command = $this->registerChatCommand("musicbox", "mbox", 1, true);
		$command->help = $this->descMbox;

		$command = $this->registerChatCommand("mbox", "mbox", 0, true);
		$command->help = $this->descMbox;
		$command = $this->registerChatCommand("mbox", "mbox", 1, true);
		$command->help = $this->descMbox;
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: MusicBox r' . $this->getVersion());
	}

	function onUnload() {
		CurrentTrackWidget::EraseAll();
		$this->connection->setForcedMusic(false, "");
		parent::onUnload();
	}

	function getMusicCsv() {
		$filename = $this->config->Url . "/index.csv";
		if (empty($filename))
			throw new \Exception("File not set.");

		$indexfile = @file_get_contents($filename, null);
		if ($indexfile === false)
			throw new \Exception("File is not found. $filename");

		$indexfile = explode("\n", $indexfile);
		if (count($indexfile) < 1)
			throw new \Exception("File empty.");
		$csvData = Array();
		foreach ($indexfile as $data) {
			if (!empty($data))
				$csvData[] = str_getcsv($data, ";", "", "");
		}

		if (count($csvData) > 1) {
			foreach ($csvData as $data)
				if (count($data) != 6) {
					throw new \Exception("Invalid file format.");
				}
		} else {
			throw new \Exception("No file data.");
		}

		return $csvData;
	}

	/*	 * "
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */

	function onReady() {
		try {
			$this->musicBox = $this->getMusicCsv();
			$this->enabled = true;
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%server%MusicBox $fff»» %error%' . utf8_encode($e->getMessage()));
			Console::println("[MUSICBOX] error: ".utf8_encode($e->getMessage()));
			$this->enabled = false;
		}

		foreach ($this->storage->players as $login => $player) {
			$this->showWidget($login);
		}
		foreach ($this->storage->spectators as $login => $player) {
			$this->showWidget($login);
		}
	}

	/*	 * "
	 * onOliverde8HudMenuReady()
	 * Function used for adding buttons to Olivers Hud Menu.
	 *
	 * @param mixed $menu
	 * @return void
	 */

	public function onOliverde8HudMenuReady($menu) {
		$parent = $menu->findButton(array("Menu", "Basic Commands"));

		if (!$parent) {
			$button["style"] = "Icons64x64_1";
			$button["substyle"] = "TrackInfo";

			$parent = $menu->addButton("Menu", "Basic Commands", $button);
		}

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "Sound";
		$parent = $menu->addButton($parent, "Force Music", $button);

		unset($button["style"]);
		unset($button["substyle"]);

		$button["plugin"] = $this;
		$button["function"] = "mlist";
		$menu->addButton($parent, "Music List", $button);

		$button["function"] = "mbox";
		$menu->addButton($parent, "Music box", $button);
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
	function onBeginChallenge($challenge, $warmUp, $matchContinuation) {
		foreach ($this->storage->players as $login => $player) {
			$this->showWidget($login);
		}
		foreach ($this->storage->spectators as $login => $player) {
			$this->showWidget($login);
		}
	}

	 /**
	 * showWidget()
	 * Helper function, shows the widget.
	 *
	 * @param mixed $login
	 * @param mixed $music
	 * @return void
	 */
	function showWidget($login) {
		$music = $this->connection->getForcedMusic();

		$artist = '$000unknown artist';
		$song = '$000unknown title';

		if (!empty($music->url)) {
			foreach ($this->musicBox as $id => $data) {

				$folder = urlencode($this->musicBox[$id][4]);
				$folder = str_replace("%2F", "/", $folder);

				$Url = $this->config->Url . $folder . rawurlencode($data[3]);
				if ($Url == $music->url) {
					$track = $id;
					$artist = '$000' . $data[1];
					$song = '$000' . $data[0];
					break;
				}
			}
		}

		$window = CurrentTrackWidget::Create($login);
		$window->setSize(50, 10);
		$window->callback = array($this, 'mlist');
		$window->setArtist($artist);
		$window->setSong($song);
		$pos = explode(",", $this->config->widgetPosition);

		$window->setPosition($pos[0], $pos[1]);
		$window->setValign("center");
		$window->show();
	}

	 /**
	 * onPlayerConnect()
	 * Function called when a player connects.
	 *
	 * @param mixed $login
	 * @param mixed $isSpectator
	 * @return void
	 */
	function onPlayerConnect($login, $isSpec) {
		$music = $this->connection->getForcedMusic();
		$this->showWidget($login, $music);
	}

	function onPlayerDisconnect($login) {
		CurrentTrackWidget::Erase($login);
	}

	 /**
	 * mbox()
	 * Function providing the /mbox command.
	 *
	 * @param mixed $login
	 * @param mixed $musicNumber
	 * @return
	 */
	function mbox($login, $musicNumber = null) {
		$player = $this->storage->getPlayerObject($login);
		if ($musicNumber == 'help') {  //no parameters --> show help
			//show help
			$this->showHelp($login, $this->helpMbox);
			return;
		}
		if ($musicNumber == 'list' || $musicNumber == 'display') {  // parametres redirect
			//$this->mjukeList($login);
			return;
		}
		if (!is_numeric($musicNumber)) {  // check for numeric value
			// show error
			$text = '%server%MusicBox $fff»» %error%Invalid songnumber!';
			$this->mlepp->sendChat($text, $player);
			return;
		}

		$musicNumber = (int) $musicNumber - 1; // do type conversion


		if (empty($this->musicBox)) {
			$text = '%server%MusicBox $fff»» %error%No songs at music MusicBox!';
			$this->mlepp->sendChat($text, $player);
			return;
		}

		if (array_key_exists($musicNumber, $this->musicBox)) {
			if (is_array($this->musicMusicBox) && array_key_exists($login, $this->musicMusicBox)) {
				$musics = $this->musicBox[$this->musicMusicBox[$login]['musicNumber']];
				$song = $musics[0] . " - " . $musics[1];
				$text = '%server%MusicBox $fff»» %music%Auto dropping %variable%' . $song . ' %music% from the MusicBox.';
				$this->mlepp->sendChat($text);


				$this->musicMusicBox[$login] = array('position' => $this->musicMusicBox[$login]['position'], 'url' => $this->musicBox[$musicNumber], 'musicNumber' => $musicNumber, 'nickName' => $player->nickName);
				$song = $this->musicBox[$musicNumber][0] . " - " . $this->musicBox[$musicNumber][1];
				$text = '%server%MusicBox $fff»» %variable%' . $song . ' $z$s%music% is added to the MusicBox by %variable%' . String::stripAllTmStyle($player->nickName) . '.';
				$this->mlepp->sendChat($text);
			} else {
				if (!is_array($this->musicMusicBox))
					$this->musicMusicBox = array();
				$this->musicMusicBox[$login] = array('position' => count($this->musicMusicBox), 'url' => $this->musicBox[$musicNumber], 'musicNumber' => $musicNumber, 'nickName' => $player->nickName);
				$song = $this->musicBox[$musicNumber][0] . " - " . $this->musicBox[$musicNumber][1];
				$text = '%server%MusicBox $fff»» %variable%' . $song . ' $z$s%music% is added to the MusicBox by %variable%' . String::stripAllTmStyle($player->nickName) . '.';
				$this->mlepp->sendChat($text);
			}
		}
		else {
			$text = '%server%MusicBox $fff»» %error%Number entered is not in music list';
			$this->mlepp->sendChat($text, $admin);
		}
	}

	 /**
	 * onEndChallenge()
	 * Function called on end of challenge.
	 *
	 * @param mixed $rankings
	 * @param mixed $challenge
	 * @param mixed $wasWarmUp
	 * @param mixed $matchContinuesOnNextChallenge
	 * @param mixed $restartChallenge
	 * @return void
	 */
	function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge) {
		// enabled ?
		if (!$this->enabled)
			return;

		// no playlist
		if (count($this->musicBox) < 1) {
			return;
		}

		if ($restartChallenge == true || $wasWarmUp == true)
			return;

		$MusicBox = array();  //assing a temp variable to hold the positions
		$Override = $this->config->Override;
		if ($Override == 'true')
			$Override = true; else
			$Override = false;

		if (!isset($this->musicMusicBox) || count($this->musicMusicBox) == 0) {

			if (count($this->musicBox) != 1) {
				$musicNumber = rand(0, (count($this->musicBox) - 1)); // use randon track from the list.
				if ($musicNumber == $this->lastPlayedMusicNumber) {
					$musicNumber = rand(0, (count($this->musicBox) - 1)); // use randon track from the list.
				}
			} else {
				$musicNumber = rand(0, (count($this->musicBox) - 1)); // use randon track from the list.
			}

			$this->lastPlayedMusicNumber = $musicNumber;

			$folder = urlencode($this->musicBox[$musicNumber][4]);
			$folder = str_replace("%2F", "/", $folder);
			$Url = $this->config->Url . $folder . rawurlencode($this->musicBox[$musicNumber][3]);
			print $Url;
			$this->connection->setForcedMusic($Override, $Url);
			$song = $this->musicBox[$musicNumber][0] . " - " . $this->musicBox[$musicNumber][1];
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Music] Enabling song: ' . $song);
			$this->currentPlaying = $Url;
			return;
		}

		foreach ($this->musicMusicBox as $login => $data) {
			$MusicBox[(int) $data['position']] = array('url' => $data['url'], 'login' => $login, 'musicNumber' => $data['musicNumber'], 'nickName' => $data['nickName']);
		}

		ksort($MusicBox);   // be sure the list is in right order
		$nextSong = $MusicBox[0];  //assign nextsong
		$newMusicBox = array();

		unset($MusicBox[0]);  // erase the current track from requested list

		foreach ($MusicBox as $data) {
			$newMusicBox[] = $data;
		}
		unset($data);
		//rebuild the MusicBox request list..
		$this->musicMusicBox = array();  //reset musicMusicBox;

		$pos = 0;
		foreach ($newMusicBox as $data) {
			$this->musicMusicBox[$data['login']] = array('url' => $data['url'], 'position' => $pos, 'musicNumber' => $data['musicNumber'], 'nickName' => $data['nickName']);
			$pos++;
		}

		$musicNumber = (int) $nextSong['musicNumber'];   // set new song
		$folder = urlencode($this->musicBox[$musicNumber][4]);
		$folder = str_replace("%2F", "/", $folder);
		$Url = $this->config->Url . $folder . rawurlencode($this->musicBox[$musicNumber][3]);
		$this->connection->setForcedMusic($Override, $Url);
		$song = $this->musicBox[$musicNumber][0] . " - " . $this->musicBox[$musicNumber][1];
		Console::println('[' . date('H:i:s') . '] [MLEPP] [Music] Enabling song: ' . $song);
		print $Url . "\n";
		$this->currentPlaying = $Url;
		$text = '%server%MusicBox $fff»»%music% Enabling song: %variable%' . $song . ' %music% requested by: %variable%' . String::stripAllTmStyle($nextSong['nickName']) . '.';
		$this->mlepp->sendChat($text);
		$this->lastPlayedMusicNumber = $musicNumber;
	}

	 /**
	 * mlist()
	 * Function providing the /mlist command.
	 * "
	 * @param mixed $fromLogin
	 * @param mixed $parameter
	 * @return
	 */
	function mlist($fromLogin, $parameter = NULL) {
		if ($parameter == "help") {
			$this->showHelp($fromLogin, $this->helpMlist);
			return;
		}

		$musiclist = $this->musicBox;

		if (count($musiclist) == 0) {
			$infoWindow = SimpleWindow::Create($fromLogin);
			$infoWindow->setTitle("Notice");
			$infoWindow->setText("The server doesn't have any music in the MusicBox. Ask kindly the server administrator to add some.");
			$infoWindow->setSize(40, 40);
			$infoWindow->centerOnScreen();
			$infoWindow->show();
			return;
		}

		if (!$this->enabled) {
			$infoWindow = SimpleWindow::Create($fromLogin);
			$infoWindow->setTitle("Notice");
			$infoWindow->setText("The music plugin is disabled.");
			$infoWindow->setSize(40, 40);
			$infoWindow->centerOnScreen();
			$infoWindow->show();
			return;
		}

		$window = MusicBoxWindow::Create($fromLogin);
		$window->setSize(210, 100);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Id', 0.1);
		$window->addColumn('Song', 0.6);
		$window->addColumn('Genre', 0.3);

		// refresh records for this window ...
		$window->clearItems();
		$id = 1;
		$entry = NULL;
		foreach ($musiclist as $data) {
			if (empty($parameter)) {
				$entry = array
					(
					'Id' => array($id, NULL, false),
					'Song' => array($data[1] . " - " . $data[0], $id, true),
					'Genre' => array($data[2], NULL, false)
				);
			} else {
				$pros = 0;

				$awords = explode(" ", $data[0]);
				$swords = explode(" ", $data[1]);
				$gwords = explode(" ", $data[2]);

				$search = array_merge($awords, $swords, $gwords);

				foreach ($search as $word) {
					similar_text($word, $parameter, $pros);
					if ($pros >= 60) {
						$entry = array
							(
							'Id' => array($id, NULL, false),
							'Song' => array($data[1] . " - " . $data[0], $id, true),
							'Genre' => array($data[3], NULL, false)
						);
						break;
					}
				}
			}
			$id++;
			if ($entry !== NULL)
				$window->addAdminItem($entry, array($this, 'onClick'));

			$entry = NULL;
		}
		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	 /**
	 * onClick()
	 * Function called on clicking.
	 *
	 * @param mixed $login
	 * @param mixed $name
	 * @param mixed $parameter
	 * @return void
	 */
	function onClick($login, $name, $parameter) {
		$this->mbox($login, ($parameter));
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