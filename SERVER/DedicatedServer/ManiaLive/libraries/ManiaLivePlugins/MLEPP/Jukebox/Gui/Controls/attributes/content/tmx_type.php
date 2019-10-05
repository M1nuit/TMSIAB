<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\content;

class tmx_type extends tmx_controls{

    function setText($text){
            $this->label->setText($this->getTmxData("tmx_type"));
        }
}
?>
