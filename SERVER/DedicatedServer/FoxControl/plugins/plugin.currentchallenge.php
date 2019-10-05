<?php
//* plugin.currentchallenge.php - A Window with the current Challenge
//* Version:   0.9.0
//* Coded by:  cyrilw && libero6
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('StartUp', 'currchallenge_startup');
control::RegisterEvent('BeginChallenge', 'currchallenge_beginchallenge');
control::RegisterEvent('PlayerConnect', 'currchallenge_playerconnect');
control::RegisterEvent('ManialinkPageAnswer', 'currchallenge_manialinkpageanswer');


function currchallenge_startup($control){ //First Start

//Author time
$control->client->query('GetCurrentChallengeInfo');
$currchallenge = $control->client->getResponse();
$currchallenge_authortime = $currchallenge['AuthorTime'];
$currchallenge_minutes = floor($currchallenge_authortime/(1000*60));
$currchallenge_seconds = floor(($currchallenge_authortime - $currchallenge_minutes*60*1000)/1000);
$currchallenge_hseconds = substr($currchallenge_authortime, strlen($currchallenge_authortime)-3, 2);
$currchallenge_authortime = sprintf('%02d:%02d.%02d', $currchallenge_minutes, $currchallenge_seconds, $currchallenge_hseconds);

$control->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
<manialink id="4">
<timeout>0</timeout>
	<quad posn="42.5 48.01 1" sizen="40 9" style="Bgs1InRace" substyle="NavButton" action="4"/>
	<label posn="43 47.3 2" textsize="1" text="$oCurrent Challenge:"/>
	<quad posn="43.5 45.3 2" sizen="2 2" style="Icons128x128_1" substyle="Challenge"/>
	<label posn="45.5 45.3 2" textsize="1" sizen="15 2" text="'.$currchallenge['Name'].'"/>
	<quad posn="43.5 43.3 2" sizen="2 2" style="Icons128x128_1" substyle="ChallengeAuthor"/>
	<label posn="45.5 43.3 2" textsize="1" sizen="15 2" text="'.$currchallenge['Author'].'"/>
	<quad posn="43.5 41.3 2" sizen="2 2" style="Icons64x64_1" substyle="RestartRace"/>
	<label posn="45.5 41.3 2" textsize="1" text="'.$currchallenge_authortime.'"/>
</manialink>
', 0, False);
}

function currchallenge_beginchallenge($control){ //Update window

//Author time
$control->client->query('GetCurrentChallengeInfo');
$currchallenge = $control->client->getResponse();
$currchallenge_authortime = $currchallenge['AuthorTime'];
$currchallenge_minutes = floor($currchallenge_authortime/(1000*60));
$currchallenge_seconds = floor(($currchallenge_authortime - $currchallenge_minutes*60*1000)/1000);
$currchallenge_hseconds = substr($currchallenge_authortime, strlen($currchallenge_authortime)-3, 2);
$currchallenge_authortime = sprintf('%02d:%02d.%02d', $currchallenge_minutes, $currchallenge_seconds, $currchallenge_hseconds);

$control->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
<manialink id="4">
<timeout>0</timeout>
	<quad posn="42.5 48.01 1" sizen="40 9" style="Bgs1InRace" substyle="NavButton" action="4"/>
	<label posn="43 47.3 2" textsize="1" text="$oCurrent Challenge:"/>
	<quad posn="43.5 45.3 2" sizen="2 2" style="Icons128x128_1" substyle="Challenge"/>
	<label posn="45.5 45.3 2" textsize="1" sizen="15 2" text="'.$currchallenge['Name'].'"/>
	<quad posn="43.5 43.3 2" sizen="2 2" style="Icons128x128_1" substyle="ChallengeAuthor"/>
	<label posn="45.5 43.3 2" textsize="1" sizen="15 2" text="'.$currchallenge['Author'].'"/>
	<quad posn="43.5 41.3 2" sizen="2 2" style="Icons64x64_1" substyle="RestartRace"/>
	<label posn="45.5 41.3 2" textsize="1" text="'.$currchallenge_authortime.'"/>
</manialink>', 0, False);
}

