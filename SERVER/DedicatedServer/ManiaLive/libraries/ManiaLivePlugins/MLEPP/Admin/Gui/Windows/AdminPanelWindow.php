<?php

namespace ManiaLivePlugins\MLEPP\Admin\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1;
use ManiaLive\Gui\Windowing\Controls\Pager;
use ManiaLib\Gui\Layouts\Flow;
use ManiaLib\Gui\Elements\Quad;
use ManiaLive\Gui\Windowing\Controls\Frame;

class AdminPanelWindow extends \ManiaLive\Gui\Windowing\Window
{
	protected $frame;
	protected $line;
	private $background;

	function initializeComponents()
	{

		$this->background = new Quad();
		$this->background->setStyle(Bgs1::Bgs1);
		$this->background->setSubStyle("ProgressBar");
		//	$this->background->setSubStyle(BgsPlayerCard::BgPlayerCardSmall);
		$this->background->setSize($this->sizeX, $this->sizeY);
		$this->background->setHalign("right");
		$this->addComponent($this->background);

		$this->frame = new Frame();
		$this->frame->applyLayout(new Flow());
		$this->frame->setPosition($this->posX + 2,$this->posY +1);
		$this->frame->setSize($this->sizeX -2, $this->sizeY -2);
		$this->frame->setHalign("right");
		$this->addComponent($this->frame);

	}

	function onLoad() {}

	function onDraw() {

	}

	function onResize()
	{
		$this->frame->setSize($this->sizeX, $this->sizeY);
		$this->background->setSize($this->sizeX, $this->sizeY);

	}

	function addItem($item)
	{
		$this->frame->addComponent($item);
	}

		function clearItems()
	{
		$this->frame->clearComponents();
	}

	function destroy()
	{
		parent::destroy();
	}
}
?>