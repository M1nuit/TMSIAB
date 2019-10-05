<?php

namespace ManiaLivePlugins\MLEPP\ChallengeWidget\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLive\Gui\Windowing\Controls\Pager;
use ManiaLib\Gui\Layouts\Flow;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Time;
use ManiaLivePlugins\MLEPP\Core\Core;

class ChallengeWidgetWindow extends \ManiaLive\Gui\Windowing\Window {

	protected $frame;
	public $nowPlaying;
	public $trackname;
	public $trackauthor;
	public $serverCountry;
	public $showinfo;
	private $tmx;
	private $mx;
	private $background;
	public $callback;
	private $serverc;
	private $textColor;
	public $challengeData;
	public $text;

	function onInit() {

	}

	function initializeComponents() {
	$mlepp = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance();
		$this->textColor = '$eee';
		
        $sizeX = 75;
        $sizeY = 28;
        
		if (empty(Core::$widget_background)) {
				$this->background = new \ManiaLib\Gui\Elements\Bgs1();
				$this->background->setSubStyle("BgButtonGrayed");
		} else {
			$this->background = new Quad();
			$this->background->setImage($mlepp->config->Widgets->defaultBackground, true);
		}
		$this->background->setSize($sizeX, $sizeY);
		$this->background->setValign("center");      
		$this->background->setHalign("center");
		//$this->background->setPosY(0);

//        $this->serverc = new Quad();
//        $this->serverc->setSize(4, 4);
//        $this->serverc->setValign("top");
//        $this->serverc->setHalign("left");
//        $this->serverc->setPosY($this->posY + 0.5);
//        $this->serverc->setPosX($this->posX - 24);


        $this->mx = new Quad();
		$this->mx->setImage('http://mlepp.klaversma.eu/images/mx.png', true);
		$this->mx->setImageFocus('http://mlepp.klaversma.eu/images/mx_hover.png', true);
		$this->mx->setAction($this->callback('onMxClick'));
		$this->mx->setSize(16, 8);
		$this->mx->setPosition($this->posX +($sizeX /2)-4, $this->posY+6);
		$this->mx->setValign("top");
		$this->mx->setHalign("center");
	
        $this->showinfo = new Label();
		$this->showinfo->setPosition($this->posX + ($sizeX / 2)-8, $this->posY + 10);
		$this->showinfo->setSize(10, 3);
		$this->showinfo->setValign("top");
		$this->showinfo->setHalign("right");
		$this->showinfo->setTextSize(0.8);
        
		$this->nowPlaying = new Label();
		$this->nowPlaying->setPosition($this->posX, $this->posY + 1); //3.5
		$this->nowPlaying->setSize(40, 3);
		$this->nowPlaying->setValign("top");
		$this->nowPlaying->setHalign("center");
		$this->nowPlaying->setTextSize(1);

		$this->trackname = new Label();
		$this->trackname->setPosition($this->posX, $this->posY + 4); // 7
		$this->trackname->setSize($sizeX-8, 4);
		$this->trackname->setValign("top");
		$this->trackname->setHalign("center");
		$this->trackname->setTextSize(3);

		$this->trackauthor = new Label();
		$this->trackauthor->setPosition($this->posX, $this->posY + 9); // 10
		$this->trackauthor->setSize($sizeX-20, 3);
		$this->trackauthor->setValign("top");
		$this->trackauthor->setHalign("center");
		$this->trackauthor->setTextSize(1);

	
	}

	function onLoad() {

	}

	function onDraw() {
		$storage = Storage::getInstance();
		$time = new Time();

		$this->clearComponents();

		$this->addComponent($this->background);

		$mlepp = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance();
		$this->addComponent($this->mx);
		

		//$this->addComponent($this->serverc);
		//$minRank = intval($storage->server->ladderServerLimitMin)/1000;
		//$maxRank = intval($storage->server->ladderServerLimitMax)/1000;
		//$ladderLabel = $minRank."-".$maxRank."k";

		$this->showinfo->setText($this->textColor . 'MX info');
		$this->addComponent($this->showinfo);


		$this->nowPlaying->setText($this->textColor . $this->text);
		$this->addComponent($this->nowPlaying);


		$this->trackname->setText($this->textColor . $this->challengeData->name);
		$this->addComponent($this->trackname);

		$this->trackauthor->setText($this->textColor . $this->challengeData->author . '$z' . $this->textColor . ' (' . $time->fromTM($this->challengeData->authorTime) . ')');
		$this->addComponent($this->trackauthor);
	}

	function onResize() {

	}

	function setCountry($text) {
		$this->serverCountry = $text;
		$this->serverc->setImage(Core::$countries . '/' . $this->serverCountry . ".dds", true);
	}

	function onTmxClick($login) {
		call_user_func($this->TMXcallBack, $login);
		//$this->redraw();
	}

	function onMxClick($login) {
		call_user_func($this->MXcallBack, $login);
		//$this->redraw();
	}
	function setChallenge($challenge) {
		$this->challengeData = $challenge;

	}
	function destroy() {
		unset($this->MXcallBack);
		unset($this->TMXcallBack);
		parent::destroy();
	}

}

?>