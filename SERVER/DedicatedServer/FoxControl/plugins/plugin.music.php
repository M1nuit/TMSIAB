<?php
//* plugin.music.php - Admin Chat Commands
//* Version:   0.9.0
//* Coded by:  matrix142
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('PlayerConnect', 'music_playerConnect');
control::RegisterEvent('StartUp', 'music_startUp');
control::RegisterEvent('BeginChallenge', 'music_beginChallenge');
control::RegisterEvent('EndChallenge', 'music_endChallenge');
control::RegisterEvent('ManialinkPageAnswer', 'music_manialinkPageAnswer');

control::RegisterEvent('Chat', 'music_chat');

//Global $music_mlcode and create the music Panel
global $music_mlcode;
$music_mlcode = '<?xml version="1.0" encoding="UTF-8" ?>
<manialink id="80000">
<quad posn="57.5 -25.75 0" sizen="15 3" halign="center" style="Bgs1InRace" action="80004" substyle="NavButtonBlink"/>
<quad posn="62.7 -26.25 1" sizen="2.2 2.2" halign="center" style="Icons64x64_1" action="80000" substyle="ClipPlay" />
<quad posn="60.2 -26.25 1" sizen="2.2 2.2" halign="center" style="Icons64x64_1" action="80001" substyle="ClipPause" />
<quad posn="57.7 -26.25 1" sizen="2.2 2.2" halign="center" style="Icons64x64_1" action="80002" substyle="ClipRewind" />
<label posn="52.5 -26.5 1" scale="0.7" text="$zMusic" />
<quad posn="51.7 -26.25 1" sizen="2.5 2.5" halign="center" style="Icons64x64_1" substyle="Music" />
</manialink>';

function manialinkPlayer ($control){
	global $db, $music_mlcode;
	$control->client->query('GetPlayerList', 300, 0);
	$ap_player_list = $control->client->getResponse();
	$ap_curr_pid = 0;
	while(isset($ap_player_list[$ap_curr_pid])){
		$ap_curr_player = $ap_player_list[$ap_curr_pid];
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".trim($ap_curr_player['Login'])."'";
		$mysql = mysqli_query($db, $sql);
		if($mysql->fetch_object()){
			$control->client->query('SendDisplayManialinkPageToLogin', $ap_curr_player['Login'], $music_mlcode, 0, False);
		}
		$ap_curr_pid++;
	}
}

function music_playerConnect ($control, $connectedplayer){
	global $db, $music_mlcode;
	
	$sql = "SELECT * FROM `admins` WHERE playerlogin = '".trim($connectedplayer['Login'])."'";
	$mysql = mysqli_query($db, $sql);
	if($mysql->fetch_object()){
		$control->client->query('SendDisplayManialinkPageToLogin', $connectedplayer['Login'], $music_mlcode, 0, False);
	}
}

function music_startUp ($control){
    manialinkPlayer($control);
    music_beginChallenge($control, null);
}

function music_beginChallenge ($control, $challenge){
    manialinkPlayer($control);
    music_play($control);
}

function nextSong ($control, $songID, $newSongID, $reset){
    global $songID, $newSongID, $url, $song, $name, $xml;
	
	//Check if $songID exists
	if(!isset($songID)){
	    $songID = 0;
	}
	
	//Load XML Data
	$xml = simplexml_load_file('plugin.music.config.xml');
	$url = $xml->config->url;
	$count = $xml->songs->song;
	$count2 = count($count);
	
	//Check if song with $songID exists
	if($songID > ($count2-1)){
	    $songID = 0;
	}
	
	//Check if $songID is lower than 0
	if($songID < 0){
	    $songID = $count2-1;
	}
	
	//Check $newSongID (if an admin has pressed a button)
	if(isset($newSongID)){	
		if($newSongID > ($count2-1)){
		    $newSongID = 0;
		}
		
		if($newSongID < 0){
		    $newSongID = $count2-1;
		}
	    $songID = $newSongID;
	}
	
	//Load XML Data
	$song = $xml->songs->song[$songID];
    $name = $xml->songs->name[$songID];
	
	//Reset $newSongID
	if($reset == "yes"){
	    $newSongID = null;
	}
}

function music_play ($control){
	global $url, $song, $play;
	
	nextSong($control, $songID, $newSongID, 'yes');
	
	//Play Music
	if($play != "no"){
	    $control->client->query('SetForcedMusic', true, $url.$song);
	}
	
	/*$control->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
	<manialink id="80001">
        <label posn="30 -25 3" sizen="20 3" scale="0.8" text="$i$oPlaying: ('.$songID.') '.$name.'" />
        <timeout>0</timeout>
	</manialink>', 0, False);*/
}

