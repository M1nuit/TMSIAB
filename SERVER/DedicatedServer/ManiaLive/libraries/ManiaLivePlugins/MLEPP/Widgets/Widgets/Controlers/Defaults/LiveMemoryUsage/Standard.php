<?php


/**
 * Description of Clock
 *
 * @author Petri Järvisalo
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\LiveMemoryUsage;

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
        parent::onPlayerConnect($login, $isSpectator);
    }

    public function onPlayerDisconnect($login){
        //The parent handles the widget we need to call it
        parent::onPlayerDisconnect($login);

    }

    public function onTick() {
		$mem = memory_get_usage();
		$time = number_format((round($mem/1024,1)))." kB";
	    $this->updateWidgetData($time);
	    $this->forceUpdateWidget();
    }

	function destroy()
	{
		parent::destroy();
		gc_collect_cycles();
	}


}
?>
