<?php

/**
 * Description of LiveRankings
 *
 * @author De Cramer Oliver
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\LiveRankings;

use ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace;
use ManiaLive\Data\Storage;

class TimeAttack extends AutoHideDuringRace {

	public function onInit() {
		parent::onInit();
		$this->setVersion(1);
		$this->enablePlayerClones();
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
		if ($this->storage->serverStatus->code == 4) {

			foreach ($this->storage->players as $login => $player) {
				parent::onPlayerConnect($login, false);
			}
			foreach ($this->storage->spectators as $login => $player) {
				parent::onPlayerConnect($login, true);
			}
			$this->copyRanks(Storage::getInstance()->ranking);
			$this->updateWidgetData($this->data);
			$this->forceUpdateWidget();
		}
		parent::onReady();
	}

	public function onPlayerNewBestTime($player, $best_old, $best_new) {

		$login = $player->login;
		$this->players[$login]->bestTime = $best_new;
		$this->players[$login]->score = $best_new;

		if ($this->players[$login]->bestTime == 0) {
			$oldRank = 0;
		} else {
			$oldRank = $this->players[$login]->rank;
		}

		$this->updateWidget($oldRank, $player->rank, $player);
		$this->copyRanks(Storage::getInstance()->ranking);
		$this->updateWidgetData($this->data);
		$this->forceUpdateWidget();
	}

	public function onPlayerConnect($login, $isSpectator) {
		//The parent handles the widget we need to call it
		parent::onPlayerConnect($login, $isSpectator);
	}

	public function onPlayerDisconnect($login) {
		if (isset($this->players[$login]) && $this->players[$login]->bestTime != 0) {
			$this->copyRanks(Storage::getInstance()->ranking);
			$this->updateWidgetData($this->data);
			$this->forceUpdateWidget();
		}
		parent::onPlayerDisconnect($login);
	}

	public function onStatusChanged($statusCode, $statusName) {
		parent::onStatusChanged($statusCode, $statusName);
		if ($statusCode != 4)
			return;

		$this->data = array();

		$this->updateWidgetData($this->data);
		$this->forceUpdateWidget();
	}

	public function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge) {
		parent::onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge);
	}

	private function copyRanks($ranks) {

		$this->data = array();
		//$done = array();

		$r = 1;
		foreach ($ranks as $i => $player) {
			if (isset($this->players[$player->login]) && $this->players[$player->login]->bestTime != 0) {
				$this->data[$r] = $this->players[$player->login];
				$this->data[$r]->rank = $r;
				$r++;
			}
		}
	}

	function destroy() {
		parent::destroy();
		gc_collect_cycles();
	}

	/* private function copyRanks($ranks){

	  $this->data = array();
	  $done = array();

	  $r = 1;
	  foreach($ranks as $i=>$player){
	  if(isset($this->players[$player->login]) && $this->players[$player->login]->bestTime != 0 && !isset($done[$player->login])){
	  $this->data[$r] = $this->players[$player->login];
	  $this->data[$r]->rank = $r;
	  $r++;
	  $done[$player->login] = 1;
	  }
	  }
	  } */
}

?>
