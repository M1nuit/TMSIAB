<?php


/**
 * Description of Sidebar
 *
 * @author Petri Järvisalo
 */

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\Defaults\WhoIam;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeController;
use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Event\Dispatcher;
use ManiaLive\DedicatedApi\Connection;

class Standard extends TypeController {

    public function onInit() {
        parent::onInit();
        $this->setVersion(1);
    }
public function onUnload() {
		parent::onUnload();
	}
    public function onPlayerConnect($login, $isSpectator){

        $player = $this->storage->getPlayerObject($login);
        $server = $this->connection->getDetailedPlayerInfo($this->storage->serverLogin);

		//The parent handles the widget we need to call it
        parent::onPlayerConnect($login, $isSpectator);
        $this->showWidget($login,array("player"=>$player,"server"=>$server,"serverinfo" => $this->storage->server));
    }

    public function onPlayerDisconnect($login){
        //The parent handles the widget we need to call it
        parent::onPlayerDisconnect($login);
    }
	
	function destroy()
	{
		parent::destroy();
		gc_collect_cycles();
	}

    //public function onBeginChallenge($challenge, $warmUp, $matchContinuation) {

    //}
}
?>
