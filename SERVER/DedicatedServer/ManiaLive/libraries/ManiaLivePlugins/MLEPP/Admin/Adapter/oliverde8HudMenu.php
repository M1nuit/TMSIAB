<?php

namespace ManiaLivePlugins\MLEPP\Admin\Adapter;

use ManiaLivePlugins\MLEPP\Admin\Admin;
use ManiaLive\Utilities\Time;

class oliverde8HudMenu {

	private $adminPlugin;
	private $menuPlugin;
	private $storage;
	private $connection;
	private $mlepp;

	public function __construct($adminPlugin, $menu, $storage, $connection) {

		$this->adminPlugin = $adminPlugin;
		$this->menuPlugin = $menu;
		$this->storage = $storage;
		$this->connection = $connection;
		$this->mlepp = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance();

		$this->generate_BasicCommands();
		$this->generate_PlayerLists();
		$this->generate_ServerSettings();
		$this->generate_GameSettings();
	}

	private function generate_BasicCommands() {
		$menu = $this->menuPlugin;

		$parent = $menu->findButton(array("admin", "Basic Commands"));
		$button["plugin"] = $this->adminPlugin;

		if (!$parent) {

			$button["style"] = "Icons64x64_1";
			$button["substyle"] = "GenericButton";

			$parent = $menu->addButton("admin", "Basic Commands", $button);
		}

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "ClipPause";
		$button["function"] = "restartTrack";
		$menu->addButton($parent, "Restart Track", $button);

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "ArrowNext";
		$button["function"] = "skipTrack";
		$menu->addButton($parent, "Skip Track", $button);

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "ArrowLast";
		$button["function"] = "forceEndRound";
		$button["plugin"] = $this;
		$button["checkFunction"] = "check_gameSettings_NoTimeAttack";
		$menu->addButton($parent, "End Round", $button);

		$button["plugin"] = $this->adminPlugin;
		unset($button["checkFunction"]);

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "TrackInfo";
		$button["function"] = "admin";
		$menu->addButton($parent, "Admin Help", $button);
	}
	public function forceEndRound($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$this->adminPlugin->forceEndRound($fromLogin, $param1, $param2, $param3);
	}
	public function check_gameSettings_NoTimeAttack(){
		return !$this->check_gameSettings_TimeAttack();
	}

	private function generate_PlayerLists() {

		$menu = $this->menuPlugin;

		//Player Related Menu
		$button["plugin"] = $this->adminPlugin;
		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "Buddy";
		$parent = $menu->addButton("admin", "Player Related", $button);

		//The buttons
		$button["function"] = "showBlacklist";
		unset($button["style"]);
		unset($button["substyle"]);
		$menu->addButton($parent, "In Black List", $button);

		$button["function"] = "ignorelist";
		unset($button["style"]);
		unset($button["substyle"]);
		$menu->addButton($parent, "In Ignore List", $button);

		$button["function"] = "banlist";
		unset($button["style"]);
		unset($button["substyle"]);
		$menu->addButton($parent, "In Ban List", $button);

		$button["function"] = "adminPlayers";
		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "Buddy";
		$menu->addButton($parent, "Manage Playerst", $button);
	}

	private function generate_GameSettings() {
		$menu = $this->menuPlugin;

		//Player Related Menu
		$button["plugin"] = $this->adminPlugin;
		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "ProfileAdvanced";
		$parent = $menu->addButton("admin", "Game Settings", $button);

		$button["function"] = "saveMatchSettings";
		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "Save";
		$menu->addButton($parent, "Save Matchsettings", $button);

		$this->gameSettings_GameMode($parent);

		$this->gameSettings_Rounds($parent);
		$this->gameSettings_TimeAttack($parent);
		$this->gameSettings_Team($parent);
		$this->gameSettings_Laps($parent);
		$this->gameSettings_Stunts($parent);
		$this->gameSettings_Cup($parent);
	}

	private function gameSettings_GameMode($parent) {

		$menu = $this->menuPlugin;

		$button["plugin"] = $this->adminPlugin;
		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "ProfileAdvanced";
		$gmode = $menu->addButton($parent, "Game Settings", $button);

		$modes = array("Rounds", "TimeAttack", "Team", "Laps", "Stunts", "Cup");
		$modes2 = array("rounds", "ta", "team", "laps", "stunts", "cup");

		for ($i = 0; $i < 6; $i++) {
			$new['style'] = 'Icons128x32_1';
			$new["substyle"] = 'RT_' . $modes[$i];
			$new["plugin"] = $this;
			$new['function'] = 'setGameMode';
			$new['params'] = $modes2[$i];
			$new["forceRefresh"] = "true";

			$menu->addButton($gmode, 'Set To:' . $modes[$i], $new);
			unset($new);
		}
	}

