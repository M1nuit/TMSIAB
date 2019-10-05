<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;

class Cell extends \ManiaLive\Gui\Windowing\Control {

    private $cell;
    protected $highlight;
    protected $type;

    function initializeComponents() {
        $this->sizeX = $this->getParam(0);
        $this->sizeY = $this->getParam(1);
        $this->type = $this->getParam(2);
        $id = $this->getParam(3);
        $login = $this->getParam(4);
        $window = $this->getParam(5);
        $uid = $this->getParam(6);

        $cellName = "ManiaLivePlugins\\MLEPP\\Jukebox\Gui\\Controls\\attributes\\" . $this->type;

        $this->cell = new $cellName($this->sizeX, $this->sizeY, $id, $login, $window, $uid);
        $this->addComponent($this->cell);
    }

    function setText($text) {
        $this->cell->setText($text);
    }
	
		public function destroy() {
		parent::destroy();
		gc_collect_cycles();
	}

}