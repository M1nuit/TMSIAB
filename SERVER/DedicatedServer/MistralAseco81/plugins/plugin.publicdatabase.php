<?php
/**
 ** 1.3
 * This script will manage global records.
 * Sends records to TMX for the "Online World Record".
 *
 ** 2.0
 * This plugin is now compatible with the localdatabase plugin!
 *
 * @author    Florian Schnell
 * @version   2.0
 */

require_once("includes/dataexchanger.inc.php");
Aseco::registerEvent("onStartup", "pdb_loadSettings");
Aseco::registerEvent("onStartup", "pdb_connect");
Aseco::registerEvent("onSync", "pdb_sync");
Aseco::registerEvent("onPlayerVote", "pdb_vote");
Aseco::registerEvent("onPlayerFinish", "pdb_playerFinish");
Aseco::registerEvent("onNewChallenge", "pdb_newChallenge");
Aseco::registerEvent("onPlayerConnect", "pdb_playerConnect");
Aseco::registerEvent("onPlayerDisconnect", "pdb_playerDisconnect");
Aseco::registerEvent("onPlayerWins", "pdb_playerWins");

function pdb_loadSettings(&$aseco) {
  global $pdb_settings;

  $aseco->console_text("[Public DB] Load settings file ...");

  $xml_parser = new Examsly();
  $settings = $xml_parser->parseXml('publicdatabase.xml');
  $msgs = $settings['SETTINGS']['MESSAGES'][0];
  $settings = $settings['SETTINGS'];

  // read mysql server settings ...
  $pdb_settings['server']['login'] = $settings['SERVER_LOGIN'][0];
  $pdb_settings['server']['password'] = $settings['SERVER_PASSWORD'][0];
  $pdb_settings['server']['ip'] = $settings['SERVER_IP'][0];
  $pdb_settings['server']['port'] = $settings['SERVER_PORT'][0];

  // display records in game?
  if (strtoupper($settings['DISPLAY'][0]) == "TRUE")
    $pdb_settings['display'] = true;
  else
    $pdb_settings['display'] = false;

  $pdb_settings['messages'] = $msgs;
}

function pdb_connect(&$aseco) {
	global $maxrecs;

  // load settings ...
  global $pdb_settings;
  // create data fields ...
  global $pdb_records;
  $pdb_records = new RecordList($maxrecs);
  global $pdb_players;
  $pdb_players = new PlayerList();
  global $pdb_challenge;
  $pdb_challenge = new Challenge();

  // get game ...
  $aseco->client->query("GetVersion");
  $version = $aseco->client->getResponse();
  $aseco->server->game = $version['Name'];

  // initialize dataexchanger ...
  $aseco->dataexchanger = new DataExchanger($pdb_settings['server']['ip'], $pdb_settings['server']['port']);
  $aseco->dataexchanger->connect();

  // display new status message ...
  $aseco->console_text("[Public DB] Try to authenticate on dataserver with '".$pdb_settings['server']['login']."' and '".$pdb_settings['server']['password']."'");

  // try to authentificate at the dataserver ...
  $response = $aseco->dataexchanger->request("Authenticate",
  array("Login" => $pdb_settings['server']['login'],
  "Pass" => $pdb_settings['server']['password'],
  "Game" => $aseco->server->getGame()));

  // all right?
  if ($response) {

    // response ok ...
    $aseco->console_text("[Public DB] Authenticated successfully at the dataserver!");
    $aseco->welcome_msgs = $response['MESSAGES'];

  } else {

    trigger_error("Could not authentificate at the dataserver!");

  }

  // get news from the server ...
  $response = $aseco->dataexchanger->request("GetWelcomeMessages");

  // all right?
  if ($response) {
    $aseco->welcome_msgs = $response["MESSAGES"];
  }
}

function pdb_vote(&$aseco, &$vote) {
  $response = $aseco->dataexchanger->request('InsertVote', array(
	"ChallengeId" => $aseco->server->challenge->id,
  	"Login" => $vote['author']->login,
  	"Score" => $vote['params'],
  	"RaceMode" => 1));
}

