<?php

namespace ManiaLivePlugins\MLEPP\Votes\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLive\Gui\Windowing\Controls\Pager;
use ManiaLib\Gui\Layouts\Flow;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLib\Gui\Elements\Button;
use ManiaLib\Gui\Elements\Format;

class VotePanel extends \ManiaLive\Gui\Windowing\Window {

	private $frame;
	private $frame2;
	protected $line;
	private $background;
	private $vote;
	private $config;

	function initializeComponents() {

		if (empty($this->config->Widgets->defaultBackground)) {
			$this->background = new \ManiaLib\Gui\Elements\Icons128x128_Blink();
			$this->background->setSubStyle("ShareBlink");
		} else {
			$this->background = new Quad();
			$this->background->setImage($this->config->Widgets->defaultBackground, true);
		}
		$this->background->setSize($this->sizeX, $this->sizeY);
		$this->background->setPosY(0);
		$this->background->setValign("center");
		$this->background->setHalign("center");


		// this is buttons frame
		$this->frame2 = new Frame();
		$this->frame2->setSize($this->sizeX, $this->sizeY);
		$this->frame2->setPosY(4.5);
		$this->frame2->setHalign("center");


		//this is text frame
		$this->frame = new Frame();
		$this->frame->applyLayout(new Flow());
		$this->frame->setSize($this->sizeX, $this->sizeY);
		$this->frame->setPosY(10);
		$this->frame->setPosX(2);
		$this->frame->setHalign("center");
	}

	function onLoad() {
		
	}

	function onShow() {
		//print "Show " . get_called_class() . " " . $this->login;
		$this->clearComponents();
		$this->addComponent($this->background);
		$this->addComponent($this->frame);
		$this->addComponent($this->frame2);
	}

	function onHide() {
		//print "Hide " . get_called_class() . " " . $this->login;
	}

	function onResize() {
		$this->frame->setSize($this->sizeX, $this->sizeY);
		$this->background->setSize($this->sizeX, $this->sizeY);
	}

	function setText($text) {
		$this->vote = new Label();
		$this->vote->setSize(45, 3);
		//	$this->vote->setValign("top");
		$this->vote->setPosY(-4.5);
		$this->vote->setHalign("center");
		$this->vote->setText('$z$s$fff' . $text);
		$this->vote->setTextSize(5);
		$this->vote->setStyle(Format::TextTitle2Blink);
		$this->frame2->addComponent($this->vote);
	}

	function setAdminText($text) {
		$this->vote = new Label();
		$this->vote->setSize(45, 3);
		//	$this->vote->setValign("top");
		$this->vote->setPosY(0);
		$this->vote->setHalign("center");
		$this->vote->setText('$z$s$fff' . $text);
		$this->vote->setTextSize(3);

		$this->frame2->addComponent($this->vote);
	}

	function addItem($item) {
		$this->frame->addComponent($item);
	}

	function clearItems() {
		$this->frame->clearComponents();
		$this->frame2->clearComponents();
	}

	function destroy() {
		unset($this->vote);
		parent::destroy();
	}

}

?>