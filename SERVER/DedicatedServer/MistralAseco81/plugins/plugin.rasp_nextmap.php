<?php
Aseco::addChatCommand('nextmap', 'Shows the next challenge');

function chat_nextmap(&$aseco, &$command) {
	global $jukebox;
	if (sizeof($jukebox) > 0) {
		$jbtemp = $jukebox;
		$nextmap = array_shift($jbtemp);
		$message = '{#server}> {#welcome}The next map is {#highlite}' . stripcolors($nextmap['Name']) . " (" .  $nextmap['Environnement'] . ")";
		$message = $aseco->formatColors($message);
		$aseco->addCall('ChatSendServerMessageToLogin', array($message, $command['author']->login));
	} else {
		$next = get_nexttrack($aseco);
		$message = '{#server}> {#welcome}The next map is {#highlite}' . stripcolors($next->name) . " (" .  $next->environment . ")";
		$message = $aseco->formatColors($message);
		$aseco->addCall('ChatSendServerMessageToLogin', array($message, $command['author']->login));
	}
}


?>