function pdb_playerFinish(&$aseco, &$finish_item) {
  global $pdb_records, $pdb_settings;

  // drove a new record?
  // go through each of the x records ...
  for ($i = 0; $i < $pdb_records->max; $i++) {

    // get the record to the position ...
    $cur_record = $pdb_records->getRecord($i);

    // if the player's time is better, then ...
    // ... and not zero (thanks eyez)
    if (($finish_item->score < $cur_record->score || !isset($cur_record)) && $finish_item->score > 0) {

      $finish_time = formatTime($finish_item->score);

      // does the player have a record already?
      $cur_rank = -1;
      for ($rank = 0; $rank < $pdb_records->count(); $rank++) {

        $rec = $pdb_records->getRecord($rank);
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
        $pdb_records->setRecord($cur_rank, $finish_item);

        // player moved up in WR list ...
        if ($cur_rank > $i) {

          // move record to the new position ...
          $pdb_records->moveRecord($cur_rank, $i);

          // do a player improved his WR rank message ...
          // replace parameters ...
          $message = formatText($pdb_settings['messages']['RECORD_NEW_RANK'][0],
          stripColors($finish_item->player->nickname),
          $finish_time,
          $i+1);

          // replace colors ...
          $message = $aseco->formatColors($message);

          // send the message ...
          if ($pdb_settings['display']) $aseco->addCall('ChatSendServerMessage', array($message));
        } else {

          // do a player improved his record message ...
          $message = formatText($pdb_settings['messages']['RECORD_NEW'][0],
          $i+1,
          $finish_time);

          // replace colors ...
          $message = $aseco->formatColors($message);

          // send the message ...
          if ($pdb_settings['display']) $aseco->addCall('ChatSendServerMessageToLogin', array($message, $finish_item->player->login));
        }

      } else { // player hasn't got a record yet ...

        // insert a new record at the specified position ...
        $pdb_records->addRecord($finish_item, $i);

        // do a player drove first record message ...
        // replace parameters ...
        $message = formatText($pdb_settings['messages']['RECORD_FIRST'][0],
        stripColors($finish_item->player->nickname),
        $finish_time,
        $i+1);

        // replace colors ...
        $message = $aseco->formatColors($message);

        // send the message ...
        if ($pdb_settings['display']) $aseco->addCall('ChatSendServerMessage', array($message));
      }

      // display record message in console ...
      $aseco->console("[Public DB] player {1} finished with {2} and took the {3}. WR place!",
      $finish_item->player->id,
      $finish_item->score,
      $i+1);

      // update aseco records ...
      if ($pdb_settings['display']) $aseco->server->records = $pdb_records;

      // release an "on record" event ...
      $aseco->releaseEvent("onPublicRecord", $finish_item);

      // insert the local record ...
      pdb_insert_record($aseco, $finish_item);

      // got the record, now stop!
      return;
    }
  }
}

function pdb_insert_record($aseco, $record) {
  global $pdb_challenge;

  // send record to dataserver ...
  $response = $aseco->dataexchanger->request('InsertRecord', array(
  "ChallengeId" => $pdb_challenge->id,
  "Login" => $record->player->login,
  "Score" => $record->score,
  "NickName" => $record->player->nickname));
}

