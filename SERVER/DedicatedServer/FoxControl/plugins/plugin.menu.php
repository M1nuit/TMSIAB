<?php
//* plugin.menu.php - FoxControl Main menu
//* Version:   0.9.0
//* Coded by:  Libero, matrix142
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('StartUp', 'menu_startup');
control::RegisterEvent('PlayerConnect', 'menu_playerconnect');
control::RegisterEvent('ManialinkPageAnswer', 'menu_mlanswer');
control::RegisterEvent('BeginChallenge', 'menu_beginchallenge');

//Display the menu button at startup
function menu_startup($control){
	global $settings;
	$code = '
	<quad posn="59 15 2" sizen="13 3.8" halign="center" valign="center" style="Bgs1InRace" substyle="NavButton" />
	<label posn="53.5 15.6 4" text="$fff$o>>'.$settings['menu_name'].' Menu" style="TextPlayerCardName" action="101" scale="0.7" />
	<quad posn="59 15 1" sizen="13 3.8" halign="center" valign="center" action="101" style="Bgs1InRace" substyle="NavButtonBlink" />';
	
	$control->display_manialink($code, 100, 0, false);
}

//Display the menu button when player connects
function menu_playerconnect($control, $connectedplayer){
	global $settings;
	$code = '
	<quad posn="59 15 2" sizen="13 3.8" halign="center" valign="center" style="Bgs1InRace" substyle="NavButton" />
	<label posn="53.5 15.6 4" text="$fff$o>>'.$settings['menu_name'].' Menu" style="TextPlayerCardName" action="101" scale="0.7" />
	<quad posn="59 15 1" sizen="13 3.8" halign="center" valign="center" action="101" style="Bgs1InRace" substyle="NavButtonBlink" />';
	
	$control->display_manialink_to_login($code, 100, 0, false, $connectedplayer['Login']);
}

//Load menu at the beginning of a new challenge
function menu_beginchallenge($control, $challdata){
	menu_startup($control);
}

