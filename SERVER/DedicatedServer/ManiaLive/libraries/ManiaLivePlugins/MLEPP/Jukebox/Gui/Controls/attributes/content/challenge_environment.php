<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\content;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;
use ManiaLive\Gui\Windowing\Elements\Xml;

class challenge_environment extends \ManiaLive\Gui\Windowing\Control{

    protected $image;

    function initializeComponents(){
        $this->sizeX = $this->getParam(0);
        $this->sizeY = $this->getParam(1);


        // insert label ...
        $this->image = new XML;
        $this->addComponent($this->image);
    }

    function onResize(){
        $this->image->setSize($this->getSizeY(), $this->getSizeY());
    }

   function setText($text){
        $imageUrl = "http://koti.mbnet.fi/reaby/xaseco/images/env/".$text.".png";
    $xml = "<quad sizen='3 3' posn='0 0 2' image='$imageUrl' />";
        $this->image->setContent($xml);
    }
}
?>
