<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Admin
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
 * You are allowed to change things of use this in other projects, as
 * long as you leave the information at the top (name, date, version,
 * website, package, author, copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */

namespace ManiaLivePlugins\MLEPP\Admin;

use ManiaLivePlugins\MLEPP\Admin\Gui\Windows\PlayersWindow;
use ManiaLivePlugins\MLEPP\Admin\Gui\Windows\AdminWindow;
use ManiaLivePlugins\MLEPP\Admin\Gui\Windows\SimpleWindow;
use ManiaLivePlugins\MLEPP\Admin\Gui\Windows\AdminPanelWindow;
use ManiaLivePlugins\MLEPP\Admin\Gui\Windows\SelectTracklistWindow;
use ManiaLivePlugins\MLEPP\Admin\Gui\Controls\Button;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\DedicatedApi\Structures\Vote;
use ManiaLive\DedicatedApi\Xmlrpc\Exception;
use ManiaLive\Utilities\Console;
use ManiaLive\Gui\Handler;
use ManiaLive\Data\Storage;
use ManiaLive\Gui\Windowing\Window;
use ManiaLive\Event\Dispatcher;
use SimpleXMLElement;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

use ManiaLivePlugins\MLEPP\Admin\Adapter\oliverde8HudMenu;

class Plugin extends \ManiaLive\PluginHandler\Plugin {

	private $AdminCommand = array();
	private $Jukebox = false;
	private $mlepp = null;
	private $votesEnabled = false;
	private $rpoints = array();
	private $updateHour;
	private $config;
	private $defaultTracklist;
	private $descAdmin = "Provides admin commands. For more help see /admin";
	private $descPlayers = "Shows all players on server with given id numbers for usage with other plugins.";
	private $helpAdmin = "The admins can control almost all serversettings from within the game.
The commands structure is based on a certain logic, that is based on 2 main groups.
These main groups are \$oget\$o and \$oset\$o.
There are many set and get commands, an interactive help system will lead you through all possible commands.
Just type \$o/admin set\$o or \$o/admin get\$o to see all possible following commands, add the variable to the command to see the next options etc.
This way you can easily get one step further each time, untill you reach the setting you want.
If you type a wrong admin command, words printed in red show you where you got wrong.

In addition to the get/set system there are some shortcuts for often used commands:
\$owarn, kick, ban\$o in combination with a login for player management.
\$oskip, restart, endround\$o to quickly control the playing track.
\$ocancel\$o to cancel a vote when the mlepp vote plugin isn't used.
\$oremove\$o in combination with the track ID (from /list) or the word \$othis\$o.
\$oadd track local \$o to add tracks stored in /UserData/Maps
\$oadd track mx <MX-ID> \$o to add tracks from Mania Exchange.";

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	 
	function onInit() {
		$this->setVersion(1050);

		$this->setPublicMethod('getVersion');
		$this->setPublicMethod('addAdminCommand');
		$this->setPublicMethod('removeAdminCommand');
		$this->setPublicMethod('saveMatchSettings');

		$this->rpoints['f1gp'] = array('Formula 1 GP Old', array(10, 8, 6, 5, 4, 3, 2, 1));
		$this->rpoints['f1new'] = array('Formula 1 GP New', array(25, 18, 15, 12, 10, 8, 6, 4, 2, 1));
		$this->rpoints['motogp'] = array('MotoGP', array(25, 20, 16, 13, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1));
		$this->rpoints['motogp5'] = array('MotoGP + 5', array(30, 25, 21, 18, 16, 15, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1));
		$this->rpoints['fet1'] = array('Formula ET Season 1', array(12, 10, 9, 8, 7, 6, 5, 4, 4, 3, 3, 3, 2, 2, 2, 1));
		$this->rpoints['fet2'] = array('Formula ET Season 2', array(15, 12, 11, 10, 9, 8, 7, 6, 6, 5, 5, 4, 4, 3, 3, 3, 2, 2, 2, 1));
		$this->rpoints['fet3'] = array('Formula ET Season 3', array(15, 12, 11, 10, 9, 8, 7, 6, 6, 5, 5, 4, 4, 3, 3, 3, 2, 2, 2, 2, 1));
		$this->rpoints['champcar'] = array('Champ Car World Series', array(31, 27, 25, 23, 21, 19, 17, 15, 13, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1));
		$this->rpoints['superstars'] = array('Superstars', array(20, 15, 12, 10, 8, 6, 4, 3, 2, 1));
		$this->rpoints['simple5'] = array('Simple 5', array(5, 4, 3, 2, 1));
		$this->rpoints['simple10'] = array('Simple 10', array(10, 9, 8, 7, 6, 5, 4, 3, 2, 1));

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
		$this->enableTickerEvent();
		$this->config = Config::getInstance();
		$this->mlepp = Mlepp::getInstance();
		
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: Admin r' . $this->getVersion());

		//the actual thing
		$this->addAdminCommand(array($this, 'unBlacklist'), array('set', 'player', 'unblack'), true, false, false);
		$this->addAdminCommand(array($this, 'unban'), array('set', 'player', 'unban'), true, false, false);
		$this->addAdminCommand(array($this, 'unignore'), array('set', 'player', 'unignore'), true, false, false);
		$this->addAdminCommand(array($this, 'warnPlayer'), array('set', 'player', 'warn'), true, false, false);
		$this->addAdminCommand(array($this, 'blacklist'), array('set', 'player', 'black'), true, false, false);
		$this->addAdminCommand(array($this, 'forceSpec'), array('set', 'player', 'spec'), true, false, false);
		$this->addAdminCommand(array($this, 'ignore'), array('set', 'player', 'ignore'), true, false, false);
		$this->addAdminCommand(array($this, 'kick'), array('set', 'player', 'kick'), true, false, false);
		$this->addAdminCommand(array($this, 'ban'), array('set', 'player', 'ban'), true, false, false);

		$this->addAdminCommand(array($this, 'showBlacklist'), array('get', 'player', 'blacklist'), false, false, false);
		$this->addAdminCommand(array($this, 'showIgnorelist'), array('get', 'player', 'ignorelist'), false, false, false);
		$this->addAdminCommand(array($this, 'showBanlist'), array('get', 'player', 'banlist'), false, false, false);

		$this->addAdminCommand(array($this, 'setServerName'), array('set', 'server', 'name'), true, false, false);
		$this->addAdminCommand(array($this, 'setServerComment'), array('set', 'server', 'comment'), true, false, false);
		$this->addAdminCommand(array($this, 'setServerPassword'), array('set', 'server', 'password'), true, false, false);
		$this->addAdminCommand(array($this, 'setSpecPassword'), array('set', 'server', 'specpassword'), true, false, false);
		$this->addAdminCommand(array($this, 'setRefereePassword'), array('set', 'server', 'refereepassword'), true, false, false);
		$this->addAdminCommand(array($this, 'setServerMaxPlayers'), array('set', 'server', 'maxplayers'), true, false, false);
		$this->addAdminCommand(array($this, 'setHideServer'), array('set', 'server', 'hide'), true, false, false);


		$this->addAdminCommand(array($this, 'setServerMaxSpectators'), array('set', 'server', 'maxspec'), true, false, false);
		$this->addAdminCommand(array($this, 'setServerChallengeDownload'), array('set', 'server', 'challengedownload'), true, false, false);
		$this->addAdminCommand(array($this, 'setServerStop'), array('set', 'server', 'stop'), true, false, false);

		$this->addAdminCommand(array($this, 'saveMatchSettings'), array('set', 'server', 'matchsettings'), true, false, false);

		$this->addAdminCommand(array($this, 'setAllWarmUpDuration'), array('set', 'server', 'warmupduration'), true, false, false);
		$this->addAdminCommand(array($this, 'setGameMode'), array('set', 'server', 'gamemode'), true, false, false);
		$this->addAdminCommand(array($this, 'skipTrack'), array('set', 'server', 'skip'), false, false, false);
		$this->addAdminCommand(array($this, 'restartTrack'), array('set', 'server', 'restart'), false, false, false);
		$this->addAdminCommand(array($this, 'setDisableRespawn'), array('set', 'server', 'respawn'), true, false, false);
		$this->addAdminCommand(array($this, 'setserverchattime'), array('set', 'server', 'chattime'), true, false, false);

		$this->addAdminCommand(array($this, 'loadMatchSettings'), array('get', 'server', 'matchsettings'), false, false, false);
		$this->addAdminCommand(array($this, 'getHideServer'), array('get', 'server', 'hide'), false, false, false);
		$this->addAdminCommand(array($this, 'getServerMaxPlayers'), array('get', 'server', 'maxplayers'), false, false, false); //
		$this->addAdminCommand(array($this, 'getServerMaxSpectators'), array('get', 'server', 'maxspec'), false, false, false);
		$this->addAdminCommand(array($this, 'getServerPasswordForSpectator'), array('get', 'server', 'specpassword'), false, false, false);
		$this->addAdminCommand(array($this, 'getServerPassword'), array('get', 'server', 'password'), false, false, false);
		$this->addAdminCommand(array($this, 'getRefereePassword'), array('get', 'server', 'refereepassword'), false, false, false);
		$this->addAdminCommand(array($this, 'getDisableRespawn'), array('get', 'server', 'respawn'), false, false, false);
		$this->addAdminCommand(array($this, 'getServerChallengeDownload'), array('get', 'server', 'challengedownload'), false, false, false);

		$this->addAdminCommand(array($this, 'getserverchattime'), array('get', 'server', 'chattime'), false, false, false);
		$this->addAdminCommand(array($this, 'shuffleTracks'), array('set', 'server', 'shuffle'), true, false, false);
		$this->addAdminCommand(array($this, 'setCallvoteTimeout'), array('set', 'server', 'votetime'), true, false, false);

		//timeattack
		$this->addAdminCommand(array($this, 'setTAlimit'), array('set', 'ta', 'timelimit'), true, false, false);
		$this->addAdminCommand(array($this, 'setAllWarmUpDuration'), array('set', 'ta', 'warmupduration'), true, false, false);

		//rounds

		$this->addAdminCommand(array($this, 'forceEndRound'), array('set', 'rounds', 'end'), false, false, false);

		$this->addAdminCommand(array($this, 'setRoundPointsLimit'), array('set', 'rounds', 'pointslimit'), true, false, false);
		$this->addAdminCommand(array($this, 'setRoundForcedLaps'), array('set', 'rounds', 'forcedlaps'), true, false, false);
		$this->addAdminCommand(array($this, 'setUseNewRulesRound'), array('set', 'rounds', 'newrules'), true, false, false);
		$this->addAdminCommand(array($this, 'setAllWarmUpDuration'), array('set', 'rounds', 'warmupduration'), true, false, false);

		//laps
		$this->addAdminCommand(array($this, 'setLapsTimeLimit'), array('set', 'laps', 'timelimit'), true, false, false);
		$this->addAdminCommand(array($this, 'setNbLaps'), array('set', 'laps', 'nblaps'), true, false, false);
		$this->addAdminCommand(array($this, 'setFinishTimeout'), array('set', 'laps', 'finishtimeout'), true, false, false);
		$this->addAdminCommand(array($this, 'setAllWarmUpDuration'), array('set', 'laps', 'warmupduration'), true, false, false);

		//team
		$this->addAdminCommand(array($this, 'setTeamPointsLimit'), array('set', 'team', 'pointslimit'), true, false, false);
		$this->addAdminCommand(array($this, 'setMaxPointsTeam'), array('set', 'team', 'maxpoints'), true, false, false);
		$this->addAdminCommand(array($this, 'setUseNewRulesTeam'), array('set', 'team', 'newrules'), true, false, false);
		$this->addAdminCommand(array($this, 'forcePlayerTeam'), array('set', 'team', 'player'), true, true, true);
		$this->addAdminCommand(array($this, 'setAllWarmUpDuration'), array('set', 'team', 'warmupduration'), true, false, false);

		//cup
		$this->addAdminCommand(array($this, 'setCupPointsLimit'), array('set', 'cup', 'pointslimit'), true, false, false);
		$this->addAdminCommand(array($this, 'setCupRoundsPerChallenge'), array('set', 'cup', 'roundsperchallenge'), true, false, false);
		$this->addAdminCommand(array($this, 'setCupWarmUpDuration'), array('set', 'cup', 'warmupduration'), true, false, false);
		$this->addAdminCommand(array($this, 'setCupNbWinners'), array('set', 'cup', 'nbwinners'), true, false, false);
		$this->addAdminCommand(array($this, 'prepareRoundPoints'), array('set', 'cup', 'custompoints'), true, false, false);
		$this->addAdminCommand(array($this, 'setFinishTimeout'), array('set', 'cup', 'finishtimeout'), true, false, false);
		
		// Admin groups
		$this->addAdminCommand(array($this, 'addAdmin'), array('add', 'admin'), true, true, false);
		$this->addAdminCommand(array($this, 'removeAdmin'), array('remove', 'admin'), true, false, false);

		//shortcuts
		$this->addAdminCommand(array($this, 'kick'), array('kick'), true, false, false);
		$this->addAdminCommand(array($this, 'ban'), array('ban'), true, false, false);
		$this->addAdminCommand(array($this, 'unban'), array('unban'), true, false, false);
		$this->addAdminCommand(array($this, 'blacklist'), array('black'), true, false, false);
		$this->addAdminCommand(array($this, 'unBlacklist'), array('unblack'), true, false, false);
		$this->addAdminCommand(array($this, 'ignore'), array('ignore'), true, false, false);
		$this->addAdminCommand(array($this, 'unignore'), array('unignore'), true, false, false);
		$this->addAdminCommand(array($this, 'skipTrack'), array('skip'), false, false, false);
		$this->addAdminCommand(array($this, 'restartTrack'), array('restart'), false, false, false);
		$this->addAdminCommand(array($this, 'warnPlayer'), array('warn'), true, false, false);
		$this->addAdminCommand(array($this, 'forceEndRound'), array('endround'), false, false, false);
		$this->addAdminCommand(array($this, 'cancelVote'), array('vote', 'deny'), false, false, false);
		$this->addAdminCommand(array($this, 'passVote'), array('vote', 'pass'), false, false, false);
		$this->addAdminCommand(array($this, 'saveMatchSettings'), array('savetracklist'), true, false, false);
		$this->addAdminCommand(array($this, 'loadMatchSettings'), array('loadtracklist'), true, false, false);
		$this->addAdminCommand(array($this, 'selectTracklist'), array('selecttracklist'), false, false, false);
		$this->addAdminCommand(array($this, 'shuffleTracks'), array('shuffle'), true, false, false);

		//spesific settings
		$this->addAdminCommand(array($this, 'pay'), array('set', 'server', 'pay'), true, true, false);
		$this->addAdminCommand(array($this, 'getPlanets'), array('get', 'server', 'planets'), false, false, false);


		if ($this->isPluginLoaded('Standard\Menubar')) {
			$this->buildAdminMenu();
		}

		if ($this->isPluginLoaded('MLEPP\Jukebox', 377)) {
			$this->Jukebox = true;
		}

		if ($this->isPluginLoaded('MLEPP\Votes')) {
			$this->votesEnabled = true;
		}

		$this->updateHour = rand(1, 8);
	}

	function onUnLoad() {
		Console::println('[' . date('H:i:s') . '] [UNLOAD] Admin r' . $this->getVersion() . '');
		parent::onUnload();
	}

	 /**
	 * buildAdminMenu()
	 * Adds buttons to the standard Menubar.
	 *
	 * @return void
	 */
	function buildAdminMenu() {
		$this->callPublicMethod('Standard\Menubar', 'initMenu', Icons128x128_1::Options);
		$this->callPublicMethod('Standard\Menubar', 'addButton', 'Show Blacklist', array($this, 'showBlacklist'), true);
		$this->callPublicMethod('Standard\Menubar', 'addButton', 'Show Bans', array($this, 'showBanlist'), true);
		$this->callPublicMethod('Standard\Menubar', 'addButton', 'Show Ignores', array($this, 'showIgnorelist'), true);
		$this->callPublicMethod('Standard\Menubar', 'addButton', 'Manage Players', array($this, 'adminPlayers'), true);
	}

	 /**
	 * onOliverde8HudMenuReady()
	 * Function used for adding buttons to Olivers Hud Menu.
	 *
	 * @param mixed $menu
	 * @return void
	 */
	public function onOliverde8HudMenuReady($menu) {
		new oliverde8HudMenu($this, $menu, $this->storage, $this->connection);
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
		if ($this->mlepp->AdminGroup->hasPermission($login, 'adminPanel')) {
			$this->showAdminPanel($login);
			return;
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

	}

	 /**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */
	function onReady() {
		$cmd = $this->registerChatCommand("admin", "admin", 5, true);
		$cmd->help = $this->descAdmin;
		$cmd = $this->registerChatCommand("admin", "admin", 4, true);
		$cmd->help = $this->descAdmin;
		$cmd = $this->registerChatCommand("admin", "admin", 3, true);
		$cmd->help = $this->descAdmin;
		$cmd = $this->registerChatCommand("admin", "admin", 2, true);
		$cmd->help = $this->descAdmin;
		$cmd = $this->registerChatCommand("admin", "admin", 1, true);
		$cmd->help = $this->descAdmin;
		$cmd = $this->registerChatCommand("admin", "admin", 0, true);
		$cmd->help = $this->descAdmin;
		$cmd = $this->registerChatCommand("players", "players", 0, true);
		$cmd->help = $this->descPlayers;

		// show adminpanel at manialive restart
		foreach ($this->storage->players as $login => $player) {
			if ($this->mlepp->AdminGroup->hasPermission($login, 'adminPanel')) {
				$this->showAdminPanel($login);
			}
		}
		// show adminpanel also to admins who spectate
		foreach ($this->storage->spectators as $login => $player) {
			if ($this->mlepp->AdminGroup->hasPermission($login, 'adminPanel')) {
				$this->showAdminPanel($login);
			}
		}
	}

	 /**
	 * onTick()
	 * Function called every second.
	 *
	 * @return void
	 */
	function onTick() {
		if (date("H:i:s") == "0" . $this->updateHour . ":00:00") {
			$this->syncGlobalBlackList();
		}
	}

	 /**
	 * cancelVote()
	 * Function calls Votes to cancel the vote.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function cancelVote($login) {
		$this->connection->cancelVote();
		if ($this->isPluginLoaded('MLEPP\Votes')) {
			$this->callPublicMethod('MLEPP\Votes', 'voteCommand', $login, "cancel");
		}
	}

	 /**
	 * passVote()
	 * Function calls Votes to pass the vote.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function passVote($login) {
		if ($this->isPluginLoaded('MLEPP\Votes')) {
			$this->callPublicMethod('MLEPP\Votes', 'voteCommand', $login, "pass");
		}
	}

	function addAdmin($fromLogin, $login, $group = false) {
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$player = Storage::GetInstance()->getPlayerObject($login);
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, 'manageAdmins')) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		if (AdminGroup::contains($login)) {
			$this->mlepp->sendChat('%adminerror%Cannot add login:%variable% ' . $login . '%adminerror% to administrators, login is defined at config.ini', $fromLogin);
			return;
		}

		if (in_array($login, $this->mlepp->AdminGroup->getAdmins())) {
			$this->mlepp->sendChat('%adminerror%Cannot add login:%variable% ' . $login . '%adminerror% from administrators, login is already an admin', $fromLogin);
			return;
		}

		if (empty($login)) {
			$this->mlepp->sendChat('%variable%/admin add admin%adminerror% takes login as second parameter and admin group as third parameter.', $fromLogin);
			return;
		}
		if (!$this->mlepp->isPlayerOnline($login)) {
			$this->mlepp->sendChat("%adminerror%Couldn't add player with login %variable%" . $login . '%adminerror% to admins, since the login doesn\'t exists on server.', $fromLogin);
			return;
		}
		if (empty($group)) {
			$group = 'admin';
		} else {
			$admingroups = $this->mlepp->AdminGroup->getGroups();
			if (!in_array($group, $admingroups)) {
				$this->mlepp->sendChat('%adminerror%Error: group %variable%' . $group . '%adminerror% is not set in config file. Adding a new admin failed.', $fromLogin);
				return;
			}
		}

		$this->mlepp->AdminGroup->addAdmin($login, $group);
		$this->showAdminPanel($login);
		$this->mlepp->AdminGroup->saveSettings();

		$groups = $this->mlepp->AdminGroup->getAdminGroups($login);
		$title = $this->mlepp->AdminGroup->getTitle($groups[0]);
		$this->mlepp->sendChat('%adminaction%' . $title . ' %variable%' . $admin->nickName . '$z$s%adminaction% adds new server admin %variable%' . $player->nickName . '$z$s%adminaction% to group: %variable%' . $group . '!');
	}

	/*
	 * REMOVE ADMIN
	 *
	 *
	 */

	function removeAdmin($fromLogin, $login) {
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$player = Storage::GetInstance()->getPlayerObject($login);
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, 'manageAdmins')) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($login)) {
			$this->mlepp->sendChat('%variable%/admin remove admin%adminerror% takes login as second parameter', $fromLogin);
			return;
		}

		if (AdminGroup::contains($login)) {
			$this->mlepp->sendChat('%adminerror%Cannot remove login:%variable% ' . $login . '%adminerror% from administrators, login is defined at config.ini', $fromLogin);
			return;
		}

		if (!in_array($login, $this->mlepp->AdminGroup->getAdmins())) {
			$this->mlepp->sendChat('%adminerror%Cannot remove login:%variable% ' . $login . '%adminerror% from administrators, login has not defined as admin', $fromLogin);
			return;
		}


		$this->mlepp->AdminGroup->removeAdmin($login);

		$groups = $this->mlepp->AdminGroup->getAdminGroups($fromLogin);
		$title = $this->mlepp->AdminGroup->getTitle($groups[0]);

		$this->mlepp->AdminGroup->saveSettings();
		if ($this->mlepp->isPlayerOnline($login)) {
			$this->mlepp->sendChat('%adminaction%' . $title . ' %variable%' . $admin->nickName . '$z$s%adminaction% sets  %variable%' . $player->nickName . '$z$s%adminaction% back to normal player!');
			$this->hideAdminPanel($login);
		} else {
			$this->mlepp->sendChat('%adminaction%' . $title . ' %variable%' . $admin->nickName . '$z$s%adminaction% sets  %variable%' . $login . '$z$s%adminaction% back to normal player!');
		}
	}

	function manualAddBlacklist($login) {
		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$configDir = $dataDir . "/Config/";

		if (file_exists($configDir . "blacklist.txt")) {
			$xml2 = simplexml_load_file($configDir . "blacklist.txt");
			$serverBlackList = array();
			foreach ($xml2->player as $data) {
				$serverBlackList[] = (string) $data->login;
			}
			$generateFile = false;
		} else {
			$serverBlackList = array();
			$generateFile = true;
		}

		if ($generateFile) {
			$add = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><blacklist></blacklist>');
		} else {
			$add = new SimpleXMLElement($xml2->asXML());
		}

		$player = $add->addChild('player');
		$player->addChild('login', $login);

		file_put_contents($configDir . "blacklist.txt", $add->asXML());
		$this->connection->loadBlackList($configDir . "blacklist.txt");
	}

	 /**
	 * syncGlobalBlackList()
	 * Function synchronizes the global blacklist.
	 *
	 * @return void
	 */
	function syncGlobalBlackList() {
		// disabled globalBlaclist Sync for maniaplanet.
		if ($this->mlepp->gameVersion->name == "ManiaPlanet")
			return;

		$this->mlepp->sendChat("Dedimania blacklist sync start.");
		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$configDir = $dataDir . "/Config/";

		$xml = simplexml_load_file("http://www.gamers.org/tmf/dedimania_blacklist.txt");
		$dediBlackList = array();
		foreach ($xml->player as $data) {
			$dediBlackList[] = (string) $data->login;
		}

		if (file_exists($configDir . "blacklist.txt")) {
			$xml2 = simplexml_load_file($configDir . "blacklist.txt");
			$serverBlackList = array();
			foreach ($xml2->player as $data) {
				$serverBlackList[] = (string) $data->login;
			}
			$generateFile = false;
		} else {
			$serverBlackList = array();
			$generateFile = true;
		}

		$diffList = array_diff($dediBlackList, $serverBlackList);
		$x = 0;

		if ($generateFile) {
			$add = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><blacklist></blacklist>');
		} else {
			$add = new SimpleXMLElement($xml2->asXML());
		}

		foreach ($diffList as $data) {
			$player = $add->addChild('player');
			$player->addChild('login', $data);
			$x++;
		}

		$this->mlepp->sendChat("%adminaction%Dedimania blacklist: merged %variable%$x %adminaction%new logins.");
		file_put_contents($configDir . "blacklist.txt", $add->asXML());
		$this->connection->loadBlackList($configDir . "blacklist.txt");
	}

	 /**
	 * showAdminPanel()
	 * Function shows the admin panel.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function showAdminPanel($login) {
		$panel = AdminPanelWindow::Create($login);
		$panel->clearItems();

		$item = new Button("Icons64x64_1", "QuitRace", "endRound");
		$item->callBack = array($this, 'panelCommand');
		$panel->addItem($item);
		//empty to make room
		$item = new Button("Bgs1", "BgEmpty", "empty");
		$item->callBack = array($this, 'panelCommand');
		$panel->addItem($item);
		//restart
		$item = new Button("Icons64x64_1", "ClipRewind", "restart");
		$item->callBack = array($this, 'panelCommand');
		$panel->addItem($item);
		//queue Restart
		if ($this->Jukebox) {
			$item = new Button("Icons64x64_1", "Refresh", "queueRestart");
			$item->callBack = array($this, 'panelCommand');
			$panel->addItem($item);
		}
		//skip
		$item = new Button("Icons64x64_1", "ClipPlay", "skip");
		$item->callBack = array($this, 'panelCommand');
		$panel->addItem($item);
		//----empty value--
		$item = new Button("Bgs1", "BgEmpty", "empty");
		$item->callBack = array($this, 'panelCommand');
		$panel->addItem($item);
		//players
		$item = new Button("Icons64x64_1", "Buddy", "players");
		$item->callBack = array($this, 'panelCommand');
		$panel->addItem($item);

		$panel->setSize(48, 8);

		$pos = explode(",", $this->config->adminPanelPosition);
		$panel->setPosition($pos[0], $pos[1]);
		$panel->setScale(0.9);
		$panel->show();
	}

	 /**
	 * hideAdminPanel()
	 * Function shows the admin panel.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function hideAdminPanel($login) {
		$panel = AdminPanelWindow::Erase($login);
	}

	 /**
	 * addAdminCommand()
	 * Helper function, adds admin command.
	 *
	 * @param mixed $callback
	 * @param mixed $commandname
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @param mixed $help
	 * @param mixed $plugin
	 * @return void
	 */
	function addAdminCommand($callback, $commandname, $param1 = null, $param2 = null, $param3 =null, $help = null, $plugin=null) {
		if (!is_array($commandname))
			$commandname = array($commandname);
		$aCommand = array();
		$aCommand['params'][] = $param1;
		$aCommand['params'][] = $param2;
		$aCommand['params'][] = $param3;
		$aCommand['callback'] = $callback;
		$aCommand['commandNb'] = count($commandname);
		$this->createArrayEntry($this->AdminCommand, $commandname, $aCommand);
	}

	 /**
	 * createArrayEntry()
	 * Helper function, creates array entry.
	 *
	 * @param string $command
	 * @param mixed $e
	 * @param mixed $val
	 * @return void
	 */
	function removeAdminCommand($e, $plugin = NULL) {
		if (!is_array($e)) {
			unset($this->AdminCommand[$e]);
			return;
		}
		$count = count($e);
		switch ($count) {
			case 1:
				unset($this->AdminCommand[$e[0]]);
				break;
			case 2:
				unset($this->AdminCommand[$e[0]][$e[1]]);
				break;
			case 3:
				unset($this->AdminCommand[$e[0]][$e[1]][$e[2]]);
				break;
			case 4:
				unset($this->AdminCommand[$e[0]][$e[1]][$e[2]][$e[3]]);
				break;
			case 5:
				unset($this->AdminCommand[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]]);
				break;
			case 6:
				unset($this->AdminCommand[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]][$e[5]]);
				break;
		}
	}

	 /**
	 * createArrayEntry()
	 * Helper function, creates array entry.
	 *
	 * @param mixed $arr
	 * @param mixed $e
	 * @param mixed $val
	 * @return void
	 */
	function createArrayEntry(&$arr, $e, &$val) {
		$count = count($e);

		switch ($count) {
			case 1:
				$arr[$e[0]] = $val;
				break;
			case 2:
				$arr[$e[0]][$e[1]] = $val;
				break;
			case 3:
				$arr[$e[0]][$e[1]][$e[2]] = $val;
				break;
			case 4:
				$arr[$e[0]][$e[1]][$e[2]][$e[3]] = $val;
				break;
			case 5:
				$arr[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]] = $val;
				break;
			case 6:
				$arr[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]][$e[5]] = $val;
				break;
		}
	}

	 /**
	 * checkcArrayKeys()
	 * Helper function, checks array keys.
	 *
	 * @param mixed $arr
	 * @param mixed $e
	 * @return
	 */
	function checkcArrayKeys(&$arr, $e) {
		if (isset($arr[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]][$e[5]]))
			return 6;
		if (isset($arr[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]]))
			return 5;
		if (isset($arr[$e[0]][$e[1]][$e[2]][$e[3]]))
			return 4;
		if (isset($arr[$e[0]][$e[1]][$e[2]]))
			return 3;
		if (isset($arr[$e[0]][$e[1]]))
			return 2;
		if (isset($arr[$e[0]]))
			return 1;
	}

	 /**
	 * adminhelp()
	 * Function providing the /admin help command.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $command
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function adminhelp($fromLogin, $command = NULL, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
	}

	 /**
	 * array_searchMultiOnKeys()
	 * Helper function, search on multiple keys.
	 *
	 * @param mixed $multiArray
	 * @param mixed $searchKeysArray
	 * @param mixed $innerarray
	 * @return
	 */
	function array_searchMultiOnKeys($multiArray, $searchKeysArray, $innerarray = array()) {


		if (in_array($searchKeysArray[0], array_keys($multiArray))) {
			$result = $multiArray[$searchKeysArray[0]]; // Iterate through searchKeys, making $multiArray smaller and smaller.

			if (is_array($result)) { // if result is an array, continue
				array_shift($searchKeysArray);  //shift the search arraykeys by one

				if (is_array($searchKeysArray)) {   // if there is arraykeys left iterate
					$innerarray = $this->array_searchMultiOnKeys($result, $searchKeysArray, $result);
				} else {  //else return resultset.
					$innerarray = $result;
				}
			}
		}
		return $innerarray;  // return final result.
	}

	 /**
	 * admin()
	 * Provides the /admin commands.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @param mixed $param4
	 * @param mixed $param5
	 * @return
	 */
	function admin($login, $param = NULL, $param1 = NULL, $param2 = NULL, $param3 = NULL, $param4 = NULL, $param5 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($login, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $login);
			return;
		}

		if ($param == NULL || $param == 'help') {
			$this->showHelp($login, $this->helpAdmin);
			return;
		}

		$commandFound = false;
		$adminparams = array($param, $param1, $param2, $param3, $param4, $param5);
		$adminCommand = $this->AdminCommand;
		$tree = ($this->array_searchMultiOnKeys($adminCommand, $adminparams));
		if (isset($tree['params'])) {

			$paramscount = 0;
			foreach ($tree['params'] as $para) {
				if ($para === true)
					$paramscount++;
			}
			$adminparam = 0;
			foreach ($adminparams as $para) {
				if ($para !== NULL)
					$adminparam++;
			}

			$validCmdNumber = $this->checkcArrayKeys($adminCommand, $adminparams);
			/* 	if ($diff != $tree['commandNb']) {
			  $this->mlepp->sendChat('$fffParameters count mismatch!',$login);
			  return;
			  } */

			switch ($paramscount) {
				case 0:
					$commandFound = true;
					call_user_func_array($tree['callback'], array($login));
					break;
				case 1:
					$commandFound = true;
					//if ($adminparams[$diff] === NULL) $this->adminParameterError($login, 1);
					call_user_func_array($tree['callback'], array($login, $adminparams[$validCmdNumber]));
					break;
				case 2:
					$commandFound = true;
					//if ($adminparams[$diff] === NULL && $adminparams[$diff+1] === NULL) $this->adminParameterError($login, 2);
					call_user_func_array($tree['callback'], array($login, $adminparams[$validCmdNumber], $adminparams[$validCmdNumber + 1]));
					break;
				case 3:
					$commandFound = true;
					//if ($adminparams[$diff] === NULL && $adminparams[$diff+1] === NULL && $adminparams[$diff+2] === NULL) $this->adminParameterError($login, 3);
					call_user_func_array($tree['callback'], array($login, $adminparams[$validCmdNumber], $adminparams[$validCmdNumber + 1], $adminparams[$validCmdNumber + 2]));
					break;
			}
		} else {
			//show help;

			if (count($tree) != 0) {
				$adminCommandCount = count($tree);

				$validCmdNumber = $this->checkcArrayKeys($adminCommand, $adminparams);
				$x = 0;
				$scope = "";
				$invalid = "";
				foreach ($adminparams as $data) {
					if ($data != null) {
						if ($x < $validCmdNumber) {
							$scope .= $data . " ";
							$x++;
						} else {
							$invalid .= $data . " ";
						}
					}
				}
				$scope = substr($scope, 0, -1);
				$invalid = substr($invalid, 0, -1);
				$help = '$fffInvalid admin command:$0f0$o/admin ' . $scope . '$f00 ' . $invalid . '$z$s$fff' . "\n";
				$help .= '$fffAvailable next commands in $fc4$o/admin ' . $scope . '$z$s$fff are:' . "\n" . ' $fc4$o';
				foreach (array_keys($tree) as $param) {
					$help .= '$fc4' . $param . '$fff, ';
				}
			} else {
				$help = '$fffPossible next admin commands are: $fc4$o';
				foreach (array_keys($adminCommand) as $param) {
					$help .= '$fc4' . $param . '$fff, ';
				}
			}
			$this->mlepp->sendChat(substr($help, 0, -2), $login);
		}
	}

	 /**
	 * adminParameterError()
	 * Function sends out a parameter error.
	 *
	 * @param mixed $login
	 * @param mixed $number
	 * @return void
	 */
	function adminParameterError($login, $number) {
		$this->mlepp->sendChat("%adminerror%Wrong number of parameters given. The admin command you entered takes %variable%$number %adminerror%of parameters!");
		Console::println('[' . date('H:i:s') . '] [MLEPP] [AdminPanel] [' . $login . '] Wrong number of parameters given.');
	}

	 /**
	 * setServerName()
	 * Admin function, sets servername.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setServerName($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->mlepp->sendChat('%adminerror%/admin set server name takes a servername as a parameter, none entered.', $fromLogin);
			return;
		}

		try {
			$this->connection->setServerName($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets new server name: %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setServerComment()
	 * Admin function, sets server comment.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function setServerComment($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$param1 = "";
		}

		try {
			$this->connection->setServerComment($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets new server comment: %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setServerMaxPlayers()
	 * Admin function, sets maximum players.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setServerMaxPlayers($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}
		if ($param1 > 150) {
			$this->mlepp->sendChat('%adminerror%Parameter value too big. Max players is limited to 150.', $fromLogin);
			return;
		}
		try {
			$this->connection->setMaxPlayers($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets server maximum players to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setServerMaxSpectators()
	 * Admin function, sets maximum spectators.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setServerMaxSpectators($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setMaxSpectators($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets server maximum spectators to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * pay()
	 * Admin function, provides copper pay possibility.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function pay($fromLogin, $param1, $param2, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->mlepp->sendChat('%adminerror%Player %variable%' . $param1 . '$z$s%adminerror% doesn\'t exist.', $fromLogin);
			return;
		}

		if (is_numeric($param2)) {
			$param2 = (int) $param2;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}
		$player = Storage::GetInstance()->getPlayerObject($param1);
		try {
			$this->connection->pay($player, $param2, 'a payment from server: ' . $this->storage->serverLogin);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% pays %variable%' . $param2 . '%adminaction% coppers from server account to %variable%' . $player->nickName);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setServerChallengeDownload()
	 * Admin function, sets the possibility to download challenges.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setServerChallengeDownload($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if ($param1 == 'true' || $param1 == 'false') {
			if ($param1 == 'true')
				$bool = true;
			if ($param1 == 'false')
				$bool = false;
		}
		else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is either true or false.', $fromLogin);
			return;
		}

		try {
			$this->connection->allowChallengeDownload($bool);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% set allow download challenge to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	function setHideServer($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		if (empty($param1)) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameters for command are: 0,1,2,visible,hidden,nations.', $fromLogin);
			return;
		}
		$validValues = array("1", "0", "2", "all", "visible", "both", "nations", "off", "hidden");
		if (in_array(strtolower($param1), $validValues, true)) {
			if ($param1 == 'off' || $param1 == 'visible')
				$output = 0;
			if ($param1 == 'all' || $param1 == 'both' || $param1 == 'hidden')
				$output = 1;
			if ($param1 == 'nations')
				$output = 2;
			if (is_numeric($param1))
				$output = $param1;
		}
		else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameters for command are: 0,1,2,visible,hidden,nations.', $fromLogin);
			return;
		}
		try {
			$this->connection->setHideServer($output);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% set Hide Server to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setDisableRespawn()
	 * Admin function, disables or enables respawn.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setDisableRespawn($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if ($param1 == 'true' || $param1 == 'false') {
			if ($param1 == 'true')
				$bool = false; // reverse the order as the command is for disable;
			if ($param1 == 'false')
				$bool = true; // ^^
		}
		else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is either true or false.', $fromLogin);
			return;
		}

		try {
			$this->connection->setDisableRespawn($bool);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% set allow respawn to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setServerStop()
	 * Admin function, stops dedi, manialive or both.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param
	 * @return void
	 */
	function setServerStop($fromLogin, $param = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		switch ($param) {
			case "dedicated":
				$this->connection->stopServer();
				break;
			case "manialive":
				die();
				break;
			case "both":
				$this->connection->stopServer();
				die();
				break;
			case "all":
				$this->connection->stopServer();
				die();
				break;
			default:
				$this->mlepp->sendChat('Correct values for the command are:%variable% dedicated, manialive, both or all.', $fromLogin);
				break;
		}
	}

	 /**
	 * setServerPassword()
	 * Admin function, sets server password.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function setServerPassword($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$param1 = "";
		}

		try {
			$this->connection->setServerPassword($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets/unsets new server password.');
			$this->mlepp->sendChat('%adminaction%New password: %variable%' . $param1, $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setSpecPassword()
	 * Admin function, sets spectator password.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function setSpecPassword($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$param1 = "";
		}

		try {
			$this->connection->setServerPasswordForSpectator($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets/unsets new spectator password.');
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setRefereePassword()
	 * Admin function, sets referee password.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function setRefereePassword($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$param1 = "";
		}
		try {
			$this->connection->setRefereePassword($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets/unsets new server referee password.');
			$this->mlepp->sendChat('%adminaction%New Password is: %variable%' . $param1, $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * forceEndRound()
	 * Admin function, forces end of the round.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function forceEndRound($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		try {
			$this->connection->forceEndRound($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% forces an endRound.');
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setGameMode()
	 * Admin function, sets gamemode.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setGameMode($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$gamemode = NULL;

		if (strtolower($param1) == "rounds")
			$gamemode = $this->mlepp->getGameModeNumber('rounds');
		if (strtolower($param1) == "timeattack" || strtolower($param1) == "ta")
			$gamemode = $this->mlepp->getGameModeNumber('timeAttack');
		if (strtolower($param1) == "team")
			$gamemode = $this->mlepp->getGameModeNumber('team');
		if (strtolower($param1) == "laps")
			$gamemode = $this->mlepp->getGameModeNumber('laps');
		if (strtolower($param1) == "stunts")
			$gamemode = $this->mlepp->getGameModeNumber('stunts');
		if (strtolower($param1) == "cup")
			$gamemode = $this->mlepp->getGameModeNumber('cup');
		if ($gamemode === NULL) {
			$this->mlepp->sendChat('Usage: /admin set server gamemode team,ta,rounds,laps,stunts,cup ', $fromLogin);
			return;
		}

		try {
			$this->connection->setGameMode($gamemode);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets game mode to %variable%' . $this->mlepp->getGameModeName($gamemode));
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * kick()
	 * Admin function, kicks player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function kick($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->mlepp->sendChat('Player %variable%' . $param1 . '$0ae doesn\' exist.', $fromLogin);
			return;
		}

		$player = Storage::GetInstance()->getPlayerObject($param1);
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		try {
			$this->connection->kick($player);
			$plNick = $player->nickName;
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% kicks the player %variable%' . $player->nickName);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setserverchattime()
	 * Admin function, sets server podium chat time.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @return void
	 */
	function setserverchattime($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter count, use time in format %variable%m:ss', $fromLogin);
			return;
		}

		$timelimit = explode(":", trim($param1));
		if (count($timelimit) == 0 || count($timelimit) != 2) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter count, use time in format %variable%m:ss', $fromLogin);
			return;
		}

		$newLimit = intval($timelimit[0] * 60 * 1000) + ($timelimit[1] * 1000) - 8000;
		if ($newLimit < 0)
			$newLimit = 0;
		try {
			$this->connection->SetChatTime($newLimit);
			$nick = $this->getNick($fromLogin);
			$this->mlepp->sendChat('$z$s$0ae%adminaction%Admin %variable%' . $nick . '$z$s%adminaction% sets new chat time limit of %variable%' . $param1 . '$0ae minutes.');
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getserverchattime()
	 * Admin function, gets server podium chat time.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	function getserverchattime($fromLogin, $param1=NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$time = $this->connection->GetChatTime();
		$time = $time['CurrentValue'];
		if ($time == 0) {
			$seconds = 0;
			$minutes = 0;
		} else {
			$seconds = floor($time / 1000) + 8;
			$minutes = round($seconds / 60, 0);
		}
		$nick = $this->getNick($fromLogin);
		$this->mlepp->sendChat('$z$s$0aeServer chat time limit is %variable%' . $minutes . ':' . $seconds . '$0ae minutes.');
	}

	 /**
	 * blacklist()
	 * Admin function, blacklists player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function blacklist($fromLogin, $param1, $param2 = "", $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1) && !$this->playerExistsDb($param1)) {
			$this->mlepp->sendChat('Player %variable%' . $param1 . '$0ae doesn\' exist.', $fromLogin);
			return;
		}

		$player = Storage::GetInstance()->getPlayerObject($param1);
		if (is_object($player)) {
			$nickname = $player->nickName;
		} else {
			$nickname = $param1;
		}

		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		try {
			if ($this->playerExists($param1)) {
				$this->connection->banAndBlackList($player, $param2, true);
			} else {
				$this->manualAddBlacklist($param1);
			}
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% blacklists the player %variable%' . $nickname);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * ban()
	 * Admin function, bans player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function ban($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->mlepp->sendChat('Player %variable%' . $param1 . '$0ae doesn\' exist.', $fromLogin);
			return;
		}

		$player = Storage::GetInstance()->getPlayerObject($param1);
		if (is_object($player)) {
			$nickname = $player->nickName;
		} else {
			$nickname = $param1;
		}

		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		try {
			$this->connection->ban($player);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% bans the player %variable%' . $nickname);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * unban()
	 * Admin function, unbans player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function unban($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->mlepp->sendChat('/admin set player unban takes a login as a parameter, none entered.', $fromLogin);
			return;
		}
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$player = new \ManiaLive\DedicatedApi\Structures\Player();
		$player->login = $param1;
		try {
			$this->connection->unBan($player);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% unbans the player ' . $player->login);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * unBlacklist()
	 * Admin function, unblacklists player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function unBlacklist($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->mlepp->sendChat('/admin set player unblack takes a s login as a parameter, none entered.', $fromLogin);
			return;
		}
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$player = new \ManiaLive\DedicatedApi\Structures\Player();
		$player->login = $param1;
		try {
			$this->connection->unBlackList($player);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% unblacklists the player ' . $player->login);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * ignore()
	 * Admin function, ignores (mute) player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function ignore($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->mlepp->sendChat('Player %variable%' . $param1 . '$0ae doesn\' exist.', $fromLogin);
			return;
		}

		$player = Storage::GetInstance()->getPlayerObject($param1);
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		try {
			$this->connection->ignore($player);
			$plNick = $player->nickName;
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% Ignores the player %variable%' . $player->nickName);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * toggleMute()
	 * Admin function, toggles mute.
	 *
	 * @param mixed $login
	 * @param mixed $target
	 * @return
	 */
	function toggleMute($login, $target) {

		$ignorelist = $this->connection->getIgnoreList(-1, 0);
		//if ignorelist is empty, then automaticly ignore the player.
		try {
			if (count($ignorelist) > 1) {
				$this->ignore($login, $target);
				return;
			}
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $login);
		}
		// if player found at ignorelist, unignore
		try {
			foreach ($ignorelist as $player) {
				if ($player->login == $target) {
					$this->unignore($login, $target);
					return;
				}
			}
			// else ignore him.
			$this->ignore($login, $target);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $login);
		}
	}

	function isLoginMuted($login) {

		$ignorelist = $this->connection->getIgnoreList(-1, 0);
		//if ignorelist is empty, then automaticly ignore the player.

		if (count($ignorelist) > 1) {
			return false;
		}

		// if player found at ignorelist, unignore
		foreach ($ignorelist as $player) {
			if ($player->login == $login) {
				return true;
			}
		}
		return false;
	}

	 /**
	 * unignore()
	 * Admin function, unignores (unmute) player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function unignore($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->mlepp->sendChat('/admin set player unignore takes a login as a parameter, none entered.', $fromLogin);
			return;
		}
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$player = new \ManiaLive\DedicatedApi\Structures\Player();
		$player->login = $param1;
		try {
			$this->connection->unIgnore($player);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% unIgnores the player ' . $player->login);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * ignorelist()
	 * Admin function, shows ignorelist.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function showIgnorelist($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$ignorelist = $this->connection->getIgnoreList(1000, 0);

		if (count($ignorelist) == 0) {
			$infoWindow = SimpleWindow::Create($fromLogin);
			$infoWindow->setTitle("Notice");
			$infoWindow->setText("The ignorelist of the server is empty.");
			$infoWindow->setSize(100, 100);
			$infoWindow->centerOnScreen();
			$infoWindow->show();
			return;
		}

		$id = 1;
		$window = AdminWindow::Create($fromLogin);
		$window->setSize(124, 61);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Id', 0.1);
		$window->addColumn('Login', 0.8);
		$window->addColumn('unIgnore', 0.1);


		// refresh records for this window ...
		$window->clearItems();
		$id = 1;
		foreach ($ignorelist as $player) {
			$entry = array
				(
				'Id' => array($id, NULL, false),
				'Login' => array($player->login, NULL, false),
				'unIgnore' => array("unIgnore", $player->login, true),
			);
			$id++;
			$window->addAdminItem($entry, array($this, 'onClick'));
		}

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	 /**
	 * banlist()
	 * Admin function, shows banlist.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function showBanlist($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$banList = $this->connection->getBanList(1000, 0);

		if (count($banList) == 0) {
			$infoWindow = SimpleWindow::Create($fromLogin);
			$infoWindow->setTitle("Notice");
			$infoWindow->setText("The banlist of the server is empty.");
			$infoWindow->setSize(100, 100);
			$infoWindow->centerOnScreen();
			$infoWindow->show();
			return;
		}

		$id = 1;
		$window = AdminWindow::Create($fromLogin);
		$window->setSize(180, 100);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Id', 0.1);
		$window->addColumn('Login', 0.8);
		$window->addColumn('unBan', 0.1);


		// refresh records for this window ...
		$window->clearItems();
		$id = 1;
		foreach ($banList as $player) {
			$entry = array
				(
				'Id' => array($id, NULL, false),
				'Login' => array($player->login, NULL, false),
				'unBan' => array("unBan", $player->login, true),
			);
			$id++;
			$window->addAdminItem($entry, array($this, 'onClick'));
		}

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	 /**
	 * forceSpec()
	 * Admin function, forces player into spectator mode.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function forceSpec($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->mlepp->sendChat('Player %variable%' . $param1 . '$0ae doesn\' exist.', $fromLogin);
			return;
		}

		$player = Storage::GetInstance()->getPlayerObject($param1);
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$this->connection->forceSpectator($player, 1);
		$this->connection->forceSpectator($player, 0);
		$plNick = $player->nickName;
		$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% Forces the player %variable%' . $player->nickName . '$z$s%adminaction% to Spectator.');
	}

	 /**
	 * warnPlayer()
	 * Admin function, warns player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function warnPlayer($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->mlepp->sendChat('Player %variable%' . $param1 . '$0ae doesn\'t exist.', $fromLogin);  //fix for notepad++ '
			return;
		}

		$player = Storage::GetInstance()->getPlayerObject($param1);
		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		$plNick = $player->nickName;
		$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% Warned the player %variable%' . $player->nickName);
		$window = SimpleWindow::Create($param1);
		$window->setTitle("Warning!");
		$window->setText($this->config->warningMessage);
		$window->setSize(80, 80);
		$window->centerOnScreen();
		$window->show();
	}

	 /**
	 * setTAlimit()
	 * Admin function, sets Time Attack limit.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setTAlimit($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter count, use time in format %variable%m:ss', $fromLogin);
			return;
		}

		$timelimit = explode(":", trim($param1));
		if (count($timelimit) == 0 || count($timelimit) != 2) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter count, use time in format %variable%m:ss', $fromLogin);
			return;
		}

		$newLimit = ($timelimit[0] * 60 * 1000) + ($timelimit[1] * 1000);

		$this->connection->setTimeAttackLimit($newLimit);
		$nick = $this->getNick($fromLogin);
		$this->mlepp->sendChat('%adminaction%Admin %variable%' . $nick . '$z$s%adminaction% sets new timelimit of %variable%' . $param1 . '$0ae minutes.');
	}

	 /**
	 * getPlanets()
	 * Admin function, gets server coppers.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	function getPlanets($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		try {
			$coppers = $this->connection->getServerPlanets();
			$this->mlepp->sendChat('Server planets: %variable%' . $coppers, $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getDisableRespawn()
	 * Admin function, gets to know if respawn is disabled of not.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	 
	function getDisableRespawn($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		try {
			$respawn = $this->connection->getDisableRespawn();
			if ($respawn['CurrentValue'] == true)
				$respawn1 = "disabled";
			else
				$respawn1 = "enabled";
			if ($respawn['NextValue'] == true)
				$respawn2 = "disabled";
			else
				$respawn2 = "enabled";
			$this->mlepp->sendChat('Current server respawn is %variable%' . $respawn1 . '$0ae and after next challenge: %variable%' . $respawn2, $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getServerComment()
	 * Admin function, gets server comment.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	 
	function getServerComment($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		try {
			$comment = $this->connection->getServerComment();
			$this->mlepp->sendChat('Server comment: %variable%' . $comment, $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getServerName()
	 * Admin function, gets server name.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	 
	function getServerName($fromLogin) {
		try {
			$name = $this->connection->getServerName();
			$this->mlepp->sendChat('Server name: %variable%' . $name, $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getServerPassword()
	 * Admin function, gets server password.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	 
	function getServerPassword($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		try {
			$name = $this->connection->getServerPassword();
			$this->mlepp->sendChat('Server password: %variable%' . $name, $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getServerPasswordForSpectator()
	 * Admin function, gets spectator password.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	 
	function getServerPasswordForSpectator($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		try {
			$name = $this->connection->getServerPasswordForSpectator();
			$this->mlepp->sendChat('Server spectator password: %variable%' . $name, $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getRefereePassword()
	 * Admin function, gets referee password.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	 
	function getRefereePassword($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		try {
			$name = $this->connection->getRefereePassword();
			$this->mlepp->sendChat('Referee password: %variable%' . $name, $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getServerMaxPlayers()
	 * Admin function, gets maximum players.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	 
	function getServerMaxPlayers($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		try {
			$data = $this->connection->getMaxPlayers();
			$this->mlepp->sendChat('Current server maximum players %variable%' . $data['CurrentValue'] . '$0ae and after next challenge: %variable%' . $data['NextValue'], $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getServerMaxSpectators()
	 * Admin function, gets maximum spectators.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	 
	function getServerMaxSpectators($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		try {
			$data = $this->connection->getMaxSpectators();
			$this->mlepp->sendChat('Current server maximum players %variable%' . $data['CurrentValue'] . '$0ae and after next challenge: %variable%' . $data['NextValue'], $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getServerChallengeDownload()
	 * Admin function, gets possibility on downloading challenges from the server.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	 
	function getServerChallengeDownload($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}
		try {
			$data = $this->connection->isChallengeDownloadAllowed();
			if ($data == true)
				$data = "true";
			else
				$data = "false";
			$this->mlepp->sendChat('Server challenge download allowed: %variable%' . $data, $fromLogin);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getHideServer()
	 * Admin function, gets possibility on downloading challenges from the server.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	function getHideServer($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$data = $this->connection->getHideServer();
		switch ($data) {
			case 0: $output = "visible";
				break;
			case 1: $output = "always hidden";
				break;
			case 2: $output = "hidden from nations";
				break;
			default: $output = "undefined";
				break;
		}
		$this->mlepp->sendChat('is server hidden: %variable%' . $output, $fromLogin);
	}

	 /**
	 * skipTrack()
	 * Admin function, skips the current track.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function skipTrack($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		try {
			$this->connection->nextChallenge();
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% skipped the Challenge');
		} catch (Exception $e) {
			//Console::println("Error:\n".$e->getMessage());
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			//$this->mlepp->sendChat('%adminerror%Change in progress. Please be patient.');
		}
	}

	 /**
	 * restartTrack()
	 * Admin function, restarts the current track.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function restartTrack($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
		try {
			$this->connection->restartChallenge();
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% restarted the Challenge');
		} catch (Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	function syncMatchlist($param1 = null) {
		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$matchsettings = $dataDir . "Maps/MatchSettings/";

		$tracklist = "tracklist.txt";

		if ($param1 != NULL)
			$tracklist = $param1;
		try {
			if (file_exists($matchsettings . $tracklist)) {
				$this->connection->loadMatchSettings($matchsettings . $tracklist);
				foreach ($this->storage->players as $player) {
					if ($this->mlepp->AdminGroup->hasPermission($player->login, "admin")) {
						$this->mlepp->sendChat('%adminaction%Using tracklist named %variable%' . $tracklist . '%adminaction%!', $player->login);
					}
				}
			} else {
				foreach ($this->storage->players as $player) {
					if ($this->mlepp->AdminGroup->hasPermission($player->login, "admin")) {
						$this->mlepp->sendChat('%adminerror%Tracklist named %variable%' . $tracklist . '%adminerror% does not exist!', $player->login);
					}
				}
				Console::println("MLEPP error, default tracklist not defined or cannot be found at filesystem.HALT.");
				die();
			}
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * loadMatchSettings()
	 * Admin function, loads MatchSettings.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function loadMatchSettings($fromLogin = NULL, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$matchsettings = $dataDir . "Maps/MatchSettings/";
		$tracklist = $this->mlepp->depot("admin")->get("settings")->defaultTracklist;
		
		if(empty($tracklist)) {
			$this->selectTracklist($fromLogin);
			return;
		} 
		
		if ($param1 != NULL)
			$tracklist = $param1;
		try {
			if ($this->checkMatchSettingsFile($tracklist)) {
				$this->connection->loadMatchSettings($matchsettings . $tracklist);
				$this->mlepp->sendChat('%adminaction%Tracklist %variable%' . $tracklist . ' %adminaction% loaded successfully!', $fromLogin);
			} else {
				$this->mlepp->sendChat('%adminerror%Tracklist named %variable%' . $tracklist . '%adminerror% does not exist!', $fromLogin);
			}
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}
	
	function selectTracklist($login) {
		$window = SelectTracklistWindow::Create($login);
		$window->setSize(200, 110);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Filename', 0.6);
		$window->addColumn('Action', 0.2);

		// refresh records for this window ...
		$window->clearItems();


		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$challengeDir = $dataDir . "Maps/MatchSettings";


		$localFiles = scandir($challengeDir);

		foreach ($localFiles as $file) {
				if ($file == ".")
						continue;
				 if ($file == "..")
					 continue;
				 
				//if (!stristr($file, ".txt"))
				//		continue;

				$entry = array
					(
					'Filename' => array(utf8_encode($file), NULL, false),
					'Action' => array("Select", array(($challengeDir . "/" . $file), $file), false)
				);
			
			$window->addAdminItem($entry, array($this, 'onFileClick'));
		}

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}
		
	function onFileClick($login, $action, $target) {
		if ($action == "Select") {
			try {
				// $target[0] = full filename with path
				// $target[1] = only filename
				$this->mlepp->depot("admin")->get("settings")->defaultTracklist = $target[1];
				$this->mlepp->sendChat("%adminaction% Selected %variable%".$target[1]."%adminaction% as new tracklist file, no changes made for Save or Load use the admin commands!",$login);
				} catch (\Exception $e) {
				$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $login);
			}
		}
	}
	

	function checkMatchSettingsFile($tracklist) {
		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$matchsettings = $dataDir . "Maps/MatchSettings/";

		try {
			if (file_exists($matchsettings . $tracklist)) {
					return true;
				} else {
					return false;
				}
			} catch (\Exception $e) {
			return false;
		}
	}

	 /**
	 * saveMatchSettings()
	 * Admin function, saves MatchSettings.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @param mixed $fromPlugin
	 * @return void
	 */
	function saveMatchSettings($fromLogin = NULL, $param1 = NULL, $param2 = NULL, $param3 = NULL, $fromPlugin = false) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);

		$matchsettings = $dataDir . "Maps/MatchSettings/";
		
		$tracklist = $this->mlepp->depot("admin")->get("settings")->defaultTracklist;
		if(empty($tracklist)) {
			$this->selectTracklist($fromLogin);
			return;
		} 
		if ($param1 != NULL && $fromPlugin === false)
			$tracklist = $param1;
		try {
				$this->connection->saveMatchSettings($matchsettings . $tracklist);
				$this->mlepp->sendChat('%adminaction%Tracklist %variable%' . $tracklist . ' %adminaction% saved successfully!', $fromLogin);
				$this->mlepp->depot("admin")->get("settings")->defaultTracklist = $tracklist;
				} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * getNick()
	 * Helper function, gets nickname of playerlogin.
	 *
	 * @param mixed $login
	 * @return
	 */
	function getNick($login) {
		return Storage::getInstance()->getPlayerObject($login)->nickName;
	}

	 /**
	 * playerExists()
	 * Function used for checking if the player exists.
	 *
	 * @param mixed $login
	 * @return
	 */
	function playerExists($login) {
		return $this->mlepp->isPlayerOnline($login);
	}

	function playerExistsDb($login) {
		if (empty($this->mlepp->db))
			return false;
		$q = "SELECT * FROM `players` WHERE `player_login` = " . $this->mlepp->db->quote($login) . ";";
		$query = $this->mlepp->db->query($q);
		if ($query->recordCount() == 0)
			return false;
		return true;
	}

	 /**
	 * showBlacklist()
	 * Admin function, shows blacklist.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function showBlacklist($fromLogin = NULL, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$blacklist = $this->connection->getBlackList(-1, 0);
		if (count($blacklist) == 0) {
			$infoWindow = SimpleWindow::Create($fromLogin);
			$infoWindow->setTitle("Notice");
			$infoWindow->setText("The blacklist of the server is empty.");
			$infoWindow->setSize(100, 100);
			$infoWindow->centerOnScreen();
			$infoWindow->show();
			return;
		}

		$window = AdminWindow::Create($fromLogin);
		$window->setSize(125, 61);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Id', 0.1);
		$window->addColumn('Login', 0.8);
		$window->addColumn('unBlack', 0.1);


		// refresh records for this window ...
		$window->clearItems();
		$id = 1;

		foreach ($blacklist as $player) {
			$entry = array
				(
				'Id' => array($id, NULL, false),
				'Login' => array($player->login, NULL, false),
				'unBlack' => array("unBlack", $player->login, true),
			);
			$id++;
			$window->addAdminItem($entry, array($this, 'onClick'));
		}

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	 /**
	 * players()
	 * Public function, shows playerlist.
	 *
	 * @param mixed $login
	 * @return
	 */
	function players($login) {
		if ($this->mlepp->AdminGroup->hasPermission($login, 'players_adminpanel')) {
			$this->adminPlayers($login);
			return;
		}

		$window = PlayersWindow::Create($login);
		$window->setSize(180, 120);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Id', 0.05);
		$window->addColumn('Spec', 0.05);
		$window->addColumn('Login', 0.45);
		$window->addColumn('NickName', 0.45);

		// refresh records for this window ...
		$window->clearItems();
		$id = 1;
		foreach ($this->storage->players as $player) {
			$entry = array
				(
				'Id' => $id,
				'Spec' => "Race",
				'Login' => $player->login,
				'NickName' => $player->nickName,
			);
			$id++;
			$window->addItem($entry);
		}
		unset($player);
		foreach ($this->storage->spectators as $player) {
			$entry = array
				(
				'Id' => $id,
				'Spec' => "Spec",
				'Login' => $player->login,
				'NickName' => $player->nickName,
			);
			$id++;
			$window->addItem($entry);
		}



		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	 /**
	 * adminPlayers()
	 * Admin function, shows playerlist.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function adminPlayers($login) {

		$window = AdminWindow::Create($login);
		$window->setSize(180, 120);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Id', 0.05);
		$window->addColumn('isSpec', 0.05);
		$window->addColumn('Login', 0.3);
		$window->addColumn('NickName', 0.3);
		$window->addColumn('Spec', 0.05);
		$window->addColumn('Warn', 0.05);
		$window->addColumn('Mute', 0.05);
		$window->addColumn('Kick', 0.05);
		$window->addColumn('Ban', 0.05);
		$window->addColumn('Black', 0.05);

		// refresh records for this window ...
		$window->clearItems();
		$id = 1;
		foreach ($this->storage->players as $player) {

			// if target player is admin, restrict from Mute, Ban and Blacklisting him.
			/* if(in_array($player->login,AdminGroup::get())) {
			  $target = NULL;
			  }
			  else {
			  $target = $player->login;
			  } */
			$target = $player->login;
			if ($this->isLoginMuted($target)) {
				$mute = array("unMute", $target, true);
			} else {
				$mute = array("Mute", $target, true);
			}
			$entry = array
				(
				'Id' => array($id, NULL, false),
				'isSpec' => array("isRace", NULL, false),
				'Login' => array($player->login, NULL, false),
				'NickName' => array($player->nickName, NULL, false),
				'Spec' => array("Spec", $player->login, true),
				'Warn' => array("Warn", $player->login, true),
				'Mute' => $mute,
				'Kick' => array("Kick", $player->login, true),
				'Ban' => array("Ban", $target, true),
				'Black' => array("Black", $target, true)
			);
			$id++;
			$window->addAdminItem($entry, array($this, 'onClick'));
		}

		foreach ($this->storage->spectators as $player) {

			// if target player is admin, restrict from Mute, Ban and Blacklisting him.
			/* if(in_array($player->login,AdminGroup::get())) {
			  $target = NULL;
			  }
			  else {
			  $target = $player->login;
			  }
			 */
			$target = $player->login;
			
			if ($this->isLoginMuted($target)) {
				$mute = array("unMute", $target, true);
			} else {
				$mute = array("Mute", $target, true);
			}
			
			$entry = array
				(
				'Id' => array($id, NULL, false),
				'isSpec' => array("isSpec", NULL, false),
				'Login' => array($player->login, NULL, false),
				'NickName' => array($player->nickName, NULL, false),
				'Spec' => array("Spec", $player->login, true),
				'Warn' => array("Warn", $player->login, true),
				'Mute' => $mute,
				'Kick' => array("Kick", $player->login, true),
				'Ban' => array("Ban", $target, true),
				'Black' => array("Black", $target, true)
			);
			$id++;
			$window->addAdminItem($entry, array($this, 'onClick'));
		}



		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	 /**
	 * onClick()
	 * Helper function, called on clicking.
	 *
	 * @param mixed $login
	 * @param mixed $action
	 * @param mixed $target
	 * @return void
	 */
	function onClick($login, $action, $target) {
		switch ($action) {
			case 'Spec':
				$this->forceSpec($login, $target);
				$this->adminPlayers($login);
				break;
			case 'Warn':
				$this->warnPlayer($login, $target);
				break;
			case 'Mute':
				$this->toggleMute($login, $target);
				$this->adminPlayers($login);
				break;
			case 'unMute':
				$this->toggleMute($login, $target);
				$this->adminPlayers($login);
				break;
			case 'Kick':
				$this->kick($login, $target);
				$this->adminPlayers($login);
				break;
			case 'Ban':
				$this->ban($login, $target);
				$this->adminPlayers($login);
				break;
			case 'unBan':
				$this->unban($login, $target);
				$this->showBanlist($login);
				break;
			case 'Black':
				$this->blacklist($login, $target);
				$this->adminPlayers($login);
				break;
			case 'unBlack':
				$this->unBlacklist($login, $target);
				$this->showBlacklist($login);
				break;
			case 'unIgnore':
				$this->unignore($login, $target);
				$this->showIgnorelist($login);
				break;
		}
		//$this->mlepp->sendChat("$action --> $target", $this->storage->getPlayerObject($login));
		// $this->players($login);
	}

	 /**
	 * panelCommand()
	 * Helper function, gets commands from panel.
	 *
	 * @param mixed $login
	 * @param mixed $action
	 * @return void
	 */
	function panelCommand($login, $action) {
		// $this->mlepp->sendChat('$fff'.$action, $login);
		switch ($action) {
			case 'skip':
				$this->skipTrack($login);
				break;
			case 'restart':
				$this->restartTrack($login);
				break;
			case 'queueRestart':
				$this->callPublicMethod('MLEPP\Jukebox', 'adminQueueRestart', $login);
				break;
			case 'players':
				$this->players($login);
				break;
			case 'voteDeny':
				$this->cancelVote($login);
				break;
			case 'votePass':
				$this->passVote($login);
				break;
			case 'endRound':
				$this->forceEndRound($login);
				break;
		}
	}

	 /**
	 * prepareRoundPoints()
	 * Helper function, prepares round points.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @return
	 */
	function prepareRoundPoints($fromLogin, $param1) {

		foreach ($this->rpoints as $pointsystem => $data) {
			if ($param1 == $pointsystem) {
				$this->setCustomRoundPoints($data[0], $data[1], $fromLogin);
				return;
			}
		}

		$tempPoints = explode(",", $param1);
		arsort($tempPoints); // sort the points from biggest to smallest..

		foreach ($tempPoints as $data) {
			$points[] = (int) $data; //do type conversion for dedicated
		}
		if (count($points) > 2) {
			try {
				$this->setCustomRoundPoints("own custom points", $points, $fromLogin);
			} catch (\Exception $e) {
				$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
				return;
			}
		} else {
			$this->mlepp->sendChat("error. you need to define atleast 2 comma separated entrys.", $fromLogin);
		}
	}

	 /**
	 * setCustomRoundPoints()
	 * Admin function, sets custom round points.
	 *
	 * @param mixed $name
	 * @param mixed $points
	 * @param mixed $fromLogin
	 * @return void
	 */
	function setCustomRoundPoints($name, $points, $fromLogin) {
		$this->connection->setRoundCustomPoints($points);
		$player = Storage::GetInstance()->getPlayerObject($fromLogin);
		$adminNick = $player->nickName;
		$showPoints = implode(",", $points);
		$this->mlepp->sendChat("%adminaction%Admin %variable%$adminNick\$z\$s%adminaction% sets round points to %variable%$name %adminaction%(%variable% $showPoints %adminaction%)");
	}

	 /**
	 * setRoundPointsLimit()
	 * Admin function, sets round points limit.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setRoundPointsLimit($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setRoundPointsLimit($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets rounds points limit to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setRoundForcedLaps()
	 * Admin function, sets round forced laps.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setRoundForcedLaps($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setRoundForcedLaps($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets forced round laps to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setUseNewRulesRound()
	 * Admin functions, sets new rules (Rounds).
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setUseNewRulesRound($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if ($param1 == 'true' || $param1 == 'false') {
			if ($param1 == 'true')
				$bool = true;
			if ($param1 == 'false')
				$bool = false;
		}
		else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is either true or false.', $fromLogin);
			return;
		}

		try {
			$this->connection->setUseNewRulesRound($bool);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets the round use new rules to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setNbLaps()
	 * Admin function, sets number of laps.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setNbLaps($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setNbLaps($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets number of laps to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setLapsTimeLimit()
	 * Admin function, sets Time Limit in Laps mode.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setLapsTimeLimit($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter count, use time in format %variable%m:ss', $fromLogin);
			return;
		}

		$timelimit = explode(":", trim($param1));
		if (count($timelimit) == 0 || count($timelimit) != 2) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter count, use time in format %variable%m:ss', $fromLogin);
			return;
		}

		$newLimit = ($timelimit[0] * 60 * 1000) + ($timelimit[1] * 1000);
		try {
			$this->connection->setLapsTimeLimit($newLimit);
			$nick = $this->getNick($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $nick . '$z$s%adminaction% sets new laps timelimit of %variable%' . $param1 . '$0ae minutes.');
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
		}
	}

	 /**
	 * setTeamPointsLimit()
	 * Admin functions, sets team points limit.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setTeamPointsLimit($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setTeamPointsLimit($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets team points limit to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setMaxPointsTeam()
	 * Admin function, sets maximum points for teams.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setMaxPointsTeam($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setMaxPointsTeam($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets team points limit to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setUseNewRulesTeam()
	 * Admin function, sets the use of new rules in Team.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setUseNewRulesTeam($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if ($param1 == 'true' || $param1 == 'false') {
			if ($param1 == 'true')
				$bool = true;
			if ($param1 == 'false')
				$bool = false;
		}
		else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is either true or false.', $fromLogin);
			return;
		}

		try {
			$this->connection->setUseNewRulesTeam($bool);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets the round use new rules to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * forcePlayerTeam()
	 * Admin function, forces players into teams.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function forcePlayerTeam($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->mlepp->sendChat('Player %variable%' . $param1 . '$0ae doesn\' exist.', $fromLogin);
			return;
		}

		$player = Storage::GetInstance()->getPlayerObject($param1);

		if ($param2 == 'red' || $param2 == 'blue') {
			if ($param1 == 'red')
				$team = 0;
			if ($param1 == 'blue')
				$team = 1;
		}
		else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is either red or blue.', $fromLogin);
			return;
		}



		try {
			$this->connection->forcePlayerTeam($player, $team);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets the round use new rules to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setCupPointsLimit()
	 * Admin function, sets points limit in Cup mode.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setCupPointsLimit($fromLogin, $param1= false, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setCupPointsLimit($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets cup points limit to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setCupRoundsPerChallenge()
	 * Admin function, sets rounds per challenge in Cup mode.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setCupRoundsPerChallenge($fromLogin, $param1 = false, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setCupRoundsPerChallenge($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets cup rounds per challenge to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setCupWarmUpDuration()
	 * Admin function, sets warm-up duration in Cup mode.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setCupWarmUpDuration($fromLogin, $param1 = false, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setCupWarmUpDuration($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets cup warmup duration to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setCupNbWinners()
	 * Admin function, sets number of winners in Cup mode.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setCupNbWinners($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setCupNbWinners($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets cup warmup duration to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setFinishTimeout()
	 * Admin function, sets finish timeout.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setFinishTimeout($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter count, use time in format %variable%m:ss', $fromLogin);
			return;
		}

		$timelimit = explode(":", trim($param1));
		if (count($timelimit) == 0 || count($timelimit) != 2) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter count, use time in format %variable%m:ss', $fromLogin);
			return;
		}

		$newLimit = ($timelimit[0] * 60 * 1000) + ($timelimit[1] * 1000);

		try {
			$this->connection->setFinishTimeout($newLimit);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets finish timeout to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * setAllWarmUpDuration()
	 * Admin function, sets warming-up duration to all modes.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setAllWarmUpDuration($fromLogin, $param1, $param2 = NULL, $param3 = NULL) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		if (is_numeric($param1)) {
			$param1 = (int) $param1;
		} else {
			$this->mlepp->sendChat('%adminerror%Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
			return;
		}

		try {
			$this->connection->setAllWarmUpDuration($param1);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets all game modes warmup duration to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	 /**
	 * showGetRoundPoints()
	 * Admin function, shows current round points.
	 *
	 * @param mixed $fromLogin
	 * @return
	 */
	function showGetRoundPoints($fromLogin) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$livePoints = $this->connection->getRoundCustomPoints();
		$comparePoints = implode(",", $livePoints);

		foreach ($this->rpoints as $data) {
			$targetPoints = implode(",", $data[1]);
			if ($comparePoints == $targetPoints) {
				$name = $data[0];
				$this->mlepp->sendChat("Round points in use: \%variable%$name \$ff0, $targetPoints", $fromLogin);
				return;
			}
		}
		$this->mlepp->sendChat("Custom round points in use: \%variable%$comparePoints ", $fromLogin);
	}

	 /**
	 * listRoundPoints()
	 * Admin function, lists round points possibilities.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function listRoundPoints($login) {
		if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, __FUNCTION__)) {
			$this->mlepp->sendChat($this->mlepp->AdminGroup->noPermissionMsg, $fromLogin);
			return;
		}

		$window = PlayersWindow::Create($login);
		$window->setSize(210, 100);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Id', 0.1);
		$window->addColumn('Name', 0.1);
		$window->addColumn('Description', 0.2);
		$window->addColumn('Points', 0.6);

		// refresh records for this window ...
		$window->clearItems();
		$id = 1;
		foreach ($this->rpoints as $name => $points) {
			$parsedPoints = implode(",", $points[1]);
			$entry = array
				(
				'Id' => $id,
				'Name' => $name,
				'Description' => $points[0],
				'Points' => $parsedPoints,
			);
			$id++;
			$window->addItem($entry);
		}
		$window->centerOnScreen();
		$window->show();
	}

	function shuffleTracks($login, $mode = null) {
		if ($mode != "true" && $mode != "false") {
			$this->mlepp->sendChat('%adminerror%/admin shuffle takes 1 parameter, which can be either %variable%true %adminerror%or %variable%false%adminerror%.', $login);
			return;
		}
		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);

		if ($this->mlepp->gameVersion->name == "ManiaPlanet") {
			$challengeDir = $dataDir . "Maps/MatchSettings";
		} else {
			$challengeDir = $dataDir . "Tracks/MatchSettings";
		}
		$tracklist = $challengeDir . "/" . $this->defaultTracklist;

		$xml = simplexml_load_file($tracklist);

		if (strtolower($mode) == "true") {
			$xml->filter->random_map_order = 1;
		} else {
			$xml->filter->random_map_order = 0;
		}

		try {
			$xml->asXML($tracklist);
			$this->connection->loadMatchSettings($tracklist);
			$admin = $this->storage->getPlayerObject($login);
			$this->mlepp->sendChat("%adminaction% Admin " . $admin->nickName . '$z%adminaction% sets shuffle tracklist to %variable%' . $mode);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
	}

	function setCallvoteTimeout($fromLogin, $param1) {
		if (empty($param1)) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter count, use time in format %variable%m:ss', $fromLogin);
			return;
		}

		$timelimit = explode(":", trim($param1));
		if (count($timelimit) == 0 || count($timelimit) != 2) {
			$this->mlepp->sendChat('%adminerror%Invalid parameter count, use time in format %variable%m:ss', $fromLogin);
			return;
		}

		$newLimit = ($timelimit[0] * 60 * 1000) + ($timelimit[1] * 1000);

		try {
			$this->connection->setCallVoteTimeOut($newLimit);
			$admin = Storage::GetInstance()->getPlayerObject($fromLogin);
			$this->mlepp->sendChat('%adminaction%Admin %variable%' . $admin->nickName . '$z$s%adminaction% sets callvote timeout to %variable%' . $param1);
		} catch (\Exception $e) {
			$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $fromLogin);
			return;
		}
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