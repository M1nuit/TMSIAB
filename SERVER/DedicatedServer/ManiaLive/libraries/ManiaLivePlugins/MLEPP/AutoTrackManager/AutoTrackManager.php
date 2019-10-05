<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 * 
 * -- MLEPP Plugin --
 * @name AutoTrackManager
 * @date 04-01-2011
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 * 
 * @author Willem 'W1lla' van den Munckhof <w1llaopgezwolle@gmail.com>
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
 * You are allowed to change things of use this in other projects, as
 * long as you leave the information at the top (name, date, version,
 * website, package, author, copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */
 
namespace ManiaLivePlugins\MLEPP\AutoTrackManager;

use ManiaLive\Utilities\Console;
use ManiaLive\Utilities\String;
use ManiaLive\Database\Connection;
use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\MLEPP\Database\Structures\multiQuery;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLive\DedicatedApi\Xmlrpc\Exception;
use ManiaLive\Features\Admin\AdminGroup;
class AutoTrackManager extends \ManiaLive\PluginHandler\Plugin {


	public static $version = 1050;
	private $playerKarmas = array();
	public static $defaultTracklist = 'tracklist.txt';
	public static $showname = '%server%AutoTrackManager loaded successfully! %variable%Type /atm';
	public static $MINVotes = "10"; // Must be greater then 0. Best is to have a functional of 10.
	public static $integervalue = "0.6"; // Ratio in percents (0.6 mean 60% good/totalvotes => 40% bad!) the tracks will be sort out if a track is lower than this value
	
	
	
	 /**
     * onInit()
     * Function called on starting the plugin.
     * 
     * @return void
     */
	 
    function onInit() {
		$this->setVersion(self::$version);
        $this->setPublicMethod('getVersion');
		$this->mlepp = Mlepp::getInstance();
		$this->config = Config::getInstance();
	}
	
	
	 /**
     * onLoad()
     * Function called on loading the plugin.
     * 
     * @return void
     */
	 
function onLoad() {
if($this->isPluginLoaded('MLEPP\Database', 251)) {
    	Console::println('['.date('H:i:s').'] [MLEPP] Plugin: AutoTrackManager r'.$this->getVersion().' by W1lla');
		$this->enableDedicatedEvents();
    	$this->enableStorageEvents();
		
        $help = "Shows atm functions.";
		$cmd = $this->registerChatCommand("atmhelp", "atmhelp", 0, true);
		$cmd->help = $help;
		}
		}
		
	 /**
     * onUnLoad()
     * Function called on unloading the plugin.
     * 
     * @return void
     */
    function onUnload() {
    	parent::onUnload();    
    }
		
	 /**
     * onPlayerConnect()
     * Function called on connecting a player.
     * 
     * @return void
     */
	 
function onPlayerConnect($login, $isSpectator) {
		$source_player = $this->storage->getPlayerObject($login);
		$message = str_replace('%nickname%', $source_player->nickName, self::$showname);
		$message = str_replace('%version%', self::$version, $message);
		$this->mlepp->sendChat($message, $source_player);        
	}
	
	 /**
     * onatmhelp()
     * Function called when player types his/her /atmhelp Shows info what it really does.
     * 
     * @return void
     */
	 
function atmhelp($login) {
		$player = $this->storage->getPlayerObject($login);
		$this->mlepp->sendChat('%autotrackmanager%AutoTrackManager lets you %variable%remove / delete%autotrackmanager% tracks from tracklist if track karma got lower than a given value!', $player);
		}

		function onBeginRace($challenge)
		{
		$this->autotrackmanager();
		}
		
	 /**
     * autotrackmanager()
     * Function used to run the whole code basicly.
     * 
     * @return void
     */
function autotrackmanager()
		{

		$challenge = $this->storage->currentChallenge;
		$uid = $challenge->uId;
		$name = $challenge->name;
	   	$q = "SELECT * FROM `karma` WHERE `karma_trackuid` = ".$this->mlepp->db->quote($uid).";";
		$query = $this->mlepp->db->query($q);
		
		$counter = 0;	
		$totalkarma = 0;
        
        $positive = 0;
        $negative = 0;
        
	$this->playerKarmas = array();
		while($data = $query->fetchStdObject()) {
                if($data->karma_value == '1' || $data->karma_value == '2') {
                    $karma_value = '--';
                    $totalkarma = ($totalkarma-1);
                    $negative++;
                } else {
                    $karma_value = '++';
                    $totalkarma = ($totalkarma+1);
                    $positive++;
                }
			$this->playerKarmas[$data->karma_playerlogin] = $karma_value;
			$counter++;
		}
		if ($counter == 0) { 
			$totalkarma = 0;
		}
		$this->totalKarma = $totalkarma;
        $this->karmaVoters = $counter;
		$admins = array();
		foreach($this->storage->players as $player) {
			$login = $player->login;
			if($this->mlepp->AdminGroup->hasPermission($login,'admin')) $admins[] = $player;
						$this->mlepp->sendChat('%autotrackmanager%Current Track Ratio is %variable%'.( ( $counter>0 )?( round($positive/$counter,2) ):('n/a') ).'%autotrackmanager% AutoTrackManager will remove it if it has a percent difference of: %variable%'.($this->config->integervalue).' ', $player);
		}
        
		foreach($this->storage->spectators as $player) {
			$login = $player->login;
			if($this->mlepp->AdminGroup->hasPermission($login,'admin')) $admins[] = $player;
			$this->mlepp->sendChat('%autotrackmanager%Current Track Ratio is %variable%'.( ( $counter>0 )?( round($positive/$counter,2) ):('n/a') ).'%autotrackmanager% AutoTrackManager will remove it if it has a percent difference of: %variable%'.($this->config->integervalue).' ', $player);
		}
		//Showing track karma status ATM Debug However should be enabled for admins to really see if its true or not.
		Console::println('['.date('H:i:s').'] [MLEPP] [ATM] Karma: '.$totalkarma.'');
		Console::println('ATM Debug: plus: '.$positive.', minus: '.$negative.', ratio: '.( ( $counter>0 )?( round($positive/$counter,2) ):('n/a') ));
		if($counter >= $this->config->MINVotes && $positive/$counter <= $this->config->integervalue) 
			{
			Console::println('['.date('H:i:s').'] [MLEPP] [ATM] Karma: removal test');
			Console::println('ATM Debug: Track too bad: '.$name);
			 /**
			 Here begins the real function of removing the track from and server and database.
			 **/
		$dataDir = $this->connection->gameDataDirectory();
        $dataDir = str_replace('\\','/',$dataDir);
        $matchsettings = $dataDir."Maps/MatchSettings/";
		$tracklist = $this->mlepp->depot("admin")->get("settings")->defaultTracklist;
		$challenge = $this->connection->getCurrentChallengeInfo();
        $dataDir = $this->connection->gameDataDirectory();
        $dataDir = str_replace('\\','/',$dataDir);
        $file = $challenge->fileName;
        $challengeFile = $dataDir."/Maps/".$file;
		try { 
        $this->connection->removeChallenge($challengeFile);
		} 
catch (Exception $e) {
Console::println("Error:\n".$e->getMessage());
}
		$this->mlepp->sendChat('%autotrackmanager%AutoTrackManager%autotrackmanager% removed this track from playlist.');
		Console::println('['.date('H:i:s').'] [MLEPP] [ATM] Removed current track from the tracklist.');
		$this->connection->saveMatchSettings($tracklist);
		$file = fopen('ATMLog.txt', 'w');
		fwrite($file, '['.date('H:i:s').'] [MLEPP] [ATM] Removed '. $name .' (UId '. $uid .') from the tracklist.\n');
		fclose($file);
		Console::println('['.date('H:i:s').'] [MLEPP] [ATM] Removing all data from database from '.$name.'');
	   	$q = "DELETE FROM challenges WHERE challenge_uid = ".$this->mlepp->db->quote($uid).";";
		$query = $this->mlepp->db->query($q);
	   	$q = "DELETE FROM localrecords WHERE record_challengeuid = ".$this->mlepp->db->quote($uid).";";
		$query = $this->mlepp->db->query($q);
	   	$q = "DELETE FROM karma WHERE karma_trackuid = ".$this->mlepp->db->quote($uid).";";
		$query = $this->mlepp->db->query($q);
			}
		}

}

?>