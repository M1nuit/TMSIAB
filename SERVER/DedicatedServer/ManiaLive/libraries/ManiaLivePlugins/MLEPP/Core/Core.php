<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Core --
 * @name Core
 * @date 02-07-2011
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

namespace ManiaLivePlugins\MLEPP\Core;

use ManiaLive\Utilities\Console;
use ManiaLib\Utils\TMStrings as String;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLive\Features\ChatCommand\Interpreter;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\MLEPP\Core\Gui\Windows\helpWindow;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLive\Gui\Windowing\CustomUI;
use ManiaLive\Gui\Windowing\WindowHandler;

class Core extends \ManiaLive\PluginHandler\Plugin {

	private $versionAddition = ' TM2 Beta';
	private $mlepp = null;
	// up-to-date check
	public static $check_interval = 1000;
	private $check_tick = 0;
	// development state?
	public static $dev_state = 1;
	public static $version = 1050;

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	function onInit() {
		$this->setVersion(self::$version);
		$this->setPublicMethod('getVersion');

		$this->mlepp = Mlepp::getInstance();
		$this->config = Config::getInstance();

		$this->versionCheck();

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
		Console::println('[' . date('H:i:s') . '] [MLEPP] Enabling MLEPP r' . $this->getVersion() . ' . . .');
		$this->setPublicMethod('showHelp');
		$this->enableDedicatedEvents();
		$this->enableStorageEvents();
		$this->sendNewMleppAlive();
		$command = $this->registerChatCommand("helpall", "help", 0, true);
		$command->help = "ingame help!";
		$command = $this->registerChatCommand("helpall", "help", 1, true);
		$command->help = "ingame help!";

		// throw error on standard admin.
		if ($this->isPluginLoaded('Standard\Admin')) {
			Console::println(' ##############################################################################');
			Console::println(' #  ERROR: plugin.load[]=\'Standard\Admin\' has to be disabled in config.ini  #');
			Console::println(' ##############################################################################');
			die();
		}
	}

	 /**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */
	function onReady() {
		//reset forced music to normal!
		if (! $this->isPluginLoaded('MLEPP\MusicBox')) {
			$this->connection->setForcedMusic(false, "");
		}
		
		
		if (count($this->storage->players) > 0) {
			foreach ($this->storage->players as $login => $player) {
				$this->onPlayerConnect($login, false);
			}
		}
		if (count($this->storage->spectators) > 0) {
			foreach ($this->storage->spectators as $login => $player) {
				$this->onPlayerConnect($login, true);
			}
		}
	}

	function sendNewMleppAlive() {
		$this->connection->dedicatedEcho("Manialive\MLEPP", (string) getmypid());
	}

	function onEcho($internal, $public) {
		if (($public == "Manialive\MLEPP") && ($internal != (string) getmypid() )) {
			die("\n\nManialive exit due new mlepp process has been initialized.");
		}
	}

