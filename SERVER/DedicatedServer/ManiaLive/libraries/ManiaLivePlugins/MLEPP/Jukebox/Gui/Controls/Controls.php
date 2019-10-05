<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;

abstract class Controls extends \ManiaLive\Gui\Windowing\Control {

    static public $plugin_jb;
    protected $id;
    protected $login;
    protected $currentSearch;
    protected $uid;

    function initializeComponents(){

        $this->sizeX = $this->getParam(0);
        $this->sizeY = $this->getParam(1);
        $this->id = $this->getParam(2);
        $this->login = $this->getParam(3);
        $this->currentSearch = $this->getParam(4);
        $this->uid = $this->getParam(5);

        $this->initializeComponents2();
    }

	abstract function setText($text);

	public function destroy() {
		self::$plugin_jb = null;
		parent::destroy();
		gc_collect_cycles();
	}
}
?>
