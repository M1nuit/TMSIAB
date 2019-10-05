<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\content;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\tmx_controls;

class tmx_awards extends tmx_controls{
    
    function setText($text){
            $this->label->setText($this->getTmxData("tmx_awards"));
        }
}
?>
