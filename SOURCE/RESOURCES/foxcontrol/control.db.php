<?php

// FoxControl
// Copyright 2010 - 2011 by FoxRace, http://www.fox-control.de

//* control.php - Main file
//* Version:   0.9.2
//* Coded by:  cyrilw, libero6, matrix142
//* Copyright: FoxRace, http://www.fox-control.de



require_once('include/GbxRemote.inc.php');
require_once('include/foxcontrol.window.php');
define('nz', "\r\n");
define('FOXC_VERSION', '0.9.2');
define('FOXC_VERSIONP', 'Beta Version');
$started = 'false';



 function console($console){
	if(trim($console) == '') return;
	$ct = explode("\n", $console);
	for($i = 0; isset($ct[$i]); $i++)
	{
		echo'['.date('d.m.y H:i:s').'] '.$ct[$i].nz;
	}
	if(file_exists('logs/'.date('d.m.Y').'.log')){
		$log = file_get_contents('logs/'.date('d.m.Y').'.log');
		$log = $log.nz.'['.date('d.m.y H:i:s').'] '.$console;
		file_put_contents('logs/'.date('d.m.Y').'.log', $log);
	}
	else{
		$newdate = date('d.m.Y');
		file_put_contents('logs/'.$newdate.'.log', '['.date('d.m.y H:i:s').'] '.$console);
		chmod ('./logs/'.$newdate.'.log', 0777);
	}
}

