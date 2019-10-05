<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\content;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\Text;

class challenge_name extends Text{

    function beforeDraw(){

        if(!$this->currentSearch->getClicks_disabled())
            $this->background->setAction($this->callback('onClicked'));
    }

    function onClicked($login){
        self::$plugin_jb->juke($login, $this->id);
    }

}
?>
