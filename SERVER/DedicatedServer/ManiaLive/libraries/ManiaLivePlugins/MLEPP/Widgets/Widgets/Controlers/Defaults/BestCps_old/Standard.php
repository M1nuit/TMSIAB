<?php

/**
 * Description of BestCps
 *
 * @author Petri Järvisalo
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\BestCps_old;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeController;
use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Event\Dispatcher;
use ManiaLive\DedicatedApi\Connection;

class Standard extends TypeController {

    private $checkpoints = array(); //  will contain number of checkpoints of arrays containing:

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
//		$this->checkpoints[$checkpointIndex] = array("nickname" => "Ath", "login" => "reaby", "score" => 1000000); 
//		}
//        $this->updateWidgetData($this->checkpoints);        
//		
//		foreach ($this->storage->players as $player) {
//		$this->showWidget($player->login, $this->checkpoints);
//		}
    }

    public function onPlayerConnect($login, $isSpectator) {
        //The parent handles the widget we need to call it
        parent::onPlayerConnect($login, $isSpectator);
        $this->showWidget($login, $this->checkpoints);
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
    }

	public function onStatusChanged($statusCode, $statusName) {
		parent::onStatusChanged($statusCode, $statusName);
	 if ($statusCode != 4) return;
	 $this->resetData();
	 $this->updateWidgetData($this->checkpoints);
     $this->forceUpdateWidget();

	}

    public function resetData() {
        $this->checkpoints[0] = array();

		$helper[0] = array("nickname" => null, "login" => null, "score" => 0); // this is to fix, if no checkpoints present on track.
        for ($x = 0; $x < $this->storage->currentChallenge->nbCheckpoints; $x++) {
            $helper[$x] = array("nickname" => null, "login" => null, "score" => 0);
        }
        $this->checkpoints = $helper;
    }

    public function onPlayerCheckpoint($playerUid, $login, $timeOrScore, $curLap, $checkpointIndex) {
        parent::onPlayerCheckpoint($playerUid, $login, $timeOrScore, $curLap, $checkpointIndex);

        if (count($this->checkpoints) > 0) {
            if (!isset($this->checkpoints[$checkpointIndex]) || $timeOrScore < $this->checkpoints[$checkpointIndex]['score'] || $this->checkpoints[$checkpointIndex]['score'] == 0) {
                $player = $this->storage->getPlayerObject($login);
                $this->checkpoints[$checkpointIndex] = array("nickname" => $player->nickName, "login" => $login, "score" => $timeOrScore);
                $this->updateWidgetData($this->checkpoints);
                $this->forceUpdateWidget();
            }
        }
    }
		function destroy()
	{
		parent::destroy();
		gc_collect_cycles();
	}

}

?>