class control {
	
	
	
	
	public function run() {
		global $control;
		$control = $this;
		$defaultcolor = '07b';
		$newline = "\n";
    
		
		console('Starting [FOX CONTROL]');
		
		$this->client = New IXR_Client_Gbx;
		
		//Initialize the config file
		console('Initialize the Config file..');
		global $settings;
		$settings = array();
		$xml = @simplexml_load_file('config.xml');
		$settings['Port'] = $xml->port;
		$settings['ServerPW'] = $xml->SuperAdminPW;
		$settings['ServerLogin'] = $xml->serverlogin;
		$settings['ServerPassword'] = $xml->serverpassword;
		$settings['CommunityCode'] = $xml->community_code;
		$settings['AdminTMLogin'] = $xml->YourTmLogin;
		$settings['ServerLocation'] = $xml->ServerLocation;
		$settings['Nation'] = $xml->nation;
		$settings['DB_Path'] = $xml->db_path;
		$settings['DB_User'] = $xml->db_user;
		$settings['DB_PW'] = $xml->db_passwd;
		$settings['DB_Name'] = $xml->db_name;
		$settings['Name_SuperAdmin'] = $xml->name_superadmin;
		$settings['Name_Admin'] = $xml->name_admin;
		$settings['Name_Operator'] = $xml->name_operator;
		$settings['Text_wrong_rights'] = $xml->text_false_rights;
		$settings['StartWindow'] = $xml->startwindow;
		$settings['Text_StartWindow'] = $xml->startwindowtext;
		$settings['Message_PlayerConnect'] = $xml->player_message_connect;
		$settings['Message_PlayerLeft'] = $xml->player_message_left;
		$settings['Color_Default'] = $xml->default_color;
		$settings['Color_Kick'] = $xml->color_kick;
		$settings['Color_Warn'] = $xml->color_warn;
		$settings['Color_Ban'] = $xml->color_ban;
		$settings['Color_UnBan'] = $xml->color_unban;
		$settings['Color_Ignore'] = $xml->color_ignore;
		$settings['Color_SetPW'] = $xml->color_setpw;
		$settings['Color_NewServername'] = $xml->color_newservername;
		$settings['Color_NewAdmin'] = $xml->color_newadmin;
		$settings['Color_RemoveAdmin'] = $xml->color_removeadmin;
		$settings['Color_Join'] = $xml->color_join;
		$settings['Color_Left'] = $xml->color_left;
		$settings['Color_OpConnect'] = $xml->color_op_connect;
		$settings['Color_AdminConnect'] = $xml->color_admin_connect;
		$settings['Color_SuperAdminConnect'] = $xml->color_superadmin_connect;
		$settings['UI_ScoreTable'] = $xml->default_scoretable_enabled;
		$settings['UI_ChallengeInfo'] = $xml->default_challenge_info_enabled;
		$settings['UI_Notice'] = $xml->notice_enabled;
		$settings['menu_name'] = $xml->menu_name;
		$settings['shortcut1_name'] = $xml->shortcut1_name;
		$settings['shortcut2_name'] = $xml->shortcut2_name;
		$settings['shortcut3_name'] = $xml->shortcut3_name;
		$settings['shortcut1'] = $xml->shortcut1;
		$settings['shortcut2'] = $xml->shortcut2;
		$settings['shortcut3'] = $xml->shortcut3;
		
		//Timezone
		date_default_timezone_set($settings['ServerLocation']);
		
		If (!$this->connect('127.0.0.1', $settings['Port'],  'SuperAdmin', $settings['ServerPW'])) {
			die('ERROR: Connection canceled!' . nz);
		} else {
		
		$defaultcolor = '07b';
		$newline = "\n";
		$this->client->query('GetServerName');
		$this->foxc_update_servername();
		$this->check_servermode();
		$this->client->query('SendHideManialinkPage');
		$this->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="1">
		<quad posn="0 43 0" sizen="30 3" style="Bgs1" halign="center" substyle="NavButton" action="0"/>
		<label text="$06fF$fffox$06fC$fffontrol$z$fff is starting.." halign="center" posn="0 42.7 1" sizen="30 3" />
		</manialink>', 0, False);
		console('[FOX CONTROL] Is now running.'.nz.nz.'PHP-Version: '.phpversion().''.$newline.'PHP-OS: '. PHP_OS .nz);
		console('-->Connecting to the database..');
		//Connect to database
		global $db;
		$db = mysqli_connect($settings['DB_Path'], $settings['DB_User'], $settings['DB_PW']);
		if(!mysqli_select_db($db, $settings['DB_Name'])) die('[ERROR] Can\'t connect to the database!');
		console('-->Connected!');
		global $FoxControl_Reboot;
		$FoxControl_Reboot = false;
		
		//Create superadminacc
		if(trim($settings['AdminTMLogin'])!=='' AND trim($settings['AdminTMLogin']!=='YourLogin')){
			$sql = "INSERT INTO `admins` (id, playerlogin, rights) VALUES ('', '".$settings['AdminTMLogin']."', '3')";
			$mysql = mysqli_query($db, $sql);
			$atmlfile = file('config.xml');
			file_put_contents('config.xml', str_replace('<YourTmLogin>'.$settings['AdminTMLogin'].'</YourTmLogin>', '', $atmlfile));
		}
		
		$this->client->query('GetServerName');
		$servername = $this->client->getResponse();
		$this->client->query('ChatSendServerMessage', '$cc0$o$06f********************************'.$newline.'$z$fff$sWelcome on '.$settings['ServerName'].'$z$s $fff!'.$newline.'This Server is running with $o$06fF$fffox$06fC$fffontrol$z'.$newline.'$s$fff'.FOXC_VERSIONP.': '.FOXC_VERSION.'$z'.$newline.'$cc0$o$s$06f********************************');
	
		console('-->Enable Callbacks');
		if (!$this->client->query('EnableCallbacks', true)) {
			console('[Error ' . $this->client->getErrorCode() . '] ' . $this->client->getErrorMessage());
			die('[Error] Cant\'t enable callbacks!');
		} else {
			console('-->Callbacks enabled' . nz);
		}
		
		//Callbacks
		global $_CB_BC, $_CB_BR, $_CB_EC, $_CB_ER, $_CB_CP, $_CB_CH, $_CB_PC, $_CB_PD, $_CB_SU, $_CB_PF, $_CB_ES, $_CB_MA, $_CB_BU;
		$_CB_BC = array(); //BeginChallenge
		$_CB_BR = array(); //BeginRace
		$_CB_EC = array(); //EndChallenge
		$_CB_ER = array(); //EndRace
		$_CB_CP = array(); //CheckPoints
		$_CB_CH = array(); //Chat
		$_CB_PC = array(); //PlayerConnect
		$_CB_PD = array(); //PlayerDisconnect
		$_CB_SU = array(); //StartUp
		$_CB_PF = array(); //PlayerFinish
		$_CB_ES = array(); //EverySecond
		$_CB_MA = array(); //ManialinkPageAnswer
		$_CB_BU = array(); //BillUpdate
		
		
		global $fc_custom_ui, $fc_active_plugins;
		$fc_custom_ui = array();
		$fc_active_plugins = array();
		
		//Load Plugins
		$xml = @simplexml_load_file('plugins.xml');
		$plugin_id = 0;
		while(isset($xml->plugin[$plugin_id])){
			console('-->Load plugin '.trim($xml->plugin[$plugin_id]).' ['.$plugin_id.']');
			if(file_exists('plugins/'.trim($xml->plugin[$plugin_id]).''))
			{
				require('plugins/'.trim($xml->plugin[$plugin_id]).'');
				$fc_active_plugins[] = trim($xml->plugin[$plugin_id]);
			}
			else die('[ERROR] Can\'t load plugin \''.trim($xml->plugin[$plugin_id]).'\'');
			$plugin_id++;
		}
		$this->client->query('SendHideManialinkPage');
		console('-->Enable custom_ui..');
		$this->custom_ui();
		console('-->Custom_ui enabled!');
		global $window;
		$window = new window();
		$this->Event_StartUp();
		$this->FoxControl();
		}
	}
	
