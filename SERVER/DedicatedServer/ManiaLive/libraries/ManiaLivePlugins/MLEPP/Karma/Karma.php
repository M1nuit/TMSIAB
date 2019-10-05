<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Karma
 * @date 30-01-2011
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

namespace ManiaLivePlugins\MLEPP\Karma;
use ManiaLivePlugins\MLEPP\Karma\Gui\Windows\KarmaWindow;
use ManiaLivePlugins\MLEPP\Karma\Gui\Windows\ListWindow;
use ManiaLivePlugins\MLEPP\Karma\Gui\Controls\Star;
use ManiaLivePlugins\MLEPP\Karma\Gui\Controls\Plus;
use ManiaLivePlugins\MLEPP\Karma\Gui\Controls\karmaLabel;
use ManiaLivePlugins\MLEPP\Karma\Gui\Controls\Header;
use ManiaLivePlugins\MLEPP\Karma\Gui\Controls\Normal;
use ManiaLive\Utilities\Console;
use ManiaLib\Utils\TMStrings as String;
use ManiaLive\Database\Connection;
use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Time;
use ManiaLivePlugins\MLEPP\LocalRecords\Gui\recordList;

use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class Karma extends \ManiaLive\PluginHandler\Plugin {

    private $playerKarmas = array();
    private $totalKarma;
    private $karmaVoters;
    private $karmaVotesPosNeg;
   	private $config;

    /**
     * onInit()
     * Function called on initialisation of ManiaLive.
     *
     * @return void
     */

	function onInit() {
		$this->setVersion(1050);
		$this->mlepp = Mlepp::getInstance();
		$this->config = Config::getInstance();
        $this->setPublicMethod('getVersion');
		$this->setPublicMethod('showWidget');
		$this->setPublicMethod('hideWidget');


	}

    /**
     * onLoad()
     * Function called on loading of ManiaLive.
     *
     * @return void
     */

	function onLoad() {
	 	if($this->isPluginLoaded('MLEPP\Database', 2.0) ) {
			$dbversion = $this->callPublicMethod('MLEPP\Database', 'getVersion', 'karma');
			if($dbversion > 1) {
		 		Console::println('['.date('H:i:s').'] [MLEPP] Plugin: Karma r'.$this->getVersion() );
	            $this->enableDedicatedEvents();
	            $this->enableStorageEvents();
			} else {
				 die('['.date('H:i:s').'] [MLEPP|Karma] Plugin couldn\'t been load because database version is wrong. update your databases.');
			}
        } else {
            Console::println('['.date('H:i:s').'] [MLEPP|Karma] Plugin couldn\'t been load because plugin \'MLEPP\Database\' isn\'t activated.');
            die('['.date('H:i:s').'] [MLEPP|Karma] Plugin couldn\'t been load because plugin \'MLEPP\Database\' isn\'t activated.');
        }

        $help = "increases current track karma.";

        if($this->config->karmaKind == 'stars') {
            $cmd = $this->registerChatCommand("+++", "vote5", 0, true);
            $cmd->help = $help;
            $cmd = $this->registerChatCommand("+", "vote3", 0, true);
            $cmd->help = $help;
            $help = "decreases current track karma.";
            $cmd = $this->registerChatCommand("-", "vote2", 0, true);
            $cmd->help = $help;
        }

		$cmd = $this->registerChatCommand("++", "vote4", 0, true);
		$cmd->help = $help;
        $help = "decreases current track karma.";
		$cmd = $this->registerChatCommand("--", "vote1", 0, true);
		$cmd->help = $help;

        if($this->config->karmaKind != 'stars') {
            $cmd = $this->registerChatCommand("karma", "showKarma", 0, true);
            $cmd->help = "shows the current track karma";
            $cmd = $this->registerChatCommand("whokarma", "whoKarma", 0, true);
            $cmd->help = "shows who voted what";
        }
	}
	
	function onUnload() {
		parent::onUnload();
		KarmaWindow::EraseAll();
	}

	 /**
	 * onReady()
     * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */

    function onReady() {
        $this->getChallengeKarma();
        $this->updateAllKarmaWindows();
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

    function onBeginChallenge($challenge, $warmUp, $matchContinuation){
        $this->getChallengeKarma();
        $this->updateAllKarmaWindows();
        if($this->config->karmaKind != 'stars') {
            $this->showKarma();
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

    function onPlayerConnect($login, $isSpec) {
		if(!array_key_exists($login, $this->playerKarmas)) {
            $highlite = 0;
		} else {
            $highlite = $this->playerKarmas[$login];
        }
		$this->updateKarmaWindow($login,$this->totalKarma,$highlite);
    }

    function onPlayerDisconnect($login) {
        if (isset($this->playerKarmas[$login])) {
            unset($this->playerKarmas[$login]);
        }
        KarmaWindow::Erase($login);
    }

    /**
     * showKarma()
     * Function providing the /karma command.
     *
     * @param mixed $login
     * @return void
     */

    function showKarma($login = null) {
        $positive = $this->karmaVotesPosNeg['positive'];
        $negative = $this->karmaVotesPosNeg['negative'];
        $message = '%server%Karma $fff»» %karma%Current track karma: %variable%'.$this->totalKarma.'%karma% (%variable%'.$this->karmaVoters.'%karma% voters, %variable%'.$positive.'%karma% ++ and %variable%'.$negative.'%karma% --) !';
        if($login != null) {
            // message to login
            $player = $this->storage->getPlayerObject($login);
            $this->mlepp->sendChat($message, $player);
        } else {
            if($this->config->showChatMessages === true) {
                // message to everyone
                $this->mlepp->sendChat($message);
            }
        }
    }
    
    function whoKarma($login) {
        if($this->config->whoKarmaAdminOnly) {
            if(!$this->mlepp->AdminGroup->hasPermission($login, 'admin')) {
                $this->mlepp->getNoPermissionMsg();
                return;
            }
        }
        
		$window = ListWindow::Create($login);
        $window->setTitle('/whokarma - Who voted what?');
		$window->setSize(180, 100);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('NickName', 0.4);
        $window->addColumn('Login', 0.4);
		$window->addColumn('Vote', 0.2);
        
        $positive = array();
        $negative = array();

		// refresh records for this window ...
		$window->clearItems();
		$id = 1;
		foreach(array_keys($this->playerKarmas) as $login) {
            $value = $this->playerKarmas[$login];
            $playerQuery = $this->mlepp->db->query("SELECT `player_nickname` FROM `players` WHERE `player_login` = '".$login."'");
            $playerInfo = $playerQuery->fetchStdObject();
            $nickName = $playerInfo->player_nickname;
            
            if($value == '+' ||$value == '++' || $value == '+++') {
                $positive[] = array('NickName' => $nickName,
				'Login' => $login,				
				'Vote' => $value);
            } else {
                $negative[] = array('NickName' => $nickName,
				'Login' => $login,				
				'Vote' => $value);
            }
        }
        
        foreach($positive as $posi) {            
			$window->addItem($posi);
		}
        
        foreach($negative as $negi) {            
			$window->addItem($negi);
		}

		$window->centerOnScreen();
		$window->show();
    }

    /**
     * getChallengeKarma()
     * Function used for getting the karma of the challenge.
     *
     * @return void
     */

    function getChallengeKarma($uid = null) {
        if(is_null($uid)) {
            $challenge = $this->storage->currentChallenge;
            $uid = $challenge->uId;
        }

	   	$q = "SELECT * FROM `karma` WHERE `karma_trackuid` = ".$this->mlepp->db->quote($uid).";";
		$query = $this->mlepp->db->query($q);

		$counter = 0;
		$totalkarma = 0;

        $positive = 0;
        $negative = 0;

		$this->playerKarmas = array();
		while($data = $query->fetchStdObject()) {
            if($this->config->karmaKind == 'stars') {
                $karma_value = $data->karma_value;
                $totalkarma += $data->karma_value;
            } else {
                if($data->karma_value == '1' || $data->karma_value == '2') {
                    $karma_value = '--';
                    $totalkarma = ($totalkarma-1);
                    $negative++;
                } else {
                    $karma_value = '++';
                    $totalkarma = ($totalkarma+1);
                    $positive++;
                }
            }
			$this->playerKarmas[$data->karma_playerlogin] = $karma_value;
			$counter++;
		}
		if ($counter == 0) {
			$totalkarma = 0;
		} elseif($this->config->karmaKind == 'stars') {
			$totalkarma = (int)($totalkarma / $counter);
		}
		$this->totalKarma = $totalkarma;
        $this->karmaVoters = $counter;
        if($this->config->karmaKind != 'stars') {
            $this->karmaVotesPosNeg = array('positive' => $positive, 'negative' => $negative);
        }
	}

	 /**
	 * updateKarmaToDatabase()
     * Function used for updating the karma in the database.
	 *
	 * @param mixed $login
	 * @param mixed $value
	 * @return void
	 */

	function updateKarmaToDatabase($login, $value) {
        $challenge = $this->storage->currentChallenge;
        $uid = $challenge->uId;
        //check if player is at database
		$g =  "SELECT * FROM `karma` WHERE `karma_playerlogin` = ".$this->mlepp->db->quote($login)."
		 AND `karma_trackuid` = ".$this->mlepp->db->quote($uid).";";

		$query = $this->mlepp->db->query($g);
 		// get player data
		$player = $this->storage->getPlayerObject($login);

		if($query->recordCount() == 0) {
		// 	--> add new player entry
				$q = "INSERT INTO `karma` (`karma_playerlogin`,
                                                    `karma_trackuid`,
                                                    `karma_value`
                                                    )
		                                VALUES (".$this->mlepp->db->quote($login).",
                                                ".$this->mlepp->db->quote($uid).",
                                                ".$this->mlepp->db->quote($value)."
                                                )";
				$this->mlepp->db->query($q);
		}
		else {
	   	//	--> update existing player entry

			$q =
			"UPDATE
			`karma`
			 SET
			 `karma_value` = ".$this->mlepp->db->quote($value)."
			 WHERE
			 `karma_playerlogin` = ".$this->mlepp->db->quote($login)."
			 AND
			 `karma_trackuid` = ".$this->mlepp->db->quote($uid).";";

			$this->mlepp->db->query($q);
		}
	}

    /**
     * vote5()
     * Function to vote 5.
     *
     * @param mixed $login
     * @return void
     */

    function vote5($login) {
		$this->applyVote($login, 5);
    }

    /**
     * vote4()
     * Function to vote 4.
     *
     * @param mixed $login
     * @return void
     */

    function vote4($login) {
		$this->applyVote($login, 4);
    }

    /**
     * vote3()
     * Function to vote 3.
     *
     * @param mixed $login
     * @return void
     */

    function vote3($login) {
		$this->applyVote($login, 3);
	}

    /**
     * vote2()
     * Function to vote 2.
     *
     * @param mixed $login
     * @return void
     */

    function vote2($login) {
		$this->applyVote($login, 2);
    }

    /**
     * vote1()
     * Function to vote 1.
     *
     * @param mixed $login
     * @return void
     */

    function vote1($login) {
		$this->applyVote($login, 1);
    }


    /**
     * applyVote()
     * Function applies the vote.
     *
     * @param mixed $login
     * @param mixed $value
     * @return void
     */

    function applyVote($login, $value) {
        if(is_int($this->config->finishForVote) && $this->config->finishForVote > 0) {
            $uid = $this->storage->currentChallenge->uId;
            $playerFinish = "SELECT * FROM `localrecords` WHERE
                                `record_challengeuid` = '".$uid."'
                                AND `record_playerlogin` = '".$login."'";
            $playerFinished = $this->mlepp->db->query($playerFinish);
            if($playerFinished->recordCount() > 0) {
                $playerFinished = $playerFinished->fetchStdObject();
                $numberOfFinishes = $playerFinished->record_nbFinish;
            } else {
                $numberOfFinishes = 0;
            }
            
            if($numberOfFinishes < $this->config->finishForVote) {
                $this->mlepp->sendChat('%server%Karma $fff»» $f00$iYou have to finish the track at least $fff'.$this->config->finishForVote.'$f00 times!', $login);
                return;
            }
        }
        
		// send chat message if so.
		if($this->config->showChatMessages === true) {
	    	$player = $this->storage->getPlayerObject($login);
	    	$challengeobject = $this->connection->getCurrentChallengeInfo();
            if($this->config->karmaKind === 'stars') {
                $starNames = array("","One","Two","Three","Four","Five");
            } else {
                $starNames = array("","--","--","++","++","++");
            }
			$this->mlepp->sendChat('%server%Karma $fff»» %karma%You voted %variable%'.$starNames[$value].'%karma% on track %variable%'.$challengeobject->name.'$z$s%karma%!', $player);

            Console::println('['.date('H:i:s').'] [MLEPP] [Karma] '.$login.' voted '.$starNames[$value].'.');
		}
		// Todo the karma for database.
		$this->updateKarmaToDatabase($login,$value);

		//redraw window
		$this->getChallengeKarma();
        $this->updateAllKarmaWindows();

        if($this->config->showChatMessages === false) {
            if($this->config->karmaKind != 'stars') {
                $this->showKarma($login);
            }
        }
	}

	 /**
	 * updateKarmaWindow()
     * Function used for updating the karma window.
	 *
	 * @param mixed $login
	 * @param integer $value
	 * @param integer $highlite
	 * @return void
	 */

	function updateKarmaWindow($login, $value = 0, $highlite = 0) {
        if($this->config->karmaKind == 'stars' && $this->isPluginLoaded('MLEPP\ChallengeWidget', 461)) {
            $window = KarmaWindow::Create($login);
            $window->setPosZ(-50);
    		$window->clearItems();
    		for ($x = 1; $x <= 5; $x++) {
    			$item = new Star($x, $login);
    			$item->callBack = array($this, 'onClick');
    			$item->highlite = false;
    			$item->active = false;
    			if($x <= $value) $item->active = true;
    			if($x == $highlite) $item->highlite = true;
    			$window->addItem($item);
    		}
    		$window->setSize(30, 10);
    		$pos = explode(",",$this->config->position);
            $window->setPosition($pos[0],$pos[1]);
    		$window->setHalign("center");
    		$window->show();
        }

        if($this->config->karmaKind == 'positivenegative' && $this->isPluginLoaded('MLEPP\ChallengeWidget', 461)) {
            $window = KarmaWindow::Create($login);
            $window->setPosZ(-50);
    		$window->clearItems();

    		$positive = $this->karmaVotesPosNeg['positive'];
            $negative = $this->karmaVotesPosNeg['negative'];

            if($this->totalKarma < 0) {
                $color = '$f00';
            } elseif($this->totalKarma > 0) {
                $color = '$0c0';
            } else {
                $color = '$fff';
            }

           	$plus = new Plus(4, $login);
    		if ($highlite == 4) $plus->highlite = true;
			$plus->callBack = array($this, 'onClick');
			$plus->sign = "plus";
			$window->addItem($plus);

			$label = new karmaLabel('$0e0  '.$positive."  ",6,3);
			$window->addItem($label);

			$minus = new Plus(2, $login);
    		if ($highlite == 2) $minus->highlite = true;
			$minus->callBack = array($this, 'onClick');
			$minus->sign = "minus";
			$window->addItem($minus);

			$label = new karmaLabel('$e00  '.$negative."  ",6,3);
			$window->addItem($label);

			$window->setSize(48, 12);
            $pos = explode(",",$this->config->position);
            $window->setPosition($pos[0],$pos[1]);
    		$window->setHalign("center");

    		$window->show();
        }
	}

	 /**
	 * updateAllKarmaWindows()
     * Function used for updating all karma windows.
	 *
	 * @return void
	 */

	function updateAllKarmaWindows() {

		foreach($this->storage->players as $login => $player) {
			if (!array_key_exists($login, $this->playerKarmas)) $highlite = 0;
			else $highlite = $this->playerKarmas[$login];

			$this->updateKarmaWindow($login,$this->totalKarma,$highlite);
		}
		foreach($this->storage->spectators as $login => $player) {
			if (!array_key_exists($login, $this->playerKarmas)) $highlite = 0;
			else $highlite = $this->playerKarmas[$login];

			$this->updateKarmaWindow($login,$this->totalKarma,$highlite);
		}

	}

    /**
     * onPlayerChat()
     * Function called when someone is chatting.
     *
     * @param mixed $playerUid
     * @param mixed $login
     * @param mixed $chat
     * @param mixed $isRegistredCmd
     * @return
     */

	function onPlayerChat($playerUid,$login,$text,$isRegistredCmd) {
		if($playerUid != 0) {
                    if(substr($text,0,1) != "/") {
                        if(trim($text) == "+++") $this->applyVote($login,5);
                        if(trim($text) == "++")  $this->applyVote($login,4);
                        if(trim($text) == "+")   $this->applyVote($login,3);
                        if(trim($text) == "-")   $this->applyVote($login,2);
                        if(trim($text) == "--")  $this->applyVote($login,1);
                    }
		}
	}

    /**

	 /**
	 * onClick()
     * Function called on clicking.
	 *
	 * @param mixed $login
	 * @param mixed $name
	 * @param mixed $parameter
	 * @return void
	 */

	function onClick($login, $returnValue) {
		$this->applyVote($login, $returnValue);
	}

	 /**
	 * hideWidget()
     * Helper function, hides the karma widget.
	 *
	 * @param mixed $login
	 * @param mixed $plugin
	 * @return void
	 */

	function hideWidget($login = NULL, $plugin = NULL) {
		$wnd = KarmaWindow::GetAll();
		foreach ($wnd as $win) {
			$win->hide();
		}
	}

	 /**
	 * showWidget()
     * Helper function, shows the karma widget.
	 *
	 * @param mixed $login
	 * @param mixed $plugin
	 * @return void
	 */

	function showWidget($login = NULL, $plugin = NULL) {
        $this->updateAllKarmaWindows();
	}
}
?>