<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers;

use ManiaLive\Data\Storage;

/**
 * Description of showDuringRace
 *
 * @author De Cramer Oliver
 */
abstract class AutoHideDuringRace extends \ManiaLivePlugins\MLEPP\Widgets\Structures\TypeController {

	public static $autoHideActive = true;
	/**
	 * 0 : Default setting, autohide activated
	 * 1 : Audto Hide dectivated, always show
	 * 2 : Auto Hide Deactivated always hide
	 */
	public static $playerSettings = array();
	/**
	 * true : is not HIden
	 * false : is Hidden
	 */
	public $actualState = array();

	public function onLoad() {
		parent::onLoad();
		\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\AutoHidePanel\Standard::$AutoHidewidgets[\get_class($this)] = $this;
	}

	public function onUnload() {
		unset(\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\AutoHidePanel\Standard::$AutoHidewidgets[\get_class($this)]);
		parent::onUnload();
	}

	public function onPlayerCheckpoint($playerUid, $login, $score, $curLap, $checkpointIndex) {
		if (!isset(self::$playerSettings[$login])) {
			self::$playerSettings[$login] = 0;
			$this->actualState[$login] = true;
		}

		if (self::$playerSettings[$login] == 0 && self::$autoHideActive) {
			$this->forceHideWidget($login);
			$this->actualState[$login] = false;
		}
	}

	public function onPlayerDisconnect($login) {
		parent::onPlayerDisconnect($login);
		unset(self::$playerSettings[$login]);
		unset($this->actualState[$login]);
		
	}

	
	public function onPlayerFinish($playerUid, $login, $timeOrScore) {
		if (!isset(self::$playerSettings[$login])) {
			self::$playerSettings[$login] = 0;
			$this->actualState[$login] = true;
		}

		if (self::$playerSettings[$login] == 0 && self::$autoHideActive) {
			$this->actualState[$login] = true;
			$this->showWidget($login);
		}
	}

	/**
	 * Redirecting the showWidget not to refresh the wondows if no need to.
	 */
	public function showWidget($login, $data = null) {
		if (!isset($this->actualState[$login])) {
			self::$playerSettings[$login] = 0;
			$this->actualState[$login] = true;
		}

		if (self::$playerSettings[$login] == 1 && $this->actualState[$login] == false) {
			$this->actualState[$login] = true;
			parent::showWidget($login, $data);
		} elseif (self::$playerSettings[$login] == 2) {
			$this->actualState[$login] = false;
			parent::showWidget($login, $data);
		} else if ($this->actualState[$login] || !self::$autoHideActive)
			parent::showWidget($login, $data);
	}

	public function updateWidget($oldRank, $newRank, $player) {

		foreach ($this->widgets as $widget) {
			if (!isset($this->actualState[$widget->getRecipient()])) {
				self::$playerSettings[$widget->getRecipient()] = 0;
				$this->actualState[$widget->getRecipient()] = true;
			}

			if ($this->actualState[$widget->getRecipient()] && $widget->needUpdate($oldRank, $newRank, $player)) {
				$widget->show();
			}
		}
	}

	public function forceUpdateWidget($login = null) {
		foreach ($this->widgets as $widget) {
			if (!isset($this->actualState[$widget->getRecipient()])) {
				self::$playerSettings[$widget->getRecipient()] = 0;
				$this->actualState[$widget->getRecipient()] = true;
			}

			if ($this->actualState[$widget->getRecipient()]) {
				$widget->show();
			}
		}
	}

}

?>
