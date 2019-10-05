<?php
namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\LiveRankings;

use ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace;
/**
 * Description of Rounds
 *
 * @author De Cramer Oliver
 */
class Rounds extends \ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace{

    private $cpData = array();
	private $lastCp;

	public function onUnload() {
		parent::onUnload();
	}

    public function onInit() {
        parent::onInit();
        $this->enablePlayerClones();
        $this->setVersion(1);
        $this->setWhenToShow(1);
    }

    public function  onLoad() {
        parent::onLoad();
        $this->enableStorageEvents();
        $this->enableDedicatedEvents();
    }



    public function onPlayerConnect($login, $isSpectator){
        //The parent handles the widget we need to call it
        parent::onPlayerConnect($login, $isSpectator);
        $this->players[$login]->rank = -1;
		$this->players[$login]->__set('lastCp', -1);

    }

	public function onPlayerDisconnect($login){
        if(isset($this->players[$login]) && $this->players[$login]->bestTime != 0){
            //$this->copyRanks(Storage::getInstance()->ranking);
            $this->refreshRanks();
			$this->updateWidgetData($this->data);
            $this->forceUpdateWidget();
        }
		
		parent::onPlayerDisconnect($login);
		gc_collect_cycles();
    }

    	public function onStatusChanged($statusCode, $statusName) {
		parent::onStatusChanged($statusCode, $statusName);
		if ($statusCode != 4) return;

        //The parent handles the widget we need to call it


        $this->data = array();

        foreach ($this->storage->players as $login => $player) {
            $this->players[$login]->rank = -1;

        }
        $this->updateWidgetData($this->data);
        $this->forceUpdateWidget();
		$this->lastCp = 0;
    }

    public function onBeginRound(){
        //Calling parent
        parent::onBeginRound();

        $this->data = array();
        $this->players = array();
        $this->cpData = array();
		$this->lastCp = 0;

        foreach ($this->storage->players as $login => $player) {
            $this->updatePlayer($player);
			//echo $player->score;
        }
        $this->updateWidgetData($this->data);
        $this->forceUpdateWidget();
    }

    public function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge){
        parent::onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge);
    }

    public function onPlayerCheckpoint($playerUid, $login, $score, $curLap, $checkpointIndex) {
        parent::onPlayerCheckpoint($playerUid, $login, $score, $curLap, $checkpointIndex);

        if(!isset($this->players[$login]))
            return;

        $player = $this->players[$login];
        $oldRank = $player->rank;
		$this->lastCp = $checkpointIndex;

		$player->__set('lastCp', $checkpointIndex);
		$player->__set('lastCpTime', $score);
		$go = false;

        if(isset($this->cpData[$checkpointIndex]) && !empty($this->cpData[$checkpointIndex])){
            $rank = \sizeof($this->cpData[$checkpointIndex]) + 1;
            //echo "go\n";
            $player->score = "+".\ManiaLive\Utilities\Time::fromTM($score - $this->cpData[$checkpointIndex][0]->__get('lastCpTime'));
			if(!isset( $this->cpData[$checkpointIndex]))
				$this->cpData[$checkpointIndex] = array();

            $this->cpData[$checkpointIndex][] = $player;
        }else{
            $rank = 1;
            $player->score = \ManiaLive\Utilities\Time::fromTM($score);
            $this->cpData[$checkpointIndex][0] = $player;
        }
        $player->LastCp = $checkpointIndex;

        if($oldRank == \sizeof($this->data)){
            unset($this->data[$oldRank]);
            $oldRank--;
            $go = true;
        }

        if($oldRank == -1){
            $this->moveRanks($rank);
			$player->rank = $rank;
			$this->data[$player->rank] = $player;

        }else if($rank != $oldRank || $go){
            $this->moveRanks($rank, $oldRank);

			$player->rank = $rank;
			$this->data[$player->rank] = $player;
			$this->refreshRanks();
        }

        $this->updateWidgetData($this->data);
        $this->updateWidget($oldRank, $player->rank, $player);
    }

    private function moveRanks($nb, $limit=-1){

        if($limit == -1)
            $limit = sizeof($this->data);

        for($i = $limit; $i >= $nb && $i >0; $i--){
            $this->data[$i+1] = $this->data[$i];
            $this->data[$i+1]->rank = $i+1;
        }
    }

	private function refreshRanks(){

		foreach($this->data as $rank => $p){

			if($p->__get('lastCp') != $this->lastCp ){
				$dif = $p->__get('lastCpTime') - $this->cpData[$p->__get('lastCp')][0]->__get('lastCpTime');

				if($dif <= 0){
					$p->score = "--:--:--";

				}else{
					$p->score = "+".\ManiaLive\Utilities\Time::fromTM($dif);
				}
			}
        }
	}


    private function showRanks(){
        foreach($this->data as $rank => $p){
            echo "[$rank] ".$p->login." - Rank : ".$p->rank." - Scroe : ".$p->score."\n";
        }
    }

    private function updatePlayer($player){
        $login = $player->login;

        if(!isset($this->players[$login])){
            $this->players[$login] = new \ManiaLivePlugins\MLEPP\Widgets\Structures\smallPlayer($player);
        }
		$this->players[$login]->rank =-1;
		$this->players[$login]->score = 0;
		$this->players[$login]->__set('lastCp', -1);
    }
	
		function destroy()
	{
		parent::destroy();
		gc_collect_cycles();
	}

}
?>
