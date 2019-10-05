<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Challenge Widget
 * @date 04-01-2011
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP team
 *
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

namespace ManiaLivePlugins\MLEPP\ChallengeWidget;

use ManiaLivePlugins\MLEPP\ChallengeWidget\Gui\Windows\ChallengeWidgetWindow;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Utilities\Console;
use ManiaLive\Gui\Handler;
use ManiaLive\Data\Storage;
use ManiaLive\Gui\Windowing\Window;
use ManiaLive\Gui\Windowing\CustomUI;
use ManiaLive\Gui\Windowing\WindowHandler;

class ChallengeWidget extends \ManiaLive\PluginHandler\Plugin {

	private $serverCountry = "";

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	 
	function onInit() {
		// this needs to be set in the init section
		$this->setPublicMethod('getVersion');
		$this->setPublicMethod('showWidget');
		$this->setPublicMethod('hideWidget');
		$this->setVersion(1050);
	}

	 /**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	 
	function onLoad() {
		$this->enableDedicatedEvents();
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: ChallengeWidget r' . $this->getVersion());
	}

	 /**
	 * onUnload()
	 * Function called on unloading of ManiaLive.
	 *
	 * @return void
	 */
	 
	function onUnload() {
		Console::println('[' . date('H:i:s') . '] [UNLOAD] [ChallengeWidget] Freeing window resources.');
		ChallengeWidgetWindow::EraseAll();
		parent::onUnload();
		Console::println('[' . date('H:i:s') . '] [UNLOAD] [ChallengeWidget] Plugin fully unloaded.');
	}

	 /**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */
	 
	function onReady() {
		$mlepp = Mlepp::getInstance();
		$serverinfo = $this->connection->getDetailedPlayerInfo($this->storage->serverLogin);
		$country = explode("|", $serverinfo->path);
		if (isset($country[1]))
			$this->serverCountry = $mlepp->mapCountry($country[1]);


		foreach ($this->storage->players as $login => $player) {
			$this->showChallengeWidget($login);
		}
		foreach ($this->storage->spectators as $login => $player) {
			$this->showChallengeWidget($login);
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
		$this->showChallengeWidget($login);
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
			$this->showChallengeWidget($login);
		}
		foreach ($this->storage->spectators as $login => $player) {
			$this->showChallengeWidget($login);
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
	 
	public function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge) {
		$challengeObj = $this->connection->getNextChallengeInfo();
		foreach ($this->storage->players as $login => $player) {
			$this->showChallengeWidget($login, $challengeObj);
		}
		foreach ($this->storage->spectators as $login => $player) {
			$this->showChallengeWidget($login, $challengeObj);
		}
	}

	 /**
	 * showWidget()
	 * Function used for initializing widget.
	 *
	 * @param mixed $login
	 * @param mixed $Plugin
	 * @return void
	 */
	 
	function showWidget($login = NULL, $Plugin = NULL) {
		foreach ($this->storage->players as $login => $player) {
			$this->showChallengeWidget($login);
		}
		foreach ($this->storage->spectators as $login => $player) {
			$this->showChallengeWidget($login);
		}
	}

	 /**
	 * hideWidget()
	 * Function used for initializing widget hiding.
	 *
	 * @param mixed $login
	 * @param mixed $Plugin
	 * @return void
	 */
	 
	function hideWidget($login = NULL, $Plugin = NULL) {
		$this->hideChallengeWidget();
	}

	 /**
	 * hideChallengeWidget()
	 * Function used for hiding the widget.
	 *
	 * @param mixed $login
	 * @return void
	 */
	 
	function hideChallengeWidget($login = NULL) {
		$wnd = ChallengeWidgetWindow::GetAll();
		foreach ($wnd as $win) {
			if ($login === NULL) {
				$win->hide();
			} else {
				if ($win->getRecipient() == $login) {
					$win->hide($login);
					break;
				}
			}
		}
	}

	 /**
	 * showChallengeWidget()
	 * Function used for showing the widget.
	 *
	 * @param mixed $login
	 * @return void
	 */
	 
	function showChallengeWidget($login, $challenge = false) {
		$player = $this->storage->getPlayerObject($login);
		$customUi = new CustomUI();
		$customUi->challengeInfo = false;
		WindowHandler::setCustomUI($customUi, $player);
		$window = ChallengeWidgetWindow::Create($login);
		$window->setPosition(0, -90);
		$window->setPosZ(-100);
		if ($challenge == false) {
			$window->challengeData = $this->storage->currentChallenge;
			$window->text = "Now Playing on";
		} else {
			$window->challengeData = $challenge;
			$window->text = "Next Playing on";
		}
		$window->TMXcallBack = array($this, 'onTmxClicked');
		$window->MXcallBack = array($this, 'onMxClicked');
		$window->Show();
	}

	 /**
	 * onTmxClicked()
	 * Function called when clicking the TMX logo.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function onTmxClicked($login) {
		$this->callPublicMethod('MLEPP\TmxInfo', 'showTMXinfo', $login, $this->storage->currentChallenge->uId);
	}

	function onMxClicked($login) {
		$this->callPublicMethod('MLEPP\ManiaExchange', 'mxinfo', $login, '');
	}

}

?>