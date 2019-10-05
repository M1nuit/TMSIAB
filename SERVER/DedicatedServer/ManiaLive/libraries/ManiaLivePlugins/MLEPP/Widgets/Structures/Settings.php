<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Structures;

class Settings {

		public $setting = array();

	public function setSetting($name, $value) {
		$this->setting[$name] = $value;
	}

	public function __get($name) {
		if (isset($this->setting[$name]))
			return $this->setting[$name];
		else
			return null;
	}

}

?>
