<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\Topbar;

use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeView;
/**
 * Description of TimeAttack
 *
 * @author Petri Järvisalo
 */
class Standard extends TypeView {

    protected $bg_title;
    protected $label_title;

    protected $bg_clock;
    protected $label_clock;
    public $data;

    public function initializeComponents() {

    }

    public function setSettings($set){
        $this->settings = $set;
		$mlepp = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance();
        //Title initialisation

		if ($mlepp->gameVersion->name == "ManiaPlanet") {
				$this->bg_title = new \ManiaLib\Gui\Elements\Bgs1(128, 10);
				$this->bg_title->setSubStyle("BgTitleGlow");
		}
		if ($mlepp->gameVersion->name == "TmForever") {
				$this->bg_title = new \ManiaLib\Gui\Elements\Bgs1InRace(128, 10);
				$this->bg_title->setSubStyle("NavButton");
		}
        $this->bg_title->setPosX(0);
        $this->bg_title->setPosY(0);
        $this->bg_title->setHalign("center");
        $this->setPosZ(-100);
    }

    public function onDraw() {
        $this->clearComponents();

        $this->addComponent($this->bg_title);

    }
    public function setData($d){
        $this->data = $d;
    }

    public function getWidgetSizeX(){
        return $this->settings->width;
    }

    public function getWidgetSizeY(){
        return (20);
    }

		public function destroy()
	{
		parent::destroy();
	}
}
?>
