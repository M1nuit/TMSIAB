<?php

namespace ManiaLivePlugins\MLEPP\ServerMail\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;

use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLib\Gui\Elements\Label;
use ManiaLive\Gui\Windowing\Controls\Panel;
class ReadWindow extends \ManiaLive\Gui\Windowing\ManagedWindow
{
	protected $panel;
	protected $frame;
	private $label;
	private $quad;
	private $callback2;
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

	function beforeDraw() {

	}

	function onResize()
	{
		$this->panel->setSize($this->sizeX, $this->sizeY);
		$this->frame->setSize($this->sizeX - 4, $this->sizeY - 8);
		$this->label->setSize($this->sizeX - 4, $this->sizeY - 8);
	}

	function setTopic($text)
	{
		$this->setTitle($text);
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