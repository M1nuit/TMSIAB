<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\content;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\tmx_controls;


class tmx_trackname extends tmx_controls{

    function setText($text){
        $this->label->setText($this->getTmxData("tmx_trackname"));
    }
}
?>
