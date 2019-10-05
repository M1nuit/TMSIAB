<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Structures;

use ManiaLive\Gui\Windowing\Window;
/**
 *
 * @author De Cramer Oliver
 */
abstract class TypeView extends Window{

    protected $settings;
    static public $pluginWidgets;
    
    public function setSettings($setting){
        $this->settings = $setting;
    }

    abstract public function getWidgetSizeX();

    abstract public function getWidgetSizeY();

		public function destroy()
	{
		parent::destroy();
	}
}
?>
