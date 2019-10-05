<?php

namespace ManiaLivePlugins\MLEPP\Database;

use ManiaLive\Utilities\Console;

class mleppDatabase {

	private $db;

	function __construct($databaseConnection) {
		$this->db = $databaseConnection;
		Console::printLn('Connection succesfully established !');
	}

	public function query($query) {
		return $this->db->query($query);
	}

	public function quote($str) {
		return $this->db->quote($str);
	}

	public function execute($query) {
		return $this->db->execute($query);
	}

	public function affectedRows() {
		return $this->db->affectedRows();
	}

	public function tableExists($table) {
		return $this->db->tableExists($table);
	}

	public function setCharset($charset) {
		return $this->db->setCharset($charset);
	}

	public function getDatabaseVersion($table, $fromPlugin = null) {
		$g = "SELECT * FROM `databaseversion` WHERE `database_table` = " . $this->quote($table) . ";";
		$query = $this->query($g);

		if ($query->recordCount() == 0) {
			return false;
		} else {
			$record = $query->fetchStdObject();
			return $record->database_version;
		}
	}

	public function setDatabaseVersion($table, $version) {
		$g = "SELECT * FROM `databaseversion` WHERE `database_table` = " . $this->quote($table) . ";";
		$query = $this->query($g);

		if ($query->recordCount() == 0) {
			$q = "INSERT INTO `databaseversion` (`database_table`,
								 `database_version`
								 ) VALUES (
								 " . $this->quote($table) . ",
								 " . $this->quote($version) . "
								 )";
			$this->query($q);
		} else {
			$q = "UPDATE
			`databaseversion`
			SET
			`database_version` = " . $this->quote($version) . "
			WHERE
			`database_table` = " . $this->quote($table) . ";";
			$this->query($q);
		}
		Console::printLn("set new database version: table $table -> version $version.");
	}

}

?>