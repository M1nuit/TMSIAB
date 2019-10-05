<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\ChatBackground;

use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeView;


class Standard extends TypeView {

    protected $background;

	public function initializeComponents() {

    }

    public function setSettings($set){
        $this->settings = $set;
		$this->background = new \ManiaLib\Gui\Elements\BgsPlayerCard($this->settings->width, $this->settings->height);
		$this->background->setSubStyle("BgPlayerCardBig");
	    $this->background->setPosX(0);
        $this->background->setPosY(0);
        $this->background->setHalign("left");
		$this->background->setValign("top");
        $this->setPosZ(-100);
    }

    public function onDraw() {
        $this->clearComponents();
        $this->addComponent($this->background);
		$this->addComponent($this->background);

    }
    public function setData($d){

    }

    public function getWidgetSizeX(){
        return ($this->settings->width);
    }

    public function getWidgetSizeY(){
        return ($this->settings->height);
    }

		public function destroy()
	{
		parent::destroy();
	}
}
?>