//The menu content
function menu_mlanswer($control, $ManialinkPageAnswer){
    global $db, $settings;
	
    //if the menu button is clicked
    if($ManialinkPageAnswer[2] == '101'){
	    //check if admin
	    if($control->is_admin($ManialinkPageAnswer[1]) == true){
		    $code = '
			<quad posn="35 4.9 20" sizen="14 33.2" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			
			<quad posn="35.5 19 21" sizen="13 3.8" valign="center" style="Bgs1InRace" action="103" substyle="NavButtonBlink" />
			<label posn="36.5 19 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$03fF$fffOX" action="103" scale="0.7"/>
			
			<quad posn="35.5 15 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
			<label posn="36.5 15 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffAdmin" action="102" scale="0.7"/>
			
			<quad posn="35.5 11 21" sizen="13 3.8" valign="center" style="Bgs1InRace" action="104" substyle="NavButtonBlink" />
			<label posn="36.5 11 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$fffTracks" action="117" scale="0.7"/>
			
			<quad posn="35.5 7 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			<label posn="36.5 7 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$fffPlayer" action="120" scale="0.7" />
			
			<quad posn="35.5 3 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			<label posn="36.5 3 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$fffChat" action="107" scale="0.7" />
			
			<quad posn="35.5 -1 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			<label posn="36.5 -1 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$fffInfo" action="119" scale="0.7" />
			
			<quad posn="35.5 -5 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			<label posn="36.5 -5 22" textsize="0.5" valign="center" style="TextPlayerCardName" text="$o$06fMania $fffCommunity" url="http://www.mania-community.de" scale="0.6" />
			
			<quad posn="35.5 -9 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			<label posn="36.5 -9 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$fffSKP Base" manialink="skp" scale="0.7" />';
			
		    $control->display_manialink_to_login($code, 101, 0, false, $ManialinkPageAnswer[1]);
			
			
			$code = '
	        <quad posn="59 15 2" sizen="13 3.8" halign="center" valign="center" style="Bgs1InRace" substyle="NavButton" />
	        <label posn="53.5 15.6 4" text="$fff$o>>'.$settings['menu_name'].' Menu" style="TextPlayerCardName" action="100" scale="0.7" />
	        <quad posn="59 15 1" sizen="13 3.8" halign="center" valign="center" action="101" style="Bgs1InRace" substyle="NavButtonBlink" />';
			
			$control->display_manialink_to_login($code, 100, 0, false, $ManialinkPageAnswer[1]);
			
		//if no admin
		}else{
		    $code = '
			<quad posn="35 6.8 20" sizen="14 29.4" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			
			<quad posn="35.5 19 21" sizen="13 3.8" valign="center" style="Bgs1InRace" action="103" substyle="NavButtonBlink" />
			<label posn="36.5 19 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$03fF$fffOX" action="103" scale="0.7"/>
			
			<quad posn="35.5 15 21" sizen="13 3.8" valign="center" style="Bgs1InRace" action="104" substyle="NavButtonBlink" />
			<label posn="36.5 15 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$fffTracks" action="117" scale="0.7"/>
			
			<quad posn="35.5 11 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			<label posn="36.5 11 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$fffPlayer" action="120" scale="0.7" />
			
			<quad posn="35.5 7 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			<label posn="36.5 7 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$fffChat" action="107" scale="0.7" />
			
			<quad posn="35.5 3 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			<label posn="36.5 3 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$fffInfo" action="119" scale="0.7" />
			
			<quad posn="35.5 -1 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			<label posn="36.5 -1 22" textsize="0.5" valign="center" style="TextPlayerCardName" text="$o$06fMania $fffCommunity" url="http://www.mania-community.de" scale="0.6" />
			
			<quad posn="35.5 -5 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
			<label posn="36.5 -5 22" textsize="1" valign="center" style="TextPlayerCardName" text="$o$fffSKP Base" manialink="skp" scale="0.7" />';
			
			//send display code to player
		    $control->display_manialink_to_login($code, 101, 0, false, $ManialinkPageAnswer[1]);
			
			
			$code = '
	        <quad posn="59 15 2" sizen="13 3.8" halign="center" valign="center" style="Bgs1InRace" substyle="NavButton" />
	        <label posn="53.5 15.6 4" text="$fff$o>>'.$settings['menu_name'].' Menu" style="TextPlayerCardName" action="100" scale="0.7" />
	        <quad posn="59 15 1" sizen="13 3.8" halign="center" valign="center" action="101" style="Bgs1InRace" substyle="NavButtonBlink" />';
			
			$control->display_manialink_to_login($code, 100, 0, false, $ManialinkPageAnswer[1]);
		}
	}
	
	/*
	BEGIN FOXRACE SUBMENU
	*/
	//FoxRace
	if($ManialinkPageAnswer[2] == '103'){
	    $code = '
		<quad posn="21.9 12.8 20" sizen="13.5 17.4" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		<quad posn="22.2 19 21" sizen="13 3.8" valign="center" style="Bgs1InRace" url="http://www.fox-control.de" substyle="NavButtonBlink"/>
		<label posn="22.9 19 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$09FFox-Control.de" url="http://www.fox-control.de" scale="0.7"/>
		<quad posn="22.2 15 21" sizen="13 3.8" valign="center" style="Bgs1InRace" manialink="foxc" substyle="NavButtonBlink"/>
		<label posn="22.9 15 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$09FFOX Control ML" manialink="foxc" scale="0.7"/>
		<quad posn="22.2 11 21" sizen="13 3.8" valign="center" style="Bgs1InRace" url="http://clan.fox-control.de" substyle="NavButtonBlink"/>
		<label posn="22.9 11 23" valign="center" textsize="1" style="TextPlayerCardName" text="$o$09FFox Clan" url="http://clan.fox-control.de" scale="0.7"/>
		<quad posn="22.2 7 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<label posn="22.9 7 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$09FCopyright" action="118" scale="0.7"/>';
		
		$control->display_manialink_to_login($code, 102, 25000, false, $ManialinkPageAnswer[1]);
	}
	
	    if($ManialinkPageAnswer[2] == '118'){
		    $code = '
		    <quad posn="0 5 21" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		    <quad posn="0 5 20" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
		    <quad posn="0 24.5 23" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
		    <quad posn="32 24.5 24" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="199"/>
		    <label posn="-7 24 25" scale="1.2" textsize="1" text="$o$fffCopyright Infos" />
		    <label posn="-20 20 25" sizen="60 2" scale="0.8" autonewline="1" text="FOX Control is an server controler coded by Cyril and Libero. Every Player can use this server controller but you had to follow these points" />
		    <label posn="-20 5 25" sizen="60 2" scale="0.8" text="-Its not allowed to use this code for unallowed commercial projects" />
		    <label posn="-20 0 25" sizen="60 2" scale="0.8" text="-You can use our controller for your own and can change the code anyway" />
		    <label posn="-20 -5 25" sizen="60 2" scale="0.8" text="-All New plugins had to be approofed by Libero and Cyrilw" />
		    <label posn="-20 -10 25" sizen="60 2" scale="0.8" text="-Its not allowed to change code parts which where between two copyright mode icons" />';
			
			$control->close_ml(102, $ManialinkPageAnswer[1]);
			$control->close_ml(104, $ManialinkPageAnswer[1]);
			$control->display_manialink_to_login($code, 103, 0, false, $ManialinkPageAnswer[1]);
		}
	/*
	END FOXRACE SUBMENU
	*/
	
	/*
	BEGIN ADMIN SUBMENU
	*/
	if($ManialinkPageAnswer[2] == '102'){
	    $code = '
		<quad posn="21.9 1.1 20" sizen="13.5 33.3" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		
		<quad posn="22.2 15 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<label posn="22.9 15 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffAdmins" action="7001" scale="0.7"/>
		
		<quad posn="22.2 11 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<label posn="22.9 11 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffTracks" action="121" scale="0.7"/>
		
		<quad posn="22.2 7 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<label posn="22.9 7 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffPlayers" action="7002" scale="0.7"/>
		
		<quad posn="22.2 3 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<label posn="22.9 3 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffCommands" action="7003" scale="0.7"/>
		
		<quad posn="22.2 -1 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<label posn="22.9 -1 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffServer" action="7004" scale="0.7"/>
		
		<quad posn="22.2 -5 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<label posn="22.9 -5 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffInfo" action="7005" scale="0.7"/>
		
		<quad posn="22.2 -9 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<label posn="22.9 -9 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffFOX Control" action="7006" scale="0.7"/>
		
		<quad posn="22.2 -13 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<label posn="22.9 -13 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffReboot" action="7007" scale="0.7"/>';
		
		$control->display_manialink_to_login($code, 102, 25000, false, $ManialinkPageAnswer[1]);
	}
	
	    //Adminlist
	    if($ManialinkPageAnswer[2] == '7001'){
	        global $admin_list_code;
		
		    admin_list($control);
		
		    $control->close_ml(102, $ManialinkPageAnswer[1]);
			$control->close_ml(104, $ManialinkPageAnswer[1]);
		    $control->display_manialink_to_login($admin_list_code, 103, 0, false, $ManialinkPageAnswer[1]);
	    }
	
	    //Playerlist
	    if($ManialinkPageAnswer[2] == '7002'){
	        $control->close_ml(102, $ManialinkPageAnswer[1]);
			$control->close_ml(104, $ManialinkPageAnswer[1]);
            $control->show_playerlist($ManialinkPageAnswer[1], true, 0);
	    }
	
	    //Admin Commands
	    if($ManialinkPageAnswer[2] == '7003'){
	        $code = '
		    <quad posn="0 5 21" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		    <quad posn="0 5 20" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
		    <quad posn="0 24.5 23" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
		    <label posn="-5 24.3 22" substyle="TextButtonSmall" text="$o$fffCommands" scale="0.75" />
		    <label posn="-33 20 22" text="$o$fff/ban playerlogin..................................................................................bans the ip of a player from this server" scale="0.7" size="1.45 2" />
		    <label posn="-33 17 22" text="$o$fff/unban playerlogin..................................................................................unbans the ip of a player from this server" scale="0.7" size="1.45 2" />
		    <label posn="-33 14 22" text="$o$fff/kick playerlogin..................................................................................kicks a player from this server, but the player can rejoin" size="1.45 2"scale="0.7" />
		    <label posn="-33 11 22" text="$o$fff/ignore playerlogin..................................................................................this player would be ignored from the chat" scale="0.7" size="1.45 2" />
		    <label posn="-33 8 22"  text="$o$fff/unignore playerlogin..................................................................................this player would be unignored from the chat" scale="0.7" size="1.45 2"/>
		    <label posn="-33 5 22"  text="$o$fff/pay playerlogin copperamount................................................................pay Coppers to an Player who is on the Server" scale="0.7" size="1.45 2" />
		    <label posn="-33 2 22"  text="$o$fff/coppers.................................................................................shows the Copperamount of the Server" scale="0.7" size="1.45 2"/>
		    <label posn="-33 -1 22" text="$o$fff/shutdown..................................................................................................shutdown Fox Control on the Server" scale="0.7" size="1.45 2"/>
		    <label posn="-33 -4 22" text="$o$fff/servername.........................................................................................sets the Servername of the current Server" scale="0.7" size="1.45 2"/>
		    <label posn="-33 -7 22" text="$o$fff/servercomment.......................................................................................sets comment of the current Server" scale="0.7" size="1.45 2"/>
		    <quad posn="32 24.5 24" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="199"/>
		    <quad posn="31 -10.5 25" style="Icons64x64_1" substyle="ArrowNext" sizen="3 3" action="7010"/>';
		
		    $control->close_ml(102, $ManialinkPageAnswer[1]);
			$control->close_ml(104, $ManialinkPageAnswer[1]);
		    $control->display_manialink_to_login($code, 103, 0, false, $ManialinkPageAnswer[1]);
	    }
		if($ManialinkPageAnswer[2] == '7010'){
	        $code = '
		    <quad posn="0 5 21" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		    <quad posn="0 5 20" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
		    <quad posn="0 24.5 23" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
		    <label posn="-5 24.3 22" substyle="TextButtonSmall" text="$o$fffCommands" scale="0.75" />
		    <label posn="-33 20 22" text="$o$fff/serverpw..................................................................................sets the Server-Password to the following word" scale="0.7" size="1.45 2" />
		    <label posn="-33 17 22" text="$o$fff/specpw..................................................................................sets the Spectator-Password to the following word" scale="0.7" size="1.45 2" />
		    <label posn="-33 14 22" text="$o$fff/warn playerlogin..................................................................................send a warn Message to the chosen Player" size="1.45 2"scale="0.7" />
		    <label posn="-33 11 22" text="$o$fff/newsuperadmin playerlogin..................................................................................adds an new Superadmin" scale="0.7" size="1.45 2" />
		    <label posn="-33 8 22"  text="$o$fff/newadmin playerlogin..................................................................................adds an new Admin" scale="0.7" size="1.45 2"/>
		    <label posn="-33 5 22"  text="$o$fff/newop playerlogin..................................................................................adds an new Operator" scale="0.7" size="1.45 2" />
		    <label posn="-33 2 22"  text="$o$fff/rmsuperadmin....................................................................................removes an Superadmin" scale="0.7" size="1.45 2"/>
		    <label posn="-33 -1 22" text="$o$fff/rmadmin.........................................................................................removes an Admin" scale="0.7" size="1.45 2"/>
		    <label posn="-33 -4 22" text="$o$fff/rmop............................................................................................removes an Operator" scale="0.7" size="1.45 2"/>
		    <label posn="-33 -7 22" text="$o$fff/endround...............................................................................ends the current Round (just avaiable in Rounds Mode)" scale="0.7" size="1.45 2"/>
		    <quad posn="32 24.5 24" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="199"/>
		    <quad posn="-34 -10.5 25" style="Icons64x64_1" substyle="ArrowPrev" sizen="3 3" action="7000"/>';
			
			$control->close_ml(102, $ManialinkPageAnswer[1]);
			$control->close_ml(104, $ManialinkPageAnswer[1]);
			$control->display_manialink_to_login($code, 103, 0, false, $ManialinkPageAnswer[1]);
        }	
	
	    //Server Info
	    if($ManialinkPageAnswer[2] == '7004'){
	        $code = '
		    <quad posn="0 5 21" sizen="70 20" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		    <quad posn="0 5 20" sizen="70 20" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
		    <quad posn="0 14.5 23" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
		    <quad posn="32 14.5 24" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="199"/>
		    <label posn="-33 10 22" text="$o$fffBetriebssystem: '.PHP_OS.'" size="1.45 2" scale="0.7" />
		    <label posn="-33 7 22" text="$o$fffPHP Version: '.phpversion().'" size="1.45 2" scale="0.7" />
		    <label posn="-33 1 22" text="$o$fffMemory usage:  '.memory_get_usage().' bytes" size="1.45 2" scale="0.7" />
		    <label posn="-33 4 22" text="$o$fffZent Version: '.zend_version().'" size="1.45 2" scale="0.7" />
		    <label posn="-5 14 26" text="$o$fffServer Stats" size="1.45 2" scale="0.7" />';
		
		    $control->close_ml(102, $ManialinkPageAnswer[1]);
			$control->close_ml(104, $ManialinkPageAnswer[1]);
		    $control->display_manialink_to_login($code, 103, 0, false, $ManialinkPageAnswer[1]);
	    }
	
	    //Info
	    if($ManialinkPageAnswer[2] == '7005'){
		    $control->client->query('GetServerCoppers');
		      $coppers=$control->client->getResponse();
		    $control->client->query('GetServerName');
		      $servername=$control->client->getResponse();
		    $control->client->query('GetServerComment');
		      $comment=$control->client->getResponse();
		    $control->client->query('GetMaxPlayers');
		      $maxpl=$control->client->getResponse();
		    $control->client->query('GetMaxSpectators');
		      $maxspec=$control->client->getResponse();
		    $control->client->query('GetServerPassword');
		      $serverpw=$control->client->getResponse();
		    $control->client->query('GetServerPasswordForSpectator');
		      $specpw=$control->client->getResponse();
		    $control->client->query('IsP2PUpload');
		      $p2pup=$control->client->getResponse();
		    $control->client->query('GetStatus');
		      $stat=$control->client->getResponse();
                  if($p2pup==1) $p2pup2 = 'true';
                  elseif($p2pup==0) $p2pup2 = 'false';
		    $control->client->query('IsChallengeDownloadAllowed');
		      $challengedl=$control->client->getResponse();
		          if($challengedl==1) $challengedl2= 'true';
		          elseif($challengedl==0) $challengedl2= 'false';
			  
		    $code = '
		    <quad posn="0 5 21" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		    <quad posn="0 5 20" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
		    <quad posn="0 24.5 23" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
		    <label posn="-5 24.3 22" substyle="TextButtonSmall" text="$o$fffServer Info" scale="0.75" />
		    <label posn="-33 20 22" text="$o$fffServername:   $z$o'.$servername.'" scale="0.85" size="1.45 2" />
		    <label posn="-33 17 22" text="$o$fffCopperamount:   $z$o$fff'.$coppers.'" scale="0.7" size="1.45 2" />
		    <label posn="-33 14 22" text="$o$fffServer Comment:   $z$o$fff'.$comment.'" size="1.45 2"scale="0.7" />
		    <label posn="-33 11 22" text="$o$fffMax Players:   $z$o$fff'.$maxpl['CurrentValue'].'" scale="0.7" size="1.45 2" />
		    <label posn="-33 8 22"  text="$o$fffMax Spectators:   $z$o$fff'.$maxspec['CurrentValue'].'" scale="0.7" size="1.45 2"/>
		    <label posn="-33 5 22"  text="$o$fffServer Password:   $z$o$fff'.$serverpw.'" scale="0.7" size="1.45 2" />
		    <label posn="-33 2 22"  text="$o$fffSpectator Password:   $z$o$fff'.$specpw.'" scale="0.7" size="1.45 2"/>
		    <label posn="-33 -1 22" text="$o$fffP2P Upload:   $z$o$fff'.$p2pup2.'" scale="0.7" size="1.45 2"/>
		    <label posn="-33 -4 22" text="$o$fffChallenge Donwload:   $z$o$fff'.$challengedl2.'" scale="0.7" size="1.45 2"/>
		    <quad posn="32 24.5 24" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="199"/>';
		
		    $control->close_ml(102, $ManialinkPageAnswer[1]);
			$control->close_ml(104, $ManialinkPageAnswer[1]);
		    $control->display_manialink_to_login($code, 103, 0, false, $ManialinkPageAnswer[1]);
	    }
	
	    //Fox Info
	    if($ManialinkPageAnswer[2] == '7006'){
	        $code = '
		    <quad posn="0 5 21" sizen="70 20" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		    <quad posn="0 5 20" sizen="70 20" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
		    <quad posn="0 14.5 23" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
		    <quad posn="32 14.5 24" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="199"/>
		    <label posn="-33 1 22" text="$o$fff" size="1.45 2" scale="0.7" />
		    <label posn="-33 4 22" text="$o$fff" size="1.45 2" scale="0.7" />
		    <label posn="-33 7 22" text="$o$fffType of Version:  '.FOXC_VERSIONP.'" size="1.45 2" scale="0.7" />
		    <label posn="-33 10 22" text="$o$06fF$fffox$06fC$fffontrol Version: '.FOXC_VERSION.'" size="1.45 2" scale="0.7" />
		    <label posn="-7 14 26" text="$o$06fF$fffox$06fC$fffontrol Infos" size="1.45 2" scale="0.7" />';
		
	        $control->close_ml(102, $ManialinkPageAnswer[1]);
			$control->close_ml(104, $ManialinkPageAnswer[1]);
	        $control->display_manialink_to_login($code, 103, 0, false, $ManialinkPageAnswer[1]);
	    }
	
	    //Reboot
	    if($ManialinkPageAnswer[2] == '7007'){
		    $control->FoxControl_reboot();
	    }
		
		/*
		BEGIN ADMIN TRACK SUBMENU
		*/
		if($ManialinkPageAnswer[2] == '121'){
	    $code = '
		<quad posn="8.6 11.05 20" sizen="13.5 4.7" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		
		<quad posn="8.9 11 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<label posn="9.6 11 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffDelete Track" action="7008" scale="0.7"/>';
		
		$control->display_manialink_to_login($code, 104, 20000, false, $ManialinkPageAnswer[1]);
		}
		
		    if($ManialinkPageAnswer[2] == '7008'){
			    $control->client->query('GetCurrentChallengeInfo');
				  $ChallengeInfo = $control->client->getResponse();
				  $FileName = $ChallengeInfo['FileName'];
			    $control->client->query('RemoveChallenge', $FileName);
				
				$control->client->query('GetDetailedPlayerInfo', $ManialinkPageAnswer[1]);
				  $PlayerInfo = $control->client->getResponse();
				  $NickName = $PlayerInfo['NickName'];
				
	            //Check Admin Rights
				$sql = "SELECT * FROM `admins` WHERE playerlogin = '".$ManialinkPageAnswer[1]."'";
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
				}
				
				$control->client->query('ChatSendServerMessage', '$0f0-> '.$Admin_Rank.' '.$NickName.'$z$s$0f0 removed '.$ChallengeInfo['Name'].'');
				write_Challenges($control);
				
				$control->close_ml(104, $ManialinkPageAnswer[1]);
			}
		/*
		END ADMIN TRACK SUBMENU
		*/
	
	/*
	END ADMIN SUBMENU
	*/
	
	//Tracks
	if($ManialinkPageAnswer[2] == '117'){
	    $control->close_ml(102, $ManialinkPageAnswer[1]);
		$control->close_ml(104, $ManialinkPageAnswer[1]);
		$array = array();
		$array[1] = $ManialinkPageAnswer[1];
		$array[2] = '999';
		challenges_display($control, $array);
	}
	
	//Player
	if($ManialinkPageAnswer[2] == '120'){
	    $control->close_ml(102, $ManialinkPageAnswer[1]);
		$control->close_ml(104, $ManialinkPageAnswer[1]);
	    $control->show_playerlist($ManialinkPageAnswer[1], false, 0);
	}
	
	/*
	SUBMENU CHAT BEGIN
	*/
	if($ManialinkPageAnswer[2] == '107'){
	    if($control->is_admin($ManialinkPageAnswer[1]) == true){
		    $code = '
			<frame posn="0 3.8 2">
		        <quad posn="21.9 -9.1 20" sizen="13.5 21.6" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		        <quad posn="22.2 -1 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		        <label posn="23.2 -1 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffFOX Rules" action="109" scale="0.7"/>
		        <quad posn="22.2 -5 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		        <label posn="23.2 -5 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffGood Game" action="110" scale="0.7"/>
		        <quad posn="22.2 -9 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		        <label posn="23.2 -9 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffLOL" action="111" scale="0.7"/>
		        <quad posn="22.2 -13 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		        <label posn="23.2 -13 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffBRB" action="112" scale="0.7"/>
		        <quad posn="22.2 -17 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		        <label posn="23.2 -17 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffAFK" action="113" scale="0.7"/>
			</frame>';
		}else{
		    $code = '
			<frame posn="0 7.6 2">
		        <quad posn="21.9 -9.1 20" sizen="13.5 21.6" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		        <quad posn="22.2 -1 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		        <label posn="23.2 -1 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffFOX Rules" action="109" scale="0.7"/>
		        <quad posn="22.2 -5 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		        <label posn="23.2 -5 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffGood Game" action="110" scale="0.7"/>
		        <quad posn="22.2 -9 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		        <label posn="23.2 -9 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffLOL" action="111" scale="0.7"/>
		        <quad posn="22.2 -13 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		        <label posn="23.2 -13 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffBRB" action="112" scale="0.7"/>
		        <quad posn="22.2 -17 21" sizen="13 3.8" valign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		        <label posn="23.2 -17 22" valign="center" textsize="1" style="TextPlayerCardName" text="$o$fffAFK" action="113" scale="0.7"/>
			</frame>';
		}
		
		$control->display_manialink_to_login($code, 102, 25000, false, $ManialinkPageAnswer[1]);
	}
	    
		//Send to Chat
	    if($ManialinkPageAnswer[2]=='109'){
		    $control->chat_with_nick('$o$09fFOX RulZzz!', $ManialinkPageAnswer[1]);
	    }
	
	    if($ManialinkPageAnswer[2]=='110'){
		    $control->chat_with_nick('$CC0G$AC0o$8C0o$6C0d$4C0 $2C0G$0C0a$0C0m$3B0e$590 $880A$A60l$D50l$F30!', $ManialinkPageAnswer[1]);
	    }
		
	    if($ManialinkPageAnswer[2]=='111'){
		    $control->chat_with_nick('$F00L$F21o$F51o$F720$FA20$FC3O$FC3O$FA20$F820$F71o$F51o$F30L', $ManialinkPageAnswer[1]);
	    }
		
	    if($ManialinkPageAnswer[2]=='112'){
		    $control->chat_with_nick('$00FB$02Fe$03E $05ER$06Di$08Dg$09Ch$09Ct$3AD $5BDB$8CEa$ADEc$DEFk$FFF!', $ManialinkPageAnswer[1]);
	    }
		
	    if($ManialinkPageAnswer[2]=='113'){
		    $control->chat_with_nick('$6F3A$5F3w$5E2a$4E2y$3E2 $3D1f$2D1r$1D1o$1C0m$0C0 $0C0K$2C2e$4D4y$6D6b$8E8o$9E9a$BEBr$DFDd$FFF!', $ManialinkPageAnswer[1]);
		    $control->client->query('ForceSpectator', $ManialinkPageAnswer[1], 1);
	        $control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
	        <manialink id="79999">
	        <quad posn="0 -27 1" sizen="25 4" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" action="79999" />
	        <label posn="0 -28 2" halign="center" style="TextPlayerCardName" text="$o$fffClick here to play!" action="79999" />
	        </manialink>', 0, false);
	    }
	/*
	SUBMENU CHAT END
	*/
	
	//Info
	if($ManialinkPageAnswer[2] == '119'){
		$control->client->query('GetServerName');
		$servername=$control->client->getResponse();
		$control->client->query('GetServerComment');
		$comment=$control->client->getResponse();
		$control->client->query('GetMaxPlayers');
		$maxpl=$control->client->getResponse();
		$control->client->query('GetMaxSpectators');
		$maxspec=$control->client->getResponse();
		$control->client->query('IsChallengeDownloadAllowed');
		$challengedl=$control->client->getResponse();
		if($challengedl==1) $challengedl2= 'true';
		elseif($challengedl==0) $challengedl2= 'false';
		global $settings;
        
		$code = '
		<quad posn="0 5 21" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<quad posn="0 5 20" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
		<quad posn="0 24.5 23" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
		<label posn="-5 24.3 22" substyle="TextButtonSmall" text="$o$fffServer Info" scale="0.75" />
		<label posn="-33 20 22" text="$o$fffServername:   $z$o'.$servername.'" scale="0.85" size="1.45 2" />
		<label posn="-33 17 22" text="$o$fffChallenge Donwload:   $z$o$fff'.$challengedl2.'" scale="0.7" size="1.45 2" />
		<label posn="-33 14 22" text="$o$fffServer Comment:   $z$o$fff'.$comment.'" size="1.45 2"scale="0.7" />
		<label posn="-33 11 22" text="$o$fffMax Players:   $z$o$fff'.$maxpl['CurrentValue'].'" scale="0.7" size="1.45 2" />
		<label posn="-33 8 22"  text="$o$fffMax Spectators:   $z$o$fff'.$maxspec['CurrentValue'].'" scale="0.7" size="1.45 2"/>
		<label posn="-33 5 22"  text="$o$fffServer Nation:   '.$settings['Nation'].'" scale="0.7" size="1.45 2" />
		<label posn="-33 2 22"  text="$o$fffServer Location:   '.$settings['ServerLocation'].'" scale="0.7" size="1.45 2"/>
		<label posn="-33 -1 22" text="$o$fff" scale="0.7" size="1.45 2"/>
		<label posn="-33 -4 22" text="$o$fff" scale="0.7" size="1.45 2"/>
		<label posn="-33 -7 22" text="$o$fff" scale="0.7" size="1.45 2"/>
		<quad posn="32 24.5 24" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="199"/>';
		
		$control->close_ml(102, $ManialinkPageAnswer[1]);
		$control->close_ml(104, $ManialinkPageAnswer[1]);
		$control->display_manialink_to_login($code, 103, 0, false, $ManialinkPageAnswer[1]);
	}
	
	//Close the menu
	if($ManialinkPageAnswer[2] == '100'){
	    $control->close_ml(101, $ManialinkPageAnswer[1]);
		$control->close_ml(102, $ManialinkPageAnswer[1]);
		$control->close_ml(104, $ManialinkPageAnswer[1]);
		$control->close_ml(105, $ManialinkPageAnswer[1]);
		$control->close_ml(106, $ManialinkPageAnswer[1]);
		$control->close_ml(107, $ManialinkPageAnswer[1]);
	    $code = '
	    <quad posn="59 15 2" sizen="13 3.8" halign="center" valign="center" style="Bgs1InRace" substyle="NavButton" />
	    <label posn="53.5 15.6 4" text="$fff$o>>'.$settings['menu_name'].' Menu" style="TextPlayerCardName" action="101" scale="0.7" />
	    <quad posn="59 15 1" sizen="13 3.8" halign="center" valign="center" action="101" style="Bgs1InRace" substyle="NavButtonBlink" />';
	
	    $control->display_manialink_to_login($code, 100, 0, false, $ManialinkPageAnswer[1]);
	}
	
	//Close windows
	if($ManialinkPageAnswer[2] == '199'){
	    $control->close_ml(103, $ManialinkPageAnswer[1]);
	}
}

