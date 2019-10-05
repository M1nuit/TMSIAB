<?php

Aseco::registerEvent('onStartup', 'event_onstartup');
Aseco::registerEvent('onSync', 'event_onsync');
Aseco::registerEvent('onPlayerFinish', 'event_finish');
Aseco::registerEvent('onNewChallenge', 'event_newtrack');
Aseco::registerEvent('onEndRace', 'event_endrace');
//Aseco::registerEvent('onPlayerConnect', 'event_playerjoin');
Aseco::addChatCommand('pb', 'Displays your personal best on the current track');
Aseco::addChatCommand('rank', 'Displays your current server rank');

/*
select `name`, sum(score) as Scr from rs_karma, challenges
where challenges.id=rs_karma.challengeid
group by `name`
order by scr
*/

class Rasp {
	var $aseco;
	var $features;
	var $ranks;
	var $settings;
	var $challenges;
	var $responses;
	var $maxrec;
	var $playerlist;

	function start(&$aseco_ext, &$config_file) {
		global $maxrecs;
		$this->aseco = $aseco_ext;
		$this->aseco->console_text('*-*-*-*-*-* RASP TMU 1.2a is running! *-*-*-*-*-*');
		$this->aseco->console_text('|...Loading Settings');
		if (!$this->settings = $this->xmlparse($config_file)) {
			$this->aseco->console_text('{RASP_ERROR} - Config file parse error!');
			return; }
		else {
			$this->aseco->console_text('|...Loaded!');
			$this->aseco->console_text('|...Checking database structure');
			if($this->checkTables() == false) {
				$this->aseco->console_text('{RASP_ERROR} - Table structure incorrect, use the rasp.sql file to correct this.');
				return;
			}
			$this->aseco->console_text('|...Structure OK!');
			$this->aseco->server->records->setLimit($maxrecs);

			$this->getChallenges(true);

//			$this->resetRanks();

		}
	}

	function xmlparse($config_file) {
 		if ($settings = $this->aseco->xml_parser->parseXml($config_file)) {
			$this->messages = $settings['RASP']['MESSAGES'][0];
			return $settings;
		} else {
			return false;
		}
	}

	function checkTables() {
  		global $ldb_settings;
		$check[1] = false;
		$check[2] = false;

		$query = 'CREATE TABLE IF NOT EXISTS `rs_rank` (
					`playerID` mediumint(9) NOT NULL default 0,
					`avg` INTEGER UNSIGNED NOT NULL default 0,
					KEY `playerID` (`playerID`)
					) TYPE=MyISAM';
		mysql_query($query);

		$environments = array("speed", "rally", "alpine", "coast", "bay", "island", "stadium");

		foreach ($environments as $environment)
			{
			$query = "ALTER TABLE `rs_rank` ADD COLUMN `$environment` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `avg`;";
/*
			$query = 'CREATE TABLE IF NOT EXISTS `rs_rank'.strtolower($environment).'` (
						`playerID` mediumint(9) NOT NULL default 0,
						`avg` INTEGER UNSIGNED NOT NULL default 0,
						KEY `playerID` (`playerID`)
						) TYPE=MyISAM';
*/
			mysql_query($query);
			}

