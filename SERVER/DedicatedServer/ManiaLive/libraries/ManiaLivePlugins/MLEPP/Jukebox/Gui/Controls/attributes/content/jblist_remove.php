<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\content;

use ManiaLib\Gui\Elements\Icons64x64_1;

use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\Controls;

/**
 * Description of jblist_remove
 *
 * @author De Cramer Oliver
 */
class jblist_remove extends Controls{

    protected $cross;

    public function initializeComponents2(){		
        // insert background ...
        $this->cross = new Icons64x64_1($this->getSizeY());
		$this->cross->setSubStyle("Close");

    }

	function beforeDraw(){
		$this->clearComponents();
		if (self::$plugin_jb->mlepp->AdminGroup->hasPermission($this->getWindow()->getRecipient(),'jbdrop')){
			$this->cross->setAction($this->callback("OnClick"));
			$this->addComponent($this->cross);
		}
	}

    public function setText($text){
		//Do nothing
    }

	public function OnClick($login){
		self::$plugin_jb->dropFromJukebox($login, $this->id);
	}
}
?>
