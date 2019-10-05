<?php

namespace ManiaLivePlugins\MLEPP\Admin\Gui\Controls;

use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Icons128x128_1;

class SpecIcon extends \ManiaLive\Gui\Windowing\Control
{
	private $background;
	private $quad;
	public $icon;
	
	function initializeComponents()
	{
		$this->sizeX = $this->getParam(0);
		$this->sizeY = $this->getParam(1);
		$this->icon = $this->getParam(2);

		// insert quad ...
		$this->quad = new Quad($this->getSizeX(), $this->getSizeY());
		
		if ($this->icon == "Race") {
		$this->quad->setStyle(Icons128x128_1::Icons128x128_1);
		$this->quad->setSubStyle(Icons128x128_1::Vehicles);
		} 
		
		if ($this->icon == "Spec") {
		$this->quad->setStyle(Icons64x64_1::Icons64x64_1);
		$this->quad->setSubStyle(Icons64x64_1::Camera);
		} 
		
		$this->addComponent($this->quad);
	}
	
	function onResize()
	{
		$this->quad->setSize($this->getSizeX(), $this->getSizeY());

	}
	
	function beforeDraw()
	{
	}
	
	function setHighlight($highlight)
	{
		$this->highlight = $highlight;
	}
	
	function onClicked($login)
	{
		call_user_func($this->callback, $login, $this->action);
		$this->redraw();
	}
}