		$query = 'CREATE TABLE IF NOT EXISTS `mistral_trackeval` (
					`ChallengeId` MEDIUMINT(9) NOT NULL DEFAULT 0,
					`PlayerId` MEDIUMINT(9) NOT NULL DEFAULT 0,
					`Eval` MEDIUMINT(9) NOT NULL DEFAULT 0,
					PRIMARY KEY(`ChallengeId`, `PlayerId`)
					) TYPE=MyISAM';
		mysql_query($query);

		$query = 'CREATE TABLE IF NOT EXISTS `mistral_erasedtracks` (
					`ChallengeUId` VARCHAR(27) NOT NULL DEFAULT "",
					`ChallengeId` MEDIUMINT(9) NOT NULL DEFAULT 0,
					`ErasedAt` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
					`ErasedBy` VARCHAR(50) NOT NULL DEFAULT "",
					PRIMARY KEY(`ChallengeUId`)
					) TYPE=MyISAM';
		mysql_query($query);

		$query = 'ALTER TABLE `records` ADD `Environment` VARCHAR(15) NOT NULL DEFAULT \'\'';
		mysql_query($query);

		$query = 'CREATE TABLE IF NOT EXISTS `rs_karma` (
								`Id` int(11) NOT NULL auto_increment,
								`Score` smallint(6) NOT NULL default 0,
								`PlayerId` mediumint(9) NOT NULL default 0,
								`ChallengeId` mediumint(9) NOT NULL default 0,
								PRIMARY KEY	(`Id`),
								UNIQUE KEY `PlayerId` (`PlayerId`,`ChallengeId`),
								KEY `ChallengeId` (`ChallengeId`)
							) TYPE=MyISAM';
		mysql_query($query);

		$res = mysql_list_tables($ldb_settings['mysql']['database']);
		while ($row = mysql_fetch_row($res)){ $tables[] = $row[0]; }

		if (in_array('rs_rank', $tables)) { $check[1] = true; }
		if (in_array('rs_karma', $tables)) { $check[2] = true; }

		mysql_free_result($res);

		if ($check[1] && $check[2]) { return true; }
		else { return false; }
	}

	function getChallenges($start=false) {
		$i = 0;

		if ($start)
			$this->aseco->console_text('|...Syncing challenges and updating environment!');

		while ($alltracks == false) {
			$tracks = $this->aseco->client->addCall('GetChallengeList', array(300, $i));
			if (!$this->aseco->client->multiquery()) {
				trigger_error('[' . $this->aseco->client->getErrorCode() . '] ' . $this->aseco->client->getErrorMessage());
				$response = array();
			} else {
				$tlist = array();
				$response = $this->aseco->client->getResponse();
				if (sizeof($response[$tracks][0]) > 0) {
					foreach ($response[$tracks][0] as $trow) {
						$newlist[] = $trow;
					}
					$i = $i + 300;
				} else {
					$alltracks = true;
					break;
				}
			}
		}

		foreach ($newlist as $row) {
			$query = 'SELECT * FROM challenges WHERE Uid=\'' . mysql_real_escape_string($row['UId']) . '\'';
			$res = mysql_query($query);
			if (!mysql_num_rows($res) > 0) {
				$track = $this->aseco->client->addCall('GetChallengeInfo', array($row['FileName']));
				if (!$this->aseco->client->multiquery()) {
					trigger_error('[' . $this->aseco->client->getErrorCode() . '] ' . $this->aseco->client->getErrorMessage());
					$response = array();
				} else
					{
					$response = $this->aseco->client->getResponse();
					$row['Author'] = $response[$track][0]['Author'];
					}
				$query = 'INSERT INTO challenges
							(Uid, Name, Author, Environment)
							VALUES
							(\'' . mysql_real_escape_string($row['UId']) . '\', \'' . mysql_real_escape_string($row['Name']) . '\', \'' . mysql_real_escape_string($row['Author']) . '\', \'' . $row['Environnement'] . '\')';
				mysql_query($query);
			}
			elseif ($start)
				{
				$cid = getTrackIDfromUID($row['UId']);
			    $query = 'update challenges set environment='.quotedString($row['Environnement']).' where id='.$cid;
				if (!mysql_query($query))
					$this->aseco->console_text('|... cannot update challenge environment.');
				$query = 'update records set environment='.quotedString($row['Environnement']).' where challengeid='.$cid;
				if (!mysql_query($query))
					$this->aseco->console_text('|... cannot update record environment.');
				}

			$tlist[] = $this->getChallengeId($row['UId']);
			mysql_free_result($res);

		}
		$this->challenges = $tlist;
	}

	function onSync(&$aseco, $data)
		{
		global $tmxdir, $feature_tmxadd;
		$sepchar = substr($aseco->server->trackdir, -1, 1);
		if ( $sepchar == '\\' )
			{
			$tmxdir = str_replace('/', $sepchar, $tmxdir);
			}

		if ( !file_exists($aseco->server->trackdir . $tmxdir ) )
			{
			$aseco->console_text('[RASP] Error - TMX Directory (' . $aseco->server->trackdir . $tmxdir . ') does not exist');
			$feature_tmxadd = false;
			}

		if ( !is_writeable($aseco->server->trackdir . $tmxdir ) )
			{
			$aseco->console_text('[RASP] Error - TMX Directory (' . $aseco->server->trackdir . $tmxdir . ') can not be written to');
			$feature_tmxadd = false;
			}

		}  //  onSync

	function resetRanks($environment = "") {
		global $maxrecs, $minrank, $singleenv;

/*
		$players = array();
		$this->aseco->console_text('|...Calculating ranks');
		$this->getChallenges();

		$tracks = $this->challenges;
		$query = 'SELECT PlayerId, COUNT( * ) AS cnt
					FROM records
					GROUP BY PlayerId
					HAVING cnt >' . $minrank;
		$res = mysql_query($query);
		while ($row = mysql_fetch_object($res)) {
			$players[] = $row->PlayerId;
		}
		mysql_free_result($res);

		$this->aseco->console_text('|......Building trackarray with PlayerId/Rank');
		foreach ($tracks as $track) {
			$rank = array();
			$query = 'SELECT PlayerId, Rank
						FROM records
						WHERE ChallengeId=\'' . $track . '\'
						ORDER BY Score ASC,`Date` ASC';
			$res = mysql_query($query);
			$i = 1;
			if (mysql_num_rows($res) == 0) {
				$trank[$track] = array();
			} else {
				while ($row = mysql_fetch_object($res)) {
					$pid = $row->PlayerId;
					$oldrank = $row->Rank;
					$trank[$track][$pid] = $i;
					if ( $oldrank != $i)
						{
						$query="UPDATE records SET rank=$i WHERE playerID=$pid AND ChallengeId=$track";
						if (!mysql_query($query))
							$this->aseco->console_text("Cannot update rank. PID=$pid, CID=$track, Rank=$i");
						}
					$i++;
				}
			}
			mysql_free_result($res);
		}

		$this->aseco->console_text('|......Calculating rank for each player');
		foreach ($players as $player) {
			$exists = false;
			$query = 'SELECT playerID FROM rs_rank WHERE playerID=\'' . $player . '\'';
			$res = mysql_query($query);
			if (mysql_num_rows($res) > 0){
				$exists = true;
			}
			$rank = array();
			foreach ($tracks as $cur) {
				if (array_key_exists($player, $trank[$cur])) {
					if ($trank[$cur][$player] > $maxrecs) {
						$rank[$cur] = $maxrecs;
					} else {
						$rank[$cur] = $trank[$cur][$player];
					}
				} else {
					$rank[$cur] = $maxrecs;
				}
			}
			$avg = array_sum($rank) / sizeof($tracks);
			$avg = floor(round($avg, 4)*10000);
			if ($exists == true) {
				$query = 'UPDATE rs_rank SET avg=' . $avg . ' WHERE playerID=\'' . $player . '\'';
				$res2 = mysql_query($query);
			} else {
				$query = 'INSERT INTO rs_rank(playerID, avg) VALUES (\'' . $player . '\', ' . $avg . ')';
				$res2 = mysql_query($query);
				if (mysql_affected_rows() != 1) {
					$errmsg = 'Tried to insert player : ' . $player . ' with average : ' . $avg . ' -FAILED!';
					$this->aseco->console_text($errmsg);
				}
			}
			mysql_free_result($res);
		}
		$this->aseco->console_text('|...Done!');
*/

		if ($environment != "")
			{
			$players = array();
			$this->aseco->console_text("|...Calculating ranks ($environment)");

/*
			$query = 'CREATE TABLE IF NOT EXISTS `rs_rank_'.strtolower($environment).'` (
						`playerID` mediumint(9) NOT NULL default 0,
						`avg` INTEGER UNSIGNED NOT NULL default 0,
						KEY `playerID` (`playerID`)
						) TYPE=MyISAM';
			mysql_query($query);
*/

			$tracks = array();
			$query = "SELECT id from challenges where Environment='$environment'";
			$result = mysql_query($query);
			while ($row = mysql_fetch_row($result))
				{
				$tracks[] = $row[0];
				}
			mysql_free_result($result);
			
			$query = 'SELECT PlayerId, COUNT( * ) AS cnt
						FROM records
						WHERE environment='.quotedString($environment).'
						GROUP BY PlayerId
						HAVING cnt >' . $minrank;
			$res = mysql_query($query);
			while ($row = mysql_fetch_object($res)) {
				$players[] = $row->PlayerId;
			}
			mysql_free_result($res);

			foreach ($tracks as $track) {
				$rank = array();
				$query = 'SELECT PlayerId, Rank
							FROM records
							WHERE ChallengeId=\'' . $track . '\'
							ORDER BY Score ASC,`Date` ASC';
				$res = mysql_query($query);
				$i = 1;
				if (mysql_num_rows($res) == 0) {
					$trank[$track] = array();
				} else {
					while ($row = mysql_fetch_object($res)) {
						$pid = $row->PlayerId;
						$oldrank = $row->Rank;
						$trank[$track][$pid] = $i;
						if ( $oldrank != $i)
							{
							$query="UPDATE records SET rank=$i WHERE playerID=$pid AND ChallengeId=$track";	
							if (!mysql_query($query))
								$this->aseco->console_text("Cannot update rank. PID=$pid, CID=$track, Rank=$i");
							}
						$i++;
					}
				}
				mysql_free_result($res);
			}

			$environments = array("speed", "rally", "alpine", "coast", "bay", "island", "stadium");

			foreach ($players as $player) {
				$exists = false;
				$query = "SELECT playerID,alpine,island,coast,speed,stadium,rally,bay FROM rs_rank WHERE playerID=$player";
/*
				$query = 'SELECT playerID FROM rs_rank_'.strtolower($environment).' WHERE playerID=\'' . $player . '\'';
*/
				$avgs = array();
				$res = mysql_query($query);
				if (mysql_num_rows($res) > 0){
					$exists = true;
					$row = mysql_fetch_array($res);
					foreach ($environments as $theenvironment)
						{
						$avgs[$theenvironment] = $row[$theenvironment];
						}
				}
				$rank = array();
				foreach ($tracks as $cur) {
					if (array_key_exists($player, $trank[$cur])) {
						if ($trank[$cur][$player] > $maxrecs) {
							$rank[$cur] = $maxrecs;
						} else {
							$rank[$cur] = $trank[$cur][$player];
						}
					} else {
						$rank[$cur] = $maxrecs;
					}
				}
				$avg = array_sum($rank) / sizeof($tracks);
				$avg = floor(round($avg, 4)*10000);

				$avgs[strtolower($environment)] = $avg;

				if (!isset($singleenv))
					{
					$avg = 0;
					foreach ($environments as $theenvironment)
						{
						if ($avgs[$theenvironment] == 0)
							{
							$avg += $maxrecs*10000;
							}
						else
							{
							if ($avgs[$theenvironment] > $maxrecs*10000)
								{
								$avg += $maxrecs*10000;
								$avgs[$theenvironment] = $maxrecs*10000;
								}
							else
								{
								$avg += $avgs[$theenvironment];
								}
							}		
						}
					$avg = floor($avg/sizeof($environments));
					}
			
				if ($exists == true) {
					$query = 'UPDATE rs_rank SET avg='.$avg.', '.strtolower($environment).'=' . $avgs[strtolower($environment)] . ' WHERE playerID=\'' . $player . '\'';
/*
					$query = 'UPDATE rs_rank_'.strtolower($environment).' SET avg=' . $avg . ' WHERE playerID=\'' . $player . '\'';
*/
					$res2 = mysql_query($query);
				} else {
					$query = 'INSERT INTO rs_rank (playerID, avg, '.strtolower($environment).') VALUES (\'' . $player . '\', ' . $avg . ', '.$avgs[strtolower($environment)].')';
/*
					$query = 'INSERT INTO rs_rank_'.strtolower($environment).'(playerID, avg) VALUES (\'' . $player . '\', ' . $avg . ')';
*/
					$res2 = mysql_query($query);
					if (mysql_affected_rows() != 1) {
						$errmsg = '('.$environment.') Tried to insert player : ' . $player . ' with average : ' . $avg . ' - FAILED!';
						$this->aseco->console_text($errmsg);
					}
				}
				mysql_free_result($res);
			}
		$this->aseco->console_text('|...Done!');
		}
	}

	function onPlayerjoin(&$aseco, $player) {
		global $feature_ranks, $feature_stats;
		
		if ($feature_ranks == true) { $this->showRank($player->login); }
		if ($feature_stats == true) { $this->showPb($player->login, $aseco->server->challenge->id); }
		$msg = 'RASP - try /list xxx where xxx is a all/part of a track name, then you can /jukebox it\'s #';
		$this->aseco->addCall('ChatSendServerMessageToLogin', array($msg, $login));
	}

	function showPb($login, $track) {
		global $maxrecs;
		$found = false;
		for ($i = 0; $i < $maxrecs; $i++) {
			$rec = $this->aseco->server->records->getRecord($i);
			if ($rec->player->login == $login) {
				$ret['time'] = $rec->score;
				$ret['rank'] = $i + 1;
				$found = true;
				break;
			}
		}
		$pid = $this->getPlayerId($login);

		if ($found == false) {
			$query2 = 'SELECT score FROM records WHERE playerID=' . $pid . ' && challengeID=' . $track . ' ORDER BY score ASC LIMIT 1';
			$res2 = mysql_query($query2);
			if (mysql_num_rows($res2) > 0) {
				$row = mysql_fetch_array($res2);
				$ret['time'] = $row['score'];
				$ret['rank'] = '{#highlite}UNRANKED';
				$found = true;
			}
			mysql_free_result($res2);
		}

		$query = 'SELECT score FROM records WHERE playerID=' . $pid . ' && challangeID=' . $track . ' ORDER BY date DESC LIMIT 10';
		$res = mysql_query($query);
		$size = mysql_num_rows($res);
		if ($size > 0) {
			$total = 0;
			while ($row = mysql_fetch_object($res)) {
				$total = $total + $row->score;
			}
			$avg = formatTime(floor($total / $size));
		} else {
			$avg = 'No Average'; }
		mysql_free_result($res);

		if ($found == true) {
				$myquery="SELECT DATE_FORMAT(Date, '%d.%m.%Y at %H:%i') as mydate FROM records r WHERE ChallengeID=".$track." AND PlayerID=".$pid;
				$myresult=mysql_query($myquery);
				$myrow=mysql_fetch_array($myresult);
				$mydate=$myrow['mydate'];
				mysql_free_result($myresult);
   	
				$message = formatText($this->messages['PB'][0],
	 			formatTime($ret['time']),
				$ret['rank'],
				$mydate);
				//$avg);
				$message = $this->aseco->formatColors($message);
				$this->aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
		} else {
			$message = formatText($this->messages['PB_NONE'][0]);
			$message = $this->aseco->formatColors($message);
			$this->aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
		}
	}

	function showRank($login) {
		global $minrank;
		$query = 'SELECT avg FROM rs_rank WHERE playerID=' . $this->getPlayerId($login) . ' ORDER BY avg ASC';
		$res = mysql_query($query);
		if (mysql_num_rows($res) > 0) {
			$row = mysql_fetch_array($res);
			$query2 = 'SELECT * FROM rs_rank WHERE avg>0 and avg <' . $row['avg'];
			$res2 = mysql_query($query2);
			$query3 = 'SELECT * FROM rs_rank';
			$res3 = mysql_query($query3);
			$message = formatText($this->messages['RANK'][0],
			mysql_num_rows($res2)+1,
			mysql_num_rows($res3),
			$row['avg']/10000);
			$message = $this->aseco->formatColors($message);
			$this->aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
			mysql_free_result($res2);
			mysql_free_result($res3);
		} else {
			$message = formatText($this->messages['RANK_NONE'][0], $minrank);
			$message = $this->aseco->formatColors($message);
			$this->aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
		}
		mysql_free_result($res);
	}

	function onFinish(&$aseco, $finish_item) {
		global $maxrecs, $feature_stats;
		if ($feature_stats == true) {
			if ($finish_item->score > 0) {
				$this->insertTime($finish_item);
			}
			for ($i = 0; $i < $maxrecs; $i++) {
				$cur_record = $aseco->server->records->getRecord($i);

				if (($finish_item->score < $cur_record->score || !isset($cur_record)) && $finish_item->score > 0) {
					$finish_time = formatTime($finish_item->score);
					$cur_rank = -1;
					for ($rank = 0; $rank < $aseco->server->records->count(); $rank++) {

						$rec = $aseco->server->records->getRecord($rank);
						if ($rec->player->login == $finish_item->player->login) {

							if ($rec->score <= $finish_item->score) {
								return;

							} else {
								$cur_rank = $rank;
							}
						}
					}
					if ($cur_rank != -1) {
						if ($cur_rank > $i) {
							if ($i > 4){
								$message = formatText($this->messages['NEW_RANK'][0],
								$finish_time,
								$i+1);
								$message = $aseco->formatColors($message);
								$aseco->addCall('ChatSendServerMessageToLogin', array($message, $finish_item->player->login));
							}
						} else {
							if ($i > 4){
								$message = formatText($aseco->chat_messages['RECORD_NEW'][0],
								$i+1,
								$finish_time);
								$message = $aseco->formatColors($message);
								$aseco->addCall('ChatSendServerMessageToLogin', array($message, $finish_item->player->login));
							}
						}
					} else {
						if ($i > 4) {
							$message = formatText($this->messages['NEW_RANK'][0],
							$finish_time,
							$i+1);
							$message = $aseco->formatColors($message);
							$aseco->addCall('ChatSendServerMessageToLogin', array($message, $finish_item->player->login));
						}
					}
					return;
				}
			}
		}
	}

	function onNewtrack(&$aseco, $challenge) {
		global $feature_karma, $feature_stats;
//		$this->getPlayerList();
//		foreach($this->playerlist as $row) {
 
		// PLAYERINFO MANIALINK USED
		$feature_stats = false;
		
		if ($feature_stats == true) {
			foreach($aseco->server->players->player_list as $pl) {
				$this->showPb($pl->login, $challenge->id);
			}
		}
		if ($feature_karma == true) {
			rasp_karma($challenge);
		}
	}

	function getPlayerId($login)
		{
		$query = 'SELECT Id
					FROM players
					WHERE Login=\'' . $login . '\'';
		$result = mysql_query($query);
		if (mysql_num_rows($result) > 0)
			{
			$row = mysql_fetch_row($result);
			$rtn = $row[0];
			}
		else
			{
			$rtn = 0;
			}
		mysql_free_result($result);
		return $rtn;
		}

	function insertTime($time){
	 	return;
	}

	function onEndrace(&$aseco, $race) {
		global $tmxplayed, $feature_ranks;
		
		// PLAYERINFO MANIALINK USED
		$challenge = $race[1];
		$this->resetRanks($challenge['Environnement']);
		$feature_ranks = false;
		
		if ($feature_ranks == true) {
			if ($tmxplayed == false) {
				$this->resetRanks();
			}
			foreach($aseco->server->players->player_list as $pl) {
				$this->showRank($pl->login);
			}
		}
	}

	function getChallengeId($uid) {
		$query = 'SELECT Id FROM challenges WHERE Uid=\'' . mysql_real_escape_string($uid) . '\'';
		$res = mysql_query($query);
		if (mysql_num_rows($res) > 0) {
			$row = mysql_fetch_row($res);
			$rtnval = $row[0];
		} else {
			$rtnval = 0;
		}
		mysql_free_result($res);
		return $rtnval;
	}
}

