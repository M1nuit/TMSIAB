<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\Clock;

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
    public $label = "Clock";

    public function initializeComponents() {

    }

    public function setSettings($set){
        $this->settings = $set;

        //Title initialisation
        /*$this->bg_title = new \ManiaLib\Gui\Elements\Bgs1($this->settings->width, 2);
        $this->bg_title->setSubStyle("NavButton");
        $this->bg_title->setPosX(0);
        $this->bg_title->setPosY(0);
                 *
                 */

        $this->label_title = new \ManiaLib\Gui\Elements\Label($this->settings->width - 2, 2);
        $this->label_title->setPosX(($this->settings->width)/2);
        $this->label_title->setPosY(0.2);
        $this->label_title->setHalign("center");
        //$this->label_title->setTextSize(0.7);
		$this->label_title->setStyle("TextRaceChat");
		$this->label_title->setText('$o$s'.$this->data);
		$this->label_title->setTextColor('fff');


    /*  //BackGround initialisation
        $this->bg_clock = new \ManiaLib\Gui\Elements\Bgs1($this->settings->width, 2);
        $this->bg_clock->setSubStyle("NavButton");
        $this->bg_clock->setPosX(0);
        $this->bg_clock->setPosY(2.1);
        $this->bg_clock->setSizeY(2.2);
        $this->bg_clock->setSizeX($this->settings->width);

        $this->label_clock = new \ManiaLib\Gui\Elements\Label($this->settings->width - 2, 2);
        $this->label_clock->setPosX(($this->settings->width)/2);
        $this->label_clock->setPosY(2.4);
        $this->label_clock->setHalign("center");
        $this->label_clock->setText($this->data);
        $this->label_clock->setTextColor("111");
        $this->label_clock->setTextSize(1); */

    }

    public function onDraw() {
        $this->clearComponents();

        //$this->addComponent($this->bg_title);
        $this->label_title->setText($this->data);
        $this->addComponent($this->label_title);
        /*$this->addComponent($this->bg_clock);
        $this->label_clock->setText($this->data);
        $this->addComponent($this->label_clock);*/

    }
    public function setData($d){
        $this->data = $d;
    }

    public function getWidgetSizeX(){
        return $this->settings->width;
    }

    public function getWidgetSizeY(){
        return (4.3);
    }

	public function destroy()
	{
		unset($this->data);
		parent::destroy();
	}
}
?>
