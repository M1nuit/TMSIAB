<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\BestCps_old;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeView;
use ManiaLive\Utilities\Time;
/**
 * Description of Standard
 *
 * @author Petri Järvisalo
 */
class Standard extends TypeView {

    protected $frame;
    public $data;

    public function initializeComponents() {

    }

    public function setSettings($set){
        $this->settings = $set;

        //Title initialisation
        $this->frame = new \ManiaLive\Gui\Windowing\Controls\Frame(280, 10);
        $this->frame->setPosX(0);
        $this->frame->setPosY(0);
    }

    public function onDraw() {
        $this->clearComponents();
                $this->frame->clearComponents();
                $this->frame->applyLayout(new \ManiaLib\Gui\Layouts\Flow());
                $this->frame->setSizeX(280);
        $this->generateFrame();
        $this->addComponent($this->frame);
    }

        public function generateFrame() {
            if (count($this->data) > 0) {
                foreach ($this->data as $id => $data) {
                    if ($data['nickname'] == null) break;
                    $frame = new \ManiaLive\Gui\Windowing\Controls\Frame(40,4);
                    $frame->setSizeX(41.5);
                    $frame->setSizeY(4.5);
                    $bg = new \ManiaLib\Gui\Elements\BgsPlayerCard(40,4);
                    $bg->setSubStyle("BgPlayerCardBig");
					$bg->setHalign('center');
					$bg->setValign('center');
                    $title = new \ManiaLib\Gui\Elements\Label(40,4);
                    $time = new Time();
                    $title->setText('$s$eee'.($id+1).'. $ff5'. $time->FromTM($data['score']).' $z$s$eee'.$data['nickname']);
                    $title->setTextSize(1);
					$title->setSizeX(39);
                    $title->setSizeY(4);
                    //$title->setPosY(0.5);
                    //$title->setPosX(1);
					$title->setHalign('center');
					$title->setValign('center');
                    $frame->addComponent($bg);
                    $frame->addComponent($title);
                    $this->frame->addComponent($frame);
                }
            }
        }
    public function setData($d){

        $this->data = $d;
    }

    public function getWidgetSizeX(){
        return 240;
    }

    public function getWidgetSizeY(){
        return (5);
    }

		public function destroy()
	{
		parent::destroy();
	}
}
?>