//These functions pass the callback data to the Rasp class...
function event_onsync(&$aseco, $data) { global $rasp; $rasp->onSync($aseco, $data); }
function event_finish(&$aseco, $data) { global $rasp; $rasp->onFinish($aseco, $data); }
function event_newtrack(&$aseco, $data) { global $rasp; $rasp->onNewtrack($aseco, $data); }
function event_endrace(&$aseco, $data) { global $rasp; $rasp->onEndrace($aseco, $data); }
function event_playerjoin(&$aseco, $data) { global $rasp; $rasp->onPlayerjoin($aseco, $data); }

//chat functions..
function chat_pb(&$aseco, &$command){
	global $rasp, $feature_stats;
	if ($feature_stats == true) {
		$rasp->showPb($command['author']->login, $aseco->server->challenge->id);
	}
}

function chat_rank(&$aseco, &$command){
	global $rasp, $feature_ranks;
	if ($feature_ranks == true) {
		$rasp->showRank($command['author']->login);
	}
}

//Starts the rasp plugin..
function event_onstartup(&$aseco) {
	global $rasp;
	$rasp_config = 'rasp.xml';

	$aseco->console_text('[rasp] Cleaning up unused data');
	$sql = 'DELETE FROM challenges where uid=\'\'';
	mysql_query($sql);
	$sql = 'DELETE FROM players where login=\'\'';
	mysql_query($sql);
	$sql = 'DELETE FROM records where not exists (select id from players p where p.id=records.PlayerId)';
	mysql_query($sql);
	$sql = 'DELETE FROM records where not exists (select id from challenges c where c.id=records.ChallengeId)';
	mysql_query($sql);

	$rasp = new Rasp();
	$rasp->start($aseco, $rasp_config);
}
?>