function admin_list($control){
		global $db, $settings, $admin_list_code;
		$id = 0;
		$admins_array = array();
		while(true){
			$sql = "SELECT * FROM `admins` ORDER BY rights DESC LIMIT ".$id.", 1";
			$mysql = mysqli_query($db, $sql);
			if($admin = $mysql->fetch_object()){
				if($admin->rights=='3') $ar = $settings['Name_SuperAdmin'];
				elseif($admin->rights=='2') $ar = $settings['Name_Admin'];
				elseif($admin->rights=='1') $ar = $settings['Name_Operator'];
				else $ar = '-';
				$control->client->query('GetDetailedPlayerInfo', trim($admin->playerlogin));
				$playerinfo = $control->client->getResponse();
				if(!isset($playerinfo['NickName'])){
					$sql = "SELECT * FROM `players` WHERE playerlogin = '".mysqli_real_escape_string($db, $admin->playerlogin)."'";
					$mysql = mysqli_query($db, $sql);
					if($admin_minfo = $mysql->fetch_object()) $a_nick = $admin_minfo->nickname;
					else{
						$a_nick = 'Could not find NickName!';
						console('Error: Could not find NickName of '.$admin->playerlogin.'!');
					}
				}
				else $a_nick = $playerinfo['NickName'];
				$a_id = $id + 1;
				$admins_array[] = '<frame posn="-34 [yy] 4">
				<label posn="0 0 0" textsize="2" sizen="2.5 2" text="$o$09f'.$a_id.'"/>
				<label posn="3 0 4" textsize="2" sizen="15 2" text="'.htmlspecialchars($a_nick).'"/>
				<label posn="19 0 4" textsize="2" sizen="10 2" text="'.$admin->playerlogin.'"/>
				<label posn="30 0 4" textsize="2" sizen="20 2" text="'.htmlspecialchars($ar).'"/>
				</frame>';
				$id++;
			}
			else break;
		}
		$admin_list_code = '
		<quad posn="0 5 1" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<quad posn="0 5 0" sizen="70 40" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
		<quad posn="0 24.5 3" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
		<label posn="-34 24.5 4" text="$o$09fAdmins"/>
		<quad posn="32 24.5 4" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="199"/>
		'.str_replace('[yy]', '20', $admins_array[0]).'
		'.str_replace('[yy]', '17.5', $admins_array[1]).'
		'.str_replace('[yy]', '15', $admins_array[2]).'
		'.str_replace('[yy]', '12.5', $admins_array[3]).'
		'.str_replace('[yy]', '10', $admins_array[4]).'
		'.str_replace('[yy]', '7.5', $admins_array[5]).'
		'.str_replace('[yy]', '5', $admins_array[6]).'
		'.str_replace('[yy]', '2.5', $admins_array[7]).'
		'.str_replace('[yy]', '0', $admins_array[8]).'
		'.str_replace('[yy]', '-2.5', $admins_array[9]).'
		'.str_replace('[yy]', '-5', $admins_array[10]).'
		'.str_replace('[yy]', '-7.5', $admins_array[11]).'
		'.str_replace('[yy]', '-10', $admins_array[12]).'
		';
}
?>
