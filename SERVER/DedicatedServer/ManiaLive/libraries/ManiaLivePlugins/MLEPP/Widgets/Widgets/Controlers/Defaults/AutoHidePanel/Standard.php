<?php


/**
 * Description of Sidebar
 *
 * @author Petri Järvisalo
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\AutoHidePanel;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeController;
use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Event\Dispatcher;


class Standard extends TypeController {

    static public $AutoHidewidgets;

    public function onInit() {
        parent::onInit();
        $this->setVersion(1);
        $this->setWhenToShow(1);
    }
	public function onUnload() {
		parent::onUnload();
	}

}
?>
