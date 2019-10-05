<?php
//* chat.admin.php - Admin Chat Commands
//* Version:   0.9.0
//* Coded by:  cyrilw
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('Chat', 'adminchat');
control::RegisterEvent('ManialinkPageAnswer','adminchat_mlanswer');

global $chall_restarted_admin;
$chall_restarted_admin = false;


function adminchat($control, $PlayerChat){
	global $db, $settings;

	//Get Infos
	$Command = explode(' ', $PlayerChat[2]);
	$control->client->query('GetDetailedPlayerInfo', $PlayerChat[1]);
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


	
	
	
	
	
	
	//Commands
	$text_false_rights = $settings['Text_wrong_rights'];
	if($Command[0]=='/specpw'){ //Sets password for spectators
		if($set_serverpw==true){
			$control->client->query('SetServerPasswordForSpectator', $Command[1]);
			$color_setpw = $settings['Color_SetPW'];
			$control->client->query('ChatSendServerMessage', $color_setpw.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_setpw.'sets the Spectatorpasswort to $fff'.$Command[1].'$z$s '.$color_setpw.'!');
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/serverpw'){ //Sets password for the Server
		if($set_spectatorpw==true){
			$control->client->query('SetServerPassword', $Command[1]);
			$color_setpw = $settings['Color_SetPW'];
			$control->client->query('ChatSendServerMessage', $color_setpw.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_setpw.'sets the Serverpasswort to $fff'.$Command[1].'$z$s '.$color_setpw.'!');
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/kick'){ //Kicks a player
		if($kick==true){
			$control->client->query('GetDetailedPlayerInfo', $Command[1]);
			$kickedplayer = $control->client->getResponse();;
			$control->player_kick($Command[1], true, $CommandAuthor);
			$color_kick = $settings['Color_Kick'];
			$control->client->query('ChatSendServerMessage', $color_kick.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_kick.'kicked $fff'.$kickedplayer['NickName'].'$z$s '.$color_kick.'!');
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/warn'){ //Warns a player
		if($warn==true){
			$control->client->query('GetDetailedPlayerInfo', $Command[1]);
			$warnedplayer = $control->client->getResponse();;
			$color_warn = $settings['Color_Warn'];
			$control->client->query('ChatSendServerMessage', $color_warn.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_warn.'warned $fff'.$warnedplayer['NickName'].'$z$s '.$color_warn.'!');
			$control->client->query('SendDisplayManialinkPageToLogin', $Command[1], '<?xml version="1.0" encoding="UTF-8" ?>
			<manialink id="4000">
			<quad posn="-64 48 15" sizen="128 96" bgcolor="0006"/>
			<quad posn="-64 48 16" sizen="128 96" image="http://fox-control.de/~skpfox/warning.bik"/>
			<quad posn="0 15 18" sizen="40 21" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
			<quad posn="0 15 17" sizen="40 21" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
			<quad posn="0 24.5 19" sizen="39 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
			<label posn="0 24.25 20" textsize="2" halign="center" text="$o$f00WARNING!"/>
			<label posn="-18 20.75 20" textsize="2" sizen="36 2" autonewline="1" text="This is an administrator warning!'.nz.'What ever you wrote or made is against our server rights.'.nz.'An administrator or Operator can kick or ban you next time!'.nz.'Be fair."/>
			<quad posn="15.75 24.5 20" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="4000"/>
			</manialink>', 0, false);
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/ban'){ //Ban a player
		if($ban==true){
			$sql = "SELECT * FROM `players` WHERE playerlogin = '".trim($Command[1])."'";
			$mysql = mysqli_query($db, $sql);
			if($data = $mysql->fetch_object()){
				$color_ban = $settings['Color_Ban'];
				$control->client->query('ChatSendServerMessage', $color_ban.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_ban.'banned $fff'.$data->nickname.$color_ban.' !');
				$control->client->query('Ban', $Command[1]);
			}
			else $control->client->query('ChatSendServerMessageToLogin', '$f00-> $fff'.trim($Command[1]).'$f00 isn\'t a valid login!', $Command[1]);
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $Command[1]);
	}
	elseif($Command[0]=='/unban'){ //Unbans a player
		if($unban==true){
			$sql = "SELECT * FROM `players` WHERE playerlogin = '".trim($Command[1])."'";
			$mysql = mysqli_query($db, $sql);
			if($data = $mysql->fetch_object()){
				$control->unban($Command[1], true, $CommandAuthor, $data);
			}
			else $control->client->query('ChatSendServerMessageToLogin', '$f00-> $fff'.trim($Command[1]).'$f00 isn\'t a valid login!', $Command[1]);
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $Command[1]);
	}
	elseif($Command[0]=='/newadmin'){
		if($add_new_admin==true){
			$control->client->query('GetDetailedPlayerInfo', $Command[1]);
			$NewAdmin = $control->client->getResponse();
			$sql = "SELECT * FROM `players` WHERE playerlogin = '".$NewAdmin['Login']."'";
			$mysql = mysqli_query($db, $sql);
			if($mysql->fetch_object()){
				$sql = "SELECT * FROM `admins` WHERE playerlogin = '".$NewAdmin['Login']."'";
				$mysql = mysqli_query($db, $sql);
				if($if_admin = $mysql->fetch_object()){
					$sql = "INSERT INTO `admins` (id, playerlogin, rights) VALUES ('', '".$NewAdmin['Login']."', '2')";
					$mysql = mysqli_query($db, $sql);
					$color_newadmin = $settings['Color_NewAdmin'];
					$control->client->query('ChatSendServerMessage', $color_newadmin.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_newadmin.'adds $fff'.$NewAdmin['NickName'].'$z$s '.$color_newadmin.'as a new Admin!');
				}
				else{
					if($if_admin->rights==1){
						$sql = "UPDATE `admins` SET rights = '2' WHERE playerlogin = '".$NewAdmin['login']."'";
						$mysql = mysqli_query($db, $sql);
						$color_newadmin = $settings['Color_NewAdmin'];
						$control->client->query('ChatSendServerMessage', $color_newadmin.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_newadmin.'adds $fff'.$NewAdmin['NickName'].'$z$s '.$color_newadmin.'as a new Admin!');
					}
				}
			}
			else{
				$control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$Admin_Rank.' $fff'.$Command[1].'$z$s $o$f00isn\'t a valid login!', $CommandAuthor['Login']);
			}
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/newsuperadmin'){
		if($add_new_superadmin==true){
			$control->client->query('GetDetailedPlayerInfo', $Command[1]);
			$NewAdmin = $control->client->getResponse();;
			$sql = "SELECT * FROM `players` WHERE playerlogin = '".$NewAdmin['Login']."'";
			$mysql = mysqli_query($db, $sql);
			if($mysql->fetch_object()){
				$sql = "SELECT * FROM `admins` WHERE playerlogin = '".$NewAdmin['Login']."'";
				$mysql = mysqli_query($db, $sql);
				if(!$if_admin = $mysql->fetch_object()){
					$sql = "INSERT INTO `admins` (id, playerlogin, rights) VALUES ('', '".$NewAdmin['Login']."', '3')";
					$mysql = mysqli_query($db, $sql);
					$color_newadmin = $settings['Color_NewAdmin'];
					$control->client->query('ChatSendServerMessage', $color_newadmin.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_newadmin.'adds $fff'.$NewAdmin['NickName'].'$z$s '.$color_newadmin.'as a new SuperAdmin!');
				}
				else{
					if($if_admin->rights==1 OR $if_admin->rights==2){
						$sql = "UPDATE `admins` SET rights = '3' WHERE playerlogin = '".$NewAdmin['login']."'";
						$mysql = mysqli_query($db, $sql);
						$color_newadmin = $settings['Color_NewAdmin'];
						$control->client->query('ChatSendServerMessage', $color_newadmin.'-> $fff'.$CommandAuthor['NickName'].'$z$s '.$color_newadmin.'adds $fff'.$NewAdmin['NickName'].'$z$s '.$color_newadmin.'as a new SuperAdmin!');
						}
					}
			}
			else{
				$control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$Admin_Rank.' $fff'.$Command[1].'$z$s $o$f00isn\'t a valid login!', $CommandAuthor['Login']);
			}
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/newop'){
		if($add_new_op==true){
			$control->client->query('GetDetailedPlayerInfo', $Command[1]);
			$NewAdmin = $control->client->getResponse();
			$sql = "SELECT * FROM `players` WHERE playerlogin = '".$NewAdmin['Login']."'";
			$mysql = mysqli_query($db, $sql);
			if($mysql->fetch_object()){
				$sql = "SELECT * FROM `admins` WHERE playerlogin = '".$NewAdmin['Login']."'";
				$mysql = mysqli_query($db, $sql);
				if(!$mysql->fetch_object()){
					$sql = "INSERT INTO `admins` (id, playerlogin, rights) VALUES ('', '".$NewAdmin['Login']."', '1')";
					$mysql = mysqli_query($db, $sql);
					$color_newadmin = $settings['Color_NewAdmin'];
					$control->client->query('ChatSendServerMessage', $color_newadmin.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_newadmin.'adds $fff'.$NewAdmin['NickName'].'$z$s '.$color_newadmin.'as a new Operator!');
				}
				else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> $fff'.$Command[1].'$z$s $o$f00isn\'t a valid login!', $CommandAuthor['Login']);
			}
			else{
				$control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$Admin_Rank.' $fff'.$Command[1].'$z$s $o$f00isn\'t a valid login!', $CommandAuthor['Login']);
			}
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/rmsuperadmin'){
		if($remove_superadmin==true){
			$ralogin = trim($Command[1]);
			$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $ralogin)."'";
			$mysql = mysqli_query($db, $sql);
			if($raplayer = $mysql->fetch_object()){
				if($raplayer->rights=='3'){
					$control->client->query('GetDetailedPlayerInfo', $ralogin);
					$ranick = $control->client->getResponse();
					$ranick = $ranick['NickName'];
					$sql = "DELETE FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $ralogin)."'";
					$mysql = mysqli_query($db, $sql);
					$control->client->query('ChatSendServerMessage', $settings['Color_RemoveAdmin'].'-> '.$Admin_Rank.' '.$CommandAuthor['NickName'].'$z$s'.$settings['Color_RemoveAdmin'].' removed Superadmin '.$ranick);
				}
				else $control->chat_message_player($ralogin.'isn\'t a SuperAdmin!', $CommandAuthor['Login']);
			}
			else $control->chat_message_player('$f90Invalid login!', $CommandAuthor['Login']);
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/rmadmin'){
		if($remove_admin==true){
			$ralogin = trim($Command[1]);
			$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $ralogin)."'";
			$mysql = mysqli_query($db, $sql);
			if($raplayer = $mysql->fetch_object()){
				if($raplayer->rights=='2'){
					$control->client->query('GetDetailedPlayerInfo', $ralogin);
					$ranick = $control->client->getResponse();
					$ranick = $ranick['NickName'];
					$sql = "DELETE FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $ralogin)."'";
					$mysql = mysqli_query($db, $sql);
					$control->client->query('ChatSendServerMessage', $settings['Color_RemoveAdmin'].'-> '.$Admin_Rank.' '.$CommandAuthor['NickName'].'$z$s'.$settings['Color_RemoveAdmin'].' removed Admin '.$ranick);
				}
				else $control->chat_message_player($ralogin.'isn\'t an Admin!', $CommandAuthor['Login']);
			}
			else $control->chat_message_player('$f90Invalid login!', $CommandAuthor['Login']);
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/rmop'){
		if($remove_op==true){
			$ralogin = trim($Command[1]);
			$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $ralogin)."'";
			$mysql = mysqli_query($db, $sql);
			if($raplayer = $mysql->fetch_object()){
				if($raplayer->rights=='1'){
					$control->client->query('GetDetailedPlayerInfo', $ralogin);
					$ranick = $control->client->getResponse();
					$ranick = $ranick['NickName'];
					$sql = "DELETE FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $ralogin)."'";
					$mysql = mysqli_query($db, $sql);
					$control->client->query('ChatSendServerMessage', $settings['Color_RemoveAdmin'].'-> '.$Admin_Rank.' '.$CommandAuthor['NickName'].'$z$s'.$settings['Color_RemoveAdmin'].' removed Operator '.$ranick);
				}
				else $control->chat_message_player($ralogin.'isn\'t a Operator!', $CommandAuthor['Login']);
			}
			else $control->chat_message_player('$f90Invalid login!', $CommandAuthor['Login']);
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/servername'){
		if($set_servername==true){
			$new_servername = str_replace('/servername ', '', $PlayerChat[2]);
			$control->client->query('SetServerName', $new_servername);
			$color_newservername = $settings['Color_NewServername'];
			$control->client->query('ChatSendServerMessage', $color_newservername.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_newservername.'sets the Servername to $fff'.$new_servername.'$z$s '.$color_newservername.'!');
			echo'-->'.$CommandAuthor['Login'].' sets the Servername to '.$new_servername.nz;
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/servercomment'){
		if($set_servercomment==true){
			$new_servername = str_replace('/servercomment ', '', $PlayerChat[2]);
			$control->client->query('SetServerComment', $new_servername);
			$color_newservername = $settings['Color_NewServername'];
			$control->client->query('ChatSendServerMessage', $color_newservername.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_newservername.'sets the Servercomment to $fff'.$new_servername.'$z$s '.$color_newservername.'!');
			echo'-->'.$CommandAuthor['Login'].' sets the Servercomment to '.$new_servername.nz;
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/adplayers'){
		$control->show_playerlist($CommandAuthor['Login'], true, 0);
	}
	elseif($Command[0]=='/reboot'){
		if($reboot_script==true){
			$control->FoxControl_reboot();
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/skip'){
		if($skip_challenge==true){
			$control->challenge_skip();
			$control->client->query('ChatSendServerMessage', '$f90-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s $f90skipped the challenge!');
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/restart' OR $Command[0]=='/res'){
		if($restart_challenge==true){
			global $chall_restarted_admin;
			$chall_restarted_admin = true;
			$control->client->query('RestartChallenge');
			$control->client->query('ChatSendServerMessage', '$f90-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s $f90restarted the challenge!');
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/endround'){
		if($force_end_round==true){
			$control->client->query('ForceEndRound');
			$control->client->query('ChatSendServerMessage', '$f90-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s $f90forced round end!');
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/su'){
		if(function_exists('ap_playerconnect')) ap_playerconnect($control, $CommandAuthor);
	}
	elseif($Command[0]=='/coppers'){
		$control->client->query('GetServerCoppers');
		$coppers = $control->client->getResponse();
		$control->client->query('ChatSendServerMessageToLogin', '$ee0-> $0f0This Server has $fff'.$coppers.'$0f0 Coppers', $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/pay'){
		if($admin_pay==true){
			$coppers_tp = trim($Command[1]);
			$coppers_tl = trim($Command[2]);
			if(is_numeric($coppers_tp)!==true){
				$control->chat_message_player('$f00The number of the Coppers to pay must be an integer!', $CommandAuthor['Login']);
			}
			elseif(trim($coppers_tl)==''){
				$control->chat_message_player('$f00No login set to pay the Coppers!', $CommandAuthor['Login']);
			}
			else{
				
				$pay_message = $CommandAuthor['NickName'].'$z$s payed '.$coppers_tp.' to you from the Server '.$settings['ServerName'].'$z$s !';
				$login = trim($copperst_tl);
				$coppers = $copperst_tp;
				settype($coppers,'integer');
				$control->client->query('Pay', $login, $coppers, $pay_message);
				$control->chat_message($Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s $0f0payed $fff'.$coppers_tp.'$0f0 Coppers to $fff'.$coppers_tl.'$0f0!', $CommandAuthor['Login']);
				
			}
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/add' AND $Command[1]=='track'){
		
		if($admin_add_track==true){
			$tmxid = $Command[2];
			if(isset($Command[3])){
				$tmxsection = $Command[3];
				if($tmxsection=='tmn') $tmxsection = 'nations';
				elseif($tmxsection=='tmnf') $tmxsection = 'tmnforever';
				elseif($tmxsection=='tms') $tmxsection = 'sunrise';
				elseif($tmxsection=='tmo') $tmxsection = 'original';
				else $tmxsection = 'united';
			}
			else $tmxsection = 'united';
			if(trim($tmxid)!=='' AND is_numeric($tmxid)){
				$url = 'http://'.$tmxsection.'.tm-exchange.com/get.aspx?action=trackgbx&id='.trim($tmxid); 
				$file = fopen($url, "rb");
				$content = stream_get_contents($file);
				fclose($file);
				if(!empty($content))
				{
					$url = 'http://'.$tmxsection.'.tm-exchange.com/apiget.aspx?action=apisearch&trackid='.trim($tmxid); 
					$file = fopen($url, "rb");
					$trackdata = stream_get_contents($file);
					fclose($file);
					$trackdata = explode("\t",$trackdata);
					if(isset($trackdata[1])){
						$control->client->query('GetTracksDirectory');
						$trackdir = $control->client->getResponse();
						$trackname = $trackdata[1];
						$trackname = str_replace('#', '_', $trackname);
						$trackname = str_replace('=', '_', $trackname);
						$trackname = str_replace('+', '_', $trackname);
						$trackname = str_replace('~', '_', $trackname);
						$trackname = str_replace('^', '_', $trackname);
						$trackname = str_replace('`', '_', $trackname);
						$trackname = str_replace(',', '_', $trackname);
						$trackname = str_replace('.', '_', $trackname);
						$write = fopen($trackdir.'Challenges/'.$trackname.'.Challenge.GBX', "w");
						fwrite($write, $content);
						fclose($write);
						$control->client->query('InsertChallenge', 'Challenges/'.$trackname.'.Challenge.GBX');
						$control->client->query('ChatSendServerMessage', '$0f0-> '.$Admin_Rank.' '.$CommandAuthor['NickName'].'$z$s$0f0 added $fff'.$trackdata[1].'$0f0 from TMX! Section: $fff'.$tmxsection.'$0f0 TMX-ID:$fff '.$trackdata[0]);
						if(function_exists('write_challenges')) write_challenges($control);
					}
					else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> Invalid TMX ID or wrong TMX section!', $CommandAuthor['Login']);
				}
				else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> Invalid TMX ID or wrong TMX section or TMX is down!', $CommandAuthor['Login']);
			}
			else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> Invalid TMX ID!', $CommandAuthor['Login']);
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/delete' AND $Command[1]=='track'){
		if($admin_delete_track==true){
			$trackid = $Command[2];
			if($trackid!==''){
				if(is_numeric($trackid)){ //Delete from id
					$trackid--;
					global $challenges;
					if(isset($challenges)){
						$remove_chall = $challenges[$trackid];
						$control->client->query('ChatSendServerMessage', '$0f0-> '.$Admin_Rank.' '.$CommandAuthor['NickName'].'$z$s$0f0 removed $fff'.$remove_chall['Name'].'$z$s$0f0!');
						$control->client->query('RemoveChallenge', $remove_chall['FileName']);
						if(function_exists('write_challenges')) write_challenges($control);
						
					}
					else $control->client->query('ChatSendServerMessageToLogin', '$f00$o->$o plugin.challenges.php isn\'t enabled!', $CommandAuthor['Login']);
					
				}
				elseif($trackid=='current'){ //Delete current Track
					$control->client->query('GetCurrentChallengeInfo');
					$remove_chall = $control->client->getResponse();
					$control->client->query('ChatSendServerMessage', '$0f0-> '.$Admin_Rank.' '.$CommandAuthor['NickName'].'$z$s$0f0 removed $fff'.$remove_chall['Name'].'$z$s$0f0!');
					$control->client->query('RemoveChallenge', $remove_chall['FileName']);
					if(function_exists('write_challenges')) write_challenges($control);
				}
				else $control->client->query('ChatSendServerMessageToLogin', '$f00$o->$o invalid Track-ID!', $CommandAuthor['Login']);
			}
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
	}
	//End of commands
}
}


//Manialinks
function adminchat_mlanswer($control, $ManialinkPageAnswer){


	/********************************/
	/**** FOR PLUGIN.PLAYERS.PHP ****/
	/********************************/
	
	if($ManialinkPageAnswer[2]=='4000'){
		$control->close_ml('4000', $ManialinkPageAnswer[1]);
	}
	
	if($ManialinkPageAnswer[2]=='11'){
		$control->close_ml('10', $ManialinkPageAnswer[1]);
	}
	
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
		
		
		
	
		if($ManialinkPageAnswer[2]>=250 AND $ManialinkPageAnswer[2]<=500){
			if($kick==true){
				$control->client->query('GetPlayerList', 300, 0);
				$player_list = $control->client->getResponse();
				$player_list_pid = $ManialinkPageAnswer[2]-250;
				if(isset($player_list[$player_list_pid])){
					$kickedplayer = $player_list[$player_list_pid];
					$control->player_kick($kickedplayer['Login'], true, $CommandAuthor);
				}
			}
			else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
		}
		elseif($ManialinkPageAnswer[2]>500 AND $ManialinkPageAnswer[2]<=750){
			if($ignore==true){
				$control->client->query('GetPlayerList', 300, 0);
				$player_list = $control->client->getResponse();
				$player_list_pid = $ManialinkPageAnswer[2]-500;
				if(isset($player_list[$player_list_pid])){
					$ignoredplayer = $player_list[$player_list_pid];
					$control->player_ignore($ignoredplayer['Login'], true, $CommandAuthor);
					$control->show_playerlist($CommandAuthor['Login'], true, 0);
				}
			}
			else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
		}
		elseif($ManialinkPageAnswer[2]>49748 AND $ManialinkPageAnswer[2]<=49999){
		if($warn==true){
			$control->client->query('GetPlayerList', 300, 0);
			$player_list = $control->client->getResponse();
			$player_list_pid = $ManialinkPageAnswer[2]-49749;
			$control->client->query('GetDetailedPlayerInfo', $player_list[$player_list_pid]['Login']);
			$warnedplayer = $control->client->getResponse();
			$color_warn = $settings['Color_Warn'];
			$control->client->query('ChatSendServerMessage', $color_warn.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_warn.'warned $fff'.$warnedplayer['NickName'].'$z$s '.$color_warn.'!');
			$control->client->query('SendDisplayManialinkPageToLogin', $warnedplayer['Login'], '<?xml version="1.0" encoding="UTF-8" ?>
			<manialink id="4000">
			<quad posn="-64 48 15" sizen="128 96" bgcolor="0006"/>
			<quad posn="-64 48 16" sizen="128 96" image="http://fox-control.de/~skpfox/warning.bik"/>
			<quad posn="0 15 18" sizen="40 21" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
			<quad posn="0 15 17" sizen="40 21" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
			<quad posn="0 24.5 19" sizen="39 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
			<label posn="0 24.25 20" textsize="2" halign="center" text="$o$f00WARNING!"/>
			<label posn="-18 20.75 20" textsize="2" sizen="36 2" autonewline="1" text="This is an administrator warning!'.nz.'What ever you wrote or made is against our server rights.'.nz.'An administrator or Operator can kick or ban you next time!'.nz.'Be fair."/>
			<quad posn="15.75 24.5 20" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="4000"/>
			</manialink>', 0, false);
		}
		else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $CommandAuthor['Login']);
		}
		elseif($ManialinkPageAnswer[2]>=50000 AND $ManialinkPageAnswer[2]<=50250){
			if($ban==true){
				$control->client->query('GetPlayerList', 300, 0);
				$player_list = $control->client->getResponse();
				$player_list_pid = $ManialinkPageAnswer[2]-50000;
				$control->client->query('GetDetailedPlayerInfo', $player_list[$player_list_pid]['Login']);
				$bannedplayer = $control->client->getResponse();
				$control->client->query('ChatSendServerMessage', $color_ban.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_ban.'banned $fff'.$bannedplayer['NickName'].$color_ban.' !');
				$control->client->query('Ban', $bannedplayer['Login']);
			}
			else $control->client->query('ChatSendServerMessageToLogin', '$f00$o-> '.$text_false_rights, $Command[1]);
		}
		
		
		/*   FOR PLUGIN PLUGIN.PLAYERS.PHP !!   */
		
		//Normal players
		elseif($ManialinkPageAnswer[2] >= 5251 AND $ManialinkPageAnswer[2] <= 5270){
			//
			$id = $ManialinkPageAnswer[2] - 5251;
			$id = $id * 14;
			$nextid = $ManialinkPageAnswer[2] + 1;
			$previd = $ManialinkPageAnswer[2] - 1;
			$control->client->query('GetPlayerList', 300, 0);
			$player_list = $control->client->getResponse();
			$curr_pid = $id;
			$curr_pid2 = 0;
			$curr_y = '20';
			$playerarray = array();
			while(isset($player_list[$curr_pid])){
				
				$curr_ml_id = $playerlist_mlid+$curr_pid;
				$curr_pdata = $player_list[$curr_pid];
				$curr_nick = $curr_pdata['NickName'];
				$curr_login = $curr_pdata['Login'];
				$curr_ladder = $curr_pdata['LadderRanking'];
				
				$url = "http://fox-control.de/~skpfox/scripts/get_data.php?login=".trim($curr_login).""; 
				$file = fopen($url, "rb");
				$pl_content = stream_get_contents($file);
				$file = fclose($file);
				$pl_content = explode('{expl}', $pl_content);
				$playerarray[] = '<label posn="-34 '.$curr_y.' 4" text="'.htmlspecialchars($curr_nick).'" sizen="15 2" textsize="2"/>
				<label posn="-13 '.$curr_y.' 4" text="'.htmlspecialchars($curr_login).'" sizen="10 2" textsize="2"/>
				<label posn="1 '.$curr_y.' 4" text="'.$curr_ladder.'" sizen="10 2" textsize="2"/>
				<label posn="12 '.$curr_y.' 4" text="SKP:" textsize="2"/>
				<label posn="16 '.$curr_y.' 4" text="'.$pl_content[0].'" textsize="2" sizen="15 2"/>
				<label posn="23 '.$curr_y.' 4" text="LVL:" textsize="2"/>
				<label posn="26.5 '.$curr_y.' 4" text="'.$pl_content[1].'" textsize="2" sizen="15 2"/>
				<quad posn="-3.25 '.$curr_y.' 4" sizen="3 2.5" style="Icons128x128_1" substyle="LadderPoints"/>';
				if($curr_pid2==13) break;
				$curr_pid++;
				$curr_pid2++;
				$curr_y = $curr_y-2.5;
			}
			if(isset($playerarray[14])) $nextarrow = '<quad posn="31.75 -12 20" sizen="3 3" style="Icons64x64_1" substyle="ArrowNext" action="'.$nextid.'"/>';
			else $nextarrow = '';
			if($id!==0) $prevarrow = '<quad posn="-34.75 -12 20" sizen="3 3" style="Icons64x64_1" substyle="ArrowPrev" action="'.$previd.'"/>';
			else $prevarrow = '';
			$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
			<manialink id="10">
			<quad posn="0 5 1" sizen="70 41" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
			<quad posn="0 5 0" sizen="70 41" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
			<quad posn="0 24.5 3" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
			<label posn="-34 24.25 4" textsize="2" text="$o$09fCurrent Players:"/>
			<quad posn="31.75 24.5 4" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="11"/>
			'.$playerarray[0].'
			'.$playerarray[1].'
			'.$playerarray[2].'
			'.$playerarray[3].'
			'.$playerarray[4].'
			'.$playerarray[5].'
			'.$playerarray[6].'
			'.$playerarray[7].'
			'.$playerarray[8].'
			'.$playerarray[9].'
			'.$playerarray[10].'
			'.$playerarray[11].'
			'.$playerarray[12].'
			'.$playerarray[13].'
			'.$nextarrow.'
			'.$prevarrow.'
			</manialink>', 0, false);
		}
		//Admins
		elseif($ManialinkPageAnswer[2] >= 5271 AND $ManialinkPageAnswer[2] <= 5290){
			$id = $ManialinkPageAnswer[2] - 5271;
			$id = $id * 14;
			$nextid = $ManialinkPageAnswer[2] + 1;
			$previd = $ManialinkPageAnswer[2] - 1;
			$curr_pid = $id;
			$curr_pid2 = 0;
			$curr_y = '20';
			$playerarray = array();
			$control->client->query('GetIgnoreList', 1000, 0);
			$ignore_list = $control->client->getResponse();
			$control->client->query('GetPlayerList', 300, 0);
			$player_list = $control->client->getResponse();
			while(isset($player_list[$curr_pid])){
				
				$curr_ml_id = $playerlist_mlid+$curr_pid;
				$curr_pdata = $player_list[$curr_pid];
				$curr_nick = $curr_pdata['NickName'];
				$curr_login = $curr_pdata['Login'];
				$curr_kick_id = 250+$curr_pid;
				$curr_ignore_id = 0;
				$curr_warn_id = 49749+$curr_pid;
				$curr_ban_id = 50000+$curr_pid;
				$curr_y_2 = $curr_y-0.25;
				$player_in_ignore_list = false;
				
				while(isset($ignore_list[$curr_ignore_id])){
					if($ignore_list[$curr_ignore_id]['Login'] == trim($curr_login)){
						$player_in_ignore_list = true;
						break;
					}
					$curr_ignore_id++;
				}
				if($player_in_ignore_list==true){
					$curr_ignore_text = 'UnIgnore';
				}
				else{
					$curr_ignore_text = 'Ignore';
				}
				$curr_ignore_id = 500+$curr_pid;
				
				$playerarray[] = '<label posn="-34 '.$curr_y.' 4" text="'.htmlspecialchars($curr_nick).'" sizen="15 2" textsize="2"/>
				<label posn="-13 '.$curr_y.' 4" text="'.htmlspecialchars($curr_login).'" sizen="10 2" textsize="2"/>
				<quad posn="0 '.$curr_y.' 4" sizen="7 2.5" style="Bgs1" substyle="NavButtonBlink" action="'.$curr_kick_id.'"/>
				<label posn="3.5 '.$curr_y_2.' 4" sizen="7 2.5" halign="center" style="TextPlayerCardName" textsize="2" text="$fff$oKick" action="'.$curr_kick_id.'"/>
				<quad posn="8 '.$curr_y.' 4" sizen="7 2.5" style="Bgs1" substyle="NavButtonBlink" action="'.$curr_ignore_id.'"/>
				<label posn="11.5 '.$curr_y_2.' 4" sizen="7 2.5" halign="center" style="TextPlayerCardName" textsize="2" text="$fff$o'.$curr_ignore_text.'" action="'.$curr_ignore_id.'"/>
				<quad posn="16 '.$curr_y.' 4" sizen="7 2.5" style="Bgs1" substyle="NavButtonBlink" action="'.$curr_warn_id.'"/>
				<label posn="19.5 '.$curr_y_2.' 4" sizen="7 2.5" halign="center" style="TextPlayerCardName" textsize="2" text="$fff$oWarn" action="'.$curr_warn_id.'"/>
				<quad posn="24 '.$curr_y.' 4" sizen="7 2.5" style="Bgs1" substyle="NavButtonBlink" action="'.$curr_ban_id.'"/>
				<label posn="27.5 '.$curr_y_2.' 4" sizen="7 2.5" halign="center" style="TextPlayerCardName" textsize="2" text="$fff$oBan" action="'.$curr_ban_id.'"/>';
				if($curr_pid2==13) break;
				$curr_pid++;
				$curr_pid2++;
				$curr_y = $curr_y-2.5;
			}
			if(isset($playerarray[14])) $nextarrow = '<quad posn="31.75 -12 4" sizen="3 3" style="Icons64x64_1" substyle="ArrowNext" action="'.$nextid.'"/>';
			else $nextarrow = '';
			if($id!==0) $prevarrow = '<quad posn="-34.75 -12 4" sizen="3 3" style="Icons64x64_1" substyle="ArrowPrev" action="'.$previd.'"/>';
			else $prevarrow = '';
			$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
			<manialink id="10">
			<quad posn="0 5 1" sizen="70 41" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
			<quad posn="0 5 0" sizen="70 41" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
			<quad posn="0 24.5 3" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
			<label posn="-34 24.25 4" textsize="2" text="$o$09fCurrent Players:"/>
			<quad posn="31.75 24.5 4" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="11"/>
			'.$playerarray[0].'
			'.$playerarray[1].'
			'.$playerarray[2].'
			'.$playerarray[3].'
			'.$playerarray[4].'
			'.$playerarray[5].'
			'.$playerarray[6].'
			'.$playerarray[7].'
			'.$playerarray[8].'
			'.$playerarray[9].'
			'.$playerarray[10].'
			'.$playerarray[11].'
			'.$playerarray[12].'
			'.$playerarray[13].'
			'.$nextarrow.'
			'.$prevarrow.'
			</manialink>', 0, false);
		}

	}

}
?>