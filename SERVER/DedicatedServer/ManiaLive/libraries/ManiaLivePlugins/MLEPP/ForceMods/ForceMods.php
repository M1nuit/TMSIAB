<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name ForceMods
 * @date 22-06-2011
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


namespace ManiaLivePlugins\MLEPP\ForceMods;


use ManiaLive\Config\Loader;
use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\DedicatedApi\Structures\Mod;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Utilities\Console;
use ManiaLive\Event\Dispatcher;


use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLivePlugins\MLEPP\ForceMods\Config;
use ManiaLivePlugins\MLEPP\ForceMods\Gui\Windows\EnvironmentWindow;
use ManiaLivePlugins\MLEPP\ForceMods\Gui\Windows\SettingsWindow;
use ManiaLivePlugins\MLEPP\ForceMods\Structures\FMod;
use ManiaLivePlugins\MLEPP\ForceMods\Structures\FModList;


class ForceMods extends \ManiaLive\PluginHandler\Plugin {

	// mode: off=0 sequential=1 random=2
	public static $mode = 1;
	public static $mods = array();
	public static $override = false;

	protected $modLists = array();
	public static $environments = array();
	private $mlepp = NULL;

	 /**
	 * @fn onInit()
	 * @brief Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	function onInit() {
		// this needs to be set in the init section
		$this->setVersion(intval(preg_replace('/[^\d]/', '', '$Revision: 1050 $')));
		$this->setPublicMethod('getVersion');

		switch($this->connection->getVersion()->name) {
			case 'TmForever':
				self::$environments = array(1 => 'Stadium', 2 => 'Island', 3 => 'Speed', 4 => 'Rally', 5 => 'Bay', 6 => 'Coast', 7 => 'Alpine');
				break;
            case 'ManiaPlanet':
                self::$environments = array(1 => 'Canyon');
                break;
		}

		//Oliverde8 Menu
		if($this->isPluginLoaded('oliverde8\HudMenu')) {
			Dispatcher::register(\ManiaLivePlugins\oliverde8\HudMenu\onOliverde8HudMenuReady::getClass(), $this);
		}
	}


	 /**
	 * @fn onLoad()
	 * @brief Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	function onLoad() {
		Console::println('['.date('H:i:s').'] [MLEPP] Plugin: ForceMods r'.$this->getVersion() );
		$this->enableDedicatedEvents();
		$this->mlepp = Mlepp::getInstance();
		self::$override = (self::$override == 'true' || self::$override === true || self::$override == 1) ? true : false;

		// 1 list per enviroment
		foreach(self::$environments as $id => $name) {
			$this->modLists[$id] = new FModList();
		}
		foreach(Config::getInstance()->mods as $config) {
			$data = explode(';', $config, 5);
			$enabled = isset($data[3]) ? $data[3] : true;
			$mod = new FMod($data[0], $data[1], $enabled);

			if(isset($data[2]) && $data[2] != '') {
				// add mod to specified environments
				$str = &$data[2];
				$i = strlen($data[2]) - 1;
				while($i >= 0) {
					$c = $str[$i];
					if(ctype_digit($c)) {
						if(array_key_exists(intval ($c), self::$environments)) {
							$this->modLists[intval($c)]->addMod($mod);
						}
					}
					$i--;
				}
			}
			else {
				// add mod to all environments
				foreach(self::$environments as $id => $name) {
					$this->modLists[$id]->addMod($mod);
				}
			}
		}

		Console::println('['.date('H:i:s').'] [MLEPP] [ForceMods] mode: '.self::$mode.'  override: '. ((Config::getInstance()->override) ? 'on' : 'off'));

		$txt = 'blabla';
		$cmd = $this->registerChatCommand("fmods", "fmods", 0, true);
		$cmd->help = $txt;
		$cmd = $this->registerChatCommand("fmods", "fmods", 1, true);
		$cmd->help = $txt;
		$cmd = $this->registerChatCommand("fmods", "fmods", 2, true);
		$cmd->help = $txt;
	}


	function onUnload() {
		parent::onUnload();
	}


	 /**
	 * onOliverde8HudMenuReady()
	 * Function used for adding buttons to Olivers Hud Menu.
	 *
	 * @param mixed $menu
	 * @return void
	 */
	public function onOliverde8HudMenuReady($menu) {

		$parent = $menu->findButton(array("Admin", "MLEPP"));
		if(!$parent) {
			$button["style"] = "Icons128x128_1";
			$button["substyle"] = "CustomStars";
			$parent = $menu->addButton("Admin", "MLEPP", $button);
		}
		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "Custom";
		$button["plugin"] = $this;
		$button["params"]="null;null";
		$button["function"] = "fmods";
		$parent = $menu->addButton($parent, "Force Mods", $button);
	}


