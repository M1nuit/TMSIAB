<?php
namespace ManiaLivePlugins\MLEPP\Admin\Gui\Controls;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Icons128x128_1;
use ManiaLib\Gui\Elements\BgRaceScore2;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;

class Button extends \ManiaLive\Gui\Windowing\Control
{
	public $callBack;
	protected $param;
	protected $icon;
	protected $background;

	function initializeComponents()
	{
		$this->param = $this->getParam(2);
		//$this->login =
		$this->sizeY = 6;
		$this->sizeX = 6;
/*		$this->background = new Quad();
		$this->background->setStyle(BgsPlayerCard::BgsPlayerCard);
		$this->background->setSubStyle(BgsPlayerCard::BgPlayerCardBig);

		$this->addComponent($this->background);*/

		$this->icon = new Quad();
		$this->icon->setStyle($this->getParam(0));
		$this->icon->setSubStyle($this->getParam(1));
		$this->addComponent($this->icon);
	}

	function beforeDraw()
	{
		$size = 6;
		$this->icon->setSize($size,$size);
		$this->icon->setAction($this->callback('onClicked'));
		$this->icon->setVisibility(true);

	}

	function onResize()
	{
	}

	function afterDraw() {}

	function onClicked($login)
	{
		call_user_func($this->callBack, $login, $this->param);
		$this->redraw();
	}

	function destroy()
	{
		$this->callBack = null;
		parent::destroy();
	}
}
?>