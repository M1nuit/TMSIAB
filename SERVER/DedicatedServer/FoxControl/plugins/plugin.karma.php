<?php
//* plugin.karma.php - Track Karma
//* Version:   0.9.0
//* Coded by:  cyrilw && libero6
//* Copyright: FoxRace, http://www.fox-control.de

//actions 2020 - 2100
//ml 2020 - 2100
control::RegisterEvent('BeginChallenge', 'karma_beginchallenge');
control::RegisterEvent('EndChallenge', 'karma_beginchallenge');
control::RegisterEvent('StartUp', 'karma_startup');
control::RegisterEvent('ManialinkPageAnswer', 'karma_mlanswer');
control::RegisterEvent('PlayerConnect', 'karma_startup');
control::RegisterEvent('Chat', 'karma_chat');

function karma_beginchallenge($control, $kar_challinfo){
	global $db;
	if(!isset($kar_challinfo['UId'])){
		$control->client->query('GetCurrentChallengeInfo');
		$kar_challinfo = $control->client->getResponse();
	}
	$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_challinfo['UId']."'";
	$mysql = mysqli_query($db, $sql);
	$kar_karma = array();
	while($kar_currvote = $mysql->fetch_object()){
		if($kar_currvote->playerlogin!=='root'){
			$kar_karma[] = $kar_currvote->vote;
		}
	}
	$kar_currid = 0;
	$kar_votes = 0;
	while(isset($kar_karma[$kar_currid])){
		if(!isset($kar_vote)) $kar_vote = $kar_karma[$kar_currid];
		else{
			$kar_vote = $kar_vote+$kar_karma[$kar_currid];
		}
		$kar_currid++;
		$kar_votes++;
	}
	if(isset($kar_vote)){
		$karmavote = $kar_vote/$kar_votes;
		$karmavote = round($karmavote, 1);
		$kar_vote2 = round($karmavote, 0);
		if($kar_vote2==1){
			$kar_s1 = '<quad posn="60.85 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s2 = '';
			$kar_s3 = '';
			$kar_s4 = '';
			$kar_s5 = '';
		}
		elseif($kar_vote2==2){
			$kar_s1 = '<quad posn="60.85 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s2 = '<quad posn="59.35 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s3 = '';
			$kar_s4 = '';
			$kar_s5 = '';
		}
		elseif($kar_vote2==3){
			$kar_s1 = '<quad posn="60.85 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s2 = '<quad posn="59.35 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s3 = '<quad posn="57.85 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s4 = '';
			$kar_s5 = '';
		}
		elseif($kar_vote2==4){
			$kar_s1 = '<quad posn="60.85 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s2 = '<quad posn="59.35 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s3 = '<quad posn="57.85 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s4 = '<quad posn="56.35 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s5 = '';
		}
		elseif($kar_vote2==5){
			$kar_s1 = '<quad posn="60.85 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s2 = '<quad posn="59.35 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s3 = '<quad posn="57.85 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s4 = '<quad posn="56.35 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
			$kar_s5 = '<quad posn="54.85 26.25 4" sizen="2 2" style="BgRaceScore2" halign="center" substyle="Fame"/>';
		}
	}
	else{
		$karmavote = '';
		$kar_s1 = '';
		$kar_s2 = '';
		$kar_s3 = '';
		$kar_s4 = '';
		$kar_s5 = '';
		
	}

	$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_challinfo['UId']."' AND playerlogin = 'root'";
	$mysql = mysqli_query($db, $sql);
	if($fullkarma = $mysql->fetch_object() AND isset($fullkarma->vote)){
		
	}
	else{
		$sql = "INSERT INTO `karma` (challengeid, challengename, playerlogin, vote) VALUES ('".$kar_challinfo['UId']."', '".mysqli_real_escape_string($db, $kar_challinfo['Name'])."', 'root', '".$karmavote."')";
		$mysql = mysqli_query($db, $sql);
	}
	
	$control->client->query('GetPlayerList', 300, 0);
	$player_list = $control->client->getResponse();
	$player_id = 0;
	while(isset($player_list[$player_id])){
		$karma_player = $player_list[$player_id];
		$sql = "SELECT * FROM `karma` WHERE playerlogin = '".$karma_player['Login']."' AND challengeid = '".$kar_challinfo['UId']."'";
		$mysql = mysqli_query($db, $sql);
		if($karma_player_data = $mysql->fetch_object()){
			$karma_player_vote = $karma_player_data->vote;
			if($karma_player_vote=='1'){
				$karma_voted_code = '<quad posn="59.9 24.25 2" sizen="2 2" style="Icons64x64_1" substyle="LvlGreen"/>';
			}
			elseif($karma_player_vote=='2'){
				$karma_voted_code = '<quad posn="58.4 24.25 2" sizen="2 2" style="Icons64x64_1" substyle="LvlGreen"/>';
			}
			elseif($karma_player_vote=='3'){
				$karma_voted_code = '<quad posn="56.9 24.25 2" sizen="2 2" style="Icons64x64_1" substyle="LvlGreen"/>';
			}
			elseif($karma_player_vote=='4'){
				$karma_voted_code = '<quad posn="55.4 24.25 2" sizen="2 2" style="Icons64x64_1" substyle="LvlGreen"/>';
			}
			elseif($karma_player_vote=='5'){
				$karma_voted_code = '<quad posn="53.9 24.25 2" sizen="2 2" style="Icons64x64_1" substyle="LvlGreen"/>';
			}
		}
		else $karma_voted_code = '';
		$control->client->query('SendDisplayManialinkPageToLogin', $karma_player['Login'], '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="2020">
		<quad posn="58.2 25.3 1" sizen="16 6.5" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		<quad posn="58.2 23.3 0" sizen="16 2.5" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		<label posn="54.5 24 5" text="$o$FFF5" scale="0.5" style="TextButtonBig" action="2060" />
		<label posn="56 24 5" text="$o$FFF4" scale="0.5" style="TextButtonBig" action="2061" />
		<label posn="57.5 24 5" text="$o$FFF3" scale="0.5" style="TextButtonBig" action="2062"/>
		<label posn="59 24 5" text="$o$FFF2" scale="0.5" style="TextButtonBig" action="2063"/>
		<label posn="60.5 24 5" text="$o$FFF1" scale="0.5" style="TextButtonBig" action="2064"/>
		<label posn="62 24 2" text="$o$F00-" scale="0.5" style="TextButtonBig"/>
		<label posn="52 24 2" text="$o$1A0+" scale="0.5" style="TextButtonBig" />
		<label posn="51 27.75 2" text="$o$FFF'.$karmavote.'" sizen="5 2" scale="0.8" style="TextButtonBig" />
		<label posn="57.5 27.75 2" text="$FFFVotes: '.$kar_votes.'" sizen="10 2" scale="0.6" style="TextButtonBig"/>
		'.$kar_s1.'
		'.$kar_s2.'
		'.$kar_s3.'
		'.$kar_s4.'
		'.$kar_s5.'
		<quad posn="60.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="58.85 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="57.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="55.85 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="54.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="60.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="58.85 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="57.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="55.85 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="54.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="60.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="58.85 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="57.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="55.85 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="54.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="60.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="58.85 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="57.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="55.85 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		<quad posn="54.35 25.5 3" sizen="1 1" style="Icons64x64_1" substyle="StarGold"/>
		'.$karma_voted_code.'
		</manialink>', 0, false);
		$player_id++;
	}
	
}


function karma_startup($control){
	$novar = false;
	karma_beginchallenge($control, $novar);
}
function karma_mlanswer($control, $ManialinkPageAnswer){
	global $db;
	$control->client->query('GetCurrentChallengeInfo');
	$kar_currchallenge = $control->client->getResponse();
	$control->client->query('GetDetailedPlayerInfo', $ManialinkPageAnswer[1]);
	$kar_pinfo = $control->client->getResponse();
	$novar = false;
	$voted = false;
	if($ManialinkPageAnswer[2]=='2060'){
		$voted = true;
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
		$mysql = mysqli_query($db, $sql);
		if($kar_challvote = $mysql->fetch_object()){
			if($kar_challvote->vote!=='5'){
				$sql = "UPDATE `karma` SET vote = '5' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
				$mysql = mysqli_query($db, $sql);
				if($kar_challvote->timestamp + 60 < time()){
					$sql = "UPDATE `karma` SET timestamp = '".time()."' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
					$mysql = mysqli_query($db, $sql);
					$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff5$0f0!');
				}
				karma_beginchallenge($control, $novar);
			}
		}
		else{
			$sql = "INSERT INTO `karma` (challengeid, playerlogin, vote, timestamp) VALUES ('".$kar_currchallenge['UId']."', '".$ManialinkPageAnswer[1]."', '5', '".time()."')";
			$mysql = mysqli_query($db, $sql);
			$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff5$0f0!');
			karma_beginchallenge($control, $novar);
		}
		
	}
	elseif($ManialinkPageAnswer[2]=='2061'){
		$voted = true;
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
		$mysql = mysqli_query($db, $sql);
		if($kar_challvote = $mysql->fetch_object()){
			if($kar_challvote->vote!=='4'){
				$sql = "UPDATE `karma` SET vote = '4' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
				$mysql = mysqli_query($db, $sql);
				if($kar_challvote->timestamp + 60 < time()){
					$sql = "UPDATE `karma` SET timestamp = '".time()."' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
					$mysql = mysqli_query($db, $sql);
					$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff4$0f0!');
				}
				karma_beginchallenge($control, $novar);
			}
		}
		else{
			$sql = "INSERT INTO `karma` (challengeid, playerlogin, vote, timestamp) VALUES ('".$kar_currchallenge['UId']."', '".$ManialinkPageAnswer[1]."', '4', '".time()."')";
			$mysql = mysqli_query($db, $sql);
			$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff4$0f0!');
			karma_beginchallenge($control, $novar);
		}
		
	}
	elseif($ManialinkPageAnswer[2]=='2062'){
		$voted = true;
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
		$mysql = mysqli_query($db, $sql);
		if($kar_challvote = $mysql->fetch_object()){
			if($kar_challvote->vote!=='3'){
				$sql = "UPDATE `karma` SET vote = '3' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
				$mysql = mysqli_query($db, $sql);
				if($kar_challvote->timestamp + 60 < time()){
					$sql = "UPDATE `karma` SET timestamp = '".time()."' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
					$mysql = mysqli_query($db, $sql);
					$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff3$0f0!');
				}
				karma_beginchallenge($control, $novar);
			}
		}
		else{
			$sql = "INSERT INTO `karma` (challengeid, playerlogin, vote, timestamp) VALUES ('".$kar_currchallenge['UId']."', '".$ManialinkPageAnswer[1]."', '3', '".time()."')";
			$mysql = mysqli_query($db, $sql);
			$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff3$0f0!');
			karma_beginchallenge($control, $novar);
		}
		
	}
	elseif($ManialinkPageAnswer[2]=='2063'){
		$voted = true;
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
		$mysql = mysqli_query($db, $sql);
		if($kar_challvote = $mysql->fetch_object()){
			if($kar_challvote->vote!=='2'){
				$sql = "UPDATE `karma` SET vote = '2' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
				$mysql = mysqli_query($db, $sql);
				if($kar_challvote->timestamp + 60 < time()){
					$sql = "UPDATE `karma` SET timestamp = '".time()."' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
					$mysql = mysqli_query($db, $sql);
					$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff2$0f0!');
				}
				karma_beginchallenge($control, $novar);
			}
		}
		else{
			$sql = "INSERT INTO `karma` (challengeid, playerlogin, vote, timestamp) VALUES ('".$kar_currchallenge['UId']."', '".$ManialinkPageAnswer[1]."', '2', '".time()."')";
			$mysql = mysqli_query($db, $sql);
			$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff2$0f0!');
			karma_beginchallenge($control, $novar);
		}
		
	}
	elseif($ManialinkPageAnswer[2]=='2064'){
		$voted = true;
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
		$mysql = mysqli_query($db, $sql);
		if($kar_challvote = $mysql->fetch_object()){
			if($kar_challvote->vote!=='1'){
				$sql = "UPDATE `karma` SET vote = '1' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
				$mysql = mysqli_query($db, $sql);
				if($kar_challvote->timestamp + 60 < time()){
					$sql = "UPDATE `karma` SET timestamp = '".time()."' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$ManialinkPageAnswer[1]."'";
					$mysql = mysqli_query($db, $sql);
					$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff1$0f0!');
				}
				karma_beginchallenge($control, $novar);
			}
		}
		else{
			$sql = "INSERT INTO `karma` (challengeid, playerlogin, vote, timestamp) VALUES ('".$kar_currchallenge['UId']."', '".$ManialinkPageAnswer[1]."', '1', '".time()."')";
			$mysql = mysqli_query($db, $sql);
			$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff1$0f0!');
			karma_beginchallenge($control, $novar);
		}
		
	}
	if($voted==true){
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."'";
		$mysql = mysqli_query($db, $sql);
		$kar_karma = array();
		while($kar_currvote = $mysql->fetch_object()){
			if($kar_currvote->playerlogin!=='root'){
				$kar_karma[] = $kar_currvote->vote;
			}
		}
		$kar_currid = 0;
		$kar_votes = 0;
		while(isset($kar_karma[$kar_currid])){
			if(!isset($kar_vote)) $kar_vote = $kar_karma[$kar_currid];
			else{
				$kar_vote = $kar_vote+$kar_karma[$kar_currid];
			}
			$kar_currid++;
			$kar_votes++;
		}
		if(isset($kar_vote)){
			$karmavote = $kar_vote/$kar_votes;
			$karmavote = round($karmavote, 1);
		}
		else $karmavote = 0;
		$sql = "UPDATE `karma` SET vote = '".$karmavote."' WHERE playerlogin = 'root' AND challengeid = '".$kar_currchallenge['UId']."'";
		$mysql = mysqli_query($db, $sql);
	}
}
function karma_chat($control, $PlayerChat){
	global $db;
	
	$Command = explode(' ', $PlayerChat[2]);
	$control->client->query('GetCurrentChallengeInfo');
	$kar_currchallenge = $control->client->getResponse();
	$control->client->query('GetDetailedPlayerInfo', $PlayerChat[1]);
	$kar_pinfo = $control->client->getResponse();
	$novar = '';
	
	if($Command[0]=='++' OR $Command[0]=='/++'){
	
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
		$mysql = mysqli_query($db, $sql);
		if($kar_challvote = $mysql->fetch_object()){
			if($kar_challvote->vote!=='5'){
				$sql = "UPDATE `karma` SET vote = '5' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
				$mysql = mysqli_query($db, $sql);
				if($kar_challvote->timestamp + 60 < time()){
					$sql = "UPDATE `karma` SET timestamp = '".time()."' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
					$mysql = mysqli_query($db, $sql);
					$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff5$0f0!');
				}
				karma_beginchallenge($control, $novar);
			}
		}
		else{
			$sql = "INSERT INTO `karma` (challengeid, playerlogin, vote, timestamp) VALUES ('".$kar_currchallenge['UId']."', '".$PlayerChat[1]."', '5', '".time()."')";
			$mysql = mysqli_query($db, $sql);
			$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff5$0f0!');
			karma_beginchallenge($control, $novar);
		}
	
	}
	elseif($Command[0]=='+' OR $Command[0]=='/+'){
	
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
		$mysql = mysqli_query($db, $sql);
		if($kar_challvote = $mysql->fetch_object()){
			if($kar_challvote->vote!=='4'){
				$sql = "UPDATE `karma` SET vote = '4' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
				$mysql = mysqli_query($db, $sql);
				if($kar_challvote->timestamp + 60 < time()){
					$sql = "UPDATE `karma` SET timestamp = '".time()."' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
					$mysql = mysqli_query($db, $sql);
					$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff4$0f0!');
				}
				karma_beginchallenge($control, $novar);
			}
		}
		else{
			$sql = "INSERT INTO `karma` (challengeid, playerlogin, vote, timestamp) VALUES ('".$kar_currchallenge['UId']."', '".$PlayerChat[1]."', '4', '".time()."')";
			$mysql = mysqli_query($db, $sql);
			$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff4$0f0!');
			karma_beginchallenge($control, $novar);
		}
	
	}
	elseif($Command[0]=='+-' OR $Command[0]=='/+-' OR $Command[0]=='-+' OR $Command[0]=='/-+'){
	
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
		$mysql = mysqli_query($db, $sql);
		if($kar_challvote = $mysql->fetch_object()){
			if($kar_challvote->vote!=='3'){
				$sql = "UPDATE `karma` SET vote = '3' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
				$mysql = mysqli_query($db, $sql);
				if($kar_challvote->timestamp + 60 < time()){
					$sql = "UPDATE `karma` SET timestamp = '".time()."' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
					$mysql = mysqli_query($db, $sql);
					$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff3$0f0!');
				}
				karma_beginchallenge($control, $novar);
			}
		}
		else{
			$sql = "INSERT INTO `karma` (challengeid, playerlogin, vote, timestamp) VALUES ('".$kar_currchallenge['UId']."', '".$PlayerChat[1]."', '3', '".time()."')";
			$mysql = mysqli_query($db, $sql);
			$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff3$0f0!');
			karma_beginchallenge($control, $novar);
		}
	
	}
	elseif($Command[0]=='-' OR $Command[0]=='/-'){
	
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
		$mysql = mysqli_query($db, $sql);
		if($kar_challvote = $mysql->fetch_object()){
			if($kar_challvote->vote!=='2'){
				$sql = "UPDATE `karma` SET vote = '2' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
				$mysql = mysqli_query($db, $sql);
				if($kar_challvote->timestamp + 60 < time()){
					$sql = "UPDATE `karma` SET timestamp = '".time()."' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
					$mysql = mysqli_query($db, $sql);
					$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff2$0f0!');
				}
				karma_beginchallenge($control, $novar);
			}
		}
		else{
			$sql = "INSERT INTO `karma` (challengeid, playerlogin, vote, timestamp) VALUES ('".$kar_currchallenge['UId']."', '".$PlayerChat[1]."', '2', '".time()."')";
			$mysql = mysqli_query($db, $sql);
			$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff2$0f0!');
			karma_beginchallenge($control, $novar);
		}
	
	}
	elseif($Command[0]=='--' OR $Command[0]=='/--'){
	
		$sql = "SELECT * FROM `karma` WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
		$mysql = mysqli_query($db, $sql);
		if($kar_challvote = $mysql->fetch_object()){
			if($kar_challvote->vote!=='1'){
				$sql = "UPDATE `karma` SET vote = '1' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
				$mysql = mysqli_query($db, $sql);
				if($kar_challvote->timestamp + 60 < time()){
					$sql = "UPDATE `karma` SET timestamp = '".time()."' WHERE challengeid = '".$kar_currchallenge['UId']."' AND playerlogin = '".$PlayerChat[1]."'";
					$mysql = mysqli_query($db, $sql);
					$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff1$0f0!');
				}
				karma_beginchallenge($control, $novar);
			}
		}
		else{
			$sql = "INSERT INTO `karma` (challengeid, playerlogin, vote, timestamp) VALUES ('".$kar_currchallenge['UId']."', '".$PlayerChat[1]."', '1', '".time()."')";
			$mysql = mysqli_query($db, $sql);
			$control->chat_message($kar_pinfo['NickName'].'$z$s$0f0 voted $fff1$0f0!');
			karma_beginchallenge($control, $novar);
		}
	
	}
}
?>