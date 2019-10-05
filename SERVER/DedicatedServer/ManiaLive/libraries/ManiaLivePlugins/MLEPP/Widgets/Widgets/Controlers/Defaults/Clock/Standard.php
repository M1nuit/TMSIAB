<?php


/**
 * Description of Clock
 *
 * @author Petri Järvisalo
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\Clock;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeController;
use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Event\Dispatcher;

class Standard extends TypeController {

    public function onInit() {
        parent::onInit();
        $this->setVersion(1);
    }

    public function  onLoad() {
        parent::onLoad();
        $this->enableStorageEvents();
        $this->enableDedicatedEvents();
        $this->enableTickerEvent();
    }
	public function onUnload() {
		parent::onUnload();
	}
	
    public function  onReady() {
        parent::onReady();
    }

    public function onPlayerConnect($login, $isSpectator){
        //The parent handles the widget we need to call it
        parent::onPlayerConnect($login, $isSpectator);
        $this->showWidget($login,date("H:i",time()));
    }

    public function onPlayerDisconnect($login){
        //The parent handles the widget we need to call it
        parent::onPlayerDisconnect($login);

    }

    public function onTick() {
        if (time()%60 == 0 ) {
			$time = date("H:i",time());
	        $this->updateWidgetData($time);
	        $this->forceUpdateWidget();
        }

    }

	function destroy()
	{
		parent::destroy();
		gc_collect_cycles();
	}


}
?>
