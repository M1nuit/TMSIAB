<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\BgsPlayerCard;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\Controls;

class Text extends Controls{

    protected $background;
    protected $label;
	private $mlepp;

	function initializeComponents2(){

        // insert background ...
        /*$this->background = new Bgs1InRace($this->getSizeX(), $this->getSizeY());
        $this->background->setSubStyle(Bgs1InRace::NavButton);
	    -- disabled because of black fields in beta */
		$this->mlepp = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance();
		$this->background = new BgsPlayerCard($this->getSizeX(), $this->getSizeY());
        $this->background->setSubStyle("BgPlayerCardBig");

        $this->addComponent($this->background);

        // insert label ...
        $this->label = new Label($this->getSizeX() - 2, $this->getSizeY());
        $this->label->setPosition(($this->getSizeX()/2), 1);
		$this->label->setHalign("center");
        $this->addComponent($this->label);
    }

    function onResize(){
        $this->background->setSize($this->getSizeX(), $this->getSizeY());
        $this->label->setSize($this->getSizeX() - 2, $this->getSizeY());
    }

    function setText($text){
        $this->label->setText($text);
    }

	public function destroy() {
		parent::destroy();
		gc_collect_cycles();
	}
}
?>
