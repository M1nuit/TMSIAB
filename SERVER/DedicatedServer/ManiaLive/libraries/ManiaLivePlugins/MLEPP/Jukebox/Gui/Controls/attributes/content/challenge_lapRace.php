<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\content;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\Text;

class challenge_lapRace extends Text{
    
    function setText($text){
        $this->label->setText($this->BoolToString($text));
    }

    private function BoolToString($string){
        if (strtoupper($string)=="FALSE" || $string=="0" || strtoupper($string)=="NO" || empty($string) || $string == false){
                return "false";
        }
        return "true";
    }
}
?>