/*
Get Answers of presses buttons
Next: Set $newSongID +1
Stop: Set $play = "no"
Prev: Set $newSongID -1
*/
function music_manialinkPageAnswer ($control, $ManialinkPageAnswer){
    //Next Song
    if($ManialinkPageAnswer[2] == "80000"){
	    $login = $ManialinkPageAnswer[1];
        if($control->is_admin($login) == true){
		    global $songID, $newSongID, $name;
			
			$control->client->query('GetDetailedPlayerInfo', $ManialinkPageAnswer[1]);
	        $Playerinfo = $control->client->getResponse();
			
		    $newSongID = $songID+1;
			
            nextSong($control, $songID, $newSongID, 'no');
	        $control->chat_message(' $z$i$s$f90Admin '.$Playerinfo['NickName'].' $z$i$s$0afsets next song to '.$name.'');
		}
	}
	//Stop playing
    if($ManialinkPageAnswer[2] == "80001"){
	    $login = $ManialinkPageAnswer[1];
        if($control->is_admin($login) == true){
            global $play;
			if($play == "no"){
			    $play = "yes";
				$control->chat_message(' $z$i$s$0afMusic will play at begin of a new map!');
			}else{
			    $play = "no";
				$control->chat_message(' $z$i$s$0afMusic will stop at end of this map!');
				$control->client->query('SetForcedMusic', false, '');
			}
		}
	}
	//Prev Song
    if($ManialinkPageAnswer[2] == "80002"){
	    $login = $ManialinkPageAnswer[1];
        if($control->is_admin($login) == true){
		    global $songID, $newSongID, $name;
			
			$control->client->query('GetDetailedPlayerInfo', $ManialinkPageAnswer[1]);
	        $Playerinfo = $control->client->getResponse();
			
		    $newSongID = $songID-1;
			
            nextSong($control, $songID, $newSongID, 'no');
	        $control->chat_message(' $z$i$s$f90Admin '.$Playerinfo['NickName'].' $z$i$s$0afsets next song to '.$name.'');
		}
	}
	if($ManialinkPageAnswer[2] == "80003"){
		$control->close_ml(80001, $ManialinkPageAnswer[1]);
	}
	if($ManialinkPageAnswer[2] == "80004"){
	    music_jukebox($control, $ManialinkPageAnswer[1]);
	}
	if($ManialinkPageAnswer[2] >= 80010 AND $ManialinkPageAnswer[2] <= 80110){
	    global $newSongID, $name;
		
	    $id = $ManialinkPageAnswer[2] - 80010;
		$newSongID = $id;
		
		nextSong($control, $songID, $newSongID, 'no');
		
		$control->client->query('GetDetailedPlayerInfo', $ManialinkPageAnswer[1]);
	    $Playerinfo = $control->client->getResponse();
		
		$control->chat_message(' '.$Playerinfo['NickName'].'$z$i$s$0af juked Song: '.$name.'');
	}
}

//Increase $songID up 1 at the end of each map
function music_endChallenge ($control){
	global $db, $music_mlcode, $songID, $newSongID, $name, $play;
	
	if($play != "no"){
	    $songID++;
        nextSong($control, $songID, $newSongID, 'no');
	    $control->chat_message(' $z$i$s$0afNext song: '.$name.'');
	}
	
	$control->client->query('GetPlayerList', 300, 0);
	$ap_player_list = $control->client->getResponse();
	$ap_curr_pid = 0;
	while(isset($ap_player_list[$ap_curr_pid])){
		$ap_curr_player = $ap_player_list[$ap_curr_pid];
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".trim($ap_curr_player['Login'])."'";
		$mysql = mysqli_query($db, $sql);
		if($mysql->fetch_object()){
			$control->client->query('SendDisplayManialinkPageToLogin', $ap_curr_player['Login'], str_replace('-25.75', '-28', str_replace('-26.5', '-28.5', str_replace('-26.25', '-28.25', $music_mlcode))), 0, False);
		}
		$ap_curr_pid++;
	}
}

function music_chat ($control, $PlayerChat){
    $Command = explode(' ', $PlayerChat[2]);
	$control->client->query('GetDetailedPlayerInfo', $PlayerChat[1]);
	$CommandAuthor = $control->client->getResponse();
	
	if($Command[0]=='/music'){
		music_jukebox($control, $CommandAuthor['Login']);
	}
}

function music_jukebox ($control, $Login){
    global $xml;
	
	$jukebox = '<?xml version="1.0" encoding="UTF-8" ?>
    <manialink id="80001">
        <quad posn="0 5 1" sizen="70 41" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
        <quad posn="0 5 0" sizen="70 41" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
        <quad posn="0 24.5 3" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
        <label posn="-34 24.25 4" textsize="2" text="$oMusic:"/>
        <quad posn="31.75 24.5 4" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="80003"/>';
		
    $id = 0;
	$y = 22;
	$x = -34;
	$line = 0;
    while(isset($xml->songs->name[$id])){
	    $jukeid = 80010 + $id;
        $jukebox .='
		<label posn="'.$x.' '.$y.' 4" sizen="9 3" style="TextCardInfoSmall" text="$i'.$xml->songs->name[$id].'" action="'.$jukeid.'"/>';
	    $id++;
		$y = $y-2.5;
	if($y < -15 AND $line == 0){
	    $x = -23;
	    $y = 24.5;
		$y = $y-2.5;
		$line = 1;
	}
	if($y < -15 AND $line == 1){
	    $x = -12;
	    $y = 24.5;
		$y = $y-2.5;
		$line = 2;
	}
	if($y < -15 AND $line == 2){
	    $x = -1;
	    $y = 24.5;
		$y = $y-2.5;
		$line = 3;
	}
	if($y < -15 AND $line == 3){
	    $x = 10;
	    $y = 24.5;
		$y = $y-2.5;
		$line = 4;
	}
	if($y < -15 AND $line == 4){
	    $x = 21;
	    $y = 24.5;
		$y = $y-2.5;
		$line = 5;
	}
    }
	$jukebox .= '</manialink>';
	$control->client->query('SendDisplayManialinkPageToLogin', $Login, $jukebox, 0, false);
}
?>