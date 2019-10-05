<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\LocalRecords;

use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\Widgets\Widgets\VHelpers\ScoreTable;

/**
 * Description of TimeAttack
 *
 * @author De Cramer Oliver
 */
class Stunt extends TimeAttack{

    //Ovveride because no need for Formating
    public function formatScore($score){
        return $score;
    }

}
?>
