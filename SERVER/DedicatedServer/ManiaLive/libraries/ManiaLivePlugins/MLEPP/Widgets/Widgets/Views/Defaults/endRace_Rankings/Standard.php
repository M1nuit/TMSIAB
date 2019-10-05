<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\endRace_Rankings;

use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\Widgets\Widgets\VHelpers\ScoreTable;

/**
 * Description of TimeAttack
 *
 * @author De Cramer Oliver
 */
class Standard extends ScoreTable{
	public function initializeComponents() {
		$this->label = "Server Rankings";
	}
}
?>
