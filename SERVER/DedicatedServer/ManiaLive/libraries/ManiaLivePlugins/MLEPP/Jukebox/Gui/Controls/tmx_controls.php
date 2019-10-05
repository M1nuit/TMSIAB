<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;

use ManiaLivePlugins\MLEPP\Database\Structures\multiQuery;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows\trackList;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\Text;

class tmx_controls extends Text{

    static private $TmxData = array();

    protected function getTmxData($dataName){
        if(!isset($this->TmxData["challenge_id"]) || $this->TmxData["challenge_id"] != $this->id){
            $this->getData();
        }

        if(isset($this->TmxData[$dataName]))
            return $this->TmxData[$dataName];
        else
            return "No Info";
    }

    private function getData(){
        $sql="SELECT t.* FROM tmxdata t, challenges c WHERE tmx_trackuid = challenge_uid AND challenge_id=".$this->id;
        $response = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance()->db->query($sql);

        if($response->recordCount()>0){
            $this->TmxData = $response->fetchAssoc();
            $this->TmxData["challenge_id"] =  $this->id;
        }else
           $this->TmxData = array();
    }

	public function destroy() {
		parent::destroy();
		gc_collect_cycles();
	}

}
?>
