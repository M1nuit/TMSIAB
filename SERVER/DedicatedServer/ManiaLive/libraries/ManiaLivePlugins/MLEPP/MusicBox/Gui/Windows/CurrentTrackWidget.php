<?php
namespace ManiaLivePlugins\MLEPP\MusicBox\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\Row;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Layouts\Flow;
use ManiaLivePlugins\MLEPP\Core\Core;

class CurrentTrackWidget extends \ManiaLive\Gui\Windowing\Window
{
	protected $frame;
	protected $quad;
	private $label;
	public $callback;
	protected $background;

	function initializeComponents()
	{
		$this->frame = new Frame();
		$this->frame->setPosition(0, 0);
		$this->addComponent($this->frame);

		$this->quad = new Quad();
		$this->quad->setStyle("Bgs1");
		$this->quad->setSubStyle("BgCard1");
		//$this->quad->setImage(Core::$widget_musicbox, true);
		$this->quad->setAction($this->callback('onClicked'));
		$this->frame->addComponent($this->quad);

		$this->label = new Label();
		$this->frame->addComponent($this->label);

		$this->label2 = new Label();
		$this->frame->addComponent($this->label2);

		//$this->label->enableAutonewline();
	}

	function onLoad() {}

	function onDraw() {


	}

	function onResize()
	{
		$this->frame->setSize($this->sizeX, $this->sizeY);
		$this->label->setSize($this->sizeX-2, $this->sizeY);
		$this->label2->setSize($this->sizeX-2, $this->sizeY);
		$this->quad->setSize($this->sizeX, $this->sizeY);
		$this->label->setPosition(($this->sizeX)/2, 1.5);
		$this->label->setHalign("center");
		$this->label2->setPosition(($this->sizeX)/2, 5.5);
		$this->label2->setHalign("center");
		$this->setScale(0.8);
	}

	function setArtist($text)
	{
		$this->label->setText($text);
	}

	function setSong($text)
	{
		$this->label2->setText($text);
	}


	function onClicked($login)
	{
		call_user_func($this->callback, $login);
		$this->redraw();
	}

	function destroy() {
		parent::destroy();
	}

}
?>