	/*FUNCTIONS*/
	
	public function custom_ui(){
		global $settings;
		if($settings['UI_ScoreTable']=='false'){
			$custom_ui_score = '<scoretable visible="false"/>';
		}
		else{
			$custom_ui_score = '<scoretable visible="true"/>';
		}
		if($settings['UI_ChallengeInfo']=='false'){
			$custom_ui_challinfo = '<challenge_info visible="false"/>';
		}
		else{
			$custom_ui_challinfo = '<challenge_info visible="true"/>';
		}
		if($settings['UI_Notice']=='false'){
			$custom_notice = '<notice visible="false"/>';
		}
		else{
			$custom_notice = '<notice visible="true"/>';
		}
		$this->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="1">
		</manialink>
		<custom_ui>
		'.$custom_ui_score.'
		'.$custom_ui_challinfo.'
		'.$custom_notice.'
		</custom_ui>', 0, false);
	}
	
	public function foxc_update_servername(){
		global $settings;
		$this->client->query('GetServerName');
		$settings['ServerName'] = $this->client->getResponse();
	}
	
	public function check_servermode(){
		$this->client->query('GetCurrentGameInfo', 1);
		$gameinfos = $this->client->getResponse();
		if($gameinfos['GameMode']!==1){
			$this->chat_message('$f00FoxControl is only configured for TIMEATTACK. An update with other GameModes will coming soon.'.nz.'FoxControl is now shuting down..');
			die();
		}
	}
	
	public function pluginIsActive($pluginName) {
		global $fc_active_plugins;
		for($i = 0; $i < count($fc_active_plugins); $i++) {
			if($fc_active_plugins[$i] == $pluginName) return true;
		}
		return false;
	}
	
	public function unban($unban_player, $unbanmessage, $CommandAuthor, $ubplayer){
		global $settings;
		$this->client->query('UnBan', $unban_player);
		global $db;
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".$CommandAuthor['Login']."'";
		$mysql = mysqli_query($db, $sql);
		if($admin_rights = $mysql->fetch_object()){
			if($admin_rights->rights==1){
				$Admin_Rank = $settings['Name_Operator'];
			}
			elseif($admin_rights->rights==2){
				$Admin_Rank = $settings['Name_Admin'];
			}
			elseif($admin_rights->rights==3){
				$Admin_Rank = $settings['Name_SuperAdmin'];
			}
			else $Admin_Rank = '';
		
		
		}
		else $Admin_Rank = '';
		if(!isset($Unbanned_player['NickName'])) $Unbanned_player['NickName'] = $unban_player;
		$color_unban = $settings['Color_UnBan'];
		$this->client->query('ChatSendServerMessage', $color_unban.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_unban.'unbanned $fff'.$ubplayer->nickname.'$z$s '.$color_unban.'!');
	}
	
