<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name LocalRecords
 * @date 20-03-2011
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP team
 *
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

namespace ManiaLivePlugins\MLEPP\Rankings;

use ManiaLive\Utilities\Console;
use ManiaLive\Utilities\String;
use ManiaLive\Database\Connection;
use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Time;
use ManiaLive\PluginHandler\Dependency;
use ManiaLivePlugins\MLEPP\Database\Structures\multiQuery;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLivePlugins\MLEPP\Rankings\Gui\Windows\TopRankingsWindow;
use ManiaLive\Event\Dispatcher;

class Rankings extends \ManiaLive\PluginHandler\Plugin {
	protected $connected;
 	private $ranks = array();
	private $stats = array();
	private $playerswithrank = null;
	private $mlepp;
	private $config;
	//help texts
	private $desc100 = "Usage: /top100";
	private $descRank = "Usage: /rank";
	private $help100 = "Open a window with the current top 100 of records holders on this server.

\$wUsage\$z:
\$o/top100\$z	- Show the 100 best players on this server.";
	private $helpRank = "Show your current server rank.

\$wUsage\$z:
\$o/rank\$z - Outputs your server rank in the chat.";

    /**
     * onInit()
     * Function called on initialisation of ManiaLive.
     *
     * @return void
     */

	public function onInit() {
        $this->setVersion(1050);
        $this->setPublicMethod('getVersion');
        $this->setPublicMethod('getRank');

		$this->config = \ManiaLivePlugins\MLEPP\Localrecords\Config::getInstance();
		
        $this->mlepp = Mlepp::getInstance();
		//Oliverde8 Menu
		if($this->isPluginLoaded('oliverde8\HudMenu')) {
			Dispatcher::register(\ManiaLivePlugins\oliverde8\HudMenu\onOliverde8HudMenuReady::getClass(), $this);
		}
    }

    /**
     * onLoad()
     * Function called on loading of ManiaLive.
     *
     * @return void
     */

	public function onLoad(){
        if($this->isPluginLoaded('MLEPP\Database', 251)) {
            Console::println('['.date('H:i:s').'] [MLEPP] Plugin: Rankings r'.$this->getVersion() );
            $this->connected = true;

            $this->enableDedicatedEvents();

            $cmd = $this->registerChatCommand("top100", "top100", 0, true);
			$cmd->help = $this->desc100;
            $cmd = $this->registerChatCommand("rank", "rank", 0, true);
			$cmd->help = $this->descRank;
			 $cmd = $this->registerChatCommand("top100", "top100", 1, true);
			$cmd->help = $this->desc100;
            $cmd = $this->registerChatCommand("rank", "rank", 1, true);
			$cmd->help = $this->descRank;
        } else {
            Console::println('['.date('H:i:s').'] [MLEPP] [Rankings] Plugin couldn\'t been load because plugin \'MLEPP\Database\' isn\'t activated.');
            $this->connected = false;
			die();
        }

    }

	 /**
     * onLoad()
     * Function called on unloading the plugin.
     *
     * @return void
     */
    function onUnload() {
    	parent::onUnload();
    }

	 /**
	 * onReady()
     * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */

    public function onReady() {
 		$this->calcRanks();
    }

	 /**
	 * onOliverde8HudMenuReady()
     * Function used for adding buttons to Olivers Hud Menu.
	 *
	 * @param mixed $menu
	 * @return void
	 */
	public function onOliverde8HudMenuReady($menu) {
		$parent = $menu->findButton(array("Menu", "Records"));
		if(!$parent) {
			$button["style"] = "Icons128x128_1";
			$button["substyle"] = "Replay";
			$parent = $menu->addButton("Menu", "Records", $button);
		}

		$button["style"] = "BgRaceScore2";
		$button["substyle"] = "LadderRank";
		$button["plugin"] = $this;
		$button["function"] = "top100";
		$parent = $menu->addButton($parent, "Rank : top100", $button);

		$parent = $menu->findButton(array("Menu", "Help"));
		if(!$parent) {
			$button["style"] = "Icons64x64_1";
			$button["substyle"] = "TrackInfo";
			$parent = $menu->addButton("Menu", "Help", $button);
		}

		$button["style"] = "BgRaceScore2";
		$button["substyle"] = "LadderRank";
		$button["plugin"] = $this;
		$button["function"] = "top100";
		$button["params"] = "help";
		$parent = $menu->addButton($parent, "Help /top100", $button);

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
        $this->calcRanks();
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
		foreach($this->storage->players as $login => $player) {
			if(array_key_exists($login, $this->ranks)) {
				$this->mlepp->sendChat('%rank%Your current server rank is %variable%'.$this->stats[$login]['place'].'/'.$this->playerswithrank.'%rank% [Average: %variable%'.round($this->ranks[$login],2).'%rank%]!', $login);
			} else {
				$this->mlepp->sendChat("%rank%You don't have a server rank yet, drive more records to have one!", $login);
			}
		}

		foreach($this->storage->spectators as $login => $player) {
			if(array_key_exists($login, $this->ranks)) {
				$this->mlepp->sendChat('%rank%Your current server rank is %variable%'.$this->stats[$login]['place'].'/'.$this->playerswithrank.'%rank% [Average: %variable%'.round($this->ranks[$login],2).'%rank%]!', $login);
			} else {
				$this->mlepp->sendChat("%rank%You don't have a server rank yet, drive more records to have one!", $login);
			}
		}
	}

