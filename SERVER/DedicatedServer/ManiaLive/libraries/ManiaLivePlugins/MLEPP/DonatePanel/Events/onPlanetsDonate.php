<?php

namespace ManiaLivePlugins\MLEPP\DonatePanel\Events;

class onPlanetsDonate extends \ManiaLive\Event\Event {
	protected $login;
	protected $amount;
	protected $plugin;

	function __construct($login, $amount, $plugin, $description) {
		$this->login = $login;
		$this->amount = $amount;
		$this->plugin = $plugin;
		$this->description = $description;
	}

	function fireDo($listener) {
		call_user_func_array(array($listener, 'onPlanetsDonate'), array($this->login, $this->amount,$this->plugin,$this->description));
	}
}


?>