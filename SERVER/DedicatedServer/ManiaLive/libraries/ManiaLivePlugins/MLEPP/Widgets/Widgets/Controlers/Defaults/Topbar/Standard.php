<?php


/**
 * Description of Sidebar
 *
 * @author Petri Järvisalo
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\Topbar;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeController;
use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Event\Dispatcher;


class Standard extends TypeController {


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
