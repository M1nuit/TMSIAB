<?php

/**
 * Description of Sidebar
 *
 * @author Petri Järvisalo
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\CpDisplay;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeController;
use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Event\Dispatcher;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Utilities\Time;

class Standard extends TypeController {

    private $inRace = false;
    private $records = array();
    private $lrPlugin;
    private $disabledUsers = array();

	private $nbCp;

public function onUnload() {
		Dispatcher::unRegister(\ManiaLivePlugins\MLEPP\LocalRecords\Events\onRecordUpdate::getClass(), $this);
        Dispatcher::unRegister(\ManiaLivePlugins\MLEPP\LocalRecords\Events\onChallengeChange::getClass(), $this);
		unset($this->lrPlugin);
		parent::onUnload();
	}
    public function onInit() {
        parent::onInit();
        $this->setVersion(1);
        $this->setWhenToShow(1);
		$this->enablePlayerClones();
    }

    public function onLoad() {
        parent::onLoad();
        $this->enableStorageEvents();
        $this->enableDedicatedEvents();

        if ($this->isPluginLoaded('MLEPP\LocalRecords')) {
            Dispatcher::register(\ManiaLivePlugins\MLEPP\LocalRecords\Events\onRecordUpdate::getClass(), $this);
            Dispatcher::register(\ManiaLivePlugins\MLEPP\LocalRecords\Events\onChallengeChange::getClass(), $this);
        }
		$this->nbCp = $this->storage->currentChallenge->nbCheckpoints;
    }

    public function onReady() {
        $this->lrPlugin = \ManiaLivePlugins\MLEPP\LocalRecords\LocalRecords::getInstance();
        $this->records = $this->lrPlugin->getRecords();

        parent::onReady();
    }

    public function onPlayerCheckpoint($playerUid, $login, $timeOrScore, $curLap, $checkpointIndex) {

        parent::onPlayerCheckpoint($playerUid, $login, $timeOrScore, $curLap, $checkpointIndex);

        if (isset($this->disabledUsers[$login]))
            return;
        if (count($this->records) > 0) {
            foreach ($this->records as $record) {
                if ($record->login == $login) {

					//Handling MultiLaps.
					if(!isset($record->ScoreCheckpoints[$checkpointIndex]) && $this->players[$login]->__get('time') != null){
						$timeOrScore -= $this->players[$login]->__get('time');
					}

                    if (isset($record->ScoreCheckpoints[$checkpointIndex])
							&& is_array($record->ScoreCheckpoints)
							&& count($record->ScoreCheckpoints) > 0
					){
                        $diff = abs($record->ScoreCheckpoints[$checkpointIndex] - $timeOrScore);
                        $time = new Time();
                        $diff = $time->FromTM($diff);

                        if ($timeOrScore < $record->ScoreCheckpoints[$checkpointIndex]) {
                            $text = '$s$00f-' . $diff;
                        } else {
                            $text = '$s$f00+' . $diff;
                        }
                        $this->showWidget($login, $text);
                        return;

                    }
                    // else {
                        // print "\n[CpDisplay] error with record datastucture for $login:\n";
                        // print_r($record->ScoreCheckpoints);
                        // return;
                    // }
                }
            }
        }
    }

	public function onPlayerFinishLap($player, $time, $checkpoints, $nbLap) {

		if($checkpoints == $this->storage->currentChallenge->nbCheckpoints){
			$this->players[$player->login]->__set("time", $time);
		}else{
			$dif = $time - $this->players[$player->login]->__get('time');
			if($dif < $this->players[$player->login]->__get('time'))
					$this->players[$player->login]->__set('time', $dif);
		}
	}

		public function onStatusChanged($statusCode, $statusName) {
			parent::onStatusChanged($statusCode, $statusName);
	 if ($statusCode != 4) return;
		$this->nbCp = $this->storage->currentChallenge->nbCheckpoints;
	}

    public function onMLEPP_LocalRecords_newRecord($login, $record, $oldRank) {
        $this->disabledUsers[$login] = true;
        $this->records = $this->lrPlugin->getRecords();
    }

    public function onMLEPP_LocalRecords_newChallange($records, $players) {
        $this->disabledUsers = array();
        $this->records = $records;
    }

    public function onMLEPP_LocalRecords_firstRecord($login, $record, $oldRank) {

    }

    public function onMLEPP_LocalRecords_bestScore($login, $record) {

    }
	
		function destroy()
	{
		parent::destroy();
		gc_collect_cycles();
	}

}

?>
