<?php
/**
 * This script saves record into a local database.
 * You can modify this file as you want,
 * to advance the information stored in the database!
 * Compatible with plugin.publicdatabase.php now!
 *
 * @author    Florian Schnell
 * @version   2.0
 */

Aseco::registerEvent('onStartup', 'ldb_loadSettings');
Aseco::registerEvent('onStartup', 'ldb_connect');
Aseco::registerEvent('onSync', 'ldb_sync');
Aseco::registerEvent('onPlayerVote', 'ldb_vote');
Aseco::registerEvent('onPlayerFinish', 'ldb_playerFinish');
Aseco::registerEvent('onNewChallenge', 'ldb_newChallenge');
Aseco::registerEvent('onPlayerConnect', 'ldb_playerConnect');
Aseco::registerEvent('onPlayerDisconnect', 'ldb_playerDisconnect');
Aseco::registerEvent('onPlayerWins', 'ldb_playerWins');

global $showtop;

function ldb_loadSettings(&$aseco) {
  global $ldb_settings;

  $aseco->console_text('[Local DB] Load settings file ...');

  $xml_parser = new Examsly();
  $settings = $xml_parser->parseXml('localdatabase.xml');
  $msgs = $settings['SETTINGS']['MESSAGES'][0];
  $settings = $settings['SETTINGS'];

  // read mysql server settings ...
  $ldb_settings['mysql']['host'] = $settings['MYSQL_SERVER'][0];
  $ldb_settings['mysql']['login'] = $settings['MYSQL_LOGIN'][0];
  $ldb_settings['mysql']['password'] = $settings['MYSQL_PASSWORD'][0];
  $ldb_settings['mysql']['database'] = $settings['MYSQL_DATABASE'][0];

  // display records in game?
  if (strtoupper($settings['DISPLAY'][0]) == 'TRUE')
    $ldb_settings['display'] = true;
  else
    $ldb_settings['display'] = false;

  $ldb_settings['messages'] = $msgs;
}

function ldb_connect(&$aseco) {
	global $maxrecs;

  // get the settings ...
  global $ldb_settings;
  // create data fields ...
  global $ldb_records;
  $ldb_records = new RecordList($maxrecs);
  global $ldb_players;
  $ldb_players = new PlayerList();
  global $ldb_challenge;
  $ldb_challenge = new Challenge();

  // display new status message ...
  $aseco->console_text('[Local DB] Try to connect to MySQL server');

  if (!mysql_connect($ldb_settings['mysql']['host'], $ldb_settings['mysql']['login'],$ldb_settings['mysql']['password'])) {
    trigger_error('[Local DB] Could not authenticate at MySQL server!', E_USER_ERROR);
  }

  if (!mysql_select_db($ldb_settings['mysql']['database'])) {
    trigger_error('[Local DB] Could not find MySQL database!', E_USER_ERROR);
  }

  $aseco->console_text('[Local DB] MySQL Server Version is ' . mysql_get_server_info());
  $aseco->welcome_msgs = array();

  $result = mysql_query('select teamname from players limit 1');
  if ( $result == false )
  	{
  	if ( mysql_errno() == 1054)
  		{
  		mysql_query('alter table players add TeamName char(60)');
 		}
	}
  else
 	{
  	mysql_free_result($result);
  	}
}

function ldb_sync(&$aseco) {

  $aseco->console_text('[Local DB] Synchronize players with database');

  // take each player in the list and simulate a join ...
  while($player = $aseco->server->players->nextPlayer()) {

    // send debug message ...
    if ($aseco->debug) $aseco->console_text('[Local DB] Sending player ' . quotedString($player->login));

    ldb_playerConnect($aseco, $player);
	$aseco->console('<< player {1} synced with db [{2}], {3} wins.',
		$player->id,
		$player->login,
		$player->wins);

  }

  // reset the player list ...
  $aseco->server->players->resetPlayers();
}