	public function setGameMode($login, $params) {
		$this->adminPlugin->setGameMode($login, $params);
	}

	private function gameSettings_Rounds($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button["style"] = 'Icons128x32_1';
		$button["substyle"] = "RT_rounds";
		$button['function'] = 'check_gameSettings_Rounds';
		$button["checkFunction"] = "check_gameSettings_Rounds";
		$parent = $menu->addButton($parent, "Round Settings", $button);

		$this->generate_GameSettings_WarmUp($parent);
		$this->generate_GameSettings_FinishTimeout($parent);
		$this->generate_GameSettings_RoundPointsLimit($parent);
		$this->generate_GameSettings_RoundForcedLaps($parent);
		$this->generate_GameSettings_RoundUseNewRules($parent);
	}

	public function check_gameSettings_Rounds() {
		if ($this->connection->getNextGameInfo()->gameMode == $this->mlepp->getGameModeNumber('Rounds')) {
			return true;
		}else
			return false;
	}

	private function gameSettings_TimeAttack($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button["style"] = 'Icons128x32_1';
		$button["substyle"] = "RT_TimeAttack";
		$button['function'] = 'check_gameSettings_TimeAttack';
		$button["checkFunction"] = "check_gameSettings_TimeAttack";
		$parent = $menu->addButton($parent, "Time Attack Settings", $button);

		$this->generate_GameSettings_WarmUp($parent);
		$this->generate_GameSettings_FinishTimeout($parent);
		$this->generate_GameSettings_TATimeLimit($parent);
	}

	public function check_gameSettings_TimeAttack() {
		if ($this->connection->getNextGameInfo()->gameMode == $this->mlepp->getGameModeNumber('TimeAttack')) {
			return true;
		}else
			return false;
	}

	private function gameSettings_Team($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button["style"] = 'Icons128x32_1';
		$button["substyle"] = "RT_Team";
		$button['function'] = 'check_gameSettings_Team';
		$button["checkFunction"] = "check_gameSettings_Team";
		$parent = $menu->addButton($parent, "Team Settings", $button);

		$this->generate_GameSettings_WarmUp($parent);
		$this->generate_GameSettings_FinishTimeout($parent);
		$this->generate_GameSettings_TeamPointsLimit($parent);
	}

	public function check_gameSettings_Team() {
		if ($this->connection->getNextGameInfo()->gameMode == $this->mlepp->getGameModeNumber('Team')) {
			return true;
		}else
			return false;
	}

	private function gameSettings_Laps($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button["style"] = 'Icons128x32_1';
		$button["substyle"] = "RT_Laps";
		$button['function'] = 'check_gameSettings_Laps';
		$button["checkFunction"] = "check_gameSettings_Laps";
		$parent = $menu->addButton($parent, "Laps Settings", $button);

		$this->generate_GameSettings_WarmUp($parent);
		$this->generate_GameSettings_FinishTimeout($parent);
		$this->generate_GameSettings_LapsTimeLimit($parent);
		$this->generate_GameSettings_LapsNbLaps($parent);
	}

	public function check_gameSettings_Laps() {
		if ($this->connection->getNextGameInfo()->gameMode == $this->mlepp->getGameModeNumber('Laps')) {
			return true;
		}else
			return false;
	}

	private function gameSettings_Stunts($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button["style"] = 'Icons128x32_1';
		$button["substyle"] = "RT_Stunt";
		$button['function'] = 'check_gameSettings_Stunts';
		$button["checkFunction"] = "check_gameSettings_Stunts";
		$parent = $menu->addButton($parent, "Stunts Settings", $button);

		$this->generate_GameSettings_WarmUp($parent);
		$this->generate_GameSettings_FinishTimeout($parent);
	}

	public function check_gameSettings_Stunts() {
		if ($this->connection->getNextGameInfo()->gameMode == $this->mlepp->getGameModeNumber('Stunt')) {
			return true;
		}else
			return false;
	}

	private function gameSettings_Cup($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button["style"] = 'Icons128x32_1';
		$button["substyle"] = "RT_Cup";
		$button['function'] = 'check_gameSettings_Cup';
		$button["checkFunction"] = "check_gameSettings_Cup";
		$parent = $menu->addButton($parent, "Cup Settings", $button);

		$this->generate_GameSettings_WarmUp($parent);
		$this->generate_GameSettings_FinishTimeout($parent);
		$this->generate_GameSettings_CupPointsLimit($parent);
		$this->generate_GameSettings_CupNbWinners($parent);
		$this->generate_GameSettings_CupRoundsPerChallenge($parent);
	}

