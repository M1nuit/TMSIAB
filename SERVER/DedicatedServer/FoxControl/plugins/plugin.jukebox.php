<?php
//* plugin.jukebox.php - Track Jukebox
//* Version:   0.9.0
//* Coded by:  cyrilw
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('EndChallenge', 'jukebox_endChallenge');

global $jukebox;
$jukebox = array();

function jukebox_endChallenge($control, $endChallData) {
	global $jukebox;
	$id = 0;
	while(isset($jukebox[$id])){
		if($jukebox[$id]['played']==false){
			$control->client->query('GetChallengeInfo', $jukebox[$id]['fileName']);
			$challenge = $control->client->getResponse();
			$control->client->query('GetDetailedPlayerInfo', $jukebox[$id]['login']);
			$player = $control->client->getResponse();
			$control->client->query('ChooseNextChallenge', $challenge['FileName']);
			$jukebox[$id]['played'] = true;
			$control->chat_message('$0e0The next challenge will be: $fff'.$challenge['Name'].'$z$s$0e0 . Juked by: $z'.$player['NickName'].'$z$s$0e0!');
			break;
		}
		else $id++;
	}
}

function jukebox_jukeChallenge($control, $fileName, $player, $sendChatMessage) {
	global $jukebox;
	$control->client->query('GetDetailedPlayerInfo', $player);
	$jukedplayer = $control->client->getResponse();
	
	
	//Check if player has already juked
	$id = 0;
	$alreadyjuked = false;
	while(isset($jukebox[$id])){
		if($jukebox[$id]['played']==false){
			if($jukebox[$id]['login']==$jukedplayer['Login']){
				$alreadyjuked = true;
				break;
			}
		}
		$id++;
	}
	
	if($alreadyjuked==true){
		$control->chat_message_player('$f90You have already juked!', $jukedplayer['Login']);
	}
	else{
		$control->client->query('GetChallengeInfo', $fileName);
		$jukedchallenge = $control->client->getResponse();
		if(!isset($jukedchallenge['Name']) || trim($jukedchallenge['Name']) == '') {
			$control->chat_message_player('$f90Challenge not found!');
			console('[WARNING] [plugin.jukebox.php] Challenge \''.$fileName.'\' not found!');
			return;
		}
		$jukebox[] = array('played' => false, 'fileName' => $fileName, 'login' => $jukedplayer['Login']);
		if($sendChatMessage == true) $control->chat_message($jukedplayer['NickName'].'$z$s$0e0 juked $fff'.$jukedchallenge['Name'].'$z$s$0f0 !');
	}

}
?>