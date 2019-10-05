<?php
//* plugin.adminpanel.php - Adminpanel
//* Version:   0.9.0
//* Coded by:  cyrilw && libero6
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('PlayerConnect', 'ap_playerconnect');
control::RegisterEvent('BeginChallenge', 'ap_newchallenge');
control::RegisterEvent('EndChallenge', 'ap_endchallenge');
control::RegisterEvent('ManialinkPageAnswer', 'ap_mlanswer');
control::RegisterEvent('StartUp', 'ap_startup');

//manialink 151
//action 180-200
global $ap_mlcode;
$ap_mlcode = '<?xml version="1.0" encoding="UTF-8" ?>
<manialink id="120">
<quad posn="57.5 -23 0" sizen="15 3" halign="center" style="Bgs1InRace" action="121" substyle="NavButtonBlink"/>
<quad posn="62.7 -23.5 2" sizen="2.2 2.2" halign="center" style="Icons64x64_1" action="181" substyle="ClipPlay" />
<quad posn="60.2 -23.5 1" sizen="2.2 2.2" halign="center" style="Icons64x64_1" action="182" substyle="ClipPause" />
<quad posn="57.7 -23.5 1" sizen="2.2 2.2" halign="center" style="Icons64x64_1" action="183" substyle="ClipRewind" />
<quad posn="51.7 -23.5 1" sizen="2.5 2.5" halign="center" style="Icons64x64_1" substyle="IconPlayers" />
<quad posn="53.7 -23.5 1" sizen="2.2 2.2" halign="center" style="Icons64x64_1" substyle="OfficialRace" />
<quad posn="53.7 -23.3 2" sizen="2.7 2.7" halign="center" style="Icons64x64_1" substyle="Close" />
<quad posn="55.7 -23.3 2" sizen="2.4 2.4" halign="center" style="Icons64x64_1" action="184" substyle="ArrowRed" />
</manialink>';


function ap_playerconnect($control, $connectedplayer){
	global $db, $ap_mlcode;
	
	$sql = "SELECT * FROM `admins` WHERE playerlogin = '".trim($connectedplayer['Login'])."'";
	$mysql = mysqli_query($db, $sql);
	if($mysql->fetch_object()){
		$control->client->query('SendDisplayManialinkPageToLogin', $connectedplayer['Login'], $ap_mlcode, 0, False);
	}
}

function ap_newchallenge($control, $calldata){
	global $db, $ap_mlcode;
	$control->client->query('GetPlayerList', 300, 0);
	$ap_player_list = $control->client->getResponse();
	$ap_curr_pid = 0;
	while(isset($ap_player_list[$ap_curr_pid])){
		$ap_curr_player = $ap_player_list[$ap_curr_pid];
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".trim($ap_curr_player['Login'])."'";
		$mysql = mysqli_query($db, $sql);
		if($mysql->fetch_object()){
			$control->client->query('SendDisplayManialinkPageToLogin', $ap_curr_player['Login'], $ap_mlcode, 0, False);
		}
		$ap_curr_pid++;
	}
}

function ap_endchallenge($control, $challdata){
	global $db, $ap_mlcode;
	$control->client->query('GetPlayerList', 300, 0);
	$ap_player_list = $control->client->getResponse();
	$ap_curr_pid = 0;
	while(isset($ap_player_list[$ap_curr_pid])){
		$ap_curr_player = $ap_player_list[$ap_curr_pid];
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".trim($ap_curr_player['Login'])."'";
		$mysql = mysqli_query($db, $sql);
		if($mysql->fetch_object()){
			$control->client->query('SendDisplayManialinkPageToLogin', $ap_curr_player['Login'], str_replace('-23', '-25', str_replace('-23.5', '-25.5', $ap_mlcode)), 0, False);
		}
		$ap_curr_pid++;
	}
}

function ap_startup($control){
	global $db, $ap_mlcode;
	$control->client->query('GetPlayerList', 300, 0);
	$ap_player_list = $control->client->getResponse();
	$ap_curr_pid = 0;
	while(isset($ap_player_list[$ap_curr_pid])){
		$ap_curr_player = $ap_player_list[$ap_curr_pid];
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".trim($ap_curr_player['Login'])."'";
		$mysql = mysqli_query($db, $sql);
		if($mysql->fetch_object()){
			$control->client->query('SendDisplayManialinkPageToLogin', $ap_curr_player['Login'], $ap_mlcode, 0, False);
		}
		$ap_curr_pid++;
	}
}

//next track

function ap_mlanswer($control, $ManialinkPageAnswer){
	global $db, $settings;

	//Get Infos
	$control->client->query('GetDetailedPlayerInfo', $ManialinkPageAnswer[1]);
	$CommandAuthor = $control->client->getResponse();
	
	
	/***********************
	***CHECK ADMIN RIGHTS***
	***********************/
	$sql = "SELECT * FROM `admins` WHERE playerlogin = '".$CommandAuthor['Login']."'";
	$mysql = mysqli_query($db, $sql);
	if($admin_rights = $mysql->fetch_object()){
		if($admin_rights->rights==1){
			require('include/op_rights.php');
			$Admin_Rank = $settings['Name_Operator'];
		}
		elseif($admin_rights->rights==2){
			require('include/admin_rights.php');
			$Admin_Rank = $settings['Name_Admin'];
		}
		elseif($admin_rights->rights==3){
			require('include/superadmin_rights.php');
			$Admin_Rank = $settings['Name_SuperAdmin'];
		}


	
	
	
	//SKIP
	if($ManialinkPageAnswer[2]=='181'){
		if($skip_challenge==true){
			$control->client->query('GetDetailedPlayerInfo', $ManialinkPageAnswer[1]);
			$Player_who_skipped = $control->client->getResponse();
			$control->challenge_skip();
			$control->client->query('ChatSendServerMessage', '$f90-> '.$Admin_Rank.' $fff'.$Player_who_skipped['NickName'].'$z$s $f90skipped the challenge!');
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	
	//FORCE END ROUND
	elseif($ManialinkPageAnswer[2]=='182'){
		if($force_end_round==true){
			$control->client->query('ForceEndRound');
			$control->client->query('ChatSendServerMessage', '$f90-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s $f90forced round end!');
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	
	//RESTART
	elseif($ManialinkPageAnswer[2]=='183'){
		if($restart_challenge==true){
			global $chall_restarted_admin;
			$chall_restarted_admin = true;
			$control->client->query('RestartChallenge');
			$control->client->query('ChatSendServerMessage', '$f90-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s $f90restarted the challenge!');
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	
	//Cancel Vote
	elseif($ManialinkPageAnswer[2]=='184'){
		if($cancel_vote==true){
			$control->client->query('CancelVote');
			$control->client->query('ChatSendServerMessage', '$f90-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s $f90cancled vote!');
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
}
}
?>