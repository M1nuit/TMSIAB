<?php

/**
 * Description of BestCps
 *
 * @author Petri Järvisalo
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\BestCps;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeController;
use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Event\Dispatcher;
use ManiaLive\DedicatedApi\Connection;

class Standard extends TypeController {

    //private $ddata = array(); //  will contain number of checkpoints of arrays containing:
	private $globalCheckpoints = array();
	private $playerCheckpoints = array();
	private $playerCpIndexes = array();
	private $totalCp = 0;
	private $firstStart = true;
	
    //  array("nickname" => null,"login" => null,"score" => null);

    public function onInit() {
        parent::onInit();
        $this->setVersion(1);
        $this->setWhenToShow(1);
    }

	public function onUnload() {
		parent::onUnload();
	}

	public function onLoad() {
        parent::onLoad();
        $this->enableStorageEvents();
        $this->enableDedicatedEvents();
    }

    public function onReady() {
        parent::onReady();
        $this->resetData();
//		for ($checkpointIndex = 0; $checkpointIndex < 27; $checkpointIndex++ ){
//		$this->globalCheckpoints[$checkpointIndex] = array("nickname" => "Ath", "login" => "reaby", "score" => 1000000); 
//		}
//        $this->updateWidgetData($this->globalCheckpoints);        
//		
//		foreach ($this->storage->players as $player) {
//		$this->showWidget($player->login, $this->globalCheckpoints);
//		}
    }

    public function onPlayerConnect($login, $isSpectator) {
        //The parent handles the widget we need to call it
        parent::onPlayerConnect($login, $isSpectator);
        $this->showWidget($login, $this->compileData());
    }

    public function onPlayerDisconnect($login) {
        //The parent handles the widget we need to call it
        parent::onPlayerDisconnect($login);
		gc_collect_cycles();
    }

    public function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge) {
        parent::onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge);
    }

    public function onBeginChallenge($challenge, $warmUp, $matchContinuation) {
        parent::onBeginChallenge($challenge, $warmUp, $matchContinuation);
		$this->firstStart = true;
    }
	
	public function onStatusChanged($statusCode, $statusName) {
		parent::onStatusChanged($statusCode, $statusName);
	 if ($statusCode != 4) return;
	 $this->resetData();
	 $this->updateWidgetData($this->compileData());
     $this->forceUpdateWidget();

	}
	
	public function onPlayerFinish($playerUid, $login, $timeOrScore) {
		parent::onPlayerFinish($playerUid, $login, $timeOrScore);
		$this->playerCheckpoints[$login] = array();
		$this->playerCpIndexes[$login] = 0;
	}
	
    public function resetData() {
			$this->globalCheckpoints = array();
			$this->playerCheckpoints = array();
			$this->playerCpIndexes = array();
			$this->totalCp = $this->storage->currentChallenge->nbCheckpoints;
			
		$helper[0] = array("nickname" => null, "login" => null, "score" => 0); // this is to fix, if no checkpoints present on track.
        for ($x = 0; $x < $this->storage->currentChallenge->nbCheckpoints; $x++) {
            $helper[$x] = array("nickname" => null, "login" => null, "score" => 0);
        }
        $this->globalCheckpoints = $helper;
    }

    public function onPlayerCheckpoint($playerUid, $login, $timeOrScore, $curLap, $checkpointIndex) {
        parent::onPlayerCheckpoint($playerUid, $login, $timeOrScore, $curLap, $checkpointIndex);
		
		
		$index = $checkpointIndex;
		
		$this->playerCpIndexes[$login] = $checkpointIndex;
		$this->playerCheckpoints[$login][$checkpointIndex] = $timeOrScore;
		
        if (count($this->globalCheckpoints) > 0) {
            if (!isset($this->globalCheckpoints[$checkpointIndex]) || $timeOrScore < $this->globalCheckpoints[$checkpointIndex]['score'] || $this->globalCheckpoints[$checkpointIndex]['score'] == 0) {
                $player = $this->storage->getPlayerObject($login);
                $this->globalCheckpoints[$checkpointIndex] = array("nickname" => $player->nickName, "login" => $login, "score" => $timeOrScore);
                $this->updateWidgetData($this->compileData());
                $this->forceUpdateWidget();
            } else {
				$this->updateWidgetData($this->compileData());
				$this->forceUpdateWidget($login);
			}
        }
    }
	
	public function compileData() {
		$returnData = new \stdClass();
		$returnData->{"global"} = $this->globalCheckpoints;
		$returnData->{"player"} = $this->playerCheckpoints;
		$returnData->{"index"} = $this->playerCpIndexes;
		$returnData->{"totalCp"} = $this->totalCp;
		return $returnData;
	}
		function destroy()
	{
		parent::destroy();
		gc_collect_cycles();
	}

}

?>