	 /**
	 * @fn onEndChallenge()
	 * @brief Function called on end of challenge.
	 *
	 * @param mixed $rankings
	 * @param mixed $challenge
	 * @param mixed $wasWarmUp
	 * @param mixed $matchContinuesOnNextChallenge
	 * @param mixed $restartChallenge
	 * @return void
	 */
	function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge) {
		if(Config::getInstance()->mode == 0) {
			return;
		}

		$nextChallenge = $this->connection->getNextChallengeInfo();
		$env = $nextChallenge->environnement;
		if($restartChallenge == 1) {
			$env = $challenge['Environnement'];
		}

		$id = array_search($env, self::$environments);

		if($id === false) {
			Console::println('['.date('H:i:s').'] [MLEPP] [ForceMods] Enviroment "'.$env.'" not defined, canceling...');
			return;
		}

		$modList = &$this->modLists[$id];
		$index = $modList->nextMod(Config::getInstance()->mode == 2);

		if($index < 0) {
			Console::println('['.date('H:i:s').'] [MLEPP] [ForceMods] No mods defined (enabled) for this environment, canceling...');
			$this->connection->setForcedMods(false, array(), true);
			return;
		}

		$mod = new Mod();
		$mod->env = $env;
		$mod->url = $modList->getUrl();

		$this->connection->setForcedMods(self::$override, $mod);
		Console::println('['.date('H:i:s').'] [MLEPP] [ForceMods] Enabling mod: '.$env.' > '.$modList->getName());
	}


	 /**
	 * @fn onPlayerDisconnect()
	 * @brief Function called when a player disconnects.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function onPlayerDisconnect($login) {
		SettingsWindow::Erase($login);
		EnvironmentWindow::Erase($login);
	}


	 /**
	 * @fn onSettingsClick()
	 * @brief Function called on SettingsWindow-Click.
	 *
	 * @param mixed $login
	 * @param mixed $action
	 * @return void
	 */
	function onSettingsClick($login, $action) {
		$admin = Storage::GetInstance()->getPlayerObject($login);

		switch($action) {
			case 'off':
				$this->setMode($admin, '0');
				SettingsWindow::Redraw();
				break;

			case 'inc':
				$this->setMode($admin, '1');
				SettingsWindow::Redraw();
				break;

			case 'rand':
				$this->setMode($admin, '2');
				SettingsWindow::Redraw();
				break;

			case 'overrOn':
				$this->setOverride($admin, 'on');
				SettingsWindow::Redraw();
				break;

			case 'overrOff':
				$this->setOverride($admin, 'off');
				SettingsWindow::Redraw();
				break;

			default:
				$id = array_search($action, self::$environments);
				if($id !== false) {
					$window = EnvironmentWindow::Create($login);
					$window->setEnvironment($action, $this->modLists[$id]);
					$window->centerOnScreen();
					$window->show();
				}
				break;
		}
	}


	 /**
	 * @fn onEnvironmentClick()
	 * @brief Function called on EnvironmentWindow-Click.
	 *
	 * @param mixed $login
	 * @param mixed $environment
	 * @param mixed $id
	 * @return void
	 */
	function onEnvironmentClick($login, $environment, $id) {
	}


	 /**
	 * @fn fmods()
	 * @brief Function called on chat-command.
	 *
	 * @param mixed $login
	 * @param mixed $cmd
	 * @param mixed $param
	 * @return void
	 */
	function fmods($login, $cmd = '', $param = '') {
		$admin = Storage::GetInstance()->getPlayerObject($login);
		$cmd = strtolower($cmd);

		if(! $this->mlepp->AdminGroup->hasPermission($login,'admin')) {
			$this->mlepp->sendChat('%error%You are not admin!', $login);
			return;
		}

		switch($cmd) {
			case 'mode':
				$this->setMode($admin, $param);
				break;

			case 'override':
				$this->setOverride($admin, $param);
				break;

			default:
				if(empty($cmd)) {
					$window = SettingsWindow::Create($login);
					$window->setCallback(array($this, 'onSettingsClick'));
					$window->centerOnScreen();
					$window->show();
				}
				else {
					$envi = ucfirst($cmd);
					$id = array_search($envi, self::$environments);
					if($id !== false) {
						$window = EnvironmentWindow::Create($login);
						$window->setEnvironment($envi, $this->modLists[$id]);
						$window->centerOnScreen();
						$window->show();
					}
				}
				break;
		}
	}


	 /**
	 * @fn getEnvironments()
	 * @brief Function sets new mode.
	 *
	 * @return array()
	 */
	static function getEnvironments() {
		return self::$environments;
	}


	 /**
	 * @fn setMode()
	 * @brief Function sets new mode.
	 *
	 * @param mixed $player
	 * @param mixed $mode
	 * @return void
	 */
	private function setMode($player, $mode) {
		switch(strtolower($mode)) {
			case '0':
			case 'off':
				Config::getInstance()->mode = 0;
				$this->connection->setForcedMods(false, array(), true);
				$this->mlepp->sendChat('%server%ForceMods: %variable%'.$player->nickName.'$z$s&adminaction% disabled Plugin');
				Console::println('['.date('H:i:s').'] [MLEPP] [ForceMods] '.$player->login.' disabled plugin');
				break;

			case '1':
				Config::getInstance()->mode = 1;
				$this->mlepp->sendChat('%server%ForceMods: %variable%'.$player->nickName.'$z$s%adminaction% set mode sequential');
				Console::println('['.date('H:i:s').'] [MLEPP] [ForceMods] '.$player->login.' set mode sequential');
				break;

			case '2':
				Config::getInstance()->mode = 2;
				$this->mlepp->sendChat('%server%ForceMods: %variable%'.$player->nickName.'$z$s%adminaction% set mode random');
				Console::println('['.date('H:i:s').'] [MLEPP] [ForceMods] '.$player->login.' set mode random');
				break;
		}
	}

	 /**
	 * @fn setOverride()
	 * @brief Function enables/disbales override.
	 *
	 * @param mixed $player
	 * @param mixed $override
	 * @return void
	 */
	private function setOverride($player, $override) {
		switch(strtolower($override)) {
			case 'false':
			case 'off':
				self::$override = false;
				$this->mlepp->sendChat('%server%ForceMods: %variable%'.$player->nickName.'$z$s%adminaction% disabled override');
				Console::println('['.date('H:i:s').'] [MLEPP] [ForceMods] '.$player->login.' disabled override');
				break;

			case 'true':
			case 'on':
				self::$override = true;
				$this->mlepp->sendChat('%server%ForceMods: %variable%'.$player->nickName.'$z$s%adminaction% enabled override');
				Console::println('['.date('H:i:s').'] [MLEPP] [ForceMods] '.$player->login.' enabled override');
				break;
		}
	}

}
?>