<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\LocalRecords;

use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\Widgets\Widgets\VHelpers\ScoreTable;

/**
 * Description of TimeAttack
 *
 * @author De Cramer Oliver
 */
class TimeAttack extends ScoreTable{


    public function initializeComponents() {
        $this->label = "Local Records";
    }
	
		public function destroy()
	{
		parent::destroy();
	}

}
?>
