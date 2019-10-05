<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\content;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\tmx_controls;

class tmx_difficulty extends tmx_controls{

    function setText($text){
            $this->label->setText($this->getTmxData("tmx_difficulty"));
        }
}
?>
