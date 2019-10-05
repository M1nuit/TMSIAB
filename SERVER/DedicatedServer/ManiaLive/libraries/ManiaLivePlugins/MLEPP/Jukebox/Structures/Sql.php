<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Structures;

class Sql {

	private $select = null;
	private $select2;
	private $from;
	private $where = null;
	private $groupBy = null;
	private $sortBy = array();
	private $limit = null;

	public function getSelect() {
		return $this->select;
	}

	public function setSelect($select) {
		$this->select = $select;
	}

	public function getSelect2() {
		return $this->select2;
	}

	public function setSelect2($select) {
		$this->select2 = $select;
	}

	public function getFrom() {
		return $this->from;
	}

	public function setFrom($from) {
		$this->from = $from;
	}

	public function getWhere() {
		return $this->where;
	}

	public function setWhere($where) {
		$this->where = $where;
	}

	public function getGroupBy() {
		return $this->groupBy;
	}

	public function setGroupBy($groupBy) {
		$this->groupBy = $groupBy;
	}

	public function getSortBy() {
		return $this->sortBy;
	}

	public function setSortBy($sortBy, $sort="Desc") {
		$this->sortBy[$sortBy] = $sort;
	}

	public function getLimit() {
		return $this->limit;
	}

	public function setLimit($limit) {
		$this->limit = $limit;
	}

	public function __toString() {

		$sql = "SELECT";
		$i = 0;
		//First the select
		if (!empty($this->select)) {
			$sql.=" " . $this->select . " ";
		} else {
			$sql.=" * ";
		}

		if (!empty($this->select2)) {
			$sql.=", " . $this->select2 . " ";
		}

		$i = 0;
		//Now the FROM
		$sql.= "FROM " . $this->from;

		if ($this->where != null)
			$sql.=" WHERE " . $this->where;

		if ($this->groupBy != null)
			$sql.=" " . $this->groupBy;

		$i = 0;
		//Now the SORT
		if (!empty($this->sortBy)) {
			$sql.=" ORDER BY ";
			foreach ($this->sortBy as $el => $sort) {
				if ($i != 0)
					$sql.=", ";
				$sql.=" $el $sort";
			}
		}

		//Now Limit
		if ($this->limit != null)
			$sql.=" LIMIT " . $this->limit[0] . ", " . $this->limit[1];

		return $sql;
	}

}

?>
