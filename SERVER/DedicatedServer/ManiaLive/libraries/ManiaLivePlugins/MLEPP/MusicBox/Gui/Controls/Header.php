<?php

namespace ManiaLivePlugins\MLEPP\MusicBox\Gui\Controls;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;

class Header extends \ManiaLive\Gui\Windowing\Control
{
	protected $background;
	protected $label;

	function initializeComponents()
	{
		$this->sizeX = $this->getParam(0);
		$this->sizeY = $this->getParam(1);

		// insert background ...
		$this->background = new BgsPlayerCard($this->getSizeX(), $this->getSizeY() - 1);
		$this->addComponent($this->background);

		// insert label ...
		$this->label = new Label($this->getSizeX() - 2, $this->getSizeY() - 1);
		$this->label->setPosition(1, 1);
		$this->addComponent($this->label);
	}

	function onResize()
	{
		$this->background->setSize($this->getSizeX(), $this->getSizeY());
		$this->label->setSize($this->getSizeX() - 2, $this->getSizeY());
	}

	function beforeDraw()
	{
		$this->background->setSubStyle("BgCard");
	}

	function setText($text)
	{
		$this->label->setText('$fff'.$text);
	}
}