function pdb_newChallenge(&$aseco, &$challenge) {
  global $pdb_challenge, $pdb_records, $pdb_settings;

  // remove old records ...
  $pdb_records->clear();

  // get information about track from dataserver ...
  $response = $aseco->dataexchanger->request("GetChallenge", array(
  "Uid" => $challenge->uid,
  "Name" => $challenge->name,
  "Author" => $challenge->author,
  "Environment" => $challenge->environment));

  // check server response ...
  if ($response) {

    // build up the record list for the challenge ...
    if ($records = $response['CHALLENGE'][0]['RECORDS']) {
      foreach ($records as $record) {
        if ($record['SCORE'][0]) {

          // create record object ...
          $record_item = new Record;
          $record_item->score = $record['SCORE'][0];

          // create a player object to put it into the record object ...
          $player_item = new Player;
          $player_item->nickname = $record['NICKNAME'][0];
          $player_item->login = $record['LOGIN'][0];
          $record_item->player = $player_item;

          // add the track information to the record object ...
          $record_item->challenge = $challenge;

          // add the created record to the list ...
          $pdb_records->addRecord($record_item);
        }
      }
    }

    // send records when debugging is set to true ...
    if ($aseco->debug) print_r($pdb_records);

    $pdb_challenge->id = $response['CHALLENGE'][0]['ID'][0];
    $pdb_challenge->score = $response['CHALLENGE'][0]['VOTINGS'][0]['SCORE'][0];
    $pdb_challenge->votes = $response['CHALLENGE'][0]['VOTINGS'][0]['COUNT'][0];

    // now commit the changes to aseco ...
    if ($pdb_settings['display']) {
      $aseco->server->records = $pdb_records;
      $aseco->server->challenge->id = $pdb_challenge->id;
      $aseco->server->challenge->score = $pdb_challenge->score;
      $aseco->server->challenge->votes = $pdb_challenge->votes;
    }

  } else {

    // send chat message of the error to all players ...
    $message = $aseco->formatColors($aseco->getChatMessage("RECORD_ERROR"));

    if ($pdb_settings['display']) $aseco->addCall('ChatSendServerMessage', array($message));
  }
}

function pdb_playerConnect(&$aseco, &$player) {
  global $pdb_players, $pdb_settings;

  $players[] = array("Login" => $player->login,
  "Game" => $aseco->server->getGame(),
  "NickName" => $player->nickname);

  // send player to dataserver ...
  $response = $aseco->dataexchanger->request("NewPlayers",
  array("Players" => $players));

  // load db records for the specified player ...
  if ($response) {
    foreach ($response['PLAYERS'] as $db_player) {
      $player_item = new Player();
      $player_item->login = $db_player['LOGIN'][0];
      $player_item->timeplayed = $db_player['TIMEPLAYED'][0];
      $player_item->wins = $db_player['WINS'][0];
      $pdb_players->addPlayer($player_item);

      if ($pdb_settings['display']) {
        $player->timeplayed = $db_player['TIMEPLAYED'][0];
        $player->wins = $db_player['WINS'][0];
      }
    }
  }
}

function pdb_playerDisconnect(&$aseco, &$player) {
  global $pdb_players;

  // update player at the dataserver ...
  $response = $aseco->dataexchanger->request("ExitPlayer",
  array("Login" => $player->login));

  // remove player from db intern player list ...
  $pdb_players->removePlayer($player->login);
}

function pdb_playerWins(&$aseco, &$player) {

  // update player at the dataserver ...
  $response = $aseco->dataexchanger->request("PlayerWins",
  array("Login" => $player->login));
}

function pdb_sync(&$aseco, &$param) {
  global $pdb_players, $pdb_settings;

  // start data synchronization ...
  $aseco->console_text("[Public DB] Synchronize players with database");

  // build player list ...
  $players = array();

  // for each player that is already on the server ...
  while ($player = $aseco->server->players->nextPlayer()) {

    // send debug messsage ...
    if ($aseco->debug) $aseco->console_text("[Public DB] Sending player '{$player->login}'");

    // add him to the request ...
    $players[] = array("Login" => $player->login,
    "Game" => $aseco->server->getGame(),
    "NickName" => $player->nickname,
    "Nation" => $player->nation,
    "IpAddress" => $player->ip);
  }

  // reset the player list ...
  $aseco->server->players->resetPlayers();

  // send the request and get the response ...
  $response = $aseco->dataexchanger->request("NewPlayers",
  array("Players" => $players));

  // get response ...
  if ($response) {
    if($response['PLAYERS']) {
      foreach ($response['PLAYERS'] as $db_player) {
        $player_item = new Player();
        $player_item->login = $db_player['LOGIN'][0];
        $player_item->timeplayed = $db_player['TIMEPLAYED'][0];
        $player_item->wins = $db_player['WINS'][0];
        $pdb_players->addPlayer($player_item);

        // change aseco players ...
        if ($pdb_settings['display']) {
          $aseco_player = $aseco->server->players->getPlayer($db_player['LOGIN'][0]);
          $aseco_player->timeplayed = $db_player['TIMEPLAYED'][0];
          $aseco_player->wins = $db_player['WINS'][0];
        }
      }
    }
  }
}
?>
