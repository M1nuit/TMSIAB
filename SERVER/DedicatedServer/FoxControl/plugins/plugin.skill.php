<?php
//* plugin.skill.php - Skillpoints
//* Version:   0.9.0
//* Coded by:  cyrilw && libero6
//* Copyright: FoxRace, http://www.fox-control.de


control::RegisterEvent('StartUp', 'skill_startup');
control::RegisterEvent('PlayerConnect', 'skill_playerconnect');
control::RegisterEvent('BeginChallenge', 'skill_newchallenge');
control::RegisterEvent('EndChallenge', 'skill_endchallenge');
control::RegisterEvent('ManialinkPageAnswer', 'skill_mlanswer');


global $settings, $skp_server_enabled, $skp_connection_code, $settings, $skp_connection_code, $skp_chall_stat;
$skp_server_enabled = true;
$skp_first_startup = false;
$skp_chall_stat = 'race';
$skp_version = '1.0.0';

//First Connection
//DON'T EDIT THIS!!
$url = 'http://fox-control.de/~skpfox/scripts/checkConnection.php?login='.$settings['ServerLogin'].'&foxc=startup&code='.$settings['CommunityCode'].'&version='.$skp_version.'&servername='.str_replace(' ', '{leer}', $settings['ServerName']);
$file = fopen($url, "rb");
$content = stream_get_contents($file);
$file = fclose($file);
$skp_server_enabled = true;
$content = explode('|', $content);
if($content[0]=='1' AND $content[1]!=='0'){
	$skp_connection_code = $content[1];
}
else{
	echo'SKP ERROR: SKP Authentification failed! Code: '.$skp_connection_code;
}
		
		
function skp_check_connection($control){
	global $settings, $skp_server_enabled, $skp_connection_code;
	
	########################################
	#//////////////////////////////////////#
	#///                                ///#
	#///  DON'T EDIT ANYTHING HERE!!!!  ///#
	#///                                ///#
	#//////////////////////////////////////#
	########################################
	
	
	$url = 'http://fox-control.de/~skpfox/scripts/checkConnection.php?login='.$settings['ServerLogin'].'&code='.$settings['CommunityCode'].'&foxcCode='.$skp_connection_code.'&servername='.str_replace(' ', '{leer}', $settings['ServerName']);
	$file = fopen($url, "rb");
	$content = stream_get_contents($file);
	$file = fclose($file);
	$skp_server_enabled = true;
	$content = explode('|', $content);
	
	if($content[0]=='1'){
		$return = 'true';
	}
	else{
		$return = 'false';
		if($content[0]=='2') die('SKP ERROR: Wrong Community Code!');
		elseif($content[0]=='3') die('SKP ERROR: Wrong Server Login!');
		elseif($content[0]=='4'){
			echo'SKP WARNING:';
			echo'******************************************************************************';
			echo'SKP ERROR: Internal Error. Please restart FoxControl later for the SKP Plugin.';
			echo'******************************************************************************';
			$return = '$f00SKP ERROR: $fffInternal Error. Please restart FoxControl later for the SKP Plugin.';
			$skp_server_enabled = false;
		}
		elseif($content[0]=='5'){
			echo'SKP WARNING:';
			echo'*********************************************************';
			echo'This Server is banned from SKP! The FoxControl Team.';
			echo'*********************************************************';
			$return = '$o$f00This Server is banned from SKP! If the ban is unjustified, contact us here: $06f$h[FoxControl]F$fffox$06fC$fffontrol$h'.nz.'$z$fff$oThe $06fF$fffox$06fC$fffontrol $06fT$fffeam.';
			$skp_server_enabled = false;
		}
		elseif($content[0]=='6'){
			echo'SKP WARNING:';
			echo'*********************************************************';
			echo'Invalid FoxC-Code. Please restart FoxControl';
			echo'*********************************************************';
			$return = '$o$f00SKP ERROR:$fff Invalid FoxC-Code.';
			$skp_server_enabled = false;
		}
		else{
			$skp_ecode = $content[0];
			if(trim($skp_ecode=='')) $skp_ecode = '??';
			echo'SKP WARNING: (Error Code: '.$skp_ecode.')';
			echo'**********************************************************************';
			echo'Connection failed! Please restart FoxControl later for the SKP Plugin.';
			echo'**********************************************************************';
			$return = '$f00SKP ERROR: $fffConnection failed! Please restart FoxControl later for the SKP Plugin. Error Code: '.$skp_ecode;
			$skp_server_enabled = false;
		}
	}
	return $return;
	
}