    /**
     * rank()
     * Function providing the /rank command.
     *
     * @param mixed $login
     * @param mixed $param
     * @return
     */

    function rank($login, $param = NULL) {
		if($param == "help") {
			$this->showHelp($login,$this->helpRank);
			return;
		}

        if(array_key_exists($login, $this->ranks)) {
            $this->mlepp->sendChat('%rank%Your current server rank is %variable%'.$this->stats[$login]['place'].'/'.$this->playerswithrank.'%rank% [Average: %variable%'.round($this->ranks[$login],2).'%rank%]!', $login);
        } else {
            $this->mlepp->sendChat("%rank%You don't have a server rank yet, drive more records to have one!", $login);
        }
    }

    /**
     * getRank()
     * Helper function, gets rank of player ($login).
     *
     * @param mixed $login
     * @return array $rank
     */

    function getRank($login) {
        if(array_key_exists($login, $this->ranks)) {
            return array("rank" => $this->stats[$login]['place'], "players" => $this->playerswithrank, "avg" => round($this->ranks[$login],2));
        } else {
            return array();
        }
    }

	 /**
	 * top100()
     * Function providing the /top100 command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @return
	 */

	function top100($login, $param = NULL) {
		if($param == "help") {
			$this->showHelp($login,$this->help100);
			return;
		}

		$i = 1;
		$stats = $this->stats;
		$q = "SELECT * FROM `players`;";
		$dbData = $this->mlepp->db->query($q);

		if($dbData->recordCount() == 0) {
			return false;
		} else {
            while($data = $dbData->fetchStdObject()) {
                $nicknames[$data->player_login] = $data->player_nickname;
            }

            $window = TopRankingsWindow::Create($login);
            $window->setSize(210, 100);
            $window->clearAll();
            // prepare cols ...
            $window->addColumn('Rank', 0.2);
            $window->addColumn('Avg', 0.2);
            $window->addColumn('NickName', 0.4);
            $window->addColumn('Records', 0.2);

            foreach($this->ranks as $login => $rank) {
                if($i > 101) break;

                $entry = array
                    (
                        'Rank' => array($i,NULL,false),
                        'Avg' =>  array(round($rank,2),NULL,false),
                        'NickName' =>  array($nicknames[$login],NULL,false),
                        'Records' =>  array($stats[$login]['count'],NULL,false)
                    );
                    $window->addActionItem($entry, array());
                    $i++;
            }

            $window->centerOnScreen();
            $window->show();
        }
	}

	 /**
	 * calcRanks()
     * Function used for calculating the ranks.
	 *
	 * @return void
	 */

	function calcRanks() {
		$serverrank = array();
		$stats = array();
		$ranks = array();
		$data = $this->getChallengeRanks();
		$trackCount = $data[0];
		$ranks = $data[1];
		$x = 1;
		unset($data);
        //echo $trackCount;
        //print_r($ranks);
		foreach($ranks as $login => $data) {
			$sum = array_sum($data);
			$count = count($data);
			$avg = ($sum + ($trackCount - $count) * $this->config->numrec) / $trackCount;
			$serverrank[$login] = $avg;
			$stats[$login] = array("sum" => $sum,"count" => $count);
		}

		asort($serverrank);
		foreach($serverrank as $login => $data) {
			$stats[$login]['place'] = $x;
			$x++;
		}
		$this->ranks = $serverrank;
		$this->stats = $stats;
        $this->playerswithrank = ($x-1);
	}

	 /**
	 * getChallengeRanks()
     * Function used for getting the ranks on the challenge.
	 *
	 * @return
	 */

	function getChallengeRanks() {

        $ranks = array();
        $uid = array();
        unset($dbData);
        unset($data);

        $tracks = $this->connection->getChallengeList(-1,0);
        $count = count($tracks);

        for ($i=0; $i<$count; $i++)
            $uid[] = $tracks[$i]->uId;

		$q = "SELECT `record_playerlogin`, record_challengeuid FROM `localrecords`
        WHERE `record_challengeuid` IN ('".implode("','",$uid)."')
        ORDER BY record_challengeuid ASC, record_score ASC";
	       $dbData = $this->mlepp->db->query($q);


            $i = 1;
			       $tmpuid = '';
			while($data = $dbData->fetchStdObject()) {

                if ($data->record_challengeuid != $tmpuid) {
                    $tmpuid = $data->record_challengeuid;
                    $i=1;
                }
                if ($i <= $this->config->numrec)
                    $ranks[$data->record_playerlogin][]= $i;
                $i++;
            }
		return array($count, $ranks);
    }

	 /**
	 * showHelp()
	 * Function used for showing the help window.
     *
	 * @param mixed $login
	 * @param mixed $text
	 * @return void
	 */

	function showHelp($login,$text) {
		$this->callPublicMethod('MLEPP\Core', 'showHelp', $login, "help for plugin ".$this->getName(), $text);
	}
}
?>