function currchallenge_playerconnect($control, $connectedplayer){ //Window when a player connect

//Author time
$control->client->query('GetCurrentChallengeInfo');
$currchallenge = $control->client->getResponse();
$currchallenge_authortime = $currchallenge['AuthorTime'];
$currchallenge_minutes = floor($currchallenge_authortime/(1000*60));
$currchallenge_seconds = floor(($currchallenge_authortime - $currchallenge_minutes*60*1000)/1000);
$currchallenge_hseconds = substr($currchallenge_authortime, strlen($currchallenge_authortime)-3, 2);
$currchallenge_authortime = sprintf('%02d:%02d.%02d', $currchallenge_minutes, $currchallenge_seconds, $currchallenge_hseconds);

$control->client->query('SendDisplayManialinkPageToLogin', $connectedplayer['Login'], '<?xml version="1.0" encoding="UTF-8" ?>
<manialink id="4">
<timeout>0</timeout>
	<quad posn="42.5 48.01 1" sizen="40 9" style="Bgs1InRace" substyle="NavButton" action="4"/>
	<label posn="43 47.3 2" textsize="1" text="$oCurrent Challenge:"/>
	<quad posn="43.5 45.3 2" sizen="2 2" style="Icons128x128_1" substyle="Challenge"/>
	<label posn="45.5 45.3 2" textsize="1" sizen="15 2" text="'.$currchallenge['Name'].'"/>
	<quad posn="43.5 43.3 2" sizen="2 2" style="Icons128x128_1" substyle="ChallengeAuthor"/>
	<label posn="45.5 43.3 2" textsize="1" sizen="15 2" text="'.$currchallenge['Author'].'"/>
	<quad posn="43.5 41.3 2" sizen="2 2" style="Icons64x64_1" substyle="RestartRace"/>
	<label posn="45.5 41.3 2" textsize="1" text="'.$currchallenge_authortime.'"/>
</manialink>', 0, False);
}

function currchallenge_manialinkpageanswer($control, $ManialinkPageAnswer){ //Big window when clicked

//Author time
$control->client->query('GetCurrentChallengeInfo');
$currchallenge = $control->client->getResponse();
$currchallenge_authortime = $currchallenge['AuthorTime'];
$currchallenge_minutes = floor($currchallenge_authortime/(1000*60));
$currchallenge_seconds = floor(($currchallenge_authortime - $currchallenge_minutes*60*1000)/1000);
$currchallenge_hseconds = substr($currchallenge_authortime, strlen($currchallenge_authortime)-3, 2);
$currchallenge_authortime = sprintf('%02d:%02d.%02d', $currchallenge_minutes, $currchallenge_seconds, $currchallenge_hseconds);

if($ManialinkPageAnswer[2]=='4'){
$control->client->query('GetNextChallengeInfo');
$nextchallenge = $control->client->getResponse();
$nextchallenge_authortime = $nextchallenge['AuthorTime'];
//Authortime
$nextchallenge_minutes = floor($nextchallenge_authortime/(1000*60));
$nextchallenge_seconds = floor(($nextchallenge_authortime - $nextchallenge_minutes*60*1000)/1000);
$nextchallenge_hseconds = substr($nextchallenge_authortime, strlen($nextchallenge_authortime)-3, 2);
$nextchallenge_authortime = sprintf('%02d:%02d.%02d', $nextchallenge_minutes, $nextchallenge_seconds, $nextchallenge_hseconds);
$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
<manialink id="4">
<timeout>0</timeout> 
	<quad posn="22.5 48.05 1" sizen="60 9" style="Bgs1InRace" substyle="NavButton" action="5"/>
	<label posn="23 47.3 2" textsize="1" text="$oCurrent Challenge:"/>
	<quad posn="23.5 45.3 2" sizen="2 2" style="Icons128x128_1" substyle="Challenge"/>
	<label posn="25.5 45.3 2" textsize="1" sizen="15 2" text="'.$currchallenge['Name'].'"/>
	<quad posn="23.5 43.3 2" sizen="2 2" style="Icons128x128_1" substyle="ChallengeAuthor"/>
	<label posn="25.5 43.3 2" textsize="1" sizen="15 2" text="'.$currchallenge['Author'].'"/>
	<quad posn="23.5 41.3 2" sizen="2 2" style="Icons64x64_1" substyle="RestartRace"/>
	<label posn="25.5 41.3 2" textsize="1" text="'.$currchallenge_authortime.'"/>
	<label posn="43 47.3 2" textsize="1" text="$oNext Challenge:"/>
	<quad posn="43.5 45.3 2" sizen="2 2" style="Icons128x128_1" substyle="Challenge"/>
	<label posn="45.5 45.3 2" textsize="1" sizen="15 2" text="'.$nextchallenge['Name'].'"/>
	<quad posn="43.5 43.3 2" sizen="2 2" style="Icons128x128_1" substyle="ChallengeAuthor"/>
	<label posn="45.5 43.3 2" textsize="1" sizen="15 2" text="'.$nextchallenge['Author'].'"/>
	<quad posn="43.5 41.3 2" sizen="2 2" style="Icons64x64_1" substyle="RestartRace"/>
	<label posn="45.5 41.3 2" textsize="1" text="'.$nextchallenge_authortime.'"/>
</manialink>', 0, False);
}
elseif($ManialinkPageAnswer[2]=='5'){ //Smallwindow
$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
<manialink id="4">
<timeout>0</timeout>
	<quad posn="42.5 48.01 1" sizen="40 9" style="Bgs1InRace" substyle="NavButton" action="4"/>
	<label posn="43 47.3 2" textsize="1" text="$oCurrent Challenge:"/>
	<quad posn="43.5 45.3 2" sizen="2 2" style="Icons128x128_1" substyle="Challenge"/>
	<label posn="45.5 45.3 2" textsize="1" sizen="15 2" text="'.$currchallenge['Name'].'"/>
	<quad posn="43.5 43.3 2" sizen="2 2" style="Icons128x128_1" substyle="ChallengeAuthor"/>
	<label posn="45.5 43.3 2" textsize="1" sizen="15 2" text="'.$currchallenge['Author'].'"/>
	<quad posn="43.5 41.3 2" sizen="2 2" style="Icons64x64_1" substyle="RestartRace"/>
	<label posn="45.5 41.3 2" textsize="1" text="'.$currchallenge_authortime.'"/>
</manialink>', 0, False);
}
}

?>