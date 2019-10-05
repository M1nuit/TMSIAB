<?php


namespace ManiaLivePlugins\MLEPP\Jukebox;

use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Connection;

use ManiaLivePlugins\MLEPP\Jukebox\Structures\multiQuery;
use ManiaLivePlugins\MLEPP\Jukebox\Structures\Sql;
use ManiaLivePlugins\MLEPP\Jukebox\Structures\SearchHandler;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows\trackList;

class DirectSearch extends SearchHandler{

    public function __construct($jbPlugin) {
        $this->jbPlugin = $jbPlugin;
    }

    public function tracklist($login, $param1, $param2){

        $stor = Storage::getInstance();

        $loginObj = Storage::GetInstance()->getPlayerObject($login);
        $window = trackList::Create($login);
        $window->setSize(210, 100);

        $window->clearRecords();


        $serverChallenges = $stor->challenges;

        $i=0;

        foreach($serverChallenges as $challenge) {

            $challenge = (array)$challenge;

            foreach($challenge as $name => $val){
                $track["challenge_".$name] = $val;
            }
            $track["challenge_id"] = $i;

            $window->addRecord($track);

            $this->trackList[$login][$i] = $track;

            $i++;
        }

        //To set uplater
        $window->setColumns($this->columns["list_default"]);

        $window->centerOnScreen();
        $window->show();
    }

    
    public function getChallangeFromNum($id, $login){
        if(isset($this->trackList[$login][$id]))
            return $this->trackList[$login][$id];
        else
            return false;
    }

    public function playerDisconnected($login){
        unset ($this->trackList[$login]);
    }

}
?>
