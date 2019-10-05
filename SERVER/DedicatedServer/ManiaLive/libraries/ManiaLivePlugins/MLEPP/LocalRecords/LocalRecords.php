<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name LocalRecords
 * @date 13-01-2011
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

namespace ManiaLivePlugins\MLEPP\LocalRecords;

use ManiaLive\Utilities\Console;
use ManiaLib\Utils\TMStrings as String;
use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Time;
use ManiaLivePlugins\MLEPP\Database\Structures\multiQuery;
use ManiaLivePlugins\MLEPP\LocalRecords\Gui\Windows\LocalRecsWindow;
use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\MLEPP\LocalRecords\Structures\Record;
use ManiaLivePlugins\MLEPP\LocalRecords\Events\onRecordUpdate;
use ManiaLivePlugins\MLEPP\LocalRecords\Events\onChallengeChange;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class LocalRecords extends \ManiaLive\PluginHandler\Plugin {

	private static $_Instance;
	private $mlepp;
	private $currentChallengeRecords = array();
	private $currentChallengePlayerRecords = array();
	private $checkpoints = array();
	private $players = array();
	private $config;
	private $wins = 0;
	private $newwins = 0;

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	public function onInit() {
		$this->setVersion(1050);
		$this->setPublicMethod('getVersion');


		$this->mlepp = Mlepp::getInstance();
		self::$_Instance = $this;
		$this->config = Config::getInstance();
	}

	public static function getInstance() {
		return self::$_Instance;
	}

	 /**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	public function onLoad() {
		$this->enableStorageEvents();
		if ($this->isPluginLoaded('MLEPP\Database', 251)) {
			Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: LocalRecords r' . $this->getVersion());
			$this->connected = true;
			$this->enableDedicatedEvents();
			$this->enableStorageEvents();
			$this->registerChatCommand("recs", "showRecordsWindow", 0, true);
		} else {
			Console::println('[' . date('H:i:s') . '] [MLEPP] [LocalRecords] Plugin couldn\'t been load because plugin \'MLEPP\Database\' isn\'t activated.');
			$this->connected = false;
		}
		if (!\is_bool($this->config->showChatRecords)) {
			$this->config->showChatRecords = $this->stringToBool($this->config->showChatRecords);
		}
		if (!\is_bool($this->config->showChatMessages)) {
			$this->config->showChatMessages = $this->stringToBool($this->config->showChatMessages);
		}
		if (!\is_bool($this->config->lapsModeCount1lap)) {
			$this->config->lapsModeCount1lap = $this->stringToBool($this->config->lapsModeCount1lap);
		}
	}

	 /**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */
	public function onReady() {
		if ($this->connected == true) {
			/* $dbinfo = $this->callPublicMethod('MLEPP\Database', 'getConnection');
			  $this->mlepp->db->connection = $dbinfo['connection'];
			  $this->dbtype = $dbinfo['dbtype'];
			 */
			$this->initDatabaseTables();
			$this->updateCurrentChallengeRecords();
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
	public function onPlayerConnect($login, $isSpec) {
		$challenge = $this->storage->currentChallenge;

		$this->checkpoints[$login] = array();

		if (!isset($this->currentChallengePlayerRecords[$login])) {
			$data = $this->getPlayerRecord($login, $challenge->uId);
			if ($data != false) {
				$this->currentChallengePlayerRecords[$login] = $data;
				//$this->showPlayerRecord($login);
			}
		}
		$this->showPlayerRecord($login);
	}

	 /**
	 * onPlayerDisconnect()
	 * Function called when a player disconnects.
	 *
	 * @param mixed $login
	 * @return void
	 */
	public function onPlayerDisconnect($login) {
		if (empty($login))
			return;
		if (isset($this->checkpoints[$login])) {
			unset($this->checkpoints[$login]);
		}
		/* disabled to fix -1 rank.
		  if (isset($this->currentChallengePlayerRecords[$login])) {
		  unset($this->currentChallengePlayerRecords[$login]);
		  } */
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
	public function onBeginChallenge($challenge, $isWarmUp, $matchContinuation) {
		$this->updateCurrentChallengeRecords();
		if ($this->config->showChatMessages) {
			if ($this->config->showChatMessageOnBeginRace) {
				$this->showFirstRecord();
				$this->showPlayerRecord();
			}
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
		if ($this->config->showChatMessages) {
			if (!$restartChallenge) {
				$this->showFirstRecord();
				$this->showPlayerRecord();
				$this->endRaceRanking($rankings);
			}
		}
	}

	function endRaceRanking($rankings) {
		$gamemode = $this->storage->gameInfos->gameMode;
		// looking if the login is online
		if (isset($rankings[0]['Login']) &&
				($rankings[0]['Login']) !== false) {
			// check for winner if there are more than one player in the server. With one its unfair.
			if ($rankings[0]['Rank'] == 1 && count($rankings) > 1 &&
					($gamemode == 4 ?
							($rankings[0]['Score'] > 0) : ($rankings[0]['BestTime'] > 0))) {
				// increase the player's wins

				$q = "UPDATE
                        `players`
                        SET `player_wins` = `player_wins`+1 where `players`.`player_login` =" . $this->mlepp->db->quote($rankings[0]['Login']) . "";
				$query = $this->mlepp->db->query($q);


				$q = "SELECT `player_wins` from `players` where `player_login` = " . $this->mlepp->db->quote($rankings[0]['Login']) . "";

				$data = $this->mlepp->db->query($q);
				$windata = $data->fetchStdObject();
				$wins = $windata->player_wins;
				if ($wins % 10 == 0) {
					Console::println('' . $rankings[0]['Login'] . ' won for the ' . $wins . '. time.');
					$this->mlepp->db->query($q);
					$message = $rankings[0]['NickName'] . '$z$s$fff won for the ' . $wins . ' time';
					$this->mlepp->sendChat($message);
				} else {
					$message = "You have won $wins. races on this server";
					try {
						if ($this->mlepp->isPlayerOnline($rankings[0]['Login'])) {
							$this->mlepp->sendChat($message, $rankings[0]['Login']);
						}
					} catch (\Exception $e) {
						## ignore the error if player has leaved the server before chatmessage is sent.
					}
				}
			}
		}
	}

	 /**
	 * onPlayerCheckpoint()
	 * Function called when someone passes a checkpoint.
	 *
	 * @param mixed $playerUID
	 * @param mixed $playerLogin
	 * @param mixed $timeScore
	 * @param mixed $currentLap
	 * @param mixed $checkpointIndex
	 * @return void
	 */
	public function onPlayerCheckpoint($playerUid, $login, $score, $curLap, $checkpointIndex) {
		$this->checkpoints[$login][] = $score;
	}

	 /**
	 * onPlayerFinish()
	 * Function called when a player finishes.
	 *
	 * @param mixed $playerUid
	 * @param mixed $login
	 * @param mixed $timeOrScore
	 * @return
	 */
	public function onPlayerFinish($playerUid, $login, $timeOrScore) {

		if (isset($this->storage->players[$login]) && $timeOrScore > 0) {
			$gamemode = $this->storage->gameInfos->gameMode;

			if ($gamemode == 4 && $this->config->lapsModeCount1lap)//Laps mode has it own on Player finish event
				return;

			$this->addRecordToDb($login, $timeOrScore, $gamemode, $this->checkpoints[$login]);
		}
		$this->checkpoints[$login] = array();
	}

	 /**
	 * onPlayerFinishLap()
	 * Function called when a player finished a lap.
	 *
	 * @param mixed $player
	 * @param mixed $time
	 * @param mixed $checkpoints
	 * @param mixed $nbLap
	 * @return
	 */
	public function onPlayerFinishLap($player, $time, $checkpoints, $nbLap) {

		if ($this->config->lapsModeCount1lap && isset($this->storage->players[$player->login]) && $time > 0) {
			$gamemode = $this->storage->gameInfos->gameMode;

			if ($gamemode != 3)//Laps mode has it own on Player finish event
				return;

			$this->addRecordToDb($player->login, $time, $gamemode, $checkpoints);
		}
	}

	 /**
	 * updateCurrentChallengeRecords()
	 * Updates currentChallengePlayerRecords and the currentChallengeRecords arrays
	 * with the current Challange Records.
	 *
	 * @return void
	 */
	private function updateCurrentChallengeRecords() {

		$this->currentChallengePlayerRecords = array();
		$this->currentChallengePlayerRecords = $this->buildConnectedPlayersRecord();

		$this->currentChallengeRecords = array(); //reset
		$this->currentChallengeRecords = $this->buildCurrentChallangeRecords(); // fetch

		Dispatcher::dispatch(new onChallengeChange($this->getRecords(), $this->currentChallengePlayerRecords));
	}

	 /**
	 * buildCurrentChallangeRecords().
	 * This function will built the currentChallengePlayerRecords
	 *
	 * @param mixed $gamemode
	 * @return
	 */
	private function buildCurrentChallangeRecords($gamemode = NULL) {


		$challenge = $this->storage->currentChallenge;

		if ($gamemode === NULL || $gamemode == '') {
			$gamemode = $this->storage->gameInfos->gameMode;
		}

		$cons = "";
		if ($this->useLapsConstraints()) {
			$cons .= " AND record_nbLaps = " . $this->getNbOfLaps();
		} else {
			$cons .= " AND record_nbLaps = 1";
		}

		$q = "SELECT * FROM `localrecords` LEFT JOIN `players` ON (`localrecords`.`record_playerlogin` = `players`.`player_login`)
					WHERE `record_challengeuid` = " . $this->mlepp->db->quote($challenge->uId) . " " . $cons . "
					ORDER BY `record_score` ASC
					LIMIT 0, " . $this->config->numrec . ";";

		$dbData = $this->mlepp->db->query($q);

		if ($dbData->recordCount() == 0) {
			return NULL;
		}

		$i = 1;
		$records = array();

		while ($data = $dbData->fetchStdObject()) {

			if (!isset($this->currentChallengePlayerRecords[$data->record_playerlogin])) {
				$record = new Record();
				$this->currentChallengePlayerRecords[$data->record_playerlogin] = $record;
			} else {
				$record = $this->currentChallengePlayerRecords[$data->record_playerlogin];
			}

			$record->rank = $i;
			$record->login = $data->record_playerlogin;
			$record->nickName = $data->player_nickname;
			$record->score = $data->record_score;
			$record->nbFinish = $data->record_nbFinish;
			$record->avgScore = $data->record_avgScore;
			$record->gamemode = $data->record_gamemode;

			$records[$i] = $record;
			$i++;
		}

		return $records;
	}

	 /**
	 * getRecords()
	 * Use to retrieve the Records of ac challange. If you get the current challanges Records it won't need SQL, it is optimized.
	 *
	 * @param mixed $challenge - default: current challenge
	 * @param mixed $gamemode
	 * @return array Record object
	 */
	public function getRecords($challenge = NULL, $gamemode = NULL) {

		$cons = "";

		if ($this->useLapsConstraints()) {
			$cons .= " AND record_nbLaps = " . $this->getNbOfLaps();
		} else {
			$cons .= " AND record_nbLaps = 1";
		}

		if ($challenge == null || $challenge->uId == $this->storage->currentChallenge->uId) {
			return $this->currentChallengeRecords;
			//return array_splice($this->currentChallengeRecords, $this->config->numrec);
		} else {
			$q = "SELECT * FROM `localrecords` LEFT JOIN `players` ON (`localrecords`.`record_playerlogin` = `players`.`player_login`)
            WHERE `record_challengeuid` = " . $this->mlepp->db->quote($challenge->uId) . "
				" . $cons . "
            ORDER BY `record_score` ASC
            LIMIT 0, " . $this->config->numrec . ";";

			$dbData = $this->mlepp->db->query($q);

			if ($dbData->recordCount() == 0) {
				return NULL;
			}

			$i = 1;
			$records = array();

			while ($data = $dbData->fetchStdObject()) {
				$record = new Record();

				$record->rank = $i;
				$record->login = $data->record_playerlogin;
				$record->nickName = $data->player_nickname;
				$record->score = $data->record_score;
				$record->nbFinish = $data->record_nbFinish;
				$record->avgScore = $data->record_avgScore;
				$record->gamemode = $data->record_gamemode;

				$records[$i] = $record;
				$i++;
			}
		}

		return $records;
	}

	 /**
	 * buildConnectedPlayersRecord()
	 * Function checks the record of the players who are online.
	 *
	 * @return
	 */
	private function buildConnectedPlayersRecord() {

		$challenge = $this->storage->currentChallenge;
		$records = array();

		foreach ($this->storage->players as $login => $player) {
			$data = $this->getPlayerRecord($login, $challenge->uId);
			if ($data != false) {
				$records[$login] = $data;
			}

			$this->checkpoints[$login] = array();
		}
		if (count($this->storage->spectators) > 0) {
			foreach ($this->storage->spectators as $login => $player) {
				$data = $this->getPlayerRecord($login, $challenge->uId);
				if ($data != false) {
					$records[$login] = $data;
				}

				$this->checkpoints[$login] = array();
			}
		}
		return $records;
	}

	 /**
	 * addRecordToDb()
	 * Helper function, adds record into the databse.
	 *
	 * @param mixed $login
	 * @param mixed $score
	 * @param mixed $gamemode
	 * @param mixed $cpScore
	 * @return void
	 */
	private function addRecordToDb($login, $score, $gamemode, $cpScore = array()) {
		$uid = $this->storage->currentChallenge->uId;
		$player = $this->storage->getPlayerObject($login);

		if (isset($this->currentChallengePlayerRecords[$login])) {

			// player already has a record on the track
			if (($this->currentChallengePlayerRecords[$login]->score > $score && $gamemode != 4)
					|| ($this->currentChallengePlayerRecords[$login]->score < $score && $gamemode == 4)) {

				$recordrank_old = $this->currentChallengePlayerRecords[$login]->rank;
				unset($data);

				$nbFinish = $this->currentChallengePlayerRecords[$login]->nbFinish + 1;
				$avgScore = (($nbFinish - 1) * $this->currentChallengePlayerRecords[$login]->avgScore + $score ) / $nbFinish;
				$this->currentChallengePlayerRecords[$login]->nbFinish++;
				$this->currentChallengePlayerRecords[$login]->avgScore = $avgScore;

				if (!empty($cpScore)) {
					$cps = implode(",", $cpScore);
					$r[0] = ", `record_checkpoints` = " . $this->mlepp->db->quote($cps) . " ";
					$r[1] = ", record_checkpoints = " . $this->mlepp->db->quote($cps) . " ";
				} else {
					$r[0] = "";
					$r[1] = "";
				}

				$cons2 = "";
				if ($this->useLapsConstraints()) {
					$cons2 .= " AND record_nbLaps = " . $this->getNbOfLaps();
				} else {
					$cons2 .= " AND record_nbLaps = 1";
				}

				// Update new time for player.
				$exec = "UPDATE `localrecords`
                SET `record_score` = " . $this->mlepp->db->quote($score) . ",
                    `record_date` = " . $this->mlepp->db->quote(date('Y-m-d H:i:s')) . ",
                    `record_nbFinish` = " . $this->mlepp->db->quote($nbFinish) . ",
                    `record_avgScore` = " . $this->mlepp->db->quote($avgScore) . ",
                    `record_gamemode` = " . $this->mlepp->db->quote($gamemode) . "
                     " . $r[0] . "
	            WHERE `record_playerlogin` = " . $this->mlepp->db->quote($login) . " " . $cons2 . "
					AND `record_challengeuid` = " . $this->mlepp->db->quote($uid) . ";";



				$this->mlepp->db->query($exec);

				// Get New rank
				//print_r($this->currentChallengeRecords);

				$i = 1;
				$do = true;
				$buff1 = null;
				$buff2 = null;
				$recordrank_new = $this->currentChallengePlayerRecords[$login]->rank;
				while (isset($this->currentChallengeRecords[$i]) && $do) {
					if (($this->currentChallengeRecords[$i]->score > $score && $gamemode != 6)
							|| ($this->currentChallengeRecords[$i]->score < $score && $gamemode == 6)) {

						if ($buff1 == null) {
							$this->currentChallengePlayerRecords[$login]->rank = $i;
							if ($recordrank_old == $i) {
								$do = false;
							} else {
								$this->currentChallengeRecords[$i]->rank++;
								$buff1 = $this->currentChallengeRecords[$i];
								$this->currentChallengeRecords[$i] = $this->currentChallengePlayerRecords[$login];
								$recordrank_new = $i;
							}
						} else {
							if ($this->currentChallengeRecords[$i]->login == $login) {
								$this->currentChallengeRecords[$i] = $buff1;
								$do = false;
							} else {
								$this->currentChallengeRecords[$i]->rank++;
								$buff2 = $this->currentChallengeRecords[$i];
								$this->currentChallengeRecords[$i] = $buff1;
								$buff1 = $buff2;
							}
						}
					}
					$i++;
				}

				if ($buff1 != null && $i > $this->config->numrec) {
					$this->currentChallengeRecords[$i] = $buff1;
				}

				$old_score = $this->currentChallengePlayerRecords[$login]->score;
				$this->currentChallengePlayerRecords[$login]->score = $score;
				$this->currentChallengePlayerRecords[$login]->ScoreCheckpoints = $cpScore;

				$this->currentChallengePlayerRecords[$login]->rank = $recordrank_new;

				unset($data);

				// if the new rank is in records range, show it.
				if ($recordrank_new <= $this->config->numrec && $recordrank_new != -1) {
					if ($recordrank_old == $recordrank_new) {

						// log console
						if (($old_score - $score) == 0) {
							Console::println('[' . date('H:i:s') . '] [MLEPP] [LocalRecords] ' . $login . ' equaled his/her ' . $recordrank_new . '. Local Record with time: ' . Time::fromTM($score) . '!');
						} else {
							Console::println('[' . date('H:i:s') . '] [MLEPP] [LocalRecords] ' . $login . ' secured his/her ' . $recordrank_new . '. Local Record with time: ' . Time::fromTM($score) . '!');
						}

						if ($this->config->showChatRecords == true && $recordrank_new <= $this->config->maxRecsDisplayed) {
							if (($old_score - $score) == 0) {
								$message = $this->controlMsg($this->config->equalRecordChat, $player, $recordrank_new, $score, $recordrank_old, $old_score);
							} else {
								$message = $this->controlMsg($this->config->securedRecordChat, $player, $recordrank_new, $score, $recordrank_old, $old_score);
							}
							$this->mlepp->sendChat($message);
						} else if ($this->config->showChatRecords == true) {
							if (($old_score - $score) == 0) {
								$message = $this->controlMsg($this->config->equalRecordPrivate, $player, $recordrank_new, $score, $recordrank_old, $old_score);
							} else {
								$message = $this->controlMsg($this->config->securedRecordPrivate, $player, $recordrank_new, $score, $recordrank_old, $old_score);
							}
							$this->mlepp->sendChat($message, $player->login);
						}
					} else {
						if ($this->config->showChatRecords == true && $recordrank_new <= $this->config->maxRecsDisplayed) {
							$message = $this->controlMsg($this->config->gainedRecordChat, $player, $recordrank_new, $score, $recordrank_old, $old_score);
							$this->mlepp->sendChat($message);
						} elseif ($this->config->showChatRecords == true) {
							$message = $this->controlMsg($this->config->gainedRecordPrivate, $player, $recordrank_new, $score, $recordrank_old, $old_score);
							$this->mlepp->sendChat($message, $player->login);
						}
						Console::println('[' . date('H:i:s') . '] [MLEPP] [LocalRecords] ' . $login . ' gained the ' . $recordrank_new . '. Local Record with time: ' . Time::fromTM($score) . ' (' . $recordrank_old . '. -' . Time::fromTM($old_score - $score) . ')!');
					}
					Dispatcher::dispatch(new onRecordUpdate($this->currentChallengePlayerRecords[$login], $recordrank_old, onRecordUpdate::newRecord));
				} else {
					Dispatcher::dispatch(new onRecordUpdate($this->currentChallengePlayerRecords[$login], $recordrank_old, onRecordUpdate::bestScore));
				}
			} else {
				$nbFinish = $this->currentChallengePlayerRecords[$login]->nbFinish + 1;
				$avgScore = (($nbFinish - 1) * $this->currentChallengePlayerRecords[$login]->avgScore + $score ) / $nbFinish;
				$this->currentChallengePlayerRecords[$login]->nbFinish++;
				$this->currentChallengePlayerRecords[$login]->avgScore = $avgScore;

				// Update new time for player.
				$cons = "";
				$cons2 = "";
				if ($this->useLapsConstraints()) {
					$cons2 .= " AND record_nbLaps = " . $this->getNbOfLaps();
				} else {
					$cons2 .= " AND record_nbLaps = 1";
				}

				$exec = "UPDATE `localrecords`
                    SET `record_nbFinish` = " . $this->mlepp->db->quote($nbFinish) . ",
                        `record_avgScore` = " . $this->mlepp->db->quote($avgScore) . "
                    WHERE `record_playerlogin` = " . $this->mlepp->db->quote($login) . "
						AND `record_challengeuid` = " . $this->mlepp->db->quote($uid) . "
							" . $cons2 . ";";


				$this->mlepp->db->query($exec);
			}
		} else {
			// player doesn't have a record on the track,so add one

			if (!empty($cpScore)) {
				$cps = implode(",", $cpScore);
				$r[0] = " " . $this->mlepp->db->quote($cps) . " ";
				$r[1] = " " . $this->mlepp->db->quote($cps) . " ";
			} else {
				$r[0] = " '' ";
				$r[1] = " '' ";
			}

			$cons = 1;
			if ($this->useLapsConstraints()) {
				$cons = $this->getNbOfLaps();
			}


			$q = "INSERT INTO `localrecords` (`record_playerlogin`,
                                                    `record_challengeuid`,
                                                    `record_score`,
                                                    `record_nbFinish`,
                                                    `record_avgScore`,
                                                    `record_gamemode`,
													`record_nbLaps`,
                                                    `record_date`,
                                                    `record_checkpoints`
                                                   ) VALUES (
                                                    " . $this->mlepp->db->quote($login) . ",
                                                    " . $this->mlepp->db->quote($uid) . ",
                                                    " . $this->mlepp->db->quote($score) . ",
                                                    1,
                                                    " . $this->mlepp->db->quote($score) . ",
                                                    " . $this->mlepp->db->quote($gamemode) . ",
													$cons,
                                                    " . $this->mlepp->db->quote(date('Y-m-d H:i:s')) . ",
                                                    " . $r[0] . "
                                                   )";

			$this->mlepp->db->query($q);
			// Get New rank
			$record = new Record();
			$record->login = $login;
			$record->nickName = $player->nickName;
			$record->score = $score;
			$record->nbFinish = 1;
			$record->avgScore = $score;
			$record->gamemode = $gamemode;
			$record->rank = -1;
			$record->ScoreCheckpoints = $cpScore;

			$this->currentChallengePlayerRecords[$login] = $record;

			$i = 1;
			$do = true;
			$done = false;
			$buff1 = null;
			$buff2 = null;
			while (isset($this->currentChallengeRecords[$i]) && $do) {
				if (($this->currentChallengeRecords[$i]->score > $score && $gamemode != 4)
						|| ($this->currentChallengeRecords[$i]->score < $score && $gamemode == 4)) {

					if ($buff1 == null) {
						$this->currentChallengeRecords[$i]->rank++;
						$buff1 = $this->currentChallengeRecords[$i];
						$this->currentChallengeRecords[$i] = $this->currentChallengePlayerRecords[$login];
						$recordrank_new = $i;
						$done = true;
					} else {
						$this->currentChallengeRecords[$i]->rank++;
						$buff2 = $this->currentChallengeRecords[$i];
						$this->currentChallengeRecords[$i] = $buff1;
						$buff1 = $buff2;
					}
				}
				$i++;
			}
			if ($buff1 != null) {
				$this->currentChallengeRecords[$i] = $buff1;
			}

			if (!$done) {
				$this->currentChallengeRecords[$i] = $this->currentChallengePlayerRecords[$login];
				$recordrank_new = $i;
			}

			/* if($this->currentChallengePlayerRecords[$login]->rank == -1 && empty($this->currentChallengeRecords[$i])){
			  $this->currentChallengeRecords[1] = $this->currentChallengePlayerRecords[$login];
			  $this->currentChallengePlayerRecords[$login]->rank = 1;
			 */

			$this->currentChallengePlayerRecords[$login]->rank = $recordrank_new;
			$this->currentChallengePlayerRecords[$login]->score = $score;

			//print_r($this->currentChallengePlayerRecords[$login]);
			//print_r($this->currentChallengeRecords);
			// if the new rank is in records range, show it.
			if ($recordrank_new <= $this->config->numrec && $recordrank_new != -1) {
				if ($this->config->showChatRecords == true && $recordrank_new <= $this->config->maxRecsDisplayed) {
					$message = $this->controlMsg($this->config->newRecordChat, $player, $recordrank_new, $score);
					$this->mlepp->sendChat($message);
				} else {
					$message = $this->controlMsg($this->config->newRecordPrivate, $player, $recordrank_new, $score);
					$this->mlepp->sendChat($message, $player->login);
				}
				Console::println('[' . date('H:i:s') . '] [MLEPP] [LocalRecords] [' . $player->login . '] Player claimed the ' . $recordrank_new . '. Local Record with time: ' . Time::fromTM($score) . '!');
				Dispatcher::dispatch(new onRecordUpdate($this->currentChallengePlayerRecords[$login], -1, onRecordUpdate::firtRecord));
			} else {
				Dispatcher::dispatch(new onRecordUpdate($this->currentChallengePlayerRecords[$login], -1, onRecordUpdate::bestScore));
			}
		}
	}

	 /**
	 * getPlayerRecord()
	 * Helper function, gets the record of the asked player.
	 *
	 * @param mixed $login
	 * @param mixed $uId
	 * @return Record $record
	 */
	private function getPlayerRecord($login, $uId) {

		$cons = "";
		if ($this->useLapsConstraints()) {
			$cons .= " AND record_nbLaps = " . $this->getNbOfLaps();
		} else {
			$cons .= " AND record_nbLaps = 1";
		}

		$q = "SELECT * FROM `localrecords`, `players`
        WHERE `record_challengeuid` = " . $this->mlepp->db->quote($uId) . "
            AND `record_playerlogin` = " . $this->mlepp->db->quote($login) . "
            AND `player_login` = `record_playerlogin`
			" . $cons . ";";

		$dbData = $this->mlepp->db->query($q);
		if ($dbData->recordCount() > 0) {

			$record = new Record();
			$data = $dbData->fetchStdObject();

			$record->rank = -1;
			$record->login = $data->record_playerlogin;
			$record->nickName = $data->player_nickname;
			$record->score = $data->record_score;
			$record->nbFinish = $data->record_nbFinish;
			$record->avgScore = $data->record_avgScore;
			$record->gamemode = $data->record_gamemode;
			$record->ScoreCheckpoints = explode(",", $data->record_checkpoints);

			return $record;
		} else {
			return false;
		}
	}

	 /**
	 * useLapsConstraints()
	 * Helper function, checks game mode.
	 *
	 * @return int $laps
	 */
	public function useLapsConstraints() {
		if (!$this->config->lapsModeCount1lap) {
			$gamemode = $this->storage->gameInfos->gameMode;

			if ($gamemode == 1 || $gamemode == 3 || $gamemode == 4 || $gamemode == 5) {
				$nbLaps = $this->getNbOfLaps();
				if ($nbLaps > 1) {
					return $this->storage->currentChallenge->lapRace;
				}
			}
		}
		return false;
	}

	 /**
	 * getNbOfLaps()
	 * Helper function, gets number of laps.
	 *
	 * @return int $laps
	 */
	public function getNbOfLaps() {
		switch ($this->storage->gameInfos->gameMode) {
			case 0:
				if ($this->storage->gameInfos->roundsForcedLaps == 0)
					return $this->storage->currentChallenge->nbLaps;
				else
					return $this->storage->currentChallenge->lapRace;
			case 2:
			case 5:
				return $this->storage->gameInfos->roundsForcedLaps;
				break;

			case 3:
				return $this->storage->gameInfos->lapsNbLaps;
				break;

			default:
				return 1;
		}
	}

	 /**
	 * showFirstRecord()
	 * Function used for showing the first record.
	 *
	 * @return
	 */
	private function showFirstRecord() {
		$challenge = $this->storage->currentChallenge;

		if ($this->currentChallengeRecords == NULL) {
			$this->mlepp->sendChat('%recordcolor%No current record on %variable%' . $challenge->name . '$z$s%recordcolor% yet!');
			$this->console('No records yet.');
			return;
		}

		$record = $this->currentChallengeRecords[1];
		$this->mlepp->sendChat('%recordcolor%Current record on %variable%' . $challenge->name . '$z$s%recordcolor% by %winnercolor%' . $record->nickName . '$z$s%recordcolor% with a time of %variable%' . Time::fromTM($record->score) . '%recordcolor%!');
		$this->console('Current record by ' . $record->login . ' with a time of ' . Time::fromTM($record->score) . '.');
	}

	 /**
	 * showPlayerRecord()
	 * Function used for showing players record to corresponding login.
	 *
	 * @return
	 */
	private function showPlayerRecord($single=false) {
		$challenge = $this->storage->currentChallenge;

		if ($this->currentChallengeRecords == NULL) {
			//$this->mlepp->sendChat('$0f0No current record on $fff'.$challenge->name.'$z$s$0f0 yet!');
			return;
		}

		if ($single != false) {
			if (isset($this->currentChallengePlayerRecords[$single])) {
				if ($this->currentChallengePlayerRecords[$single]->rank == -1) {
					$message = '%recordcolor%You own the %variable%' . $this->currentChallengePlayerRecords[$single]->rank . '. $z$s%recordcolor%record on %variable%' . $challenge->name . '$z$s%recordcolor% with a time of %variable%' . Time::fromTM($this->currentChallengePlayerRecords[$single]->score) . '%recordcolor%!';
					$this->mlepp->sendChat($message, $single);
					return;
				}
				$message = '%recordcolor%You own the %variable%' . $this->currentChallengePlayerRecords[$single]->rank . '. $z$s%recordcolor%record on %variable%' . $challenge->name . '$z$s%recordcolor% with a time of %variable%' . Time::fromTM($this->currentChallengePlayerRecords[$single]->score) . '%recordcolor%!';
			} else {
				$message = "%recordcolor%You don't have a record yet!";
			}
			$this->mlepp->sendChat($message, $single);
			return;
		}

		foreach ($this->storage->players as $login => $player) {
			if (isset($this->currentChallengePlayerRecords[$login])) {
				if ($this->currentChallengePlayerRecords[$login]->rank == -1) {
					//$message = '%recordcolor%You own a record%variable% which is outside the top:' . $this->config->numrec . '. $z$s%recordcolor%record on %variable%' . $challenge->name . '$z$s%recordcolor% with a time of %variable%' . Time::fromTM($this->currentChallengePlayerRecords[$login]->score) . '%recordcolor%!';
					$message = "%recordcolor%You don't have a ranked record yet!";
					$this->mlepp->sendChat($message, $login);
					continue;
				}
				$message = '%recordcolor%You own the %variable%' . $this->currentChallengePlayerRecords[$login]->rank . '. $z$s%recordcolor%record on %variable%' . $challenge->name . '$z$s%recordcolor% with a time of %variable%' . Time::fromTM($this->currentChallengePlayerRecords[$login]->score) . '%recordcolor%!';
			} else {
				$message = "%recordcolor%You don't have a record yet!";
			}
			$this->mlepp->sendChat($message, $login);
		}
	}

	 /**
	 * showRecordsWindow()
	 * Function providing the records window.
	 *
	 * @param mixed $login
	 * @param bool $test
	 * @return void
	 */
	function showRecordsWindow($login, $test = false) {
		if ($test == null)
		//echo "happy";
			if ($this->currentChallengeRecords != false) {
				$window = LocalRecsWindow::Create($login);
				$window->clearAll();

				$challenges = $this->storage->challenges;
				$window->addColumn('Rank', 0.1);
				$window->addColumn('Login', 0.3);
				$window->addColumn('NickName', 0.4);
				$window->addColumn('Score', 0.2);
				$window->clearItems();

				$i = 1;
				while (isset($this->currentChallengeRecords[$i])) {
					$data = $this->currentChallengeRecords[$i];
					$entry = array(
						'Rank' => array($data->rank),
						'Login' => array($data->login),
						'NickName' => array('$fff' . $data->nickName),
						'Score' => array(Time::fromTM($data->score))
					);

					$window->addItem($entry);
					$i++;
				}
				$window->setSize(180, 100);
				$window->centerOnScreen();
				$window->show();
			} else {
				$loginObj = Storage::GetInstance()->getPlayerObject($login);
				$message = '%error%$iNo records found!';
				$this->mlepp->sendChat($message, $loginObj);
				$this->console('[' . $login . '] Asked for Records Window, but no records yet.');
			}
	}

	 /**
	 * initDatabaseTables()
	 * Function providing the Local Records database,
	 * called on initializing of ManiaLive.
	 *
	 * @return
	 */
	private function initDatabaseTables() {
		if (!$this->mlepp->db->tableExists('localrecords')) {

			$q = "CREATE TABLE `localrecords` (
                               	`record_id` MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                `record_challengeuid` VARCHAR( 27 ) NOT NULL DEFAULT '0',
                                `record_playerlogin` VARCHAR( 30 ) NOT NULL DEFAULT '0',
                                `record_gamemode` INTEGER NOT NULL,
								`record_score` MEDIUMINT( 9 ) DEFAULT '0',
                                `record_nbFinish` MEDIUMINT( 4 ) DEFAULT '0',
                                `record_avgScore` MEDIUMINT( 9 ) DEFAULT '0',
                                `record_checkpoints` TEXT,
                                `record_date` DATETIME
                            ) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = MYISAM ;";
			$this->mlepp->db->query($q);
			$this->console("Created records table successfully.");
			$this->mlepp->db->setDatabaseVersion('localrecords', 1);
		}

		//We will modify the table
		if ($this->mlepp->db->getDatabaseVersion('localrecords') == '1') {
			//Updating To Version 2

			$g = "ALTER TABLE  `localrecords` ADD  `record_nbLaps` INT( 3 ) NOT NULL AFTER  `record_gamemode`";
			$this->mlepp->db->query($g);

			$g = "UPDATE  `localrecords` SET  `record_nbLaps` =  '1'";
			$this->mlepp->db->query($g);

			$this->console("Updated records table to V2 successfully.");
			$this->mlepp->db->setDatabaseVersion('localrecords', 2);
		}

		if ($this->mlepp->db->getDatabaseVersion('localrecords') == '2') {
			//Updating To Version 3

			$g = "ALTER TABLE  localrecords ADD INDEX (  record_challengeuid ,  record_playerlogin ,  record_nbLaps);";
			$this->mlepp->db->query($g);

			$this->console("Updated records table to V3 successfully.");
			$this->mlepp->db->setDatabaseVersion('localrecords', 3);
		}
		return;


		$this->initDatabaseTables();
	}

	 /**
	 * console()
	 * Helper function, addes MLEPP messages.
	 *
	 * @param mixed $text
	 * @return void
	 */
	function console($text) {
		Console::println('[' . date('H:i:s') . '] [MLEPP] [LocalRecords] ' . $text);
	}

	 /**
	 * stringToBool()
	 * Sets string into boolean.
	 *
	 * @param string $string
	 * @return bool $bool
	 */
	private function stringToBool($string) {
		if (strtoupper($string) == "FALSE" || $string == "0" || strtoupper($string) == "NO" || empty($string))
			return false;
		return true;
	}

	 /**
	 * controlMsg()
	 * Helper function, used for parsing the join/leave messages.
	 *
	 * @param mixed $msg
	 * @param mixed $player
	 * @return
	 */
	function controlMsg($msg, $player, $recordrank_new, $score, $recordrank_old=0, $old_score=0) {

		$message = $msg;
		$message = str_replace('%nickname%', $player->nickName, $message);
		$message = str_replace('%newrank%', $recordrank_new, $message);
		$message = str_replace('%score%', Time::fromTM($score), $message);
		$message = str_replace('%oldrank%', $recordrank_old, $message);
		$message = str_replace('%oldscore%', Time::fromTM($old_score), $message);
		$message = str_replace('%diff%', Time::fromTM($old_score - $score), $message);

		return $message;
	}

}

?>