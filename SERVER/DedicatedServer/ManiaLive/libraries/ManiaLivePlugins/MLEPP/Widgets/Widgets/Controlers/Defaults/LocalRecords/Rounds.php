<?php


/**
 * Description of LiveRankings
 *
 * @author De Cramer Oliver
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\LocalRecords;

use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Event\Dispatcher;
use ManiaLive\Data\Storage;

class Rounds extends \ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace {

    private $lrPlugin;

    public function onInit() {
        parent::onInit();
        $this->setVersion(0.1);
        $this->setWhenToShow(1);

        //Oliverde8 Menu
        if($this->isPluginLoaded('MLEPP\LocalRecords')) {
            Dispatcher::register(\ManiaLivePlugins\MLEPP\LocalRecords\Events\onRecordUpdate::getClass(), $this);
            Dispatcher::register(\ManiaLivePlugins\MLEPP\LocalRecords\Events\onChallengeChange::getClass(), $this);
        }
    }
	public function onUnload() {
		Dispatcher::unRegister(\ManiaLivePlugins\MLEPP\LocalRecords\Events\onRecordUpdate::getClass(), $this);
        Dispatcher::unRegister(\ManiaLivePlugins\MLEPP\LocalRecords\Events\onChallengeChange::getClass(), $this);
		unset($this->lrPlugin);
		parent::onUnload();
	}
    public function  onLoad() {
        parent::onLoad();
        $this->enableStorageEvents();
        $this->enableDedicatedEvents();
    }

    public function  onReady() {
        parent::onReady();
        $this->lrPlugin = \ManiaLivePlugins\MLEPP\LocalRecords\LocalRecords::getInstance();
        $this->onMLEPP_LocalRecords_newChallange($this->data, array());
    }

    public function onPlayerConnect($login, $isSpectator){
        //The parent handles the widget we need to call it
        parent::onPlayerConnect($login, $isSpectator);
    }

    public function onPlayerDisconnect($login){
        //The parent handles the widget we need to call it
        parent::onPlayerDisconnect($login);
    }

    public function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge){
        parent::onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge);
    }

    public function onMLEPP_LocalRecords_newRecord($login, $record, $oldRank){
        $this->updateWidgetData($this->lrPlugin->getRecords());
        $this->data = $this->lrPlugin->getRecords();
        $this->updateWidget($oldRank, $record->rank, $this->storage->getPlayerObject($login));
    }

    public function onMLEPP_LocalRecords_newChallange($records, $players){
        $this->data = $records;
        $this->updateWidgetData($records);
        $this->inRace = true;
        $this->forceUpdateWidget();
    }

    public function onMLEPP_LocalRecords_firstRecord($login, $record, $oldRank ){

    }

    public function  onMLEPP_LocalRecords_bestScore($login, $record ){

    }
}
?>