	 /**
	 * onTick()
	 * Function triggered every second, checks MLEPP version.
	 *
	 * @return void
	 */
	function onTick() {
		// check for an MLEPP update every 1000 seconds (standard)
		$this->check_tick++;
		if ($this->check_tick == self::$check_interval) {
			$this->consoleUpdateCheck(self::$version);
			$this->check_tick = 0;
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
		$parent = $menu->findButton(array("Menu", "Help"));

		if (!$parent) {
			$button["style"] = "Icons64x64_1";
			$button["substyle"] = "TrackInfo";

			$parent = $menu->addButton("Menu", "Help", $button);
		}
		$button["plugin"] = $this;

		unset($button["style"]);
		unset($button["substyle"]);

		$button["function"] = "help";
		$button["params"] = "null";
		$menu->addButton($parent, "Commands", $button);
	}

	 /**
	 * consoleUpdateCheck()
	 * Function checks if the current MLEPP version is up-to-date and outputs into the console.
	 *
	 * @param mixed $version
	 * @return void
	 */
	function consoleUpdateCheck($version) {
		/* $check = $this->checkUptodate($version);
		  if($check != 'uptodate') {
		  Console::println(' ##############################################################################');
		  Console::println('                     MLEPP - ManiaLive Extending Plugin Pack r.'.self::$version);
		  Console::println(' ##############################################################################');
		  Console::println('   Your MLEPP installation is outdated!                                        ');
		  Console::println('   * Current version: r'.self::$version.'                                                    #');
		  Console::println('   * Latest version: r'.$check);
		  Console::println(' ##############################################################################');
		  Console::println('            Get the latest version on: http://mlepp.googlecode.com!            ');
		  Console::println('    Outdated versions can contain bugs that are fixed in the latest version.   ');
		  Console::println(' ##############################################################################');
		  }
		 */
	}

	function versionCheck() {
		$bExitApp = false;
		Console::println(' ');
		Console::println(' ');
		Console::println(' ');
		Console::println(' ');
		Console::println(' L o a d i n g   M a n i a L i v e   E x t e n d i n g   P l u g i n   P a c k ');
		Console::println(' ');
		Console::println('        ::::    ::::    :::          ::::::::::   :::::::::    :::::::::       ');
		Console::println('        +:+:+: :+:+:+   :+:          :+:          :+:    :+:   :+:    :+:      ');
		Console::println('        +:+ +:+:+ +:+   +:+          +:+          +:+    +:+   +:+    +:+      ');
		Console::println('        +#+  +:+  +#+   +#+          +#++:++#     +#++:++#+    +#++:++#+       ');
		Console::println('        +#+       +#+   +#+          +#+          +#+          +#+             ');
		Console::println('        #+#       #+#   #+#          #+#          #+#          #+#             ');
		Console::println('        ###       ###   ##########   ##########   ###          ###             ');
		Console::println(' ');
		Console::println('   Knutselmaaster   Nouseforname   oliverde8   TheM   reaby   schmidi  w1lla   ');
		Console::println(' ');
		Console::println(' ');
		Console::println('');
		Console::println(' Starting self test for needed requirements...');

		if (version_compare(PHP_VERSION, '5.3.5') >= 0) {
			Console::println(' * Minimum of PHP version 5.3.5: Pass (' . PHP_VERSION . ')');
		} else {
			Console::println(' * Minimum of PHP version 5.3.5: Fail (' . PHP_VERSION . ')');
			$bExitApp = true;
		}

		if (\ManiaLiveApplication\Version < 239) {
			Console::println(' * Manialive minimum r239... Fail (' . \ManiaLiveApplication\Version . ')');
			$bExitApp = true;
		} else {
			Console::println(' * Manialive minimum r239... Pass (' . \ManiaLiveApplication\Version . ')');
		}

		switch ($this->mlepp->gameVersion) {
			case "TmForever":
				$oDate = date_create(Connection::getInstance()->getVersion()->build);
				$oCompareDate = new \DateTime('2011-02-21');

				if ($oDate < $oCompareDate) {
					Console::println(' * Dedicated server minimum 2011-02-21.... Fail');
					$bExitApp = true;
				} else {
					Console::println(' * Dedicated server minimum 2011-02-21.... Pass');
				}
				break;
			case "ManiaPlanet":
				if (true) {
					Console::println(' * Running ManiaPlanet dedicated server.... Pass');
				} else {
					Console::println(' * Running ManiaPlanet dedicated server.... Fail (' . Connection::getInstance()->getVersion()->name . ')');
					$bExitApp = true;
				}
		}

		if ($bExitApp == true) {
			Console::println('');
			Console::println(' Requirements incompatibility, please correct the issues and try again.');
			Console::println('');
			Console::println('');
			Console::println('');
			Console::println('');
			Console::println('');
			die();
		}

		//$check = $this->checkUptodate(self::$version);
//        if($check != 'uptodate') {
//            Console::println('');
//            Console::println('');
//            Console::println(' Please note, that a new MLEPP version available: '.$check);
//            Console::println('');
//            Console::println('');
//        } else {
//            Console::println('');
//            Console::println('');
//            Console::println('');
//            Console::println('');
//        }
	}

	 /**
	 * checkUptodate()
	 * Function checks MLEPP version with external versionfile.
	 *
	 * @param mixed $version
	 * @return
	 */
	function checkUptodate($version) {
		//return 'uptodate';
		$currentversion = $version;
		if (self::$dev_state == 0) {
			$latestversion = @file_get_contents("http://mlepp.klaversma.eu/mlepp_version.txt");
		} elseif (self::$dev_state == 1) {
			$latestversion = @file_get_contents("http://mlepp.klaversma.eu/mlepp_version_dev.txt");
		}

		if ($latestversion === false)
			return 'uptodate';

		if ($currentversion < $latestversion) {
			return $latestversion;
		} else {
			return 'uptodate';
		}
	}

	 /**
	 * help()
	 * Function provides the /help command.
	 *
	 * @param mixed $login
	 * @param mixed $helpObject
	 * @return void
	 */
	function help($login, $helpObject = NULL) {
		Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $login . '] Player used /helpall');
		$inter = Interpreter::getInstance();
		$interCommands = $inter->getRegisteredCommands();
		$allCommands = array();

		foreach ($interCommands as $commands) {
			foreach ($commands as $argumentCount => $command) {
				if ($command->isPublic && (!count($command->authorizedLogin) || in_array($login, $command->authorizedLogin))) {
					$allCommands[(string) $command->name] = $command->help;
				}
			}
		}

		unset($command);
		ksort($allCommands);
		if ($helpObject === NULL) {
			$output = "Available Commands, type \$i\$FC4/help command\$i\$fff for more info: \n";
			foreach ($allCommands as $command => $help) {
				$output .='$o$FC4' . $command . '$o$fff, ';
			}
			$output = substr($output, 0, -2);
			$this->mlepp->sendChat($output, $login);
		} else {
			if (array_key_exists((string) $helpObject, $allCommands)) {
				$help = "Ingame help for /" . (string) $helpObject . ":\n\$o\$FC4";
				$this->mlepp->sendChat($help . $allCommands[(string) $helpObject], $login);
			} else {
				$this->mlepp->sendChat("Sorry, command $helpObject is not registered chat command.", $login);
				Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveTracks] [' . $login . '] Tried to ask for help for an unkown command.');
			}
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
		if (!empty($this->config->joinPlayer)) {
			$message = str_replace('%nickname%', $source_player->nickName, $this->config->joinPlayer);
			$message = str_replace('%version%', self::$version . $this->versionAddition, $message);
			$this->mlepp->sendChat($message, $login);
		}

		/* 	TODO: find a good way to keep customui per player stored.
		 *         $customUi = new CustomUI();

		  if(!$this->mlepp->config->CustomUi->checkpoints) {
		  $customUi->checkpointList = false;
		  }
		  if($this->mlepp->config->CustomUi->notices) {
		  $customUi->notice = true;
		  }

		  WindowHandler::setCustomUI($customUi,$source_player);
		 */
	}

