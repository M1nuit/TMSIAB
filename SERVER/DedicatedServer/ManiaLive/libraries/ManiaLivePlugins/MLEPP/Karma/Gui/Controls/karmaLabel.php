<?php
namespace ManiaLivePlugins\MLEPP\Karma\Gui\Controls;

use ManiaLib\Gui\Elements\Label;

class karmaLabel extends \ManiaLive\Gui\Windowing\Control
{

	private $label;
	private $text = "";

	function initializeComponents()
	{
		$this->text = $this->getParam(0);
		$this->sizeX = $this->getParam(1);
		$this->sizeY = $this->getParam(2);

		$this->label = new Label();
		$this->label->setPosY(1);
		$this->label->setHalign("center");
		$this->label->setValign("center");
		$this->addComponent($this->label);
		
	}

	function beforeDraw()
	{
		$this->label->setText($this->text);
	}

	function onResize()
	{
	}

	function afterDraw() {}


	function destroy()
	{
		unset($this->label);
		unset($this->text);
		unset($this->sizeX);
		unset($this->sizeY);
		parent::destroy();
	}

	Function setText($text) {
		$this->text = $text;
	}
}
?>