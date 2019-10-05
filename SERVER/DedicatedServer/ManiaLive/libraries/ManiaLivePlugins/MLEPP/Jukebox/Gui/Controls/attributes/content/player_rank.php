<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\content;

use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\attributes\Text;
use ManiaLivePlugins\MLEPP\LocalRecords\LocalRecords;
use ManiaLivePlugins\MLEPP\Database;

class player_rank extends Text {

	function setText($text) {

        $nbRecords = $this->getNbRecords();
		$numrec = \ManiaLivePlugins\MLEPP\LocalRecords\Config::getInstance()->numrec;
		if($nbRecords==0){
            $this->label->setText("-/-");
            return;

        }elseif($nbRecords > $numrec){
            $nbRecords = $numrec;
        }

        $playerTime = $this->getPlayerTime();

        if($playerTime == 0){
            $this->label->setText("-/".$nbRecords);
            return;
        }

        $q = "SELECT count(*) as rank
        FROM localrecords
        WHERE record_challengeuid = '".$this->uid."'
                AND record_score < ".$playerTime;


        $dbData = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance()->db->query($q);


        if ($data = $dbData->fetchAssoc()) {
            $myRank = $data["rank"] + 1;
            if ($myRank > $numrec) {
                $myRank = "-";
            }
        } else {
            $myRank = "-";
        }


         while($data = $dbData->fetchAssoc()) {
          if($data["record_playerlogin"] == $this->login)
          $myRank = $i;

          $i++;
          }

        $this->label->setText($myRank . "/" .$nbRecords);

    }

    private function getNbRecords() {

        $q = "SELECT count(*) as Nbrank
        FROM localrecords
        WHERE record_challengeuid = '".$this->uid."'";


        $dbData = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance()->db->query($q);

        if ($dbData->recordCount() == 0) {
            return 0;
        }

        if ($data = $dbData->fetchAssoc()) {
            return $data['Nbrank'];
        }
       return 0;

    }

    private function getPlayerTime(){

        $q = "SELECT  record_score
        FROM localrecords
        WHERE record_challengeuid = '".$this->uid."'
            AND record_playerlogin = '".$this->login."'";

        $dbData = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance()->db->query($q);

        if ($dbData->recordCount() == 0) {
            return 0;
        }

        if ($data = $dbData->fetchAssoc()) {
            return $data['record_score'];
        }
        return 0;


    }


}

?>
