<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name IdleKick
 * @date 09-06-2011
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP team
 * @copyright 2010 - 2011
 *
 * Plugin inspirated by the Mistral IdleKick plugin for XAseco.
 * Using code out of that plugin, thanks to Xymph and Mistral.
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

namespace ManiaLivePlugins\MLEPP\IdleKick;

use ManiaLive\Utilities\Console;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLive\Features\Admin\AdminGroup;

use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class IdleKick extends \ManiaLive\PluginHandler\Plugin {

	private $mlepp;
	private $idleList = array();
	private $config;
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
		$this->enableDedicatedEvents();
		$this->enableStorageEvents();
		$this->mlepp = Mlepp::getInstance();
		$this->config = Config::getInstance();

		Console::println('['.date('H:i:s').'] [MLEPP] Plugin: IdleKick r'.$this->getVersion() );

		foreach($this->storage->players as &$player) {
			$playerObject = $this->storage->getPlayerObject($player->login);
			$this->idleList[$playerObject->login] = 0;
		}
		foreach($this->storage->spectators as &$spectator) {
			$playerObject = $this->storage->getPlayerObject($spectator->login);
			$this->idleList[$playerObject->login] = 0;
		}

		$this->config->kickAdmins = ($this->config->kickAdmins == 'true' || $this->config->kickAdmins === true || $this->config->kickAdmins == 1) ? true : false;
	}


	 /**
	 * onUnload()
	 * Function called on Unload
	 *
	 * @return void
	 */

	function onUnload() {
		parent::onUnload();
	}

	 /**
	 * onPlayerConnect()
	 * Function called when a player connects.
	 *
	 * @param mixed $login
	 * @param mixed $isSpectator
	 * @return void
	 */

	function onPlayerConnect($login, $isSpectator) {
		$this->idleList[$login] = 0;
	}

	 /**
	 * onPlayerDisconnect()
	 * Function called when a player disconnects.
	 *
	 * @param mixed $login
	 * @return void
	 */

	function onPlayerDisconnect($login) {
		unset($this->idleList[$login]);
	}

	 /**
	 * onBeginRace()
	 * Function called on begin of the race.
	 *
	 * @param mixed $challengeInfo
	 * @return void
	 */

	function onBeginRace($challengeInfo) {
		foreach($this->idleList as &$idle) {
			$idle++;
		}
	}

	 /**
	 * onPlayerChat()
	 * Function called when someone is chatting.
	 *
	 * @param mixed $chat
	 * @return void
	 */

	function onPlayerChat($PlayerUid, $Login, $Text, $IsRegistredCmd) {
		$this->idleList[$Login] = 0;
	}

	 /**
	 * onPlayerCheckpoint()
	 * Function called when someone passes a checkpoint.
	 *
	 * @param mixed $playerUID
	 * @param mixed $playerLogin
	 * @param mixed $timeScore
	 * @param mixed $currentLap
	 * @param mixed $checkpointIndex
	 * @return void
	 */

	function onPlayerCheckpoint($playerUID, $playerLogin, $timeScore, $currentLap, $checkpointIndex) {
		$this->idleList[$playerLogin] = 0;
	}

	 /**
	 * onPlayerChangeSide()
	 * Function called on player changed side.
	 *
	 * @param mixed $rankings
	 * @param mixed $challenge
	 * @return void
	 */

  function onPlayerChangeSide($player, $oldSide) {
  	if ($oldSide == 'spectator') {
  		$this->idleList[$player->login] = 0;
  	}
  }

	 /**
	 * onEndRace()
	 * Function called on the end of the race.
	 *
	 * @param mixed $rankings
	 * @param mixed $challenge
	 * @return void
	 */

	function onEndRace($rankings, $challenge) {
    // look for forcing into spec or kick
    foreach($this->idleList as $login => $idle) {
		if ($this->mlepp->isPlayerOnline($login) == false) {
			unset($this->idleList[$login]);
			continue;
		}

		$playerObject = $this->storage->getPlayerObject($login);

			if($this->config->kickAdmins === false) {
				if($this->mlepp->AdminGroup->hasPermission($login,'admin')) {
					continue;
				}
			}

			if($idle == $this->config->specRounds) {
				// send chat message about kick
				$specmessage = $this->parseMessage($this->config->specMessagePublic, $playerObject);
				$this->mlepp->sendChat($specmessage);
				// send console message
				Console::println('['.date('H:i:s').'] [MLEPP] [IdleKick] Force '.$login.' into spectator mode after '.$this->config->specRounds.' rounds.');
				// force into spectator
				$this->connection->forceSpectator($playerObject, 1);
				// give possibility to switch back
				$this->connection->forceSpectator($playerObject, 0);
			}
			elseif($idle >= $this->config->kickRounds) {
				// send chat message about kick
				$kickmessage = $this->parseMessage($this->config->kickMessagePublic, $playerObject);
				$this->mlepp->sendChat($kickmessage);
				// send console message
				Console::println('['.date('H:i:s').'] [MLEPP] [IdleKick] Kicking '.$login.' after '.$this->config->kickRounds.' rounds.');
				// kick player
				$this->connection->kick($playerObject, $this->mlepp->parseColors($this->config->kickMessagePrivate));
				// remove from playerList
				unset($this->idleList[$login]);
			}
		}
	}

	 /**
	 * parseMessage()
	 * Helper function, used for parsing messages.
	 *
	 * @param mixed $message
	 * @param mixed $playerObject
	 * @return
	 */

	function parseMessage($message, $playerObject) {
		$msg = str_replace('%nickname%', $playerObject->nickName, $message);
		$msg = str_replace('%idleRounds%', $this->idleList[$playerObject->login], $msg);
		return $msg;
	}

}
?>