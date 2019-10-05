<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name DonatePanel
 * @date 22-08-2011
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

namespace ManiaLivePlugins\MLEPP\DonatePanel;

use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Connection as MlConnection;
use ManiaLive\Database\Connection;
use ManiaLivePlugins\MLEPP\DonatePanel\Gui\DonatePanelWindow;
use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\MLEPP\DonatePanel\Events\onPlanetsDonate;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class DonatePanel extends \ManiaLive\PluginHandler\Plugin {

	public static $billId = array();
	private $enabled = true;
	private $mlepp;
	private $show;
	private $descDonate = "add description here.";
	private $help = "Help for plugin.";
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
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: DonatePlanets r' . $this->getVersion());
		$this->enableDedicatedEvents();
		$this->mlepp = Mlepp::getInstance();
        $this->config = Config::getInstance();
        
		foreach ($this->storage->players as $login => $player) {
			if ($this->enabled) {
				$this->show[$login] = DonatePanelWindow::Create($login);
				$this->show[$login]->show();
			}
		}

		foreach ($this->storage->spectators as $login => $player) {
			if ($this->enabled) {

				$this->show[$login] = DonatePanelWindow::Create($login);
				$this->show[$login]->show();
			}
		}

		$cmd = $this->registerChatCommand("donate", "donate", 1, true);
		$cmd->help = $this->descDonate;
		$cmd = $this->registerChatCommand("donate", "donate", 0, true);
		$cmd->help = $this->descDonate;
	}

	 /**
	 * onOliverde8HudMenuReady()
	 * Function used for adding buttons to Olivers Hud Menu.
	 *
	 * @param mixed $menu
	 * @return void
	 */
	public function onOliverde8HudMenuReady($menu) {
		$parent = $menu->findButton(array("Menu", "Donate Planets"));

		if (!$parent) {
			$button["style"] = "Icons128x128_1";
			$button["substyle"] = "Planets";

			$parent = $menu->addButton("Menu", "Donate Planets", $button);
		}

		$button["plugin"] = $this;
		$button["function"] = "Donate";

		$donations = array("100", "200", "500", "1000", "2000");
		foreach ($donations as $value) {
			$button["params"] = $value;
			$menu->addButton($parent, "Donate " . $value, $button);
		}
	}

	function onUnload() {
		parent::onUnload();
		DonatePanelWindow::EraseAll();
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
		foreach ($this->storage->players as $login => $player) {
			if ($this->enabled) {
				$this->show[$login]->hide();
			}
		}

		foreach ($this->storage->spectators as $login => $player) {
			if ($this->enabled) {
				$this->show[$login]->hide();
			}
		}
	}

	 /**
	 * onBeginRace()
	 * Function called on begin of the race.
	 *
	 * @param mixed $challengeInfo
	 * @return void
	 */
	function onBeginRace($challenge) {
		foreach ($this->storage->players as $login => $player) {
			if ($this->enabled) {
				$this->show[$login]->show();
			}
		}

		foreach ($this->storage->spectators as $login => $player) {
			if ($this->enabled) {
				$this->show[$login]->show();
			}
		}
	}

	 /**
	 * onBillUpdated()
	 * Function called when a bill is updated.
	 *
	 * @param mixed $billId
	 * @param mixed $state
	 * @param mixed $stateName
	 * @param mixed $transactionId
	 * @return
	 */
	function onBillUpdated($billId, $state, $stateName, $transactionId) {

		if (count(self::$billId) == 0)
			return;

		foreach (self::$billId as $data) {
			if ($billId == $data[0]) {
				if ($state == 4) {  // Success
					$login = $data[1];
					$amount = $data[2];
					$fromPlayer = $this->storage->getPlayerObject($login);
					if ($amount < $this->config->donateAmountForGlobalMsg) {
						$this->mlepp->sendChat('%donate%You donated %variable%' . $amount . '%donate% Planets to the server.$z$s%donate%, Thank You.', $login);
					} else {
						$this->mlepp->sendChat('%donate%The server recieved a donation of %variable%' . $amount . '%donate% Planets from %variable%' . $fromPlayer->nickName . '$z$s%donate%, Thank You.');
					}
					Dispatcher::dispatch(new onPlanetsDonate($login, $amount, "MLEPP\DonatePlanets", "player donate"));
					unset(self::$billId[$data[0]]);
					break;
				}

				if ($state == 5) { // No go
					$login = $data[1];
					$amount = $data[2];
					$fromPlayer = $this->storage->getPlayerObject($login);
					$this->mlepp->sendChat('%error%No Planets billed.', $fromPlayer);

					unset(self::$billId[$data[0]]);
					break;
				}

				if ($state == 6) {  // Error
					$login = $data[1];
					$fromPlayer = $this->storage->getPlayerObject($login);
					$this->mlepp->sendChat('%error% There was error with player %variable%' . $fromPlayer->nickName . '$z$s%error% donation.');
					unset(self::$billId[$data[0]]);
					break;
				}
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
	function onPlayerConnect($login, $isSpec) {
		if ($this->enabled) {
			$player = $this->storage->getPlayerObject($login);
			$this->show[$login] = DonatePanelWindow::Create($login);
			$this->show[$login]->show();
		}
	}

	function onPlayerDisconnect($login) {
		DonatePanelWindow::Erase($login);
		unset($this->show[$login]);
	}

	 /**
	 * Donate()
	 * Function provides the /donate command.
	 *
	 * @param mixed $login
	 * @param mixed $amount
	 * @return void
	 */
	function donate($login, $amount = null) {
		$player = $this->storage->getPlayerObject($login);
		if ($amount == "help" || $amount == null) {
			$this->showHelp($login);
			return;
		}
		if (is_numeric($amount)) {
			$amount = (int) $amount;
		} else {
			$this->mlepp->sendChat('%error%Donate takes one argument and it needs to be numeric.', $login);
			return;
		}

		$fromPlayer = $this->storage->getPlayerObject($login);
		$storage = Storage::getInstance();
		$connection = MlConnection::getInstance();
		$toPlayer = new \ManiaLive\DedicatedApi\Structures\Player();
		if (empty($this->config->toLogin)) {
			$toPlayer->login = $storage->serverLogin;
		} else {
			$toPlayer->login = $this->config->toLogin;
		}
		$fromPlayer = $storage->getPlayerObject($login);
		$billId = $connection->sendBill($fromPlayer, (int) $amount, 'Planets Donation', $toPlayer);
		DonatePlanets::$billId[$billId] = array($billId, $login, $amount);
	}

	function showHelp($login) {
		$this->callPublicMethod('MLEPP\Core', 'showHelp', $login, "help for plugin " . $this->getName(), $this->help);
	}

}

?>