function ldb_playerConnect(&$aseco, &$player) {
  global $ldb_players, $ldb_settings;

  $zone = getZone($aseco, $player->login);
  $nation = getNation($zone);
  $player->zone = $zone;
  $player->nation = getNation($zone);

  // try to update the player ...
  $query = 'UPDATE players SET
  	NickName=' . quotedString($player->nickname) . ',
    Nation=' . quotedString($player->nation) . ',
    Game=' . quotedString($aseco->server->getGame()) . ',
    UpdatedAt=NOW()
    WHERE Login=' . quotedString($player->login);

  // commit ...
  $result = mysql_query($query);

  // was updated ...
  if (mysql_affected_rows() > 0) {
    // get stats of the player ...
    $query = 'SELECT Wins AS wins, TimePlayed AS timeplayed, teamname
    	FROM players
    	WHERE Login=' . quotedString($player->login);

    // commit ...
    $result = mysql_query($query);

    // save player stats ...
    $dbplayer = mysql_fetch_object($result);
    $ldb_player = new Player();
    $ldb_player->wins = $dbplayer->wins;
    $ldb_player->timeplayed = $dbplayer->timeplayed;
    $ldb_players->addPlayer($ldb_player);

	if ( ($player->teamname == '' || $player->teamname == -1) && $dbplayer->teamname != '' )
		{
		$player->teamname = $dbplayer->teamname;
		}

	if ( $player->teamname == -1 )
		{
		$player->teamname = '';
		}

    // update aseco player ...
    if ($ldb_settings['display'])
		{
		if ( $player->wins < $dbplayer->wins )
			{
      		$player->wins = $dbplayer->wins;
			}
		if ( $player->timeplayed < $dbplayer->timeplayed )
			{
      		$player->timeplayed = $dbplayer->timeplayed;
			}
    	}
  	mysql_free_result($result);

  // could not be updated ...
  } else {
    // insert player into database ...
    $query = 'INSERT INTO players
    (Login, Game, NickName, Nation, UpdatedAt)
    VALUES
    (' . quotedString($player->login) . ', ' . quotedString($aseco->server->getGame()) . ', ' . quotedString($player->nickname) . ', ' . quotedString($player->nation) . ', NOW())';

    // commit ...
    $result = mysql_query($query);

    // player was just inserted, so don't get any stats ...

    // could not be inserted ...
    if (mysql_affected_rows() == 0) {
      trigger_error('Player could not be inserted/updated!', E_USER_WARNING);
    }
  }
}

function ldb_playerDisconnect(&$aseco, &$player) {
  global $ldb_players;

  // ignore players with empty logins ...
  if ($player->login == '') return;

  // update the player in the database ...
  $query = 'UPDATE players
	SET UpdatedAt=NOW(),
  	Wins=' . $player->getWins() . ',
	TimePlayed=TimePlayed+' . $player->getTimeOnline() . '
	WHERE Login=' . quotedString($player->login);

  // commit ...
  $result = mysql_query($query);

  // remove player from list ...
  $ldb_players->removePlayer($player->login);

  if (mysql_affected_rows() != 1) {
    trigger_error('Could not update leaving player in database! ('.$query.')', E_USER_WARNING);
  }
}

function ldb_vote(&$aseco, &$vote) {

  $score = $vote['params'];
  $player = $vote['author'];

  if (!($playerid = ldb_getPlayerId($player))) {
    trigger_error('Player was not found in the database!', E_USER_WARNING);
  }

  // insert vote into database ...
  $query = 'INSERT INTO votes (Score, PlayerId, ChallengeId)
  					VALUES (' . $score . ', ' . $playerid . ', ' . $aseco->server->challenge->id . ')';

  // commit ...
  $result = mysql_query($query);

  if (mysql_affected_rows() != 1) {
    trigger_error('Vote was not inserted, maybe player voted already for this track! ('.mysql_error().')', E_USER_WARNING);
  }
}

