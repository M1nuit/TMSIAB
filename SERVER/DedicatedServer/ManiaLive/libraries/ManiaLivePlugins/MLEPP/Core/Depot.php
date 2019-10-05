<?php

/**
 * Description of Depot
 *
 * @author Reaby
 */

namespace ManiaLivePlugins\MLEPP\Core;

class Depot {

	private $group;
	private $subGroup;
	private $db;

	function __construct($group, $subGroup) {
		$this->group = $group;
		$this->subGroup = $subGroup;
		$this->db = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance()->db;
	}

	public function __get($param) {
		$q = "SELECT * FROM `depot` where `depot_group` = ".$this->db->quote($this->group)." and `depot_subGroup` = ".$this->db->quote($this->subGroup)." and `depot_label` = ".$this->db->quote($param).";";
		$query = $this->db->query($q);
		if ($query->recordCount() == 0) return null;

		$data = $query->fetchStdObject();
		$unser = unserialize($data->depot_value);
		return $unser;
	}

	public function __set($param, $value) {
		$q = "SELECT * FROM `depot` where `depot_group` = ".$this->db->quote($this->group)." and `depot_subGroup` = ".$this->db->quote($this->subGroup)." and `depot_label` = ".$this->db->quote($param).";";
		$query = $this->db->query($q);
		$value = serialize($value);

		if ($query->recordCount() == 0) {
			$q = "INSERT INTO `depot` (`depot_group`,
                                         `depot_subGroup`,
                                         `depot_label`,
			                             `depot_value`)
								VALUES (" . $this->db->quote($this->group) . ",
                                        " . $this->db->quote($this->subGroup). ",
										" . $this->db->quote($param). ",
										" . $this->db->quote($value). ");";
			$query = $this->db->query($q);
		} else {
			$q = "UPDATE `depot` SET `depot_value` = ".$this->db->quote($value)."  where `depot_group` = ".$this->db->quote($this->group)." and `depot_subGroup` = ".$this->db->quote($this->subGroup)." and `depot_label` = ".$this->db->quote($param).";";
			$query = $this->db->query($q);
		}
	}

}

?>
