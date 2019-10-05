<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Votes
 * @date 14-02-2011
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

namespace ManiaLivePlugins\MLEPP\Votes;

use ManiaLivePlugins\MLEPP\Votes\Structures\Vote;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Utilities\Console;
use ManiaLib\Gui\Elements\Icons128x128_1;
use ManiaLive\Gui\Handler;
use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Xmlrpc\Request;
use ManiaLive\Event\Dispatcher;
// Panel Grahpics
use ManiaLivePlugins\MLEPP\Votes\Gui\Windows\VotePanel;
use ManiaLivePlugins\MLEPP\Votes\Gui\Windows\ProgressPanel;
use ManiaLivePlugins\MLEPP\Votes\Gui\Controls\Button;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class Votes extends \ManiaLive\PluginHandler\Plugin {

	private $billId = array();
	private $skip = array();
	private $restart = array();
	public $votesEnabled = true;
	protected $vote;
	private $action = NULL;
	private $challengeWidget = false;
	private $karmaWidget = false;
	private $mlepp;
	private $config;
	private $currentRestarts = 0;

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	function onInit() {
		$this->setVersion(1050);
		$this->setPublicMethod('getVersion');
		$this->setPublicMethod('voteCommand');

		//Oliverde8 Menu
		if ($this->isPluginLoaded('oliverde8\HudMenu')) {
			Dispatcher::register(\ManiaLivePlugins\oliverde8\HudMenu\onOliverde8HudMenuReady::getClass(), $this);
		}
	}

	 /**
	 * onUnload()
	 * Function called on unloading the plugin.
	 *
	 * @return void
	 */
	function onUnload() {
		$this->connection->setCallVoteTimeOut((int) 30000);

		Console::println('[' . date('H:i:s') . '] [UNLOAD] [Votes] Freeing window resources.');
		VotePanel::EraseAll();
		ProgressPanel::EraseAll();

		if ($this->isPluginLoaded('MLEPP\Admin', 251)) {
			$this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', array('set', 'server', 'callvote'));   //remove commands from admin
			Console::println('[' . date('H:i:s') . '] [UNLOAD] [Votes] Removed all dependend vote commands from admin.');
		}
		parent::onUnload();
		Console::println('[' . date('H:i:s') . '] [UNLOAD] [Votes] Plugin fully unloaded.');
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
		$this->config = Config::getInstance();

		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: Votes r' . $this->getVersion());

		$command = $this->registerChatCommand('skip', 'skip', 0, true);
		$command->help = "Starts a vote for skipping the challenge. Usage: /skip";
		$command = $this->registerChatCommand('restart', 'restart', 0, true);
		$command->help = "Starts a vote for restarting the challenge. Usage: /restart";
		$command = $this->registerChatCommand('replay', 'restart', 0, true);
		$command->help = "Starts a vote for restarting the challenge. Usage: /replay";

		if ($this->isPluginLoaded('MLEPP\Admin', 251)) {
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'useCallVotes'), array("set", "server", "callvote"), true, false, false);
		}

		if ($this->isPluginLoaded('MLEPP\ChallengeWidget')) {
			$this->challengeWidget = true;
		}

		if ($this->isPluginLoaded('MLEPP\Karma')) {
			$this->karmaWidget = true;
		}

		if ($this->isPluginLoaded('Standard\Menubar')) {
			$this->callPublicMethod('Standard\Menubar', 'initMenu', Icons128x128_1::Create);
			$this->callPublicMethod('Standard\Menubar', 'addButton', 'Vote for Skip', array($this, 'skip'), false);
			$this->callPublicMethod('Standard\Menubar', 'addButton', 'Vote for Restart', array($this, 'restart'), false);
			$this->callPublicMethod('Standard\Menubar', 'addButton', 'Pay for Skip', array($this, 'skipPay'), false);
			$this->callPublicMethod('Standard\Menubar', 'addButton', 'Pay for Restart', array($this, 'restartPay'), false);
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


		$parent2 = $menu->findButton(array("Menu", "Votes"));
		if (!$parent2) {
			$button["style"] = "Icons128x128_1";
			$button["substyle"] = "Create";
			$parent2 = $menu->addButton("Menu", "Votes", $button);
		}

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "ArrowLast";
		$button["plugin"] = $this;
		$button["function"] = 'skip';
		$parent = $menu->addButton($parent2, "Vote for Skip", $button);

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "ClipRewind";
		$button["plugin"] = $this;
		$button["function"] = 'restart';
		$parent = $menu->addButton($parent2, "Vote for Restart", $button);

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "ArrowLast";
		$button["plugin"] = $this;
		$button["function"] = 'skipPay';
		$parent = $menu->addButton($parent2, "Pay for Skip", $button);

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "ClipRewind";
		$button["plugin"] = $this;
		$button["function"] = 'restartPay';
		$parent = $menu->addButton($parent2, "Pay for Restart", $button);
	}

	 /**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */
	function onReady() {
		$this->enableDedicatedEvents();
		$this->enableTickerEvent();

		// disable ingame callvotes.
		$this->connection->setCallVoteTimeOut(0);
		// create votes.
		$this->vote = new Vote();
	}

	 /**
	 * onTick()
	 * Function called every second.
	 *
	 * @return void
	 */
	function onTick() {
		if (!empty($this->action)) {
			if ($this->action == "pass") {
				$this->mlepp->sendChat('%server%Vote $fff»» %vote%Vote Passed!');
				switch ($this->vote->command) {
					case "restartChallenge":
						if ($this->config->useQueueRestart) {
							$this->callPublicMethod('MLEPP\Jukebox', 'playerQueueRestart', "");
							$this->vote->reset();
						} else {
							$this->vote->pass();
						}
						break;
					default:
						$this->vote->pass();
						break;
				}

				$this->hideProgressWindows();
				$this->hideVoteWindow();
				$this->showChallengeWidget();
				$this->action = NULL;
			}
			if ($this->action == "deny") {
				$this->mlepp->sendChat('%server%Vote $fff»» %error%Vote Denied!');
				$this->vote->deny();
				$this->hideProgressWindows();
				$this->hideVoteWindow();
				$this->showChallengeWidget();
				$this->action = NULL;
			}
		}

		if (!empty($this->vote->timeout)) {
			if (time() < $this->vote->timeout) {
				
			} else {
				$this->mlepp->sendChat('%server%Vote $fff»» %error%Vote Timeout.');
				$this->vote->deny();
				$this->hideVoteWindow();
				$this->hideProgressWindows();
				$this->showChallengeWidget();
			}
		}
	}

	 /**
	 * onEndChallenge()
	 * Function called on the end of challenge.
	 *
	 * @param mixed $rankings
	 * @param mixed $challenge
	 * @param mixed $wasWarmUp
	 * @param mixed $matchContinuesOnNextChallenge
	 * @param mixed $restartChallenge
	 * @return void
	 */
	function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge) {
		$this->resetVote();

		if ($restartChallenge) {
			$this->currentRestarts++;
		} else {
			$this->currentRestarts = 0;
		}
	}

	function onBeginChallenge($challenge, $warmUp, $matchContinuation) {
		$this->resetVote();
	}

	function resetVote() {
		if (!empty($this->vote->command) || $this->vote->command != NULL) {
			$this->vote->deny();
			$this->hideVoteWindow();
			$this->hideProgressWindows();
			$this->showChallengeWidget();
		}

		//reset votes
		$this->skip = array();
		$this->restart = array();
	}

	function checkForPayAdminPresent() {
		if ($this->config->disablePayingOnAdminPresent == true || $this->config->disablePayingOnAdminPresent == 'true') {
			foreach ($this->storage->players as $login => $player) {
				if ($this->mlepp->AdminGroup->hasPermission($login, 'admin')) {
					return true;
				}
			}
			foreach ($this->storage->spectators as $login => $player) {
				if ($this->mlepp->AdminGroup->hasPermission($login, 'admin')) {
					return true;
				}
			}
		}
		return false;
	}

	function checkForVotingAdminPresent() {
		if ($this->config->disableVotingOnAdminPresent === true || $this->config->disableVotingOnAdminPresent == 'true') {
			foreach ($this->storage->players as $login => $player) {
				if ($this->mlepp->AdminGroup->hasPermission($login, 'admin')) {
					return true;
				}
			}
			foreach ($this->storage->spectators as $login => $player) {
				if ($this->mlepp->AdminGroup->hasPermission($login, 'admin')) {
					return true;
				}
			}
		}
		return false;
	}

	function skipPay($login) {
		if ($this->checkForPayAdminPresent()) {
			$this->mlepp->sendChat("%error% Pay for skip is disabled due admin is present at server.", $login);
			return;
		}
		$fromPlayer = $this->storage->getPlayerObject($login);


		if ($fromPlayer->onlineRights != 3) {
			$this->mlepp->sendChat('%error%Pay is disabled from nations players.', $fromPlayer);
			return;
		}
		$toPlayer = new \ManiaLive\DedicatedApi\Structures\Player();
		if (empty($this->config->payToLogin)) {
			$toPlayer->login = $this->storage->serverLogin;
		} else {
			$toPlayer->login = $this->config->payToLogin;
		}
		$billId = $this->connection->sendBill($fromPlayer, (int) $this->config->skipAmount, 'Pay for Skip', $toPlayer);
		$this->billId[$billId] = array($billId, $login, (int) $this->config->skipAmount, "skip");
	}

	function restartPay($login) {

		if ($this->checkForPayAdminPresent()) {
			$this->mlepp->sendChat("%error% Pay for restart is disabled due admin is present at server.", $login);
			return;
		}

		$fromPlayer = $this->storage->getPlayerObject($login);
		if ($fromPlayer->onlineRights != 3) {
			$this->mlepp->sendChat('%error%Pay is disabled from nations players.', $fromPlayer);
			return;
		}

		$toPlayer = new \ManiaLive\DedicatedApi\Structures\Player();
		if (empty($this->config->payToLogin)) {
			$toPlayer->login = $this->storage->serverLogin;
		} else {
			$toPlayer->login = $this->config->payToLogin;
		}
		$fromPlayer = $this->storage->getPlayerObject($login);
		$billId = $this->connection->sendBill($fromPlayer, (int) $this->config->restartAmount, 'Pay for Restart', $toPlayer);
		$this->billId[$billId] = array($billId, $login, (int) $this->config->restartAmount, "restart");
	}

	 /**
	 * skip()
	 * * Function providing the skip vote.
	 *
	 * @param mixed $login
	 * @return
	 */
	function skip($login) {
		if ($this->checkForVotingAdminPresent()) {
			$this->mlepp->sendChat("%error% Vote for skip is disabled due admin is present at server.", $login);
			return;
		}
		if ($this->checkValidVote($login))
			return;

		if (array_key_exists($login, $this->skip)) {
			$this->mlepp->sendChat("%error%You can call a vote only once in a challenge!", $login);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Votes] [' . $login . '] Already voted.');
			return true;
		}

		if ($this->config->chatmessages === true) {
			$this->mlepp->sendChat('%server%Vote $fff»» %vote%A callvote to skip the current map has started!');
		}

		$this->vote->command = "nextChallenge";
		$this->vote->vote = "Skip Challenge?";
		$this->vote->timeout = time() + $this->config->timeout;
		$this->vote->starter = $login;
		$this->hideChallengeWidget();
		$this->showVoteWindow("Skip Challenge?");
		$this->skip[$login] = $login;
	}

	function restart($login) {
		if ($this->checkForVotingAdminPresent()) {
			$this->mlepp->sendChat("%error% Vote for restart is disabled due admin is present at server.", $login);
			return;
		}
		if ($this->checkValidVote($login))
			return;

		if (array_key_exists($login, $this->restart)) {
			$this->mlepp->sendChat('%server%Vote $fff»» %error%You can call a vote only once in a challenge!', $login);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Votes] [' . $login . '] Already voted.');
			return true;
		}

		if ($this->currentRestarts >= $this->config->maxRestarts) {
			$this->mlepp->sendChat('%server%Vote $fff»» %error%The challenge has already been restarted %variable%' . $this->config->maxRestarts . '%error% times!', $login);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Votes] [' . $login . '] Already restarted ' . $this->config->maxRestarts . ' times.');
			return;
		}

		if ($this->config->chatmessages === true) {
			$this->mlepp->sendChat('%server%Vote $fff»» %vote%A callvote to restart the current map has started!');
		}

		$player = $this->storage->getPlayerObject($login);
		$this->vote->command = "restartChallenge";
		$this->vote->vote = "Restart Challenge?";
		$this->vote->timeout = time() + $this->config->timeout;
		$this->vote->starter = $login;
		$this->hideChallengeWidget();
		$this->showVoteWindow("Restart Challenge?");
	}

	 /**
	 * checkValidVote()
	 * Checks if the vote is valid.
	 *
	 * @param mixed $login
	 * @return boolean $valid
	 */
	function checkValidVote($login) {
		if (!$this->votesEnabled) {
			$this->mlepp->sendChat('%server%Vote $fff»» %error%You can\'t call a vote, admin has disabled the callvotes', $login);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Votes] [' . $login . '] Admin disabled the callvotes.');
			return true;
		}

		if (!empty($this->vote->command)) {
			$this->mlepp->sendChat('%server%Vote $fff»» %error%Callvote allready cast.', $login);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Votes] [' . $login . '] Callvote already cast.');
			return true;
		}

		if (array_key_exists($login, $this->storage->spectators)) {
			$this->mlepp->sendChat('%server%Vote $fff»» %error%You can start a vote only when you are driving!', $login);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Votes] [' . $login . '] Spectators can\'t start votes.');
			return true;
		}
		return false;
	}

	 /**
	 * useCallVotes()
	 * Admin commands for Votes.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @return void
	 */
	function useCallVotes($login, $param) {
		$loginObj = $this->storage->getPlayerObject($login);

		if ($param == "true" || $param == "false" || $param == "enable" || $param == "disable") {
			if ($param == "true" || $param == "enable") {
				$bool = true;
				$value = "enabled";
			}
			if ($param == "false" || $param == "disable") {
				$bool = false;
				$value = "disabled";
			}
			$this->votesEnabled = $bool;
			$this->mlepp->sendChat('%server%Vote $fff»»%adminaction%Admin ' . $loginObj->nickName . '$z$s%adminaction% has %variable%' . $value . '%adminaction% the user call votes!');
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Votes] [' . $login . '] Admin ' . $value . ' the call votes.');
		} else {
			$this->mlepp->sendChat('%server%Vote $fff»»%adminerror%/admin set plugin callvotes takes either %variable%true/enable%adminerror% or %variable%false/disable%adminerror% as a parameter', $login);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Votes] [' . $login . '] Wrong use of command.');
		}
	}

	 /**
	 * updateVote()
	 * Function updates the vote.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function updateVote($login) {
		$yes = $this->vote->yes;
		$no = $this->vote->no;
		$total = count($this->storage->players);

		$yesRatio = ($yes / $total) * 100;
		$noRatio = ($no / $total) * 100;

		if ($yesRatio >= 50) {
			$this->action = "pass";
		}
		if ($noRatio >= 50) {
			$this->action = "deny";
		}
	}

	 /**
	 * voteCommand()
	 * Function providing the vote command.
	 *
	 * @param mixed $login
	 * @param mixed $action
	 * @param mixed $pluginId
	 * @return void
	 */
	function voteCommand($login, $action, $pluginId = null) {
		switch ($action) {
			case 'no':
				$this->hideVoteWindow($login);
				$this->vote->no++;
				$this->showProgressWindow($login);
				$this->updateAllProgressWindows();
				$this->updateVote($login);
				break;
			case 'yes':
				$this->hideVoteWindow($login);
				$this->vote->yes++;
				$this->showProgressWindow($login);
				$this->updateAllProgressWindows();
				$this->updateVote($login);
				break;
			case 'pass':
				$admin = $this->storage->getPlayerObject($login);

				$groups = $this->mlepp->AdminGroup->getAdminGroups($login);
				$title = $this->mlepp->AdminGroup->getTitle($groups[0]);

				$this->mlepp->sendChat('%server%Vote $fff»»%adminaction%' . $title . ' %variable%' . $admin->nickName . '$z$s%adminaction% passed the callvote');
				Console::println('[' . date('H:i:s') . '] [MLEPP] [Votes] [' . $login . '] Passed the callvote.');
				$this->action = "pass";
				break;
			case 'cancel':
				$admin = $this->storage->getPlayerObject($login);

				$groups = $this->mlepp->AdminGroup->getAdminGroups($login);
				$title = $this->mlepp->AdminGroup->getTitle($groups[0]);

				$this->mlepp->sendChat('%server%Vote $fff»»%adminaction%' . $title . ' %variable%' . $admin->nickName . '$z$s%adminaction% denies the callvote');
				Console::println('[' . date('H:i:s') . '] [MLEPP] [Votes] [' . $login . '] Denied the callvote.');
				$this->action = "deny";
				break;
		}
	}

	 /**
	 * hideVoteWindow()
	 * Function hides the vote window.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function hideVoteWindow($login = NULL) {
		$wnd = VotePanel::GetAll();
		foreach ($wnd as $win) {
			if ($login === NULL) {
				$win->hide();
				$win->hide();
				$win->destroy();
			} else {
				if ($win->getRecipient() == $login) {
					$win->hide($login);
					$win->hide($login);
					$win->destroy();
					break;
				}
			}
		}
	}

	 /**
	 * showVoteWindow()
	 * Function shows the vote window.
	 *
	 * @param mixed $text
	 * @return void
	 */
	function showVoteWindow($text) {
		$starter = $this->storage->getPlayerObject($this->vote->starter)->nickName;

		foreach ($this->storage->players as $player) {
			$window = VotePanel::Create($player->login);
			$window->setPosZ(-100);
			$window->clearItems();
			$window->setText($text);
			if ($this->mlepp->AdminGroup->hasPermission($player->login, 'adminVotes')) {
				$window->setAdminText($starter);
			}
			$item = new Button("yes", "yes");
			$item->callBack = array($this, 'voteCommand');
			$window->addItem($item);

			$item = new Button("no", "no");
			$item->callBack = array($this, 'voteCommand');
			$window->addItem($item);

			// if admin, then show controls.

			if ($this->mlepp->AdminGroup->hasPermission($player->login, 'adminVotes')) {
				$item = new Button("pass", "pass");
				$item->callBack = array($this, 'voteCommand');
				$window->addItem($item);

				$item = new Button("deny", "cancel");
				$item->callBack = array($this, 'voteCommand');
				$window->addItem($item);
			}

			//$window->clearAll();
			$window->setSize(75, 28);
			$window->setPosition(0, -90);
			$window->show();
		}
	}

	 /**
	 * showProgressWindow()
	 * Function shows the progress window.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function showProgressWindow($login) {
		$window = ProgressPanel::Create($login);
		$window->setSize(75, 28);
		$window->setPosition(0, -90);
		$window->setPosZ(-100);
		if ($this->mlepp->AdminGroup->hasPermission($login, 'adminVotes')) {
				$item = new Button("pass", "pass");
				$item->callBack = array($this, 'voteCommand');
				$window->addButton($item);

				$item = new Button("deny", "cancel");
				$item->callBack = array($this, 'voteCommand');
				$window->addButton($item);
			}			
		
		$window->setVote($this->vote);
	
		$window->show();
	}

	 /**
	 * updateAllProgressWindows()
	 * Function updates all progress windows.
	 *
	 * @return void
	 */
	function updateAllProgressWindows() {
		$wnd = ProgressPanel::GetAll();
		foreach ($wnd as $win) {
			$win->Redraw();
		}
	}

	 /**
	 * hideProgressWindows()
	 * Function hides the progress window.
	 *
	 * @return void
	 */
	function hideProgressWindows() {
		$wnd = ProgressPanel::GetAll();
		foreach ($wnd as $win) {
			$win->hide();
			$win->hide();
			$win->destroy();
		}
	}

	 /**
	 * showChallengeWidget()
	 * Function calles ChallengeWidget and Karma
	 * to show their widgets.
	 *
	 * @return void
	 */
	function showChallengeWidget() {
		if ($this->challengeWidget)
			$this->callPublicMethod('MLEPP\ChallengeWidget', 'showWidget');
		if ($this->karmaWidget)
			$this->callPublicMethod('MLEPP\Karma', 'showWidget');
	}

	 /**
	 * hideChallengeWidget()
	 * Function calles ChallengeWidget and Karma
	 * to hide their widgets.
	 *
	 * @return void
	 */
	function hideChallengeWidget() {
		if ($this->challengeWidget)
			$this->callPublicMethod('MLEPP\ChallengeWidget', 'hideWidget');
		if ($this->karmaWidget)
			$this->callPublicMethod('MLEPP\Karma', 'hideWidget');
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
		if (count($this->billId) == 0)
			return;

		foreach ($this->billId as $data) {
			if ($billId == $data[0]) {
				if ($state == 4) {  // Success
					$login = $data[1];
					$amount = $data[2];
					$action = $data[3];
					$fromPlayer = $this->storage->getPlayerObject($login);
					if ($action == "skip") {
						$this->mlepp->sendChat('%server%Vote $fff»» %vote%' . $fromPlayer->nickName . '$z$s%vote% pays and skips the challenge');
						$this->connection->nextChallenge();
					}
					if ($action == "restart") {
						$this->mlepp->sendChat('%server%Vote $fff»» %vote%' . $fromPlayer->nickName . '$z$s%vote% pays and restarts the challenge');
						if ($this->config->useQueueRestart) {
							$this->callPublicMethod('MLEPP\Jukebox', 'playerQueueRestart', $login);
						} else {
							$this->connection->restartChallenge();
						}
					}
					unset($this->billId[$data[0]]);
					break;
				}

				if ($state == 5) { // No go
					$login = $data[1];
					$amount = $data[2];
					$fromPlayer = $this->storage->getPlayerObject($login);
					$this->mlepp->sendChat('%error%No coppers billed.', $fromPlayer);

					unset($this->billId[$data[0]]);
					break;
				}

				if ($state == 6) {  // Error
					$login = $data[1];
					$fromPlayer = $this->storage->getPlayerObject($login);
					$this->mlepp->sendChat('%error% There was error with player%variable%' . $fromPlayer->nickName . '$z$s%error% payment.');
					unset($this->billId[$data[0]]);
					break;
				}
			}
		}
	}

}

?>