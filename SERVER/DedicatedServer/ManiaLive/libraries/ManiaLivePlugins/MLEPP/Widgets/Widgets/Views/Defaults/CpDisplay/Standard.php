<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\CpDisplay;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeView;

/**
 * Description of Standard
 *
 * @author Petri Järvisalo
 */
class Standard extends TypeView {

    protected $cp_time;
    public $data;

    public function initializeComponents() {

    }

    public function setSettings($set){
        $this->settings = $set;

        //Title initialisation
        $this->cp_time = new \ManiaLib\Gui\Elements\Label($this->settings->width, 10);
        $this->cp_time->setPosX(-10);
        $this->cp_time->setPosY(0);
        $this->cp_time->setStyle("TextRaceChrono");
        $this->cp_time->setScale(0.6,0.6);
        if (!empty($this->settings->timeout)) {
            $this->setTimeout($this->settings->timeout);
        }
    }

    public function onDraw() {
        $this->clearComponents();
        $this->cp_time->setText($this->data);
        $this->addComponent($this->cp_time);
    }
    public function setData($d){

        $this->data = $d;
    }

    public function getWidgetSizeX(){
        return $this->settings->width;
    }

    public function getWidgetSizeY(){
        return (2);
    }

	public function destroy()
	{
		parent::destroy();
	}
}
?>