	public function playerconnect($connected_player){
		$this->custom_ui();
		global $db, $settings;
		
		$foxcontrol->version = FOXC_VERSION;
		$foxcontrol->versionpraefix = FOXC_VERSIONP;
		$this->client->query('GetServerName');
		$servername = $this->client->getResponse();
		$newline = "\n";
		$this->client->query('GetDetailedPlayerInfo', $connected_player[0]);
		$connectedplayer = $this->client->getResponse();
	
	
		if($connectedplayer['Login']=='cyrilw' OR $connectedplayer['Login']=='libero6' OR $connectedplayer['Login']=='jensoo7' OR $connectedplayer['Login']=='matrix142'){
			$player_joincolor = '$06f';
			$player_rank = '$oF$fffox'.$player_joincolor.'T$fffeam '.$player_joincolor.'M$fffember$o';
		}
		else{
			$sql = "SELECT * FROM `admins` WHERE playerlogin = '".$connectedplayer['Login']."'";
			$mysql = mysqli_query($db, $sql);
			if($player_rank = $mysql->fetch_object()){
				if($player_rank->rights==1){
					$player_rank = $settings['Name_Operator'];
					$player_joincolor = $settings['Color_OpConnect'];
				}
				elseif($player_rank->rights==2){
					$player_rank = $settings['Name_Admin'];
					$player_joincolor = $settings['Color_AdminConnect'];
				}
				elseif($player_rank->rights==3){
					$player_rank = $settings['Name_SuperAdmin'];
					$player_joincolor = $settings['Color_SuperAdminConnect'];
				}
			}
			else{
				$player_rank = 'Player';
				$player_joincolor = '';
			}
		}
	
		$color_join = $settings['Color_Join'];
		if($settings['Message_PlayerConnect']==true) $this->client->query('ChatSendServerMessage', $color_join.'New '.$player_joincolor.$player_rank.' $fff'.$connectedplayer['NickName'].'$z$s'.$color_join.' from $fff'.str_replace('World|', '', $connectedplayer['Path']).' '.$color_join.'connected!');
		$this->client->query('ChatSendServerMessageToLogin', '$fffWelcome '.$connectedplayer['NickName'].'$z$fff$s on '.$servername.$newline.'$z$s$fffThis Server is running with $o$06fF$fffox$06fC$fffontrol$z$fff$s ('.$foxcontrol->versionpraefix.': '.$foxcontrol->version.' )'.$newline.'$oHave fun!', $connected_player[0]);  
		console('New '.str_replace('$o', '', $player_rank).' ' . $connected_player[0]  . ' connected! IP: '.$connectedplayer['IPAddress'].'');
		$sql = "SELECT * FROM `players` WHERE playerlogin = '".$connectedplayer['Login']."'";
		$mysql = mysqli_query($db, $sql);
		if(!$mysql->fetch_object()){
			$sql = "INSERT INTO `players` (id, playerlogin, nickname, lastconnect) VALUES ('', '".$connectedplayer['Login']."', '".mysqli_real_escape_string($db, $connectedplayer['NickName'])."', '".time()."')";
			$mysql = mysqli_query($db, $sql);
		}
		else{
			$sql = "UPDATE `players` SET nickname = '".mysqli_real_escape_string($db, $connectedplayer['NickName'])."' WHERE playerlogin = '".$connectedplayer['Login']."'";
			$mysql = mysqli_query($db, $sql);
			$sql = "UPDATE `players` SET lastconnect = '".time()."' WHERE playerlogin = '".$connectedplayer['Login']."'";
			$mysql = mysqli_query($db, $sql);
		}
		
		//Create welcome window
		if($settings['StartWindow'] == true)
		{
			global $window;
			$window->init();
			$window->title('$fffWelcome on $z$o$fff'.$servername.'$z$fff!');
			$window->size('60', '');
			$window->close(false);
			$content = $settings['Text_StartWindow'];
			$content = str_replace('{player}', $connectedplayer['NickName'].'$z$fff', $content);
			$content = str_replace('{server}', $servername.'$z$fff', $content);
			$content = str_replace('FoxControl', '$o$06fF$fffox$06fC$fffontrol$o', $content);
			$content = explode('{newline}', $content);
			for($i = 0; isset($content[$i]); $i++)
			{
				$window->content($content[$i]);
			}
			$window->addButton('Ok', '20', true);
			$window->show($connectedplayer['Login']);
		}
	}
	
