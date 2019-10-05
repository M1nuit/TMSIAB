<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes;

use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\BgsPlayerCard;

class Title extends Text{

   function beforeDraw(){
       // $this->background->setSubStyle(Bgs1InRace::NavButtonBlink); -- disabled because of misfunction in beta
       $this->background->setSubStyle("BgCard");
   }
		public function destroy() {
		parent::destroy();
		gc_collect_cycles();
	}
}
?>