function ldb_playerFinish(&$aseco, &$finish_item) {
  global $ldb_records, $ldb_settings, $showtop;

  if (!isset($finish_item->player->login) || $finish_item->player->login=="")
  	return;

  // drove a new record?
  // go through each of the x records ...
  for ($i = 0; $i < $ldb_records->max; $i++) {

    // get the record to the position ...
    $cur_record = $ldb_records->getRecord($i);

    // if the player's time is better, then ...
    // ... and not zero (thanks eyez)
    if (($finish_item->score < $cur_record->score || !isset($cur_record)) && $finish_item->score > 0) {

      $finish_time = formatTime($finish_item->score);

      // does the player have a record already?
      $cur_rank = -1;
	  $x = $finish_item->player->login;
	  $x = $finish_item->player->id;
      for ($rank = 0; $rank < $ldb_records->count(); $rank++) {
        $rec = $ldb_records->getRecord($rank);
        if ($rec->player->login == $finish_item->player->login) {

          // new record isn't as good as the old one ...
          if ($rec->score <= $finish_item->score) {
            return;

            // old record isn't as good as the new one ...
          } else {
            $cur_rank = $rank;
          }
        }
      }

      if ($cur_rank != -1) { // player has a record in top5 already ...

        // update his record ...
        $ldb_records->setRecord($cur_rank, $finish_item);

        // player moved up in LR list ...
        if ($cur_rank > $i) {

          // move record to the new position ...
          $ldb_records->moveRecord($cur_rank, $i);

          // do a player improved his LR rank message ...
		  if ($i<$showtop) // pub
		  	{
            // replace parameters ...
            $message = formatText($ldb_settings['messages']['RECORD_NEW_RANK_PUB'][0],
          	stripColors($finish_item->player->nickname),
          	$i+1,
          	$finish_time);

            // replace colors ...
            $message = $aseco->formatColors($message);

            // send the message ...
            if ($ldb_settings['display'])
				{
				$aseco->addCall('ChatSendServerMessage', array($message));
				}
          	}
          else // private
          	{
        	// do a player drove first record message private ...
    	    $message = formatText($ldb_settings['messages']['RECORD_NEW_RANK_PRIV'][0],
        	$i+1,
	        $finish_time);

	        // replace colors ...
    	    $message = $aseco->formatColors($message);

	        // send the message ...
    	    if ($ldb_settings['display'])
				{
				$aseco->addCall('ChatSendServerMessageToLogin', array($message, $finish_item->player->login));	
				}
			}
          
        } else { // do a player improved his record message ...

          if ($i<$showtop) // public
          	{
        	// do a player drove first record message pubic ...
    	    // replace parameters ...
	        $message = formatText($ldb_settings['messages']['RECORD_NEW_PUB'][0],
        	stripColors($finish_item->player->nickname),
        	$i+1,
    	    $finish_time);

    	    // replace colors ...
	        $message = $aseco->formatColors($message);

    	    // send the message ...
	        if ($ldb_settings['display'])
				{
				$aseco->addCall('ChatSendServerMessage', array($message));
				}
			}
		  else // private
		  	{
            $message = formatText($ldb_settings['messages']['RECORD_NEW_PRIV'][0],
            $i+1,
            $finish_time);

            // replace colors ...
            $message = $aseco->formatColors($message);

            // send the message ...
            if ($ldb_settings['display'])
				{
				$aseco->addCall('ChatSendServerMessageToLogin', array($message, $finish_item->player->login));
				}
          	}
        }

      } else { // player hasn't got a record yet ...

	    // insert a new record at the specified position ...
        $ldb_records->addRecord($finish_item, $i);
		if ($i<$showtop)
			{
        	// do a player drove first record message pubic ...
    	    // replace parameters ...
	        $message = formatText($ldb_settings['messages']['RECORD_FIRST_PUB'][0],
        	stripColors($finish_item->player->nickname),
    	    $i+1,
	        $finish_time);

    	    // replace colors ...
	        $message = $aseco->formatColors($message);

    	    // send the message ...
	        if ($ldb_settings['display'])
				{
				$aseco->addCall('ChatSendServerMessage', array($message));
				}
        	}
        else
        	{
        	// do a player drove first record message private ...
    	    $message = formatText($ldb_settings['messages']['RECORD_FIRST_PRIV'][0],
        	$i+1,
	        $finish_time);

	        // replace colors ...
    	    $message = $aseco->formatColors($message);

	        // send the message ...
    	    if ($ldb_settings['display'])
				{
				$aseco->addCall('ChatSendServerMessageToLogin', array($message, $finish_item->player->login));		
				}		
			}
      }

      // display record message in console
      $aseco->console('[Local DB] {1} finished with {2} and took the {3}. LR place!',
      	$finish_item->player->login,
      	$finish_item->score,
      $i+1);
      // update aseco records ...
      if ($ldb_settings['display']) $aseco->server->records = $ldb_records;

      // release an 'on record' event ...
      $aseco->releaseEvent('onLocalRecord', $finish_item);

      // insert the local record
      ldb_insert_record($finish_item);

	  // UPDATE PLAYERINFO MANIALINK
	  displayAllPlayerInfo($aseco, 0, TRUE);

      // got the record, now stop!
      return;
    }
  }
}

function ldb_insert_record($record) {
  global $ldb_challenge;

  if (!($playerid = ldb_getPlayerId($record->player))) {
    trigger_error('Player was not found in the database!', E_USER_WARNING);
  }

  $query = 'INSERT INTO records
            (ChallengeId, PlayerId, Score, Date, Environment)
            VALUES
            (' . $ldb_challenge->id . ', ' . $playerid . ', ' . $record->score . ', NOW(), '.quotedString($ldb_challenge->environment).')';

  // commit ...
  $result = mysql_query($query);

  // could not be inserted?
  // so player had a record already ...
  if (mysql_affected_rows() != 1) {
    $query = 'UPDATE records
              SET Score=' . $record->score . ', Date=NOW()
			  WHERE ChallengeId=' . $ldb_challenge->id . ' AND PlayerId=' . $playerid;

    // commit ...
    $result = mysql_query($query);

    // could not be updated?
    // then there's something going wrong!
    if (mysql_affected_rows() != 1) {
      trigger_error('Could not insert record into database!', E_USER_WARNING);
    }
  }
}

