<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;

use ManiaLivePlugins\MLEPP\Jukebox\Structures\multiQuery;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows\trackList;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\Text;

class tmx_controls extends Text{

    protected function getTmxData($dataName){
        $sql = new multiQuery();

        $sql->mysql="SELECT $dataName FROM tmxdata t, challenges c WHERE tmx_trackuid = challenge_uid AND challenge_id=".$this->id;
        $sql->sqlite="SELECT $dataName FROM tmxdata t, challenges c WHERE tmx_trackuid = challenge_uid AND challenge_id=".$this->id;

        $response = self::$plugin_jb->query($sql);

        if($response->recordCount()>0){
            $resu = $response->fetchAssoc();
            return $resu[$dataName];
        }else
            return "no info";
    }

}
?>
