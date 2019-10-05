<?php


/**
 * Description of Sidebar
 *
 * @author Petri Järvisalo
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\endRace_Rankings;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeController;
use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Event\Dispatcher;
use ManiaLive\DedicatedApi\Connection;

class Standard extends TypeController {
		
	public function onInit() {
		parent::onInit();
        $this->setVersion(2);
		$this->setWhenToShow(2);
	}

	public function  onLoad() {
		parent::onLoad();
		$this->enableDedicatedEvents();
	}

	public function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge){

		print_r($this->callPublicMethod("MLEPP\Rankings", "getRank"));
		/**
		 * @todo put the data in $this->data
		 */
		parent::onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge);
	}
}
?>