	public function playerdisconect($playerdata){
		global $db, $settings;
		
		$sql = "SELECT * FROM `players` WHERE playerlogin = '".mysqli_real_escape_string($db, $playerdata[0])."'";
		if($mysql = mysqli_query($db, $sql)){
			if($player_lastcon = $mysql->fetch_object()){
				$player_timeplayed = $player_lastcon->timeplayed;
				$player_lastcon = $player_lastcon->lastconnect;
				if($player_lastcon!=='0'){
					$player_timeplayed2 = time()-$player_lastcon;
					$player_timeplayed = $player_timeplayed+$player_timeplayed2;
					$sql = "UPDATE `players` SET timeplayed = '".mysqli_real_escape_string($db, $player_timeplayed)."' WHERE playerlogin = '".mysqli_real_escape_string($db, $playerdata[0])."'";
					$mysql = mysqli_query($db, $sql);
				}
			}
		}
		$sql = "UPDATE `players` SET lastconnect = '0' WHERE playerlogin = '".mysqli_real_escape_string($db, $playerdata[0])."'";
		if($mysql = mysqli_query($db, $sql)){
		
		}
		$disconnectedplayer = $playerdata[0];
		if($disconnectedplayer=='cyrilw' OR $disconnectedplayer=='libero6' OR $disconnectedplayer=='jensoo7' OR $connectedplayer['Login']=='matrix142'){
			$player_joincolor = '$06f';
			$player_rank = '$o'.$player_joincolor.'F$fffox'.$player_joincolor.'T$fffeam '.$player_joincolor.'M$fffember$o';
		}
		else{
			$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $disconnectedplayer)."'";
			$mysql = mysqli_query($db, $sql);
			if($player_rank = $mysql->fetch_object()){
				if($player_rank->rights==1){
					$player_rank = $settings['Name_Operator'];
					$player_joincolor = $settings['Color_OpConnect'];
				}
				elseif($player_rank->rights==2){
					$player_rank = $settings['Name_Admin'];
					$player_joincolor = $settings['Color_AdminConnect'];
				}
				elseif($player_rank->rights==3){
					$player_rank = $settings['Name_SuperAdmin'];
					$player_joincolor = $settings['Color_SuperAdminConnect'];
				}
			}
			else{
				$player_rank = 'Player';
				$player_joincolor = '';
			}
		}
		$sql = "SELECT * FROM `players` WHERE playerlogin = '".mysqli_real_escape_string($db, $disconnectedplayer)."'";
		if($mysql = mysqli_query($db, $sql)){
			if(!$disconnectedplayer2 = $mysql->fetch_object()){
				$disconnectedplayer2 = $disconnectedplayer;
			}
		}
		if($settings['Message_PlayerLeft']==true){
			$color_left = $settings['Color_Left'];
			$this->client->query('ChatSendServerMessage', $color_left.$player_rank.' $fff'.$disconnectedplayer2->nickname.'$z$s'.$color_left.' left the game!');
		}
		console('Player ' . $disconnectedplayer  . ' left the game');
	}
	
	public function formattime($time_to_format){

		//FORMAT TIME
		$formatedtime_minutes = floor($time_to_format/(1000*60));
		$formatedtime_seconds = floor(($time_to_format - $formatedtime_minutes*60*1000)/1000);
		$formatedtime_hseconds = substr($time_to_format, strlen($time_to_format)-3, 2);
		$formatedtime = sprintf('%02d:%02d.%02d', $formatedtime_minutes, $formatedtime_seconds, $formatedtime_hseconds);
	
		return $formatedtime;

	}
	
	public function formattime_hour($time_to_format){

		//FORMAT TIME
		$formatedtime_houres = floor($time_to_format/3600);
	
		return $formatedtime_houres.'h';

	}
	
	public function is_admin($player_to_check){
		global $db;
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $player_to_check)."'";
		if($mysql = mysqli_query($db, $sql)){
			if($admin_rights = $mysql->fetch_object()){
				return true;
				
			}
			else return false;
		}
		else return false;
		
	}
	
	public function get_rights($player){
		global $db;
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $player)."'";
		if($mysql = mysqli_query($db, $sql)){
			if($admin_rights = $mysql->fetch_object()){
				return $admin_rights->rights;
			}
			else return 0;
		}
		else return 0;
	}
	
	public function player_kick($player_to_kick, $kickmessage, $CommandAuthor){ //function to kick a player. The first parameter is the login of the player. The others are optional. If the secound parameter = true, then write the script a message in the chat. The third parameter is the Nickname of the player who kicked the player (only when message = true)
		$control->client = $this->client;
		global $db, $settings;
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $CommandAuthor['Login'])."'";
		$mysql = mysqli_query($db, $sql);
		if($admin_rights = $mysql->fetch_object()){
			if($admin_rights->rights==1){
				$Admin_Rank = $settings['Name_Operator'];;
			}
			elseif($admin_rights->rights==2){
				$Admin_Rank = $settings['Name_Admin'];;
			}
			elseif($admin_rights->rights==3){
				$Admin_Rank = $settings['Name_SuperAdmin'];;
			}
			else $Admin_Rank = '';
			
			
		}
			$control->client->query('GetDetailedPlayerInfo', $player_to_kick);
			$kickedplayer = $control->client->getResponse();
			if($kickmessage==true){
				$color_kick = $settings['Color_Kick'];
				$control->client->query('ChatSendServerMessage', $color_kick.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_kick.'kicked $fff'.$kickedplayer['NickName'].'$z$s '.$color_kick.'!');
			}
			$control->client->query('Kick', $kickedplayer['Login']);
		}
		
	public function player_ignore($player_to_ignore, $ignoremessage, $CommandAuthor){ //function to ignore a player. The first parameter is the login of the player. The others are optional. If the secound parameter = true, then write the script a message in the chat. The third parameter is the Nickname of the player who ignored the player (only when message = true)
		global $db, $settings;
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $CommandAuthor['Login'])."'";
		$mysql = mysqli_query($db, $sql);
		if($admin_rights = $mysql->fetch_object()){
			if($admin_rights->rights==1){
				$Admin_Rank = $settings['Name_Operator'];
			}
			elseif($admin_rights->rights==2){
				$Admin_Rank = $settings['Name_Admin'];
			}
			elseif($admin_rights->rights==3){
				$Admin_Rank = $settings['Name_SuperAdmin'];
			}
			else $Admin_Rank = '';
			
			
		}
		$this->client->query('GetDetailedPlayerInfo', $player_to_ignore);
		$ignoredplayer = $this->client->getResponse();
		$this->client->query('GetIgnoreList', 1000, 0);
		$ignore_list = $this->client->getResponse();
		$curr_ignore_id = 0;
		$player_in_ignore_list = false;
		while(isset($ignore_list[$curr_ignore_id])){
			if($ignore_list[$curr_ignore_id]['Login'] == trim($ignoredplayer['Login'])){
				$player_in_ignore_list = true;
				break;
			}
			$curr_ignore_id++;
		}
		if($player_in_ignore_list==true){
			if($ignoremessage==true){
				$color_ignore = $settings['Color_Ignore'];
				$this->client->query('ChatSendServerMessage', $color_ignore.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_ignore.'unignored $fff'.$ignoredplayer['NickName'].'$z$s '.$color_ignore.'!');
			}
			$this->client->query('UnIgnore', $ignoredplayer['Login']);
		}
		else{
			if($ignoremessage==true){
				$color_ignore = $settings['Color_Ignore'];
				$this->client->query('ChatSendServerMessage', $color_ignore.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_ignore.'ignored $fff'.$ignoredplayer['NickName'].'$z$s '.$color_ignore.'!');
			}
			$this->client->query('Ignore', $ignoredplayer['Login']);
		}
		}
	
	public function display_manialink($ml_code, $ml_id, $ml_duration, $ml_closewhenclick){
		if(isset($ml_duration) AND $ml_duration!=='' AND $ml_duration!=='0') $ml_duration = $ml_duration;
		else $ml_duration = '0';
		if(!isset($ml_closewhenclick) OR $ml_closewhenclick=='') $ml_closewhenclick = false;
		$this->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="'.$ml_id.'">
		<timeout>0</timeout>
		'.$ml_code.'
		</manialink>', $ml_duration, $ml_closewhenclick);
	}
	
	public function display_manialink_to_login($ml_code, $ml_id, $ml_duration, $ml_closewhenclick, $ml_login){
		if(isset($ml_duration) AND $ml_duration!=='' AND $ml_duration!=='0') $ml_duration = $ml_duration;
		else $ml_duration = '0';
		if(!isset($ml_closewhenclick) OR $ml_closewhenclick=='') $ml_closewhenclick = false;
		$this->client->query('SendDisplayManialinkPageToLogin', $ml_login, '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="'.$ml_id.'">
		<timeout>0</timeout>
		'.$ml_code.'
		</manialink>', $ml_duration, $ml_closewhenclick);
	}
	
	public function chat_with_nick($chat_to_write, $chat_nick){
		$this->client->query('GetDetailedPlayerInfo', $chat_nick);
		$chat_nick = $this->client->getResponse();
		$chat_nick = $chat_nick['NickName'];
		$this->client->query('ChatSendServerMessage', '$ee0['.$chat_nick.'$z$s$ee0] '.$chat_to_write);
	}
	
	public function show_playerlist($show_to_login, $admin_rights, $starting_id){
		
		if(file_exists('plugins/plugin.players.php')){
			global $p_show_to_login, $p_admin_rights, $p_starting_id;
			$p_show_to_login = $show_to_login;
			$p_admin_rights = $admin_rights;
			$p_starting_id = $starting_id;
			require('plugins/plugin.players.php');
		}
		else die('File \'plugins/plugin.players.php\' don\'t exists!');
		
	}
	
	public function close_ml($close_id, $close_to_login){
		if(isset($close_to_login) AND $close_to_login!==''){
		$this->client->query('SendDisplayManialinkPageToLogin', $close_to_login, '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="'.$close_id.'">
		</manialink>', 1, false);
		}
		else{
		$this->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="'.$close_id.'">
		</manialink>', 1, false);
		}
	}
	
	public function rgb_decode($string){
		$string = str_replace('$o', '', $string);
		$string = str_replace('$s', '', $string);
		$string = str_replace('$n', '', $string);
		$string = str_replace('$i', '', $string);
		$string = str_replace('$w', '', $string);
		$string = str_replace('$t', '', $string);
		$string = str_replace('$z', '', $string);
		$string = str_replace('$g', '', $string);
		$string = str_replace('$l', '', $string);
		$string = str_replace('$h', '', $string);
		$string = preg_replace('/\$(0|1|2|3|4|5|6|7|8|9|a|b|c|d|e|f)(0|1|2|3|4|5|6|7|8|9|a|b|c|d|e|f)(0|1|2|3|4|5|6|7|8|9|a|b|c|d|e|f)/i', '', $string);
		return $string;
	}
	
	public function chat_message($chat_message){
		$this->client->query('ChatSendServerMessage', '$ee0->'.$chat_message);
	}
	
	public function chat_message_player($chat_message, $player){
		$this->client->query('ChatSendServerMessageToLogin', '$ee0->'.$chat_message, $player);
	}
	
	public function console($console_message){
		console($console_message);
	}

	
	public function calc_time($time_to_calc){
	global $calculated_time;
	$calctime = $time_to_calc;
	$calctime_minutes = floor($calctime/(1000*60));
	$calctime_seconds = floor(($calctime - $calctime_minutes*60*1000)/1000);
	$calctime_hseconds = substr($calctime, strlen($calctime)-3, 2);
	$calculated_time = sprintf('%02d:%02d.%02d', $calctime_minutes, $calctime_seconds, $calctime_hseconds);
	}
	
	/************************
	*********EVENTS**********
	************************/
	public function RegisterEvent($EventName, $FunctionName){
	global $_CB_BC, $_CB_BR, $_CB_EC, $_CB_ER, $_CB_CP, $_CB_CH, $_CB_PC, $_CB_PD, $_CB_SU, $_CB_PF, $_CB_ES, $_CB_MA, $_CB_BU;
	//BC -> BeginChallenge
	//BR -> BeginRace
	//EC -> EndChallenge
	//ER -> EndRace
	//CP -> CheckPoint
	//CH -> Chat
	//PC -> PlayerConnect
	//PD -> PlayerDisconnect
	//SU -> StartUp
	//PF -> PlayerFinish
	//ES -> EverySecond
	//MA -> ManialinkPageAnswer
	//BU -> BillUpdate
	
	if($EventName=='BeginChallenge'){
		$_CB_BC[] = $FunctionName;
	}
	elseif($EventName=='BeginRace'){
		$_CB_BR[] = $FunctionName;
	}
	elseif($EventName=='EndChallenge'){
		$_CB_EC[] = $FunctionName;
	}
	elseif($EventName == 'EndRace'){
		$_CB_ER[] = $FunctionName;
	}
	elseif($EventName=='CheckPoint'){
		$_CB_CP[] = $FunctionName;
	}
	elseif($EventName=='Chat'){
		$_CB_CH[] = $FunctionName;
	}
	elseif($EventName=='PlayerConnect'){
		$_CB_PC[] = $FunctionName;
	}
	elseif($EventName=='PlayerDisconnect'){
		$_CB_PD[] = $FunctionName;
	}
	elseif($EventName=='PlayerFinish'){
		$_CB_PF[] = $FunctionName;
	}
	elseif($EventName=='StartUp'){
		$_CB_SU[] = $FunctionName;
	}
	elseif($EventName=='ManialinkPageAnswer'){
		$_CB_MA[] = $FunctionName;
	}
	elseif($EventName=='EverySecond'){
		$_CB_ES[] = $FunctionName;
	}
	elseif($EventName=='BillUpdate'){
		$_CB_BU[] = $FunctionName;
	}
	
	}
	
	public function Event_BeginChallenge($calldata){
		$this->foxc_update_servername();
		global $_CB_BC;
		$id = 0;
		while(isset($_CB_BC[$id])){
			$_CB_BC[$id]($this, $calldata);
			$id++;
		}
	}
	
	public function Event_BeginRace($calldata){
		global $_CB_BR;
		$id = 0;
		while(isset($_CB_BR[$id])){
			$_CB_BR[$id]($this, $calldata);
			$id++;
		}
	}
	
	public function Event_EndChallenge($calldata){
		global $_CB_EC;
		$id = 0;
		while(isset($_CB_EC[$id])){
			$_CB_EC[$id]($this, $calldata);;
			$id++;
		}
	}
	
	public function Event_EndRace($calldata){
		global $_CB_ER;
		$id = 0;
		while(isset($_CB_ER[$id])){
			$_CB_ER[$id]($this, $calldata);;
			$id++;
		}
	}
	
	public function Event_CheckPoint($calldata){
		global $_CB_CP;
		$id = 0;
		while(isset($_CB_CP[$id])){
			$_CB_CP[$id]($this, $calldata);;
			$id++;
		}
	}
	
	public function Event_PlayerConnect($calldata){
		global $_CB_PC;
		$id = 0;
		while(isset($_CB_PC[$id])){
			$_CB_PC[$id]($this, $calldata);
			$id++;
		}
	}
	
	public function Event_PlayerDisconnect($calldata){
		global $_CB_PD;
		$id = 0;
		while(isset($_CB_PD[$id])){
			$_CB_PD[$id]($this, $calldata);
			$id++;
		}
	}
	
	public function Event_ManialinkPageAnswer($calldata){
		global $_CB_MA;
		$id = 0;
		while(isset($_CB_MA[$id])){
			$_CB_MA[$id]($this, $calldata);
			$id++;
		}
	}
	
	public function Event_StartUp(){
		$this->custom_ui();
		global $_CB_SU;
		$id = 0;
		while(isset($_CB_SU[$id])){
			$_CB_SU[$id]($this);
			$id++;
		}
	}
	
	public function Event_Chat($calldata){
		global $_CB_CH;
		$id = 0;
		while(isset($_CB_CH[$id])){
			$_CB_CH[$id]($this, $calldata);
			$id++;
		}
	}
	
	public function Event_PlayerFinish($calldata){
		global $_CB_PF;
		$id = 0;
		while(isset($_CB_PF[$id])){
			$_CB_PF[$id]($this, $calldata);
			$id++;
		}
	}
	
	public function Event_EverySecond(){
		global $_CB_ES;
		$id = 0;
		while(isset($_CB_ES[$id])){
			$_CB_ES[$id]($this);
			$id++;
		}
	}
	
	public function Event_BillUpdate($calldata){
		global $_CB_BU;
		$id = 0;
		while(isset($_CB_BU[$id])){
			$_CB_BU[$id]($this, $calldata);
			$id++;
		}
	}
	
	public function FoxControl_reboot(){
		global $FoxControl_Reboot;
		$this->client->query('SendHideManialinkPage');
		$FoxControl_Reboot = true;
	}
	
	public function challenge_skip(){
		$this->client->query('NextChallenge');
	}
	
	public function FoxControl(){
	
		$defaultcolor = '07b';
		$newline = "\n";
		$servername = $this->client->getResponse();
		$current_time = time();
		global $db;
	
		//Main loop
		while(true) {
			global $FoxControl_Reboot;
			if($FoxControl_Reboot==true) break;
			if($current_time!==time()) $this->Event_EverySecond();
			$current_time = time();
			if(!isset($curr_time_30sec)){
				$curr_time_30sec = time();
				$this->custom_ui();
			}
			if($curr_time_30sec<=time()-30){
				$this->custom_ui();
				$curr_time_30sec = time();
			}
			
			$this->client->readCB(1);
			$calls = $this->client->getCBResponses();
			$ManialinkPageAnswer = '';
			if (!empty($calls)) {
				foreach($calls as $call) {
					$cbname = $call[0];
					$cbdata = $call[1];
					switch($cbname) {
						case 'TrackMania.PlayerConnect': //Player Connect
						$this->client->query('GetDetailedPlayerInfo', $cbdata[0]);
						$connectedplayer = $this->client->getResponse();
						$this->playerconnect($cbdata);
						$this->Event_PlayerConnect($connectedplayer);
						break;
			
						case 'TrackMania.PlayerDisconnect': //Player Disconnect
						$this->playerdisconect($cbdata);
						$this->Event_PlayerDisconnect($cbdata);
						
						break;
			
						case 'TrackMania.PlayerManialinkPageAnswer':
						if($cbdata[2] >= 10000 && $cbdata[2] <= 10010){
							global $window;
							$window->mlAnswer($cbdata);
						}
						$this->Event_ManialinkPageAnswer($cbdata);
						break;
			
						case 'TrackMania.PlayerChat':
						$this->Event_Chat($cbdata);
						break;
			
						case 'TrackMania.BeginRace':
						$this->Event_BeginRace($cbdata);
						break;
			
						case 'TrackMania.EndRace':
						$this->Event_EndRace($cbdata);
						$TrackMania_EndRace = $cbdata;
						break;
						
						case 'TrackMania.PlayerCheckpoint':
						$this->Event_CheckPoint($cbdata);
						break;
						
						case 'TrackMania.BillUpdated':
						$this->Event_BillUpdate($cbdata);
						break;
						
						case 'TrackMania.BeginChallenge':
						$this->check_servermode();
						$this->Event_BeginChallenge($cbdata);
						break;
			
						case 'TrackMania.EndChallenge':
						global $chall_restarted_admin;
						if($chall_restarted_admin!==true){
							$this->Event_EndChallenge($cbdata);
						}
						else $chall_restarted_admin = false;
						break;
						
						case 'TrackMania.PlayerFinish':
						$this->Event_PlayerFinish($cbdata);
						break;
						
					}
				}
			}
		        
	
			$calls = array();
			usleep(1);
		}
		if(isset($FoxControl_Reboot)){
			if($FoxControl_Reboot==true){
				echo exec("sh control.sh start");
				die();
			}
		}
        $this->client->Terminate(); 
	
	}

    
	protected function connect($Ip, $Port, $AuthLogin, $AuthPassword) {
    
		if (!$this->client->InitWithIp(strval($Ip), intval($Port))) {
			echo'ERROR: Wrong Port! Used Port:'.$settings['Port'].nz;
		} else {
        
		if (!$this->client->query('Authenticate', strval($AuthLogin), strval($AuthPassword))) {
			echo'ERROR: Invalid Password!'.nz;
		} else {

			return true;
            
		}
		}
	}

}


$control = new control;
$control->run();
?> 