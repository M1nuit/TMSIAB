<?php

namespace ManiaLivePlugins\MLEPP\Karma\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLive\Gui\Windowing\Controls\Pager;
use ManiaLib\Gui\Layouts\Flow;
use ManiaLib\Gui\Elements\Quad;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLivePlugins\MLEPP\Core\Core;

class KarmaWindow extends \ManiaLive\Gui\Windowing\Window {

    private $frame;
    private $line;
    private $background;
    private $label;

    function initializeComponents() {

        //$mlepp = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance();

        if (empty(Core::$widget_background)) {
            $this->background = new \ManiaLib\Gui\Elements\BgsPlayerCard(36, 6);
            $this->background->setSubStyle("BgPlayerCardBig");
        } else {
            $this->background = new Quad();
            $this->background->setImage(Core::$widget_background, true);
        }
		$this->background->setHalign("center");
		$this->background->setValign("center");
		
        //$this->background->setSize();

        $this->frame = new Frame();
		$this->frame->applyLayout(new Flow());
		$this->frame->setSize(26,6);
        $this->frame->setHalign("center");
		$this->frame->setValign("center");
        $this->frame->setPosX(4);
		$this->frame->setPosY(1.5);


        $this->label = new \ManiaLib\Gui\Elements\Label(30, 3);
        $this->label->setText('$s$eeeTrack Popularity');
        $this->label->setPosY(-6);
        $this->label->setTextSize(1.5);
        $this->label->setHalign("center");
		$this->label->setValign("top");
		
    }

    function onLoad() {
        
    }

    function onDraw() {
        $this->clearComponents();
		$this->addComponent($this->background);
        $this->addComponent($this->label);
        $this->addComponent($this->frame); 
    }

    function onResize() {
        //$this->frame->setSize($this->sizeX, $this->sizeY);
        //$this->background->setSize($this->sizeX, $this->sizeY);
    }

    function addItem($item) {
        $this->frame->addComponent($item);
    }

    function clearItems() {
        $this->frame->clearComponents();
    }

    function destroy() {
        parent::destroy();
    }

}

?>