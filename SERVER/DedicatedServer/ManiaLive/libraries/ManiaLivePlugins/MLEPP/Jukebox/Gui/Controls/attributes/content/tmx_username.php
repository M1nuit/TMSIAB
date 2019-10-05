<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\content;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\Time;

class tmx_username extends tmx_controls{

    function setText($text){
            $this->label->setText($this->getTmxData("tmx_usernam"));
        }
}
?>