	public function check_gameSettings_Cup() {
		if ($this->connection->getNextGameInfo()->gameMode == $this->mlepp->getGameModeNumber('Cup')) {
			return true;
		}else
			return false;
	}

	/*	 * *********************************
	 * Different setting Menus
	 */

	private function generate_GameSettings_WarmUp($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button["style"] = 'BgRaceScore2';
		$button["substyle"] = "Warmup";
		$wup = $menu->addButton($parent, "Warm Up Duration", $button);

		$times = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 10);
		foreach ($times as $Time) {
			$new['style'] = 'BgRaceScore2';
			$new["substyle"] = 'SandTimer';
			$new['function'] = 'save_GameSettings_WarmUp';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			if ($Time == 0) {
				$menu->addButton($wup, "Close it", $new);
			} else {
				$menu->addButton($wup, "Set to : " . $Time, $new);
			}

			unset($new);
		}
	}

	public function save_GameSettings_WarmUp($login, $params) {
		$this->adminPlugin->setWU($login, $params);
	}

	private function generate_GameSettings_FinishTimeout($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$wup = $menu->addButton($parent, "Finish Time Out", $button);

		$times = array(0, 10, 30, 45, 60, 90, 120);
		foreach ($times as $Time) {
			$new['style'] = 'BgRaceScore2';
			$new["substyle"] = 'SandTimer';
			$new['function'] = 'save_GameSettings_FinishTimeout';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			$menu->addButton($wup, "Set to : " . $Time, $new);

			unset($new);
		}
	}

	public function save_GameSettings_FinishTimeout($login, $params) {
		$this->adminPlugin->setFinishTimeout($login, $params);
	}

	/*	 * ***************************
	 * Round Settings
	 */

	private function generate_GameSettings_RoundPointsLimit($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button['style'] = 'BgRaceScore2';
		$button["substyle"] = 'Points';
		$wup = $menu->addButton($parent, "Point Limit", $button);

		$times = array(10, 20, 30, 40, 50, 75, 100, 120, 150);
		foreach ($times as $Time) {
			$new['style'] = 'BgRaceScore2';
			$new["substyle"] = 'Points';
			$new['function'] = 'save_GameSettings_RoundPointsLimit';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			if ($Time == 0) {
				$menu->addButton($wup, "Close it", $new);
			} else {
				$menu->addButton($wup, "Set to : " . $Time, $new);
			}

			unset($new);
		}
	}

	public function save_GameSettings_RoundPointsLimit($login, $params) {
		$this->adminPlugin->setRoundPointsLimit($login, $params);
	}

	private function generate_GameSettings_RoundForcedLaps($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button['style'] = 'BgRaceScore2';
		$button["substyle"] = 'Laps';
		$wup = $menu->addButton($parent, "Forced Laps", $button);

		$times = array(1, 2, 5, 8, 10, 20, 25, 30, 45, 50);
		foreach ($times as $Time) {
			$new['style'] = 'BgRaceScore2';
			$new["substyle"] = 'Laps';
			$new['function'] = 'save_GameSettings_RoundPointsLimit';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			if ($Time == 1) {
				$menu->addButton($wup, "Disable", $new);
			} else {
				$menu->addButton($wup, "Set to : " . $Time, $new);
			}

			unset($new);
		}
	}

	public function save_GameSettings_RoundForcedLaps($login, $params) {
		$this->adminPlugin->setRoundForcedLaps($login, $params);
	}

	private function generate_GameSettings_RoundUseNewRules($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button["function"] = "save_GameSettings_RoundUseNewRules";
		$button["switchFunction"] = "get_GameSettings_RoundUseNewRules";
		$button["forceRefresh"] = true;

		$wup = $menu->addButton($parent, "Use New Rules", $button);
	}

	public function save_GameSettings_RoundUseNewRules($login) {

		if ($this->connection->getNextGameInfo()->roundsUseNewRules)
			$val = "false";
		else
			$val = "true";

		$this->adminPlugin->setUseNewRulesRound($login, $val);
	}

	public function get_GameSettings_RoundUseNewRules() {
		return $this->connection->getNextGameInfo()->roundsUseNewRules;
	}

	/*	 * ********************************
	 * Time Attack Settings
	 */

	public function generate_GameSettings_TATimeLimit($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button["style"] = 'BgRaceScore2';
		$button["substyle"] = "SendScore";
		$wup = $menu->addButton($parent, "Time Limit", $button);

		$times = array(30, 60, 90, 120, 180, 240, 300, 360, 390, 480);
		foreach ($times as $Time) {
			$new['style'] = 'BgRaceScore2';
			$new["substyle"] = 'SandTimer';
			$new['function'] = 'save_GameSettings_TATimeLimit';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			$menu->addButton($wup, "Set to : " .  Time::fromTM($Time * 1000), $new);

			unset($new);
		}
	}

	public function save_GameSettings_TATimeLimit($login, $params) {
		$this->adminPlugin->TAlimit($login, $params);
	}

	/*	 * *********************************
	 * Team Settings
	 */

	private function generate_GameSettings_TeamPointsLimit($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button['style'] = 'BgRaceScore2';
		$button["substyle"] = 'Points';
		$wup = $menu->addButton($parent, "Point Limit", $button);

		$times = array(10, 20, 30, 40, 50, 75, 100, 120, 150);
		foreach ($times as $Time) {
			$new['style'] = 'BgRaceScore2';
			$new["substyle"] = 'Points';
			$new['function'] = 'save_GameSettings_TeamPointsLimit';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			if ($Time == 1) {
				$menu->addButton($wup, "Disable", $new);
			} else {
				$menu->addButton($wup, "Set to : " . $Time, $new);
			}

			unset($new);
		}
	}

	public function save_GameSettings_TeamPointsLimit($login, $params) {
		$this->adminPlugin->setTeamPointsLimit($login, $params);
	}

	private function generate_GameSettings_TeamMaxPoints($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button['style'] = 'BgRaceScore2';
		$button["substyle"] = 'Points';
		$wup = $menu->addButton($parent, "Max Points", $button);

		$times = array(1, 3, 5, 8, 10, 15, 20, 30, 50);
		foreach ($times as $Time) {
			$new['style'] = 'BgRaceScore2';
			$new["substyle"] = 'Points';
			$new['function'] = 'save_GameSettings_TeamMaxPoints';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			if ($Time == 1) {
				$menu->addButton($wup, "Disable", $new);
			} else {
				$menu->addButton($wup, "Set to : " . $Time, $new);
			}

			unset($new);
		}
	}

	public function save_GameSettings_TeamMaxPoints($login, $params) {
		$this->adminPlugin->setMaxPointsTeam($login, $params);
	}

	/*	 * ********************************
	 * Team Settings
	 */
	/* $this->generate_GameSettings_LapsTimeLimit($parent);
	  $this->generate_GameSettings_LapsNbLaps($parent); */

	private function generate_GameSettings_LapsTimeLimit($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button['style'] = 'BgRaceScore2';
		$button["substyle"] = 'SendScore';
		$wup = $menu->addButton($parent, "Time Limit", $button);

		$times = array(0, 10, 30, 60, 90, 120, 180, 240, 300);
		foreach ($times as $Time) {
			$new['style'] = 'BgRaceScore2';
			$new["substyle"] = 'SandTimer';
			$new['function'] = 'save_GameSettings_LapsTimeLimit';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			if ($Time == 1) {
				$menu->addButton($wup, "Disable", $new);
			} else {
				$menu->addButton($wup, "Set to : " . $Time, $new);
			}

			unset($new);
		}
	}

	public function save_GameSettings_LapsTimeLimit($login, $params) {
		$this->adminPlugin->setLapsTimeLimit($login, $params);
	}

	private function generate_GameSettings_LapsNbLaps($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$wup = $menu->addButton($parent, "Nb Laps", $button);

		$times = array(1, 2, 5, 8, 10, 20, 25, 30, 45, 50);
		foreach ($times as $Time) {
			$new['function'] = 'save_GameSettings_LapsNbLaps';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			if ($Time == 1) {
				$menu->addButton($wup, "Disable", $new);
			} else {
				$menu->addButton($wup, "Set to : " . $Time, $new);
			}

			unset($new);
		}
	}

	public function save_GameSettings_LapsNbLaps($login, $params) {
		$this->adminPlugin->setNbLaps($login, $params);
	}

	/*	 * *********************************
	 * Cup Settings
	 */

	private function generate_GameSettings_CupPointsLimit($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button['style'] = 'BgRaceScore2';
		$button["substyle"] = 'Points';
		$wup = $menu->addButton($parent, "Points Limit", $button);

		$times = array(10, 20, 30, 40, 50, 75, 100, 120, 150);
		foreach ($times as $Time) {
			$new['style'] = 'BgRaceScore2';
			$new["substyle"] = 'Points';
			$new['function'] = 'save_GameSettings_CupPointsLimit';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			if ($Time == 1) {
				$menu->addButton($wup, "Disable", $new);
			} else {
				$menu->addButton($wup, "Set to : " . $Time, $new);
			}

			unset($new);
		}
	}

	public function save_GameSettings_CupPointsLimit($login, $params) {
		$this->adminPlugin->setCupPointsLimit($login, $params);
	}

	private function generate_GameSettings_CupRoundsPerChallenge($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;

		$wup = $menu->addButton($parent, "Round Par Challenge", $button);

		$times = array(1, 2, 3, 5, 7, 8, 10);
		foreach ($times as $Time) {
			$new['function'] = 'save_GameSettings_CupRoundsPerChallenge';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			if ($Time == 1) {
				$menu->addButton($wup, "Disable", $new);
			} else {
				$menu->addButton($wup, "Set to : " . $Time, $new);
			}

			unset($new);
		}
	}

	public function save_GameSettings_CupRoundsPerChallenge($login, $params) {
		$this->adminPlugin->setCupRoundsPerChallenge($login, $params);
	}

	private function generate_GameSettings_CupNbWinners($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this;
		$button['style'] = 'Icons64x64_1';
		$button["substyle"] = 'OfficialRace';
		$wup = $menu->addButton($parent, "Nb Winners", $button);

		$times = array(1, 2, 3, 5, 7, 8, 10);
		foreach ($times as $Time) {
			$new['style'] = 'Icons64x64_1';
			$new["substyle"] = 'OfficialRace';
			$new['function'] = 'save_GameSettings_CupRoNbWinners';
			$new["plugin"] = $this;
			$new["params"] = $Time;

			if ($Time == 1) {
				$menu->addButton($wup, "Disable", $new);
			} else {
				$menu->addButton($wup, "Set to : " . $Time, $new);
			}

			unset($new);
		}
	}

	public function save_GameSettings_CupRoNbWinners($login, $params) {
		$this->adminPlugin->setCupRoundsPerChallenge($login, $params);
	}

	/*	 * ***********************************
	 * Server Settings
	 */

	private function generate_ServerSettings() {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this->adminPlugin;
		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "Options";
		$parent = $menu->addButton("admin", "Server Settings", $button);

		$separator["seperator"] = true;

		unset($button["style"]);
		unset($button["substyle"]);
		$button["forceRefresh"] = true;
		$button["plugin"] = $this;
		$button["function"] = "ServerSettings_setDisableRespawn";
		$button["switchFunction"] = "ServerSettings_getDisableRespawn";
		$menu->addButton($parent, "Disable Respawn", $button);

		$button["plugin"] = $this;
		$button["function"] = "ServerSettings_setChallengeDownload";
		$button["switchFunction"] = "ServerSettings_getChallengeDownload";
		$menu->addButton($parent, "Challlange Dwld", $button);
		unset($button["switchFunction"]);

		$menu->addButton($parent, "Other ...", $separator);

		$button["forceRefresh"] = false;
		$button["plugin"] = $this->adminPlugin;
		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "ProfileVehicle";
		$button["function"] = "getServerMaxPlayers";
		$menu->addButton($parent, "Max Players", $button);

		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "BgRaceScore2";
		$button["function"] = "Spectator";
		$menu->addButton($parent, "Max Spectator", $button);

		$separator["style"] = "Icons128x128_1";
		$separator["substyle"] = "Multiplayer";
		$menu->addButton($parent, "Max Players ...", $separator);

		unset($button["style"]);
		unset($button["substyle"]);
		$button["function"] = "getServerPasswordForSpectator";
		$menu->addButton($parent, "Spectator Password", $button);

		$button["function"] = "getServerPassword";
		$menu->addButton($parent, "Server Password", $button);

		$button["function"] = "getRefereePassword";
		$menu->addButton($parent, "Referee Password", $button);

		$separator["style"] = "Icons128x128_1";
		$separator["substyle"] = "Padlock";
		$menu->addButton($parent, "Paswords", $separator);
	}

	public function ServerSettings_getDisableRespawn() {
		$respawn = $this->connection->getDisableRespawn();
		return $respawn['NextValue'];
	}

	public function ServerSettings_setDisableRespawn($login, $params) {
		$respawn = $this->connection->getDisableRespawn();

		if ($respawn['NextValue'])
			$val = "true";
		else
			$val = "false";

		$this->adminPlugin->setDisableRespawn($login, $val);
	}

	public function ServerSettings_getChallengeDownload() {
		return $data = $this->connection->isChallengeDownloadAllowed();
	}

	public function ServerSettings_setChallengeDownload($login) {

		if (!$this->connection->isChallengeDownloadAllowed())
			$val = "true";
		else
			$val = "false";

		$this->adminPlugin->setServerChallengeDownload($login, $val);
	}

}

?>
