<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes;

use ManiaLive\Utilities\Time as MlTime;

class Time extends Text{

   function setText($text){
        $this->label->setText(MlTime::fromTM($text));
    }
	
			public function destroy() {
		parent::destroy();
		gc_collect_cycles();
	}

}
?>
