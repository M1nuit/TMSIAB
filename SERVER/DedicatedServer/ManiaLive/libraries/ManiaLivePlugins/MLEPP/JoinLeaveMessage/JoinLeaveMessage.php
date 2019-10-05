<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Join/Leave Message
 * @date 04-01-2011
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

namespace ManiaLivePlugins\MLEPP\JoinLeaveMessage;

use ManiaLive\Utilities\Console;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Config\Loader;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class JoinLeaveMessage extends \ManiaLive\PluginHandler\Plugin {

	public $rankingsEnabled = false;
	protected $rank;
	private $mlepp;
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
		$this->mlepp = Mlepp::getInstance();
		Console::println('['.date('H:i:s').'] [MLEPP] Plugin: JoinLeaveMessage r'.$this->getVersion() );
	}

	function onUnload() {
		parent::onUnload();
	}
	 /**
	 * onReady()
     * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */

    function onReady() {
        if($this->isPluginLoaded('MLEPP\Rankings', 284)) {
            $this->rankingsEnabled = true;
        }
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
		$source_player = $this->storage->getPlayerObject($login);

		Console::println('['.date('H:i:s').'] [MLEPP] [JoinLeaveMessage] '.$login.' joins the server.');

		$message = $this->controlMsg(Config::getInstance()->standardJoinMsg, $source_player);
   		$admin_message = $this->controlMsg(Config::getInstance()->adminJoinMsg, $source_player);

		if($this->rankingsEnabled == true) {
			$rank = $this->callPublicMethod('MLEPP\Rankings', 'getRank', $login);
			if ($rank) {
				$message = $this->controlMsg(Config::getInstance()->rankedJoinMsg, $source_player);
				$admin_message = $this->controlMsg(Config::getInstance()->adminRankedJoinMsg, $source_player);
			}
		}


		if($this->mlepp->isPlayerOnline($login)) {
            // show join message to players
            foreach($this->storage->players as $login => $player) {
                if(in_array($login, $this->mlepp->AdminGroup->getAdmins() )){
                     $this->mlepp->sendChat($admin_message,$player);
                } else {
                    $this->mlepp->sendChat($message,$player);
                }
            }
        }

		// show join message also to spectators
		if($this->mlepp->isPlayerOnline($login)) {
            foreach($this->storage->spectators as $login => $player) {
                if(in_array($login, $this->mlepp->AdminGroup->getAdmins())){
                     $this->mlepp->sendChat($admin_message,$player);
                } else {
                     $this->mlepp->sendChat($message,$player);
                }
            }
        }
	}

	 /**
	 * onPlayerDisconnect()
     * Function called when a player disconnects.
	 *
	 * @param mixed $login
	 * @return void
	 */

	function onPlayerDisconnect($login) {
		Console::println('['.date('H:i:s').'] [MLEPP] [JoinLeaveMessage] '.$login.' left the server.');
        if($this->mlepp->isPlayerOnline($login)) {
            $source_player = $this->storage->getPlayerObject($login);
            $message = $this->controlMsg(Config::getInstance()->leaveMsg, $source_player);
		} else {
            $message = '%variable%»» %variable%'.$login.'$z$s%server% has left the server.';
		}
		$this->mlepp->sendChat($message);
	}

	 /**
	 * controlMsg()
     * Helper function, used for parsing the join/leave messages.
	 *
	 * @param mixed $msg
	 * @param mixed $player
	 * @return
	 */

	function controlMsg($msg, $player) {
		if(isset($player->path) && is_string($player->path)) $path = str_replace('World|', '', $player->path);
		else $path = "unknown";

		if(isset($player->ladderStats['PlayerRankings'][0]['Ranking'])) {
			$ladderrank = $player->ladderStats['PlayerRankings'][0]['Ranking'];
			if(empty($ladderrank) || $ladderrank == -1 || $ladderrank == false ) $ladderrank = "n/a";
		} else {
			$ladderrank = "n/a";
		}

		if($player->isSpectator) {
			$spec = ' $f00(Spec)';
		} else {
			$spec = '';
		}

        if(in_array($player->login,$this->mlepp->AdminGroup->getAdminsByGroup('root'))) {
            $title = $this->mlepp->AdminGroup->getTitle('root');
            //$title = "MasterAdmin";
        } elseif (in_array($player->login,$this->mlepp->AdminGroup->getAdminsByGroup('admin'))) {
            $title = $this->mlepp->AdminGroup->getTitle('admin');
			//$title = "Admin";
		} elseif (in_array($player->login,$this->mlepp->AdminGroup->getAdminsByGroup('operator'))) {
            $title = $this->mlepp->AdminGroup->getTitle('operator');
			//$title = "Operator";
		} else {
            $groups = $this->mlepp->AdminGroup->getAdminGroups($player->login);
            if(!empty($groups[0])) {
                $title = $this->mlepp->AdminGroup->getTitle($groups[0]);
            } else {
                $title = "Player";
            }
		}

		$message = $msg;
		$message = str_replace('%nickname%', $player->nickName, $message);
		$message = str_replace('%spec%', $spec, $message);
                $country = explode("|",$path);
                if (isset($country[0])) {
                    $message = str_replace('%country%', $country[0], $message);
                }
                else {
                    $message = str_replace('%country%', $path, $message);
                }
		$message = str_replace('%ladderrank%', number_format((int)$ladderrank, 0, '', ' '), $message);
		$message = str_replace('%login%', $player->login, $message);
		$ip = explode(":",$player->iPAddress);
		$message = str_replace('%ip%', $ip[0], $message);
		$message = str_replace('%version%', $player->clientVersion, $message);
		$message = str_replace('%title%', $title, $message);

        if($this->rankingsEnabled == true) {
            $rank = $this->callPublicMethod('MLEPP\Rankings', 'getRank', $player->login);
            if($rank) {
                $serverrank = '%variable%'.$rank['rank'].'/'.$rank['players'].' %server%(avg. %variable%'.$rank['avg'].'%server%)';
            } else {
                $serverrank = '%variable%n/a%server%';
            }
			$message = str_replace('%serverrank%', $serverrank, $message);
        }

		return $message;
	}

}
?>