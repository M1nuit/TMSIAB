<?php

namespace ManiaLivePlugins\MLEPP\Karma\Gui\Controls;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Icons128x128_1;
use ManiaLib\Gui\Elements\BgRaceScore2;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;

class Plus extends \ManiaLive\Gui\Windowing\Control {

	public $callBack;
	public $sign;
	public $highlite = false;
	private $gfxPlus;
	
	private $PlusValue;
	
	private $gfxBackground;
	private $login;

	function initializeComponents() {
		$this->PlusValue = $this->getParam(0);
		//$this->highlite = $this->getParam(1);
		//$this->login = $this->getParam(2);
		$this->sizeY = 5;
		$this->sizeX = 5;
		//$this->gfxBackground = new Quad();
		//$this->addComponent($this->gfxBackground);

		$this->gfxPlus = new Quad();
		$this->gfxPlus->setPosZ(-101);
		$this->gfxPlus->setValign("center");
		$this->gfxPlus->setHalign("center");
		$this->gfxPlus->setPosY(2);
		$this->addComponent($this->gfxPlus);
	}

	function beforeDraw() {
		$sizeBig = 5;
		$sizeSmall = 5;

		if ($this->sign == "plus") {
			$this->sizeY = $sizeBig;
			$this->sizeX = $sizeBig;
			//$this->gfxBackground->setSize($sizeBig,$sizeBig);
			if ($this->highlite == true) {
				$this->gfxPlus->setImage("http://koti.mbnet.fi/reaby/manialive/images/1up_hover.png", true);
			} else {
				$this->gfxPlus->setImage("http://koti.mbnet.fi/reaby/manialive/images/1up.png", true);
			}
			$this->gfxPlus->setImageFocus("http://koti.mbnet.fi/reaby/manialive/images/1up_hover.png", true);
			$this->gfxPlus->setSize($sizeBig, $sizeBig);
		} else {
		
			$this->sizeY = $sizeSmall;
			$this->sizeX = $sizeSmall;
			
			if ($this->highlite == true) {
				$this->gfxPlus->setImage("http://koti.mbnet.fi/reaby/manialive/images/1down_hover.png", true);
			} else {
				$this->gfxPlus->setImage("http://koti.mbnet.fi/reaby/manialive/images/1down.png", true);
			}
			$this->gfxPlus->setImageFocus("http://koti.mbnet.fi/reaby/manialive/images/1down_hover.png", true);
			$this->gfxPlus->setSize($sizeSmall, $sizeSmall);
		}

		$this->gfxPlus->setAction($this->callback('onClicked'));
		$this->gfxPlus->setVisibility(true);
		$this->gfxPlus->setPosZ(-101);
	}

	function onResize() {
		
	}

	function afterDraw() {
		
	}

	function onClicked($login) {
		call_user_func($this->callBack, $login, $this->PlusValue);
		//$this->redraw();
	}

	function destroy() {
		$this->callBack = null;
		parent::destroy();
	}

}

?>