<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\AutoHidePanel;

use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeView;

/**
 * Description of TimeAttack
 *
 * @author De Cramer Oliver
 */
class Standard extends TypeView {

	public function initializeComponents() {
		$this->label = "Local Records";
	}

	public function onDraw() {
		$this->clearComponents();

		//Icons64x64_1
		$icon = new \ManiaLib\Gui\Elements\Icons64x64_1();
		$icon->setSizeX(6);
		$icon->setSizeY(6);

		if (!isset(\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$playerSettings[$this->getRecipient()]))
			\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$playerSettings[$this->getRecipient()] = 0;

		switch (\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$playerSettings[$this->getRecipient()]) {
			case 2 :
				$icon->setSubstyle("LvlRed");
				break;
			case 1 :
				$icon->setSubstyle("LvlGreen");
				break;
			case 0 :
				$icon->setSubstyle("LvlYellow");
				break;
		}
		$icon->setAction($this->callback('onClick'));

		$this->addComponent($icon);
	}

	public function onClick($login) {
		if (!isset(\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$playerSettings[$this->getRecipient()]))
			\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$playerSettings[$this->getRecipient()] = 0;

		\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$playerSettings[$this->getRecipient()]++;
		if (\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$playerSettings[$this->getRecipient()] > 2)
			\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$playerSettings[$this->getRecipient()] = 0;
		$this->show();

		if(\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$playerSettings[$this->getRecipient()] == 2){
			foreach (\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\AutoHidePanel\Standard::$AutoHidewidgets as $w) {
				$w->forceHideWidget($login);
			}
		}else{
			foreach (\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\AutoHidePanel\Standard::$AutoHidewidgets as $w) {
				$w->showWidget($login);
			}
		}
	}

	public function setData($d) {
		$this->data = $d;
	}

	public function getWidgetSizeX() {
		return (6);
	}

	public function getWidgetSizeY() {
		return (6);
	}

	public function destroy()
	{
		parent::destroy();
	}

}

?>
