<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Database --
 * @name Database
 * @date 26-06-2011
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

namespace ManiaLivePlugins\MLEPP\Database;

use ManiaLive\Utilities\Console;
use ManiaLive\Event\Dispatcher;
use ManiaLib\Utils\TMStrings as String;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class Database extends \ManiaLive\PluginHandler\Plugin {

	protected $mlepp;

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	function onInit() {
		$version = 1050;
		$this->setVersion($version);
		$this->setPublicMethod('getVersion');
		//ML Repository
		
		$this->mlepp = Mlepp::getInstance();
	}

	 /**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	function onLoad() {
		Console::println('[' . date('H:i:s') . '] [MLEPP] Enabling MLEPP Database r' . $this->getVersion());
		$this->enableDatabase();
		$this->enableDedicatedEvents();


		$this->setPublicMethod('insertChallenge');
		if ($this->isPluginLoaded('MLEPP\AddRemoveTracks', 251)) {
			Dispatcher::register(\ManiaLivePlugins\MLEPP\AddRemoveTracks\Events\onTrackAdded::getClass(), $this);
		}
		if ($this->isPluginLoaded('MLEPP\DonatePanel', 251)) {
			Dispatcher::register(\ManiaLivePlugins\MLEPP\DonatePanel\Events\onPlanetsDonate::getClass(), $this);
		}
	}

	 /**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */
	function onReady() {
		$this->mlepp->db = new mleppDatabase($this->db);
		$this->initCreateTables();
		$this->updatePlayersOnline();
		$this->updateServerChallenges();
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
		$this->updatePlayer($login);
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
	 * onBeginChallenge()
	 * Function called on begin of challenge.
	 *
	 * @param mixed $challenge
	 * @param mixed $warmUp
	 * @param mixed $matchContinuation
	 * @return void
	 */
	function onBeginChallenge($challenge, $warmUp, $matchContinuation) {
		$this->checkDatabaseChallenge($challenge);
	}

	 /**
	 * onTrackAdded()
	 * Function called after adding a track.
	 *
	 * @param mixed $login
	 * @param mixed $filename
	 * @param bool $isTmx
	 * @param mixed $gameversion
	 * @return void
	 */
	function onTrackAdded($login, $filename, $isTmx = false, $gameversion = NULL) {
		$challenge = $this->connection->getChallengeInfo($filename);
		$this->insertChallenge($challenge, $login);
		$this->console('Challenge inserted via add/remove tracks event.');

		if ($isTmx) {
			$this->updateChallengeTMXdata($challenge->uId, $gameversion);
			$this->console('Challenge TMXData inserted via add/remove tracks event.');
		}
	}

	 /**
	 * onplanetDonate()
	 * Function called when someone makes a donation to the server.
	 *
	 * @param mixed $login
	 * @param mixed $amount
	 * @param mixed $plugin
	 * @param mixed $description
	 * @return void
	 */
	function onPlanetsDonate($login, $amount, $plugin, $description) {

		$q = "INSERT INTO `planettransactions` (`planets_login`,
                                                    `planets_amount`,
                                                    `planets_fromplugin`,
                                                    `planets_description`,
                                                    `planets_transfertime`
                                                    )
		                                VALUES (" . $this->mlepp->db->quote($login) . ",
                                                " . $this->mlepp->db->quote($amount) . ",
                                                " . $this->mlepp->db->quote($plugin) . ",
                                                " . $this->mlepp->db->quote($description) . ",
                                           		" . $this->mlepp->db->quote(date('Y-m-d H:i:s')) . "
                                                )";
		$this->mlepp->db->query($q);
		$this->console('planetdonation from ' . $login . ' added to database');
	}

	 /**
	 * console()
	 * Helper function, outputs into the console with MLEPP messages.
	 *
	 * @param mixed $text
	 * @return void
	 */
	function console($text) {
		Console::println('[' . date('H:i:s') . '] [MLEPP] [Database] ' . $text);
	}

	 /**
	 * getHTTPdata()
	 * Helper function, gets trackdata of TMX.
	 *
	 * @param mixed $url
	 * @return
	 */
	function getHTTPdata($url) {
		$options = array('http' => array(
				'user_agent' => 'manialive tmx-getter', // who am i
				'max_redirects' => 1000, // stop after 10 redirects
				'timeout' => 1000, // timeout on response
				));
		$context = stream_context_create($options);
		return @file_get_contents($url, true, $context);
	}

	/*	 * **********************************
	 * Gets the table version in use
	 * returns database version, or false if data not exist.
	 * ******************************************
	 */

	 /**
	 * getDatabaseVersion()
	 * Gets the table version.
	 *
	 * @param mixed $table
	 * @param mixed $fromPlugin
	 * @return int $tableVersion
	 * @return bool false - data does not exist
	 */
	function getDatabaseVersion($table, $fromPlugin = null) {
		$g = "SELECT * FROM `databaseversion` WHERE `database_table` = " . $this->mlepp->db->quote($table) . ";";
		$query = $this->mlepp->db->query($g);

		if ($query->recordCount() == 0) {
			return false;
		} else {
			$record = $query->fetchStdObject();
			return $record->database_version;
		}
	}

	 /**
	 * setDatabaseVersion()
	 * Helper function, sets database version.
	 *
	 * @param mixed $table
	 * @param mixed $version
	 * @return void
	 */
	function setDatabaseVersion($table, $version) {

		$g = "SELECT * FROM `databaseversion` WHERE `database_table` = " . $this->mlepp->db->quote($table) . ";";
		$query = $this->mlepp->db->query($g);

		if ($query->recordCount() == 0) {

			$q = "INSERT INTO `databaseversion` (`database_table`,
								 `database_version`
								 ) VALUES (
								 " . $this->mlepp->db->quote($table) . ",
								 " . $this->mlepp->db->quote($version) . "
								 )";
			$this->mlepp->db->query($q);
		} else {

			$q = "UPDATE
			`databaseversion`
			SET
			`database_version` = " . $this->mlepp->db->quote($version) . "
			WHERE
			`database_table` = " . $this->mlepp->db->quote($table) . ";";

			$this->mlepp->db->query($q);
		}
		$this->console("set new database version: table $table -> version $version.");
	}

	 /**
	 * initCreateTables()
	 * Function called onInit, checks tables and creates if needed.
	 *
	 * @return void
	 */
	function initCreateTables() {
		$this->console("Checking and creating tables if needed..");
		if (!$this->mlepp->db->tableExists('databaseversion'))
			$this->createDatabaseTable();

		if (!$this->mlepp->db->tableExists('players'))
			$this->createPlayersTable();

		if (!$this->mlepp->db->tableExists('challenges'))
			$this->createChallengesTable();

		if (!$this->mlepp->db->tableExists('tmxdata'))
			$this->createTmxTable();

		if (!$this->mlepp->db->tableExists('planettransactions'))
			$this->createPlanetsTable();

		if (!$this->mlepp->db->tableExists('karma'))
			$this->createKarmaTable();

		if (!$this->mlepp->db->tableExists('serverchallengelist'))
			$this->createserverchallengelist();

		if (!$this->mlepp->db->tableExists('depot'))
			$this->createDepotTable();

		//Updating Tables To V2
		if ($this->mlepp->db->getDatabaseVersion('players') == '1')
			$this->updatePlayersTable2();

		if ($this->mlepp->db->getDatabaseVersion('challenges') == '1')
			$this->updateChallengesTable2();

		if ($this->mlepp->db->getDatabaseVersion('karma') == '1')
			$this->updateKarmaTable2();

		if ($this->mlepp->db->getDatabaseVersion('tmxdata') == '1')
			$this->updateTmxdataTable2();
	}

	 /**
	 * createDatabaseTable()
	 * Helper function, creates Database version table.
	 *
	 * @return void
	 */
	function createDatabaseTable() {

		$q = "CREATE TABLE `databaseversion` (
					`database_id` mediumint(9) NOT NULL AUTO_INCREMENT,
					`database_table` varchar(50) NOT NULL,
					`database_version` mediumint(9) NOT NULL,
					 PRIMARY KEY (`database_id`)
                ) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM";
		$this->mlepp->db->query($q);
		$this->console("created database version table.");
	}

	 /**
	 * createplanetsTable()
	 * Helper function, creates planets table.
	 *
	 * @return void
	 */
	function createPlanetsTable() {
		if ($this->getDatabaseVersion('planettransactions') == false)
			$this->setDatabaseVersion('planettransactions', 1);

		$q = "CREATE TABLE `planettransactions` (
                                    `planets_id` MEDIUMINT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                    `planets_login` VARCHAR( 50 ) NOT NULL ,
                                    `planets_amount` MEDIUMINT ( 9 ) NOT NULL ,
                                    `planets_fromplugin` VARCHAR( 50 ) NOT NULL ,
                                    `planets_description` VARCHAR( 200 ),
                                    `planets_transfertime` DATETIME
                                    ) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = MYISAM ;";
		$this->mlepp->db->query($q);
		$this->console("created planet transactions table.");
	}

	 /**
	 * createDepotTable()
	 * Helper function, creates planets table.
	 *
	 * @return void
	 */
	function createDepotTable() {
		if ($this->getDatabaseVersion('depot') == false)
			$this->setDatabaseVersion('depot', 1);


		$q = 'CREATE TABLE `depot` (
		`depot_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
		`depot_group` VARCHAR(45) NOT NULL,
		`depot_subgroup` VARCHAR(45) NOT NULL,
		`depot_label` VARCHAR(45) NOT NULL,
		`depot_value` TEXT NOT NULL,
		PRIMARY KEY (`depot_id`),
		INDEX (depot_group, depot_subgroup, depot_label))
		ENGINE = MYISAM;';

		$this->mlepp->db->query($q);
		$this->console("created depot table.");
	}

	 /**
	 * createKarmaTable()
	 * Helper function, creates Karma table.
	 *
	 * @return void
	 */
	function createKarmaTable() {
		if ($this->getDatabaseVersion('karma') == false)
			$this->setDatabaseVersion('karma', 1);

		$q = "CREATE TABLE `karma` (
                                    `karma_id` MEDIUMINT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                    `karma_playerlogin` VARCHAR( 50 ) NOT NULL ,
                                    `karma_trackuid` VARCHAR( 50 ) NOT NULL ,
                                    `karma_value` MEDIUMINT ( 5 ) NOT NULL
                                    ) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = MYISAM ;";
		$this->mlepp->db->query($q);
		$this->console("created karma table.");
	}

	 /**
	 * createTmxTable()
	 * Helper function, creates TmxInfo table.
	 *
	 * @return void
	 */
	function createTmxTable() {
		if ($this->getDatabaseVersion('tmxdata') == false)
			$this->setDatabaseVersion('tmxdata', 1);
		$q = "CREATE TABLE `tmxdata` (
                                    `tmx_id` MEDIUMINT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                    `tmx_trackuid` VARCHAR( 27 ) NOT NULL ,
                                    `tmx_tmxid` MEDIUMINT( 12 ) NOT NULL ,
                                    `tmx_trackname` VARCHAR( 100 ) NOT NULL ,
                                    `tmx_username` VARCHAR( 100 ) NOT NULL ,
                               		`tmx_type` VARCHAR( 30 ) NOT NULL ,
                                    `tmx_environment` VARCHAR( 30 ) NOT NULL ,
                                    `tmx_mood` VARCHAR( 50 ) NOT NULL,
                                    `tmx_style` VARCHAR( 20 ) NOT NULL,
                                    `tmx_routes` VARCHAR( 20 ) NOT NULL,
                                    `tmx_lenght` INT( 10 ) NOT NULL,
                                    `tmx_difficulty` VARCHAR( 20 ) NOT NULL,
                                    `tmx_lbscore` INT( 12 ) NOT NULL,
                                    `tmx_gameversion` VARCHAR( 20 ) NOT NULL,
                                    `tmx_comments` TEXT,
                                    `tmx_awards` TEXT
                                    ) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = MYISAM ;";
		$this->mlepp->db->query($q);
		$this->console("created tmxdata table.");
	}

	 /**
	 * createserverchallengelist()
	 * Helper function, creates serverchallengelist table.
	 *
	 * @return void
	 */
	function createserverchallengelist() {
		if ($this->getDatabaseVersion('serverchallengelist') == false) {
			$this->setDatabaseVersion('serverchallengelist', 1);
		}


		$q = "CREATE TABLE `serverchallengelist` (
                       `Challenge_id` INT( 11 ) NOT NULL ,
                       `server_login` VARCHAR( 100 ) NOT NULL ,
                       PRIMARY KEY (  `Challenge_id` ,  `server_login` )) ENGINE = MYISAM ;";
		$this->mlepp->db->query($q);
	}

	 /**
	 * createChallengesTable()
	 * Helper function, creates Challenges table.
	 *
	 * @return void
	 */
	function createChallengesTable() {
		if ($this->getDatabaseVersion('challenges') == false) {
			$this->setDatabaseVersion('challenges', 1);
		}

		$q = "CREATE TABLE `challenges` (
                                    `challenge_id` MEDIUMINT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                    `challenge_uid` VARCHAR( 27 ) NOT NULL ,
                                    `challenge_name` VARCHAR( 100 ) NOT NULL ,
                                    `challenge_nameStripped` VARCHAR( 100 ) NOT NULL ,
                                    `challenge_file` VARCHAR( 200 ) NOT NULL ,
                                    `challenge_author` VARCHAR( 30 ) NOT NULL ,
                                    `challenge_environment` VARCHAR( 15 ) NOT NULL,

                                    `challenge_mood` VARCHAR( 50 ) NOT NULL,
                                    `challenge_bronzeTime` INT( 10 ) NOT NULL,
                                    `challenge_silverTime` INT( 10 ) NOT NULL,
                                    `challenge_goldTime` INT( 10 ) NOT NULL,
                                    `challenge_authorTime` INT( 10 ) NOT NULL,
                                    `challenge_copperPrice` INT( 10 ) NOT NULL,
                                    `challenge_lapRace` INT( 1 ) NOT NULL,
                                    `challenge_nbLaps` INT( 3 ) NOT NULL,
                                    `challenge_nbCheckpoints` INTEGER( 3 ) NOT NULL,
                                    `challenge_addedby` MEDIUMINT(9),
                                    `challenge_addtime` DATETIME
                                    ) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = MYISAM ;";
		$this->mlepp->db->query($q);
		$this->console("created challenges table.");
	}

	 /**
	 * createPlayersTable()
	 * Helper function, creates Players table.
	 *
	 * @return void
	 */
	function createPlayersTable() {
		if ($this->getDatabaseVersion('players') == false) {
			$this->setDatabaseVersion('players', 1);
		}
		$q = "CREATE TABLE `players` (
					`player_id` mediumint(9) NOT NULL AUTO_INCREMENT,
					`player_login` varchar(50) NOT NULL,
					`player_nickname` varchar(100) NOT NULL,
					`player_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					`player_wins` mediumint(9) NOT NULL DEFAULT '0',
					`player_timeplayed` mediumint(9) NOT NULL DEFAULT '0',
					`player_onlinerights` varchar(10) NOT NULL,
					`player_ip` varchar(50),
					`player_clanid` mediumint(9),
					PRIMARY KEY (`player_id`)
                ) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM";
		$this->mlepp->db->query($q);
		$this->console("created players table.");
	}

	private function updatePlayersTable2() {

		$q = "ALTER TABLE  players ADD INDEX (  player_login )";
		$this->mlepp->db->query($q);

		$this->console("Updated players table to V2 successfully.");
		$this->setDatabaseVersion('players', 2);
	}

	private function updateChallengesTable2() {

		$q = "ALTER TABLE  challenges ADD INDEX (  challenge_uid )";
		$this->mlepp->db->query($q);

		$this->console("Updated challenges table to V2 successfully.");
		$this->setDatabaseVersion('challenges', 2);
	}

	private function updateTmxdataTable2() {



		$q = "ALTER TABLE  tmxdata ADD INDEX (  tmx_trackuid )";
		$this->mlepp->db->query($q);



		$q = "ALTER TABLE  tmxdata ADD INDEX (  tmx_tmxid )";
		$this->mlepp->db->query($q);

		$this->console("Updated tmxdata table to V2 successfully.");
		$this->setDatabaseVersion('tmxdata', 2);
	}

	private function updateKarmaTable2() {

		$q = "SHOW INDEX FROM karma";
		$query = $this->mlepp->db->query($q);

		$done = array();
		if ($query->recordCount() != 0) {
			while ($data = $query->fetchStdObject()) {
				if ($data->Non_unique == 1 && !isset($done[$data->Key_name])) {
					$q = "ALTER TABLE  karma DROP INDEX " . $data->Key_name . " ;";
					$this->mlepp->db->query($q);
					$done[$data->Key_name] = 1;
				}
			}
		}

		$q = "ALTER TABLE  karma ADD INDEX (  karma_playerlogin, karma_trackuid )";
		$this->mlepp->db->query($q);

		$this->console("Updated karma table to V2 successfully.");
		$this->setDatabaseVersion('karma', 2);
	}

	 /**
	 * updatePlayersOnline()
	 * Hepler function, updates all online players.
	 *
	 * @return void
	 */
	function updatePlayersOnline() {

		foreach ($this->storage->players as $login => $player) { // get players
			$this->updatePlayer($login);
		}

		foreach ($this->storage->spectators as $login => $player) { // get spectators
			$this->updatePlayer($login);
		}
	}

	 /**
	 * updatePlayer()
	 * Helper function, updates player.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function updatePlayer($login) {

		//check if player is at database

		$g = "SELECT * FROM `players` WHERE `player_login` = " . $this->mlepp->db->quote($login) . ";";
		$query = $this->mlepp->db->query($g);
		// get player data
		$player = $this->storage->getPlayerObject($login);

		if ($query->recordCount() == 0) {
			// 	--> add new player entry

			$q = "INSERT INTO `players` (`player_login`,
                                                    `player_nickname`,
                                                    `player_updated`,
                                                    `player_ip`,
                                                    `player_onlinerights`,
                                                    `player_wins`,
                                                    `player_timeplayed`
                                                    )
		                                VALUES (" . $this->mlepp->db->quote($player->login) . ",
                                                " . $this->mlepp->db->quote($player->nickName) . ",
                                                " . $this->mlepp->db->quote(date('Y-m-d H:i:s')) . ",
                                                " . $this->mlepp->db->quote($player->iPAddress) . ",
                                                " . $this->mlepp->db->quote($player->onlineRights) . ",
                                                0,
                                                0
                                                )";
			$this->mlepp->db->query($q);
		} else {
			//	--> update existing player entry


			$q =
					"UPDATE
			`players`
			 SET
			 `player_nickname` = " . $this->mlepp->db->quote($player->nickName) . ",
			 `player_updated` = " . $this->mlepp->db->quote(date('Y-m-d H:i:s')) . ",
			 `player_ip` =  " . $this->mlepp->db->quote($player->iPAddress) . ",
			 `player_onlinerights` = " . $this->mlepp->db->quote($player->onlineRights) . "
			 WHERE
			 `player_login` = " . $this->mlepp->db->quote($login) . ";";
			$this->mlepp->db->query($q);
		}
	}

	 /**
	 * updateServerChallenges()
	 * Helper function, updates server challenges.
	 *
	 * @return void
	 */
	function updateServerChallenges() {
		//get server challenges
		$serverChallenges = $this->storage->challenges;
		//get database challenges

		$g = "SELECT * FROM `challenges`;";
		$query = $this->mlepp->db->query($g);

		$databaseUid = array();
		//get database uid's of tracks.
		while ($data = $query->fetchStdObject()) {
			$databaseUid[$data->challenge_uid] = $data->challenge_uid;
		}

		unset($data);
		$addCounter = 0;
		foreach ($serverChallenges as $data) {
			// check if database doesn't have the challenge already.
			if (!array_key_exists($data->uId, $databaseUid)) {
				$this->insertChallenge($data);
				$addCounter++;
			}
		} // foreach
		$this->console("created $addCounter new challenge entries to database!");
	}

	 /**
	 * insertChallenge()
	 * Helper function, inserts challenge into database.
	 *
	 * @param mixed $data
	 * @param string $login
	 * @return void
	 */
	public function insertChallenge($data, $login = 'n/a') {
		if (empty($data->mood)) {
			$connection = \ManiaLive\DedicatedApi\Connection::getInstance();
			try {
				$data = $connection->getChallengeInfo($data->fileName);
			} catch (\Exception $e) {
				$this->mlepp->sendChat('%adminerror%' . $e->getMessage(), $login);
			}
		}

		$q = "INSERT INTO `challenges` (`challenge_uid`,
                                    `challenge_name`,
                                    `challenge_nameStripped`,
                                    `challenge_file`,
                                    `challenge_author`,
                                    `challenge_environment`,
                                    `challenge_mood`,
                                    `challenge_bronzeTime`,
                                    `challenge_silverTime`,
                                    `challenge_goldTime`,
                                    `challenge_authorTime`,
                                    `challenge_copperPrice`,
                                    `challenge_lapRace`,
                                    `challenge_nbLaps`,
                                    `challenge_nbCheckpoints`,
                                    `challenge_addedby`,
                                    `challenge_addtime`
                                    )
                                VALUES (" . $this->mlepp->db->quote($data->uId) . ",
                                " . $this->mlepp->db->quote($data->name) . ",
                                " . $this->mlepp->db->quote(String::stripAllTmStyle($data->name)) . ",
                                " . $this->mlepp->db->quote($data->fileName) . ",
                                " . $this->mlepp->db->quote($data->author) . ",
                                " . $this->mlepp->db->quote($data->environnement) . ",
                                " . $this->mlepp->db->quote($data->mood) . ",
                                " . $this->mlepp->db->quote($data->bronzeTime) . ",
                                " . $this->mlepp->db->quote($data->silverTime) . ",
                                " . $this->mlepp->db->quote($data->goldTime) . ",
                                " . $this->mlepp->db->quote($data->authorTime) . ",
                                " . $this->mlepp->db->quote($data->copperPrice) . ",
                                " . $this->mlepp->db->quote($data->lapRace) . ",
                                " . $this->mlepp->db->quote($data->nbLaps) . ",
                                " . $this->mlepp->db->quote($data->nbCheckpoints) . ",
                                " . $this->mlepp->db->quote($login) . ",
                                " . $this->mlepp->db->quote(date('Y-m-d H:i:s')) . "
                                )";
		$this->mlepp->db->query($q);
	}

	 /**
	 * checkDatabaseChallenge()
	 * Helper function, checks the challenge in the database.
	 *
	 * @param mixed $challenge
	 * @return void
	 */
	function checkDatabaseChallenge($challenge) {
		$q = "SELECT * FROM `challenges` WHERE `challenge_uid` = " . $this->mlepp->db->quote($challenge["UId"]) . ";";
		$query = $this->mlepp->db->query($q);

		if ($query->recordCount() == 0) {
			$this->insertChallenge($this->storage->currentChallenge);
		}
	}

	 /**
	 * updateTMXdatabase()
	 * Helper function, updates all TMX data in the database.
	 *
	 * @return void
	 */
	function updateTMXdatabase() {
		// deletes table due to double numbers.

		$g = "TRUNCATE TABLE `tmxdata`;";
		$query = $this->mlepp->db->query($g);

		//get all tmxdata...

		$g = "SELECT * FROM `tmxdata`;";
		$query = $this->mlepp->db->query($g);

		$tmxData = array();
		while ($data = $query->fetchStdObject()) {
			$tmxData[$data->tmx_trackuid] = $data->tmx_trackuid;
		}
		unset($data);

		//get all database data

		$g = "SELECT * FROM `challenges`;";
		$query = $this->mlepp->db->query($g);
		$addCounter = 0;
		while ($data = $query->fetchStdObject()) {
			if (!array_key_exists($data->challenge_uid, $tmxData)) {
				$this->updateChallengeTMXdata($data->challenge_uid);
				$addCounter++;
			}
		}
	}

	 /**
	 * updateChallengeTMXdata()
	 * Helper function, updates TMX info in the database
	 *
	 * @param mixed $uid
	 * @return
	 */
	function updateChallengeTMXdata($uid) {
		$data = $this->fetchTMXdata($uid);
		if ($data === false)
			return;  // if no tmx data bail out.
		$dat = $this->getTMXawards($uid);
		if ($dat === false)
			return;  // if no tmx data bail out.
		$q = "INSERT INTO `tmxdata` (`tmx_trackuid`,
                                                    `tmx_tmxid`,
                                                    `tmx_trackname`,
                                                    `tmx_username`,
                                                    `tmx_type`,
                                                    `tmx_environment`,
                                                    `tmx_mood`,
                                                    `tmx_style`,
                                                    `tmx_routes`,
                                                    `tmx_lenght`,
                                                    `tmx_difficulty`,
                                                    `tmx_lbscore`,
                                                    `tmx_gameversion`,
                                                    `tmx_comments`,
                                                    `tmx_awards`
                                                    )
                                                VALUES (" . $this->mlepp->db->quote($uid) . ",
                                                                " . $this->mlepp->db->quote($data[0]) . ",
                                                " . $this->mlepp->db->quote($data[1]) . ",
                                                " . $this->mlepp->db->quote($data[3]) . ",
                                                " . $this->mlepp->db->quote($data[7]) . ",
                                                " . $this->mlepp->db->quote($data[8]) . ",
                                                " . $this->mlepp->db->quote($data[9]) . ",
                                                " . $this->mlepp->db->quote($data[10]) . ",
                                                " . $this->mlepp->db->quote($data[11]) . ",
                                                " . $this->mlepp->db->quote($data[12]) . ",
                                                " . $this->mlepp->db->quote($data[13]) . ",
                                                " . $this->mlepp->db->quote($data[14]) . ",
                                                " . $this->mlepp->db->quote($data[15]) . ",
                                                " . $this->mlepp->db->quote($data[16]) . ",
                                                " . $this->mlepp->db->quote($dat[12]) . "
                                                )";
		$this->mlepp->db->query($q);
	}

	 /**
	 * fetchTMXdata()
	 * Helper function, fetches TMX data.
	 *
	 * @param mixed $uid
	 * @return
	 */
	function fetchTMXdata($uid) {
		$found = false;
		$prefixes = array('tmnforever', 'nations', 'united', 'sunrise', 'original');
		foreach ($prefixes as $prefix) {
			if ($found === false) {
				$tmxData = $this->getHTTPdata('http://' . $prefix . '.tm-exchange.com/apiget.aspx?action=apitrackinfo&uid=' . $uid);
				if ($tmxData == 0) {
					$found = false;
				} else {
					$found = true;
					break;
				}
			}
		}

		if ($found === false) {
			return false;
		} else {
			$data = explode("\t", $tmxData);
			return $data;
			//$print_r($data);
		}
	}

	 /**
	 * getTMXawards()
	 * Helper function, gets TMX awards.
	 *
	 * @param mixed $uid
	 * @return
	 */
	function getTMXawards($uid) {
		$found = false;
		$data = $this->fetchTMXdata($uid);
		// get tmxid from track uid
		//print_r($data);
		// assign tmx id
		$tmxid = $data[0];
		//get tmxdata with tmxid fetched.
		$prefixes = array('tmnforever', 'nations', 'united', 'sunrise', 'original');

		foreach ($prefixes as $prefix) {
			if ($found === false) {
				$tmdata = $this->getHTTPdata('http://' . $prefix . '.tm-exchange.com/apiget.aspx?action=apisearch&trackid=' . $tmxid);
				if ($tmdata == 0) {
					$found = false;
				} else {
					$found = true;
					break;
				}
			}
		}

		if ($found === false) {
			return false;
		} else {
			//return tmx-data
			$dat = explode("\t", $tmdata);
			return $dat;
			//$print_r($dat);
		}
	}

	 /**
	 * getTMXdata()
	 * Helper function, updating compleate TMX database.
	 *
	 * @param mixed $fromLogin
	 * @return void
	 */
	function getTMXdata($fromLogin) {
		$admin = $this->storage->getPlayerObject($fromLogin);
		$login = $admin->login;

		$this->mlepp->sendChat('$fff»» %adminaction%Starting full database TMX update, this might take long..', $admin);
		$this->console($login . ' started the full TMX database update.');
		$this->updateTMXdatabase();
		$this->mlepp->sendChat('$fff»» %adminaction%TMXupdate done.', $admin);
		$this->console($login . '\'s full TMX database update is done.');
	}

}

// end of plugin
?>
