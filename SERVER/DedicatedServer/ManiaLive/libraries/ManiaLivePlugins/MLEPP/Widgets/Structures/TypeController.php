<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Structures;

use ManiaLive\Data\Storage;

/**
 *
 * @author De Cramer Oliver
 */
abstract class TypeController extends \ManiaLive\PluginHandler\Plugin {

	static protected $pluginWidgets;
	protected $widgets = array();
	protected $name;
	protected $settings;
	private $generatePlayerClone = false;
	private $hasLoaded = false;
	protected $players;
	protected $data = null;
	private $whenShow = 0;
	private $theState = 'Race';

	public function onInit() {
		$name = explode("\\", \get_class($this));
		$this->name = $name[\sizeof($name) - 3] . "\\" . $name[\sizeof($name) - 2] . "\\" . $name[\sizeof($name) - 1];

		$this->setPublicMethod('setSetings');
		$this->settings = new Settings();
		$this->whenShow = 0;
	}

	public function onLoad() {
		$this->enableDedicatedEvents();
	}

	public function onReady() {

		foreach ($this->storage->players as $login => $player) {
			$this->onPlayerConnect($login, false);
		}

		foreach ($this->storage->spectators as $login => $player) {
			$this->onPlayerConnect($login, true);
		}

		if ($this->storage->serverStatus->code == 4) {
			$this->theState = 'Race';
		}
		$this->hasLoaded = true;
	}

	public function onPlayerConnect($login, $isSpectator) {
		$this->showWidget($login, $this->data);

		if ($this->generatePlayerClone && !isset($this->players[$login])) {
			$player = \ManiaLive\Data\Storage::getInstance()->getPlayerObject($login);
			;
			$this->players[$login] = new smallPlayer($player);
		}

		$this->needToHide($login);
	}

	public function onStatusChanged($statusCode, $statusName) {
		if ($statusCode != 4)
			return;

		//The parent handles the widget we need to call it
		//parent::onBeginChallenge($challenge, $warmUp, $matchContinuation);

		$this->theState = 'Race';
		$this->players = array();

		// fix for onBeginChallenge problem
		if (isset($this->storage->players)) {
			foreach ($this->storage->players as $login => $player) {
				$this->onPlayerConnect($login, false);
			}

			foreach ($this->storage->spectators as $login => $player) {
				$this->onPlayerConnect($login, true);
			}
		}
	}

	public function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge) {
		$this->theState = 'EndRace';
		foreach ($this->storage->players as $login => $player) {
			$this->showWidget($login);
			$this->needToHide($login);
		}

		foreach ($this->storage->spectators as $login => $player) {
			$this->showWidget($login);
			$this->needToHide($login);
		}
	}

	public function setSetings($setting) {
		$this->settings = $setting;
		if (!\is_array($this->widgets))
			return;
		foreach ($this->widgets as $w) {
			$w->setPosition($this->settings->posX, $this->settings->posY);
			$w->setSettings($this->settings);
			$w->show();
		}
	}

	public function showWidget($login, $data = null) {
		if (!isset($this->widgets[$login])) {
			$viewName = "\ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\\" . $this->name;
			$w = $viewName::Create($login);
			if ($data == null)
				$w->setData($this->data);
			else
				$w->setData($data);

			$this->widgets[$login] = $w;
		}
		else {
			$w = $this->widgets[$login];
		}

		if ($w != null) {
			$w->setPosition($this->settings->posX, $this->settings->posY);
			$w->setSettings($this->settings);

			if ($data != null)
				$w->setData($data);
			else
				$w->setData($this->data);

			$w->show();
		}
	}

	public function eraseWidget($login) {
		if (isset($this->widgets[$login])) {
			$this->widgets[$login]->hide();
			$this->widgets[$login]->Erase($login);
			unset($this->widgets[$login]);
		}
	}

	public function updateWidget($oldRank, $newRank, $player) {
		foreach ($this->widgets as $widget) {
			if ($widget->needUpdate($oldRank, $newRank, $player) == true) {
				print_r($this->data);
				$widget->setData($this->data);
				$widget->show();
			}else
				$widget->setData($this->data);
		}
	}

	public function resetWidgetData() {
		foreach ($this->widgets as $widget) {
			$widget->resetData();
		}
	}

	public function updateWidgetData($data) {
		foreach ($this->widgets as $widget) {
			$widget->setData($data);
		}
	}

	public function forceUpdateWidget($login = null) {
		if ($login == null) {
			foreach ($this->widgets as $widget) {
				$widget->show();
			}
		} else {
			$this->widgets[$login]->show();
		}
	}

	public function forceHideWidget($login = null) {
		if ($login == null) {
			foreach ($this->widgets as $widget) {
				$widget->hide();
			}
		} elseif (isset($this->widgets[$login])) {
			$this->widgets[$login]->hide();
		}
	}

	public function needToHide($login) {

		$show = false;

		//If showing in permanent
		if ($this->whenShow == 0)
			$show = true;

		//If showing only during Race
		if (!$show && $this->theState == 'Race' && $this->whenShow == 1)
			$show = true;

		//If showin only at endRace
		if (!$show && $this->theState == 'EndRace' && $this->whenShow == 2)
			$show = true;

		if (!$show) {
			$this->widgets[$login]->hide();
		}

		return $show;
	}

	public function onPlayerDisconnect($login) {
		if (isset($this->widgets[$login])) {
			$this->eraseWidget($login);
			unset($this->players[$login]);
			parent::onPlayerDisconnect($login);
		}
	}

	public function onUnload() {
		$viewName = "\ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\\" . $this->name;
		$w = $viewName::EraseAll();
		unset($this->players);
		unset($this->widgets);
		unset($this->settings);
		unset($this->data);
		parent::onUnload();
	}

	public function getWidget($login) {
		return (isset($this->widgets[$login]) ? $this->widgets[$login] : false);
	}

	public function getSizeX($login) {
		return (isset($this->widgets[$login]) ? $this->widgets[$login]->getSizeX() : false);
	}

	public function getSizeY($login) {
		return (isset($this->widgets[$login]) ? $this->widgets[$login]->getSizeY() : false);
	}

	public function getPosX($login) {
		return (isset($this->widgets[$login]) ? $this->widgets[$login]->getPosX() : false);
	}

	public function getPosY($login) {
		return (isset($this->widgets[$login]) ? $this->widgets[$login]->getPosY() : false);
	}

	/**
	 * 0 : Show permanantly, it is default value
	 * 1 : Show During Race only
	 * 2 : Show at the end of the Race
	 *
	 *
	 * @param <type> When to show
	 */
	public function setWhenToShow($value) {
		$this->whenShow = $value;
	}

	protected function enablePlayerClones() {
		if ($this->hasLoaded) {
			throw new Exception("You need to activate pleyer cloning onInit not later");
		}
		$this->generatePlayerClone = true;
	}

}

?>
