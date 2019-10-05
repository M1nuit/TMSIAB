<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name ProfanityFilter
 * @date 31-08-2011
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

namespace ManiaLivePlugins\MLEPP\ProfanityFilter;

use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLive\Utilities\Console;
//use ManiaLive\PluginHandler\PluginHandler;

class ProfanityFilter extends \ManiaLive\PluginHandler\Plugin {
	
	private $_aPlayers = array();
	private $_aWordlist = array();
	private $config;
	private $mlepp;
	
	function onInit() {
		$this->setVersion(1050);
		$this->setPublicMethod('getVersion');
	}

	function onLoad() {
		$this->enableDedicatedEvents();
		$this->enableStorageEvents();
		$this->config = Config::getInstance();
		$this->mlepp = Mlepp::getInstance();
		$this->_aWordlist = explode(",",$this->config->wordlist);
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: ProfanityFilter r' . $this->getVersion());

	}

	function onReady() {
		if ($this->isPluginLoaded('MLEPP\CustomChat')) {
			$this->callPublicMethod('MLEPP\CustomChat', 'ProfanityFilter', true);
		}	
	}
	function onUnload() {
		if ($this->isPluginLoaded('MLEPP\CustomChat')) {
			$this->callPublicMethod('MLEPP\CustomChat', 'ProfanityFilter', false);
		}	
		parent::onUnload();
		
	}

	function onPlayerChat($playerUid, $login, $text, $isRegistredCmd) {
		if ($playerUid == 0) return;
		if (substr($text, 0, 1) == "/") return;
		
		$aText = explode(" ",$text);
		$aResult = 0;
		
		foreach ($aText as $sText) {
			if (in_array(strtolower($sText),$this->_aWordlist,true)) {
				$aResult++;
			}
		}
		
		if ($aResult != 0)  {
			if (!isset($this->_aPlayers[$login])) {
				$oClass = new \stdClass();
				$oClass->{'login'} = $login;
				$oClass->{'nickName'} = $this->storage->getPlayerObject($login)->nickName;
				$oClass->{'hitCount'} = $aResult;
				$oClass->{'timeStamp'} = time();
				$this->_aPlayers[$login] = $oClass;
				$this->checkAction($this->_aPlayers[$login]);
			}
			else {
				$this->_aPlayers[$login]->hitCount += $aResult;
				$this->checkAction($this->_aPlayers[$login]);
			}
			 
		}
	}		
	
	
	function checkAction($oPlayer) {
		if ($oPlayer->hitCount >= $this->config->maxAttempts) {
			switch ($this->config->action) {
				case 'kick':
					$this->connection->kick($oPlayer->login, 'Explicit language');
					break;
				case 'ban':
					$this->connection->ban($oPlayer->login, 'Explicit language');
					break;
				case 'mute':
					$this->connection->ignore($oPlayer->login);
					$this->mlepp->sendChat("%adminaction%Player ".$oPlayer->nickName.'$z$s%adminaction% has been now muted for explicit language!');
					break;
				default:
					break;
			}
			unset($this->_aPlayers[$oPlayer->login]);
		}
		else {
			$this->mlepp->sendChat("%adminerror%Warning for explicit usage of language. ".$oPlayer->nickName."\$z\$s%adminerror% has %variable%".($this->config->maxAttempts-$oPlayer->hitCount)."%adminerror% attempts left before %variable%".$this->config->action);			
		}
	}

}

?>