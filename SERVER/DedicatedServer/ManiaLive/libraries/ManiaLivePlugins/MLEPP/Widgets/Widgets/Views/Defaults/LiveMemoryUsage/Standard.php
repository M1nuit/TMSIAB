<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\LiveMemoryUsage;

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
    public $label = "LiveMemoryUsage";

    public function initializeComponents() {

    }

    public function setSettings($set){
        $this->settings = $set;
		$this->label_title = new \ManiaLib\Gui\Elements\Label($this->settings->width - 2, 2);
        $this->label_title->setPosX(($this->settings->width)/2);
        $this->label_title->setPosY(0.2);
        $this->label_title->setHalign("center");
        $this->label_title->setTextSize(1);
		//$this->label_title->setStyle("TextTitle3");
        $this->label_title->setText('$fff$s'.$this->data);

    }

    public function onDraw() {
        $this->clearComponents();
	    $this->label_title->setText($this->data);
        $this->addComponent($this->label_title);
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
