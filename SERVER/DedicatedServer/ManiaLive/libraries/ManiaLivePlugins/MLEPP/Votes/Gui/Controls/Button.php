<?php
namespace ManiaLivePlugins\MLEPP\Votes\Gui\Controls;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Icons128x128_1;
use ManiaLib\Gui\Elements\BgRaceScore2;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLib\Gui\Elements\Format;

class Button extends \ManiaLive\Gui\Windowing\Control
{
	public $callBack;
	private $param;
	private $btn;
	private $lbl;



	function initializeComponents()
	{
		$this->param = $this->getParam(1);
		$this->sizeX = 18;
		$this->sizeY = 5;

		$this->btn = new Quad();
		$this->btn->setStyle("Bgs1");
		$this->btn->setSubStyle("BgCard1");
		$this->btn->setPosX(($this->sizeX / 2));
		$this->btn->setPosY(0);
		
		$this->btn->setHalign("center");

	//	$this->btn->setPosition(($this->sizeX / 2) - 7, 4);
		$this->addComponent($this->btn);

		$this->lbl = new Label();
		$this->lbl->setTextSize(2);
		$this->lbl->setStyle(Format::TextButtonNav);
		
		$this->lbl->setPosX(($this->sizeX / 2));
		$this->lbl->setPosY(1.5);
		$this->lbl->setHalign("center");
		$this->lbl->setText('$222'.$this->getParam(0));
		$this->lbl->setScale(0.7);
		//$this->lbl->setPosition( ($this->sizeX / 2), 1.5);
		$this->addComponent($this->lbl);

	}

	function beforeDraw()
	{

		$this->btn->setSize(18, 4);
		$this->lbl->setSize(18, 4);
		$this->btn->setAction($this->callback('onClicked'));
		$this->btn->setVisibility(true);
		$this->lbl->setVisibility(true);

	}

	function onResize()
	{
	}

	function afterDraw() {}

	function onClicked($login)
	{
		call_user_func($this->callBack, $login, $this->param);
	}

	function destroy()
	{
		$this->callBack = null;
		parent::destroy();
	}
}
?>