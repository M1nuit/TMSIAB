<?php

namespace ManiaLivePlugins\MLEPP\RandomMessage;

class Config extends \ManiaLib\Utils\Singleton {
	public $type = 'endChallenge';
	public $delay = 180;
	public $infoname = '$ff3[$o$f00INFO$z$ff3$s]';
	public $infocolor = '$z$ae0$s';
	public $messages = array('All windows can be maximized, and minimized to the taskbar. Like in operating systems, more than one window can be kept open like this.',
		'For more information about $fffMLEPP$ae0, please visit: $fff$lhttp://mlepp.trackmania.nl$l$ae0!',
		'Please don\'t sound your horn throughout the entire track!');

}

?>
