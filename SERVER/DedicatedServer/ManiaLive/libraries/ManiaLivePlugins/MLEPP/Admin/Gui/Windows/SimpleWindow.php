<?php

namespace ManiaLivePlugins\MLEPP\Admin\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\Row;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLib\Gui\Elements\Label;
use ManiaLive\Gui\Windowing\Controls\Panel;

class SimpleWindow extends \ManiaLive\Gui\Windowing\ManagedWindow
{
	protected $panel;
	protected $frame;
	private $label;

	function initializeComponents()
	{
		$this->panel = new Panel();
		$this->addComponent($this->panel);

		$this->frame = new Frame();
		$this->frame->setPosition(2, 16);
		$this->panel->addComponent($this->frame);

		$this->label = new Label();
		$this->label->enableAutonewline();
		$this->frame->addComponent($this->label);

	}

	function onLoad() {}

	function onDraw() {}

	function onResize()
	{
		$this->panel->setSize($this->sizeX, $this->sizeY);
		$this->frame->setSize($this->sizeX - 4, $this->sizeY - 8);
		$this->label->setSize($this->sizeX - 4, $this->sizeY - 8);
	}

	function setTitle($text)
	{
		$this->panel->setTitle($text);
	}

	function setText($text)
	{
		$this->label->setText($text);
	}

	function destroy()
	{
		parent::destroy();
	}
}
?>