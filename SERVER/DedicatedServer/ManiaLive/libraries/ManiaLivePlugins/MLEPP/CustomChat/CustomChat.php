<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Custom Chat
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

namespace ManiaLivePlugins\MLEPP\CustomChat;

use ManiaLive\Utilities\Console;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLive\Features\Admin\AdminGroup;

class CustomChat extends \ManiaLive\PluginHandler\Plugin {

	private $mlepp;
	private $config;
	private $_aWordlist = null;
	private $_useMasking = false;
	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	function onInit() {
		// this needs to be set in the init section
		$this->setVersion(1050);
		$this->setPublicMethod('getVersion');
		$this->setPublicMethod('sendChat');
		$this->setPublicMethod('ProfanityFilter');
	}

	 /**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	function onLoad() {
		$this->enableDedicatedEvents();
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: CustomChat r' . $this->getVersion());

		try {
			$this->connection->chatEnableManualRouting(true);
		} catch (\Exception $e) {
			Console::println('[' . date('H:i:s') . '] [MLEPP] [CustomChat] Couldn\'t initialize custom chat.' . "\n" . ' Error from server: ' . $e->getMessage());
			die();
		}

		$this->mlepp = Mlepp::getInstance();
		$this->config = Config::getInstance();
	}
	function onReady() {
			if ($this->isPluginLoaded('MLEPP\ProfanityFilter')) {
			$this->_aWordlist = explode(",",\ManiaLivePlugins\MLEPP\ProfanityFilter\Config::getInstance()->wordlist);
			$this->_useMasking = true;
		}	
	}
	
	Function ProfanityFilter($status, $fromPlugin = null) {
		if ($status == true) {
		$this->_aWordlist = explode(",",\ManiaLivePlugins\MLEPP\ProfanityFilter\Config::getInstance()->wordlist);
		$this->_useMasking = true;
		}
		if ($status == false) {
			$this->_useMasking = false;
		}
	}
	 /** 
	 * onPlayerChat()
	 * Function called when someone is chatting.
	 *
	 * @param mixed $playerUid
	 * @param mixed $login
	 * @param mixed $chat
	 * @param mixed $isRegistredCmd
	 * @return
	 */
	function onPlayerChat($playerUid, $login, $text, $isRegistredCmd) {
		if ($playerUid != 0) {
			$this->sendChat($login,$text);
		}
	}
	function sendChat($login, $text, $plugin = null ) {
			$source_player = $this->storage->getPlayerObject($login);
			$nick = $source_player->nickName;
			$nick = str_ireplace('$w', '', $nick);
			if ($this->_useMasking) {
				$text = str_ireplace($this->_aWordlist, '&#¤&#¤', $text);
			}
			$text = str_ireplace('$l', '', $text);
			$text = preg_replace('/(^|[ ]|((https?|ftp):\/\/))(([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)|localhost|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|net|org|info|biz|gov|name|edu|[a-zA-Z][a-zA-Z]))(:[0-9]+)?((\/|\?)[^ "]*[^ ,;\.:">)])?/i', '\$l$0\$l', $text);
			if (substr($text, 0, 1) != "/") {
				try {
					$adminSign = "";
					if (in_array($login, $this->mlepp->AdminGroup->getAdminsByGroup('operator')))
						$adminSign = $this->mlepp->AdminGroup->getSign('operator');
					if (in_array($login, $this->mlepp->AdminGroup->getAdminsByGroup('admin')))
						$adminSign = $this->mlepp->AdminGroup->getSign('admin');
					if (in_array($login, $this->mlepp->AdminGroup->getAdminsByGroup('root')))
						$adminSign = $this->mlepp->AdminGroup->getSign('root');
				} catch (\Exception $e) {
					Console::println('[' . date('H:i:s') . '] [MLEPP] [CustomChat] coundn\'t get the priviledged user sign. Propably there is problem with the config-admin.ini file. Please update or check the file stucture.' . "\n" . $e->getMessage());
				}

				try {
					if (!empty($adminSign)) {
						$this->connection->chatSendServerMessage("\$fff" . $adminSign . " $nick\$z\$s" . $this->config->adminChatColor . "  " . $text);
					} else {
						$this->connection->chatSendServerMessage("\$fff$nick\$z\$s" . $this->config->publicChatColor . "  " . $text);
					}
				} catch (\Exception $e) {
					Console::println('[' . date('H:i:s') . '] [MLEPP] [CustomChat] error sendin chat from ' . $login . ': ' . $text . ' with folloing error' . "\n" . $e->getMessage());
				}
			}
	}
	 /**
	 * onUnload()
	 * Function called on unloading this plugin.
	 *
	 * @return void
	 */
	function onUnload() {
		$this->connection->chatEnableManualRouting(false);
		parent::onUnload();
	}

}

?>