function ldb_newChallenge(&$aseco, &$challenge) {
  global $ldb_challenge, $ldb_records, $ldb_settings;
  $ldb_records->clear();

  $query = 'SELECT c.Id AS ChallengeId, r.Score, p.NickName, p.Login, r.Date, SUM(v.Score) AS VotingSum, COUNT(v.Score) AS VotingCount
            FROM challenges c
            LEFT JOIN records r ON (r.ChallengeId=c.Id)
            LEFT JOIN votes v ON (v.ChallengeId=c.Id)
            LEFT JOIN players p ON (r.PlayerId=p.Id)
            WHERE c.Uid=' . quotedString(mysql_real_escape_string($challenge->uid)) . '
            GROUP BY r.Id
            ORDER BY r.Score ASC
            LIMIT ' . $aseco->server->records->max;

  // commit ...
  $result = mysql_query($query);

  // challenge found in database ...
  if (mysql_num_rows($result) > 0) {

    // get each record ...
    while ($record = mysql_fetch_array($result)) {

      // create record object ...
      $record_item = new Record;
      $record_item->score = $record['Score'];

      // create a player object to put it into the record object ...
      $player_item = new Player;
      $player_item->nickname = $record['NickName'];
      $player_item->login = $record['Login'];
      $record_item->player = $player_item;

      // add the track information to the record object ...
      $record_item->challenge = $challenge;
	  $record_item->date = $record['Date'];

      // add the created record to the list ...
      $ldb_records->addRecord($record_item);

      $ldb_challenge->id = $record['ChallengeId'];
      $ldb_challenge->score = $record['VotingSum'];
      $ldb_challenge->votes = $record['VotingCount'];
      $ldb_challenge->environment = $challenge->environment;

      $challenge->id = $record['ChallengeId'];		// rasp .3, was inside the following if statement block

      // get challenge info ...
      if ($ldb_settings['display']) {
        $challenge->score = $record['VotingSum'];
        $challenge->votes = $record['VotingCount'];
      }
    }

    // update aseco records ...
    if ($ldb_settings['display']) $aseco->server->records = $ldb_records;

    // send records when debugging is set to true ...
//    if ($aseco->debug) print_r($ldb_records);

	mysql_free_result($result);

	// update environment
    $query = 'update challenges set environment='.quotedString($challenge->environment).' where id='.$ldb_challenge->id;
    if (!mysql_query($query))
		trigger_error('Could not update environment of challenge in database!', E_USER_WARNING);

  // challenge isn't in database yet ...
  } else {

    // then create it ...
    $query = 'INSERT INTO challenges
              (Uid, Name, Author, Environment)
              VALUES
              (' . quotedString(mysql_real_escape_string($challenge->uid)) . ', ' . quotedString(stripColors($challenge->name)) . ', ' . quotedString($challenge->author) . ', ' . quotedString($challenge->environment) . ')';

    // commit ...
    $result = mysql_query($query);

    // challenge was inserted successfully ...
    if (mysql_affected_rows() == 1) {

      // ... get its Id now ...
      $query = 'SELECT c.Id
                FROM challenges c
                WHERE c.Uid=' . quotedString(mysql_real_escape_string($challenge->uid));

      $result = mysql_query($query);

      if (mysql_num_rows($result) == 1) {

        $row = mysql_fetch_row($result);
        $ldb_challenge->id = $row[0];
		$challenge->id = $row[0];		// rasp .3, added this so it could have the id for karma stuff (and maybe other things)
        $aseco->server->records->clear();

      } else {

        // challenge Id could not be found ...
        trigger_error('Could not get challenge Id!', E_USER_WARNING);
      }
	mysql_free_result($result);

    } else {

      // challenge could not be inserted ...
      trigger_error('Could not insert challenge into database!', E_USER_WARNING);
    }
  }
}

function ldb_playerWins(&$aseco, &$player)
	{
	$wins = $player->getWins();
	$sql = 'update players set wins=' . $wins . ' where login=' . quotedString($player->login);
	$result = mysql_query($sql);
	if ( mysql_affected_rows() != 1 )		// something wrong if more/less than one row affected
		{
		trigger_error('Could not update winning player in database! (' . mysql_error() . ")\r\nsql=$sql", E_USER_WARNING);
		}
	}  //  ldb_playerWins

function ldb_getPlayerId(&$player) {

  // build query ...
  $query = 'SELECT Id
            FROM players
            WHERE Login=' . quotedString($player->login);

  // commit ...
  $result = mysql_query($query);

  // return result ...
  if (mysql_num_rows($result) > 0) {
    $row = mysql_fetch_row($result);
	$rtnval = $row[0];
  } else {
    $rtnval = 0;
  }
  mysql_free_result($result);
  return $rtnval;
}
?>
