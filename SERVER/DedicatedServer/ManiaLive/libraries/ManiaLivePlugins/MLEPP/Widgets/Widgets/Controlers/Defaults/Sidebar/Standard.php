<?php


/**
 * Description of Sidebar
 *
 * @author Petri Järvisalo
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\Sidebar;

use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Event\Dispatcher;

class Standard extends \ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace {

    public function onInit() {
        parent::onInit();
        $this->setVersion(1);
        $this->setWhenToShow(1);
    }
	public function onUnload() {
		parent::onUnload();
	}
	
		function destroy()
	{
		parent::destroy();
		gc_collect_cycles();
	}
}
?>
