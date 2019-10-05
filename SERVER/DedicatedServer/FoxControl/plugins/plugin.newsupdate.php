<?php
control::RegisterEvent('StartUp', 'news_startup');
control::RegisterEvent('BeginChallenge', 'news_beginchallenge');
control::RegisterEvent('PlayerConnect', 'news_playerconnect');
control::RegisterEvent('EndChallenge', 'news_endchallenge');

function news_startup($control){
$link = 'http://fox-control.de/fc/fnu092.php';
$datei = fopen($link, "rb");
$inhalt = stream_get_contents($datei);
fclose($datei);

 $control->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
 <manialink id="955">
 '.$inhalt.'
 </manialink>', 0, False);
}

function news_beginchallenge($control, $var_1125){
	news_startup($control);
}

function news_playerconnect($control, $nu_playerdata){
	$link = 'http://fox-control.de/fc/fnu092.php';
	$datei = fopen($link, "rb");
	$inhalt = stream_get_contents($datei);
	fclose($datei);

	$control->client->query('SendDisplayManialinkPageToLogin', $nu_playerdata['Login'], '<?xml version="1.0" encoding="UTF-8" ?>
	<manialink id="955">
	'.$inhalt.'
	</manialink>', 0, False);
}
function news_endchallenge($control, $nu_newsend){
	$control->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
	<manialink id="955">
	</manialink>', 0, False);
}

?>