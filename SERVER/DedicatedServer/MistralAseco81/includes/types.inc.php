<?php
/**
 * Structure of an Record.
 */
class Record {
  var $player;
  var $challenge;
  var $score;
  var $date;
}


/**
 * Manages a list of records.
 * Add records to the list and remove them.
 */
class RecordList {
  var $record_list;
  var $max;

  function RecordList($limit) {
    $this->record_list = array();
    $this->max = $limit;
  }

  function setLimit($limit) {
    $this->max = $limit;
  }

  function getRecord($rank) {
    return $this->record_list[$rank];
  }

  function setRecord($rank, $record) {
    if(isset($this->record_list[$rank])) {
      return $this->record_list[$rank] = $record;
    } else {
      return false;
    }
  }

  function moveRecord($from, $to) {
    moveArrayElement($this->record_list, $from, $to);
  }

  function addRecord($record, $rank = -1) {
    // if no rank was set for this record, then put it to the end of the list ...
    if ($rank == -1) {
      $rank = count($this->record_list);
    }

    // do not insert a record behind the border of the list ...
    if ($rank >= $this->max) return;

    // do not insert a record with no score ...
    if ($record->score <= 0) return;

    // if the given object is a record ...
    if (get_class($record) == "Record") {

      // if records are getting too much, drop the last from the list ...
      if (count($this->record_list) >= $this->max) {
        array_pop($this->record_list);
      }

      // insert the record on the specific position ...
      return insertArrayElement($this->record_list, $record, $rank);
    }

  }

  function count() {
    return count($this->record_list);
  }

  function clear() {
    $this->record_list = array();
  }
}


/**
 * Structure of an Player.
 * Can be instanciated with a rpc "GetPlayerInfo" response.
 */
class Player {
  var $login;
  var $nickname;
  var $id;
  var $isspectator;
  var $isofficial;
  var $ladderrank;
  var $hasvoted;
  var $created;
  var $newwins;
  var $wins;
  var $timeplayed;
  var $tracklist;
  var $msgs;
  var $teamname;

  function getWins() {
    return $this->wins + $this->newwins;
  }

  function getTimePlayed() {
    return $this->timeplayed + $this->getTimeOnline();
  }

  function getTimeOnline() {
    return time() - $this->created;
  }

  // instanciates a player with an rpc response ...
  function Player($rpc_infos = null) {
    if ($rpc_infos) {
	  $this->login = $rpc_infos['Login'];
      $this->nickname = $rpc_infos['NickName'];
      $this->id = $rpc_infos['PlayerId'];
      $this->isspectator = $rpc_infos['IsSpectator'];
      $this->isofficial = $rpc_infos['IsInOfficialMode'];
      $this->ladderrank = $rpc_infos['LadderRanking'];
	  $this->teamname = $rpc_infos['TeamId'];		// will have to look this up somehow
      $this->created = time();
    }
    $this->hasvoted = false;
    $this->wins = 0;
    $this->newwins = 0;
  }
}


/**
 * Manages players on the server.
 * Add player and remove them.
 */
class PlayerList {
  var $player_list;

  function PlayerList()
  	{
  	$this->player_list = array();
  	}  //  PlayerList
  function nextPlayer() {
    if (is_array($this->player_list)) {
      $player_item = current($this->player_list);
      next($this->player_list);
      return $player_item;
    } else {
      $this->resetPlayers();
      return false;
    }
  }

  function resetPlayers() {
    if (is_array($this->player_list)) {
      reset($this->player_list);
    }
  }

  function addPlayer(&$player) {
    if (get_class($player) == "Player" && $player->login > "") {
      $this->player_list[$player->login] = $player;
      return true;
    } else {
      return false;
    }
  }

  function removePlayer($login) {
    $player = $this->player_list[$login];
    unset($this->player_list[$login]);
    return $player;
  }

  function getPlayer($login) {
	return $this->player_list[$login];
  }

  function resetVotings() {
    if (!empty($this->player_list)) {
      foreach ($this->player_list as $player) {
        $player->hasvoted = false;
      }
    }
  }
}


/**
 * Can store challenge information.
 * You can instanciate with an rpc "GetChallengeInfo" response.
 */
class Challenge {
  var $id;
  var $name;
  var $uid;
  var $filename;
  var $author;
  var $environment;
  var $mood;
  var $bronzetime;
  var $silvertime;
  var $goldtime;
  var $authortime;
  var $copperprice;
  var $laprace;
  var $score;
  var $votes;

  // instanciates the class with and rpc response ...
  function Challenge($rpc_infos = null) {
    if ($rpc_infos) {

      // import the xml rpc response of a challenge ...
      $this->id = 0;
      $this->name = $rpc_infos['Name'];
      $this->uid = $rpc_infos['UId'];
      $this->filename = $rpc_infos['FileName'];
      $this->author = $rpc_infos['Author'];
      $this->environment = $rpc_infos['Environnement'];
      $this->mood = $rpc_infos['Mood'];
      $this->bronzetime = $rpc_infos['BronzeTime'];
	  $this->silvertime = $rpc_infos['SilverTime'];
      $this->goldtime = $rpc_infos['GoldTime'];
      $this->authortime = $rpc_infos['AuthorTime'];
    } else {

      // set defaults ...
      $this->id = 0;
      $this->name = "none";
    }
  }
}


/**
 * Contains information about a rpc call.
 */
class RPCCall {
  var $index;
  var $id;
  var $callback;
  var $call;

  function RPCCall($id, $index, $callback, $call) {
    $this->id = $id;
    $this->index = $index;
    $this->callback = $callback;
    $this->call = $call;
  }
}


/**
 * Contains information about a chat command.
 */
class ChatCommand {
  var $name;
  var $help;
  var $isadmin;

  function ChatCommand($name, $help, $isadmin) {
    $this->name = $name;
    $this->help = $help;
    $this->isadmin = $isadmin;
  }
}


/**
 * Stores basic information of the server
 * Aseco is running on.
 */
class Server {
  var $game;
  var $ip;
  var $port;
  var $login;
  var $pass;
  var $status;
  var $challenge;
  var $records;
  var $players;
  var $admins;
  var $gameinfo;
  var $trackdir;

  function getGame() {
    switch ($this->game) {
      case "TmNationsESWC":
        return "TMN";
      case "TmSunrise":
        return "TMS";
      case "TmOriginal":
        return "TMO";
      case "TmUnited":
        return "TMU";
	case "TmForever":
	  return "TMF";
    }
  }

  function Server($ip, $port, $login, $pass) {
    $this->ip = $ip;
    $this->port = $port;
    $this->login = $login;
    $this->pass = $pass;
  }
}


/**
 * Contains information to the
 * current game which is played.
 */
class Gameinfo {
  var $mode;
  var $chattime;

  // returns current game mode as string.
  function getMode() {
    switch ($this->mode) {
      case 1:
        return "TimeAttack";
        break;
      case 2:
        return "Rounds";
        break;
      case 3:
        return "Team";
        break;
      default:
        return "Undefined";
        break;
    }
  }

  function Gameinfo($rpc_infos = null) {
    if($rpc_infos) {
      $this->mode = $rpc_infos['GameMode'];
      $this->chattime = $rpc_infos['ChatTime'];
    }
  }
}

class Masterserver {
  var $ip;
  var $port;
  var $login;
  var $pass;

  function Masterserver () {
    // do nothing ...
  }
}
?>
