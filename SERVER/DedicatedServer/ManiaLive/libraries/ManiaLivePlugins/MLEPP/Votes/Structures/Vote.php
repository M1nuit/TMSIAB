<?php

namespace ManiaLivePlugins\MLEPP\Votes\Structures;

use ManiaLive\DedicatedApi\Connection;
use ManiaLivePlugins\MLEPP\Votes\Votes;

class Vote {

	public $yes;
	public $no;
	public $command;
	public $parameter;
	public $vote;
	public $timeout;
	public $starter;

	public function pass() {
		$conn = Connection::getInstance();
		$command = $this->command;
		if ($command != NULL || $command != "") {
			$conn->$command();
		}
		$this->reset();
	}

	public function deny() {
		$this->reset();
	}

	public function reset() {
		$this->yes = NULL;
		$this->no = NULL;
		$this->command = NULL;
		$this->parameter = NULL;
		$this->vote = NULL;
		$this->timeout = NULL;
		$this->starter = NULL;
	}

}

?>