	public function onPlayerChat($playerUid, $login, $text, $isRegistredCmd) {
		if ($playerUid == 0)
			return;

		try {
			$player = $this->storage->getPlayerObject($login);
			$nick = $player->nickName;
		} catch (\Exception $e) {
			$nick = "[]";
		}

		$text = trim($text);
		try {
			$adminSign = "";
			if (in_array($login, $this->mlepp->AdminGroup->getAdminsByGroup('operator')))
				$adminSign = $this->mlepp->AdminGroup->getSign('operator');
			if (in_array($login, $this->mlepp->AdminGroup->getAdminsByGroup('admin')))
				$adminSign = $this->mlepp->AdminGroup->getSign('admin');
			if (in_array($login, $this->mlepp->AdminGroup->getAdminsByGroup('root')))
				$adminSign = $this->mlepp->AdminGroup->getSign('root');
		} catch (\Exception $e) {
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Core] coundn\'t get the priviledged user sign. Propably there is problem with the config-admin.ini file. Please update or check the file stucture.' . "\n" . $e->getMessage());
		}

		$this->mlepp->log("[$adminSign $nick] ($login): $text");
	}

	 /**
	 * showHelp()
	 * Function for showing help in a window.
	 *
	 * @param mixed $login
	 * @param mixed $title
	 * @param mixed $text
	 * @param mixed $plugin
	 * @return void
	 */
	public function showHelp($login, $title, $text, $plugin = NULL) {
		$infoWindow = helpWindow::Create($login);
		$infoWindow->setTitle($title);
		$infoWindow->setText($text);
		$infoWindow->setSize(210, 120);
		$infoWindow->centerOnScreen();
		$infoWindow->show();
	}

}

?>