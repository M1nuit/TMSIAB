<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProfileHandler
 *
 * @author Reaby
 */

namespace ManiaLivePlugins\MLEPP\Core\Structures;

class DepotGroup {
	private $group;

	public function __construct($group = null) {
		if ($group === null) throw new Exception("Error!");
		$this->group = $group;
	}

	public function get($subGroup) {
		return new \ManiaLivePlugins\MLEPP\Core\Depot($this->group, $subGroup);
	}
}

?>
