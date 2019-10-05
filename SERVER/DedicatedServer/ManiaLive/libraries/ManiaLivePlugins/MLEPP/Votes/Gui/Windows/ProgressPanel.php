<?php

namespace ManiaLivePlugins\MLEPP\Votes\Gui\Windows;

use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLivePlugins\MLEPP\Votes\Votes;
use ManiaLive\Data\Storage;

class ProgressPanel extends \ManiaLive\Gui\Windowing\Window {

	private $frame;
	protected $line;
	protected $vote;
	private $background;
	private $frame2;

	function initializeComponents() {
		//$this->mlepp = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance();
		//if (empty($this->config->Widgets->defaultBackground)) {
			$this->background = new \ManiaLib\Gui\Elements\Icons128x128_Blink();
			$this->background->setSubStyle("ShareBlink");
	/*	} else {
			$this->background = new Quad();
			$this->background->setImage($this->config->Widgets->defaultBackground, true);
		}
	 */
		$this->background->setSize($this->sizeX, $this->sizeY);
		$this->background->setPosY(0);
		$this->background->setValign("center");
		$this->background->setHalign("center");


		$this->frame = new Frame();
		$this->frame->setSize((0.8 * $this->sizeX), (0.8 * $this->sizeY));
		$this->frame->setPosY(3.5);
		$this->frame->setPosX(6);
		$this->frame->setHalign("center");
		
		$this->frame2 = new Frame();
		$this->frame2->applyLayout(new \ManiaLib\Gui\Layouts\Flow());
		$this->frame2->setSize(75,15);
		$this->frame2->setPosY(14);
		//$this->frame2->setPosX();
		$this->frame2->setHalign("center");
		
		

	}

	function onLoad() {

	}

	function onDraw() {
		$this->clearComponents();
		$this->addComponent($this->background);
		$this->addComponent($this->frame);
		$this->addComponent($this->frame2);
		$stor = Storage::GetInstance();

		$yes = $this->vote->yes;
		$no = $this->vote->no;
		$total = count($stor->players);

		$yesRatio = ($yes / $total) * 100;
		$noRatio = ($no / $total) * 100;

		$yesSize = ($yesRatio / 100) * (0.8 * $this->sizeX);
		$noSize = ($noRatio / 100) * (0.8 * $this->sizeX);

		$this->frame->clearComponents();

		$vote = new Label();
		$vote->setSize(30, 3);
		$vote->setPosition(($this->sizeX / 2) - 6, -2);
		$vote->setPosY(-3);
		$vote->setHalign("center");
		$vote->setText('$z$s$fff' . $this->vote->vote);
		$vote->setTextSize(4);
		$this->frame->addComponent($vote);

		$btn = new Quad();
		//$btn->setImage(,true);
		$btn->setStyle("BgRaceScore2");
		$btn->setSubStyle("CupFinisher");
		$btn->setPosition(0, 2.5);
		$btn->setSize($yesSize, 3.5);
		$this->frame->addComponent($btn);

		$btn = new Quad();
		//$btn->setImage(Core::$widget_votebar_no,true);
		$btn->setStyle("BgRaceScore2");
		$btn->setSubStyle("CupPotentialFinisher");
		$btn->setPosition(0, 5.5);
		$btn->setSize($noSize, 3.5);
		$this->frame->addComponent($btn);
		if ($yes != 0) {
			$lyes = new Label();
			$lyes->setSize(10, 3.5);
			$lyes->setPosition($yesSize, 2.5);
			$lyes->setScale(0.7);
			$lyes->setHalign("right");
			$lyes->setText('$z$s$fff' . round($yesRatio, 1) . " [" . $yes . "]");
			$this->frame->addComponent($lyes);
		}

		if ($no != 0) {
			$lno = new Label();
			$lno->setSize(10, 3.5);
			$lno->setScale(0.7);
			$lno->setPosition($noSize, 5.5);
			$lno->setHalign("right");
			$lno->setText('$z$s$fff' . round($noRatio, 1) . " [" . $no . "]");
			$this->frame->addComponent($lno);
		}
		
	}

	function onResize() {
		$this->frame->setSize($this->sizeX, $this->sizeY);
		$this->background->setSize($this->sizeX, $this->sizeY);
	}

	function setVote($vote) {
		$this->vote = $vote;
	}
	function addButton($item) {
		$this->frame2->addComponent($item);
	}
	
	function clearButtons() {
		$this->frame2->clearComponents();
	}

	function addItem($item) {
		$this->frame->addComponent($item);
	}

	function clearItems() {
		$this->frame->clearComponents();
	}

	function destroy() {
		unset($this->vote);
		parent::destroy();
	}

}

?>