//manialink 179


function skill_startup($control){
	global $settings, $skp_connection_code;
	$checkConnection = skp_check_connection($control);
	
	if($checkConnection=='true'){
	
		$newline = "\n";
		$control->client->query('GetPlayerList', 300, 0);
		$player_list = $control->client->getResponse();
		$player_id = 0;
		$skp_serverlogin = $settings['ServerLogin'];
		
		while(isset($player_list[$player_id])){
			$skill_player = $player_list[$player_id];
			
			$url = "http://fox-control.de/~skpfox/scripts/update_data.php?serverlogin=".$serverlogin."&foxcCode=".$skp_connection_code."&login=".trim($skill_player['Login'])."&nick=".trim(str_replace(' ', '{leer}', $skill_player['NickName'])).""; 
			$file = fopen($url, "rb");
			$file = fclose($file);
			
			$url = "http://fox-control.de/~skpfox/scripts/get_data.php?serverlogin=".$serverlogin."&foxcCode=".$skp_connection_code."&login=".trim($skill_player['Login']).""; 
			$file = fopen($url, "rb");
			$skp_content = stream_get_contents($file);
			$file = fclose($file);
			$skp_content = explode('{expl}', $skp_content);
			
			if($skp_content[0]=='banned'){
				$control->client->query('SendDisplayManialinkPageToLogin', $skill_player['Login'], '<?xml version="1.0" encoding="UTF-8" ?>
				<manialink id="179">
				<quad posn="-38 -45.7 1" sizen="50 2.3" halign="center" style="Bgs1InRace" action="120" substyle="NavButtonBlink"/>
				<quad posn="-38 -45.7 0" sizen="50 2.3" halign="center" style="Bgs1InRace" action="120" substyle="BgTitle2"/>
				<label posn="-38 -46.2 5" textsize="1" halign="center" sizen="48 2" text="$o$F00You are banned from SKP! The SKP Plugin is disabled for you!" />
				</manialink>', 0, False);
			}
			else{
			
				$player_skills = $skp_content[0];
				$player_skilllevel = $skp_content[1];
				$player_skilllevelpoints = $skp_content[3];
				$player_skillsize = $skp_content[4];
					
				if($player_skillsize<1) $player_skillsize = '1';
				if($player_skillsize=='') $player_skillsize = '1';
				$control->client->query('SendDisplayManialinkPageToLogin', $skill_player['Login'], '<?xml version="1.0" encoding="UTF-8" ?>
				<manialink id="179">
				<quad posn="-38 -45.7 1" sizen="50 2.3" halign="center" style="Bgs1InRace" action="120" substyle="NavButtonBlink"/>
				<quad posn="-38 -45.7 0" sizen="50 2.3" halign="center" style="Bgs1InRace" action="120" substyle="BgTitle2"/>
				<quad posn="-53 -46 3" sizen="'.$player_skillsize.' 1.8" style="Bgs1InRace" action="120" substyle="ProgressBarSmall"/>
				<quad posn="-53 -46 2" sizen="33 1.8" style="BgsPlayerCard" substyle="BgActivePlayerName"/>
				<label posn="-62 -46.5 5" scale="0.4" text="$o$F00Your Skill Rank :" />
				<label posn="-36.5 -46.4 5" scale="0.4" halign="center" text="$o$FFF'.$player_skills.'/'.$player_skilllevelpoints.' skp" />
				<label posn="-19.5 -46.4 5" scale="0.4" text="$o$FFFLVL:" />
				<label posn="-17 -46.4 5" scale="0.4" text="$o$FFF'.$player_skilllevel.'" />
				</manialink>', 0, False);
				$player_skills = '';
				$player_skilllevel = '';
				
			}
			$player_id++;
		}
	}
	elseif($checkConnection!=='false'){
		$control->client->query('ChatSendServerMessage', $checkConnection);
	}
}

function skill_playerconnect($control, $skill_player){
	global $skp_chall_stat;
	if($skp_chall_stat=='race'){
		skill_startup($control);
	}
}

function skill_newchallenge($control, $newchallenge_infos){
	global $skp_chall_stat;
	$skp_chall_stat = 'race';
	skill_startup($control);
}

function skill_endchallenge($control, $skill_calldata){
	global $settings, $skp_server_enabled, $skp_connection_code, $skp_chall_stat;
	$skp_chall_stat = 'score';
	if($skp_server_enabled==true){
		$newline = "\n";
		$url = "http://fox-control.de/~skpfox/scripts/get_data.php?serverlogin=".$serverlogin."&foxcCode=".$skp_connection_code."&record=show"; 
		$file = fopen($url, "rb");
		$skp_most = stream_get_contents($file);
		$file = fclose($file);
		$skp_most = explode('{expl}', $skp_most);
		
		
		$skill_most_skp = $skp_most[0];
		$skill_most_player = $skp_most[1];

		
		$skill_players = 0;
		while(isset($skill_calldata[0][$skill_players])){
			if($skill_calldata[0][$skill_players]['BestTime'] > '0'){
				$skill_players++;
			}
			else{
				break;
			}
		}
		if($skill_players>=20) $skill_players = 20;
		$skill_pid = 0;
		$skill_calldata = $skill_calldata[0];
		$masterserver_login = $settings['ServerLogin'];
		while(isset($skill_calldata[$skill_pid])){
			$skill_calldata2 = $skill_calldata[$skill_pid];
			if(isset($skill_calldata2['BestTime']) AND trim($skill_calldata2['BestTime'])!=='' AND trim($skill_calldata2['BestTime'])!=='???' AND trim($skill_calldata2['BestTime'])>=0){
				if($skill_calldata2['Rank']=='1') $skill_plus = 10;
				elseif($skill_calldata2['Rank']=='2') $skill_plus = 8;
				elseif($skill_calldata2['Rank']=='3') $skill_plus = 7;
				elseif($skill_calldata2['Rank']=='4') $skill_plus = 6;
				elseif($skill_calldata2['Rank']=='5') $skill_plus = 5;
				elseif($skill_calldata2['Rank']=='6') $skill_plus = 4;
				elseif($skill_calldata2['Rank']=='7') $skill_plus = 3;
				elseif($skill_calldata2['Rank']=='8') $skill_plus = 2;
				elseif($skill_calldata2['Rank']=='9') $skill_plus = 1;
				else $skill_plus = 0;
				$skill_plus = $skill_plus*$skill_players;
				$url = "http://fox-control.de/~skpfox/scripts/send_skp.php?playerlogin=".$skill_calldata2['Login']."&skp=".$skill_plus."&players=".$skill_players."&serverlogin=".$masterserver_login."&foxcCode=".$skp_connection_code."&nickname=".str_replace(' ', '{leer}', $skill_calldata2['NickName']).""; 
				$file = fopen($url, "rb");
				$skp_content = stream_get_contents($file);
				$file = fclose($file);
				$skp_content = explode('{expl}', $skp_content);
				if($skp_content[0]=='banned'){
					$control->client->query('ChatSendServerMessageToLogin', '$f00-> $fffYou are banned from SKP!', $skill_calldata2['Login']);
				}
				elseif($skp_content[0]=='invalidcode'){
					$control->client->query('ChatSendServerMessageToLogin', '$f00-> $fffThis server has an invalid FoxC-Code! Can\'t add your skillpoints.', $skill_calldata2['Login']);
				}
				else{
				$skp_skill_lvl = $skp_content[1];
				$new_skill = $skp_content[0];
				
				
					//Check level
					if(trim($skp_content[2])=='true'){
						$skp_skill_lvl++;
						$control->client->query('SendDisplayManialinkPageToLogin', $skill_calldata2['Login'], '<?xml version="1.0" encoding="UTF-8" ?>
						<manialink id="9">
						<quad posn="0 25 6" sizen="70 19" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
						<quad posn="0 25 5" sizen="70 19" halign="center" style="Bgs1InRace" substyle="BgList"/>
						<quad posn="0 24.25 17" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
						<quad posn="31.75 24.25 19" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="9"/>
						<label posn="-34 24.25 19" textsize="2" text="$o$0f0SkillPoints  -  Level UP!"/>
						<label posn="-34 20 19" manialink="skp?userpanel&amp;235sdf68e45&amp;rto=downloads&amp;authentication=1" addplayerid="1" style="TextCardSmallScores2" text="$fff$oCongratulation, you are now in Level $0f0$w'.$skp_skill_lvl.'$z$o$fff!'.$newline.'You unlocked a new Download at the $h[skp]Manialink SKP$h'.$newline.'You had to login and then your able'.$newline.'to donwload your stuff there.'.$newline.'Have fun with the Download and $09fFoxControl"/>
						</manialink>', 0, False);
						$control->client->query('ChatSendServerMessageToLogin', '$f00-> $oLEVEL UP!$o You unlocked a new download! You can download it here: $hskp$h', $skill_calldata2['Login']);
					}
				}
			}
			else{
				$skill_plus = '0';
				$url = "http://fox-control.de/~skpfox/scripts/get_data.php?serverlogin=".$serverlogin."&foxcCode=".$skp_connection_code."&login=".trim($skill_calldata2['Login']).""; 
				$file = fopen($url, "rb");
				$skp_content = stream_get_contents($file);
				$file = fclose($file);
				$skp_content = explode('{expl}', $skp_content);
				$new_skill = $skp_content[0];
				$skp_skill_lvl = $skp_content[1];

			}
			
			$control->client->query('ChatSendServerMessageToLogin', '$f00-> Your Skillpoints: $fff'.$new_skill.' $f00( $fff+'.$skill_plus.'$f00   Level: $fff'.$skp_skill_lvl.'$f00 )', $skill_calldata2['Login']);
			$control->client->query('SendDisplayManialinkPageToLogin', $skill_calldata2['Login'], '<?xml version="1.0" encoding="UTF-8" ?>
			<manialink id="179">
			<quad posn="-64 -33 1" sizen="15 15" style="Bgs1InRace" substyle="NavButtonBlink"/>
			<label posn="-56.5 -33.5 2" sizen="15 2" halign="center" style="TextCardSmallScores2Rank" textsize="2" text="$o$fffSkill Points:" manialink="skp"/>
			<label posn="-63.5 -36 2" textsize="1" text="Your skp:"/>
			<label posn="-57 -36 2" textsize="1" sizen="7 1" text="$09f$o'.$new_skill.'"/>
			<label posn="-63.5 -38 2" textsize="1" text="Your Level:"/>
			<label posn="-57 -38 2" textsize="1" sizen="7 1" text="$09f$o'.$skp_skill_lvl.'"/>
			<label posn="-63.5 -40 2" textsize="1" text="New skp:"/>
			<label posn="-57 -40 2" textsize="1" sizen="7 1" text="$09f$o+'.$skill_plus.'"/>
			<label posn="-63.5 -43 2" textsize="1" text="Most skp:"/>
			<label posn="-57 -43 2" textsize="1" sizen="7 1" text="$09f$o'.$skill_most_skp.'"/>
			<label posn="-63.5 -45 2" textsize="1" text="Held by:"/>
			<label posn="-57 -45 2" textsize="1" sizen="7 1" text="'.htmlspecialchars(stripslashes($skill_most_player)).'"/>
			</manialink>', 0, false);
			$skill_pid++;
		}
	}
	
}
function skill_mlanswer($control, $CallData){
	if($CallData[2]=='9') $control->close_ml(9, '');
}
?>