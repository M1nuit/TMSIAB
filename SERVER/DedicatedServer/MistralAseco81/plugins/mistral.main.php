<?php
/** SEKTION FOR REGISTRATION
*********************************************************************/
// events
Aseco::registerEvent("onChat", "checkChatForWords");
Aseco::registerEvent("onNewChallenge", "buildMistralWins");
Aseco::registerEvent("onNewChallenge", "logChallenge");
// players
Aseco::addChatCommand("official", "this is the noob detection");
Aseco::addChatCommand("wow", "sends a WOW message to everyone");
Aseco::addChatCommand("hb", "sing happy birthday to login");
Aseco::addChatCommand("stats", "displays statistics of current player");
Aseco::addChatCommand("recs", "displays a list of the current top 15 records");
Aseco::addChatCommand('top', 'Displays the top 15 ranked players');
// admins
Aseco::addChatCommand('fixwins', 'synchronises wins with backup and fixes broken ones', true);
Aseco::addChatCommand('checkdb', 'shows challenges from db, that are not online', true);
Aseco::addChatCommand('cleanupdb', 'deletes all related data from deleted players/challenges', true);
Aseco::addChatCommand('ratio', 'sets callvote ratio in %', true);
Aseco::addChatCommand('team', 'set team challenge mode (5 pts tracklimit)', true);
Aseco::addChatCommand('time', 'set time challenge mode (6 minutes)', true);

global $asecoadmin, $settingsfile, $trigger;

/** Send Notice
***********************************/
function chat_sn(&$aseco, $command) {
	$player=$command['author'];
	$login=$player->login;

	if (!$aseco->isAdmin($login))
		return;
		  
	// send the message ...
	$aseco->addCall("SendNotice", array($command['params'], ''));
}

/** SEKTION FOR ADMIN COMMANDS - myChatAdmin($aseco, $command);
****************************************************************/
function myChatAdmin($aseco, $command) {
	global $con, $jukebox, $tmxdir, $blacklist, $guestlist, $asecoadmin, $settingsfile;

	$admin = $command['author'];

	// split params into array
	$arglist = explode(' ', $command['params'], 2);
	$command['params'] = explode(' ', $command['params']);

	$cmdcount = count($arglist);

	/********************************************************************
	 * Show server's coppers
	 */
	if ($command['params'][0] == "coppers")
	{
		$mylogin = $admin->login;
		$aseco->client->query("GetServerCoppers");
		$coppers=$aseco->client->getResponse();
		$aseco->addCall("ChatSendServerMessageToLogin", array("> Server has $coppers coppers.", $admin->login));
	}

	/********************************************************************
	 * Exit Aseco
	 */
	elseif ($command['params'][0] == "exit")
	{
		$mylogin = $admin->login;
		if ($mylogin != $asecoadmin)	
			return;
		$aseco->client->Terminate();
		mysql_close();
		die("Admin told ASECO to exit.");
	}

	/**
	* Force lottery win manually! (if pay failed)
	*/
  	elseif ($command['params'][0] == "win" && $command['params'][1] != "")
	{
		$mylogin = $admin->login;
		if ($mylogin != $asecoadmin)	
			return;

		$winnerlogin = $command['params'][1];
		$winnernickname = getNicknameFromLogin($winnerlogin);
		
		if ($winnernickname=="unknown")
		{
			$aseco->addCall("ChatSendServerMessageToLogin", array("> Unknown login: $winnerlogin", $admin->login));
			return;
		}		

		payLottery($aseco, $winnerlogin, $winnernickname);
	}

	/********************************************************************
	 * Back up Wins and fix them if less than before.
	 */
	if ($command['params'][0] == "fixwins")
		{
		fixwins($aseco, $command);
		}

	/**
	 * Deletes Records from not existant players/tracks from db.
	 */
	elseif ($command['params'][0] == "cleanupdb")
		{
		$mylogin = $admin->login;
		$myplayer = $aseco->server->players->getPlayer($mylogin);

		cleanupdb($aseco, $myplayer);
		$aseco->addCall("ChatSendServerMessageToLogin", array("> Database cleanup queries executed.", $admin->login));		
		$aseco->client->multiquery();
		$settingsdir = $aseco->server->trackdir."MatchSettings/";
		$date = date("-Ymd-His");
		$backupfile = $settingsfile.$date;
		rename($settingsdir.$settingsfile.".txt", $settingsdir.$backupfile.".txt");
		$aseco->addCall("SaveMatchSettings", array("MatchSettings/".$settingsfile.".txt"));
		$aseco->addCall('SaveGuestList', array($guestlist));
		$aseco->addCall('LoadGuestList', array($guestlist));
		$aseco->addCall('SaveBlackList', array($blacklist));
		$aseco->addCall('LoadBlackList', array($blacklist));
		$aseco->addCall("ChatSendServerMessageToLogin", array("> Matchsettings, Guest-, Blacklist saved.", $admin->login));
		$aseco->client->multiquery();

		fixwins($aseco, $command);
		}

	/**
	 * Checks online tracks and compares with tracklist from db.
	 */
  	elseif ($command['params'][0] == "checkdb")
		{
		$mylogin = $admin->login;
		$myplayer = $aseco->server->players->getPlayer($mylogin);

		$tracks = getOfflineTracks($aseco, $myplayer);
		$message = "Tracks in DB that are not online:\n\n";
		foreach($tracks as $track)
			{
			$message .= "\$z" . $track['id'] . " - " . $track['name'] . "\n";
			}
		popup_msg($mylogin, $message);
		}

	/**
	 * Force an info message for testing
	 */
  	elseif ($command['params'][0] == "info")
		{
		mistral_infomessage($aseco,$command);
		}

	/**
	* Sets callvote ratio.
	*/
  	elseif ($command['params'][0] == "ratio" && $command['params'][1] != "")
		{
		// get callvote ratio ...
		$ratio = $command['params'][1];
		$serverratio = (double)$ratio;
		$serverratio = $serverratio/100; 

		// tell the server to set the callvote ratio
		$aseco->addCall("SetCallVoteRatio",array($serverratio));

		// display console message ...
		$aseco->console("admin [{1}] sets callvote ratio to {2}%! ({3})",
		$command['author']->id,
		$ratio,
		$serverratio);
    
		// replace parameters ...
		$message = formatText("{#server}>> Admin sets callvote ratio to {1}%!", $ratio);
    
		// replace colors ...
		$message = $aseco->formatColors($message);
    
		// send chat message ...
		$aseco->addCall(ChatSendServerMessage, array($message));
		}

	/**
	 * Sets server mode to time challenge.
	 */
	elseif ($command['params'][0] == "time")
		{
		$aseco->addCall(SetGameMode, array(1));
		$aseco->addCall(SetTimeAttackLimit, array(360000));
		$aseco->addCall(SetTimeAttackSynchStartPeriod, array(0));
		$message = $aseco->formatColors("{#server}>> Admin set mode to TIME CHALLENGE (6 mins)!");
    	$aseco->addCall(ChatSendServerMessage, array($message));
		}
    
	/**
	 * Sets server mode to team challenge.
	 */
	elseif ($command['params'][0] == "team")
		{
		$aseco->addCall(SetGameMode, array(2));
		$aseco->addCall(SetTeamPointsLimit, array(5));
		$aseco->addCall(SetMaxPointsTeam, array(0));
		$aseco->addCall(SetUseNewRulesTeam, array(true));
		$message = $aseco->formatColors("{#server}>> Admin set mode to TEAM CHALLENGE (5 points, new rules)!");
   		$aseco->addCall(ChatSendServerMessage, array($message));
		}
	}

/** SEKTION FOR MODS
*********************************************************************/

/** myWelcomeMessage
****************************************************************/
function myWelcomeMessage($aseco, $player)
	{
	$query = "SELECT count(id) from players";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$plcount = $row[0];
	mysql_free_result($result);

	$query = "SELECT count(id) from records";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$recount = $row[0];
	mysql_free_result($result);

	$query = "SELECT count(id) from challenges";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$trcount = $row[0];
	mysql_free_result($result);

	$message = formatText($aseco->getChatMessage('WELCOME'),
		$player->nickname,
		$plcount,
		$recount,
		$trcount);

	return $message;
	}

/** top - delete function chat_top5() in plugin.rasp.php
********************************************************/
function chat_top($aseco, $command, $environment="") {
	global $rasp, $manialinkstack;

	$ppp = 20;

	$player = $command['author'];

	$allaction = 30005;
	$speedaction = 30027;
	$alpineaction = 30028;
	$rallyaction = 30029;
	$bayaction = 30030;
	$islandaction = 30031;
	$coastaction = 30032;
	$stadiumaction = 30033;

	$allbg = "BgTitle3_4";
	$speedbg = "BgTitle3_4";
	$alpinebg = "BgTitle3_4";
	$rallybg = "BgTitle3_4";
	$stadiumbg = "BgTitle3_4";
	$baybg = "BgTitle3_4";
	$islandbg = "BgTitle3_4";
	$coastbg = "BgTitle3_4";
	
	if ($environment == "")
		$allbg = "BgTitle3_1";
	if ($environment == "Speed")
		$speedbg = "BgTitle3_1";
	if ($environment == "Alpine")
		$alpinebg = "BgTitle3_1";
	if ($environment == "Rally")
		$rallybg = "BgTitle3_1";
	if ($environment == "Bay")
		$baybg = "BgTitle3_1";
	if ($environment == "Island")
		$islandbg = "BgTitle3_1";
	if ($environment == "Coast")
		$coastbg = "BgTitle3_1";
	if ($environment == "Stadium")
		$stadiumbg = "BgTitle3_1";

	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;
		
	$ra = 15;
	$na = 50;
	$av = 15;
	$width = $ra+$na+$av;
	$height = $ppp*2+18;
	$hw = $width/2;
	$hh = $height/2;
	$qw = $width/4;
	$tqw = 3*$width/4;
	$player->msgsw=$width;
	$player->msgsh=$height;

	$header = "<?xml version='1.0' encoding='utf-8' ?><manialink id='120'><frame posn='-$hw $hh $manialinkstack'>";
	$header .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";
	$header .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$header .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="Top 100 Players"/>';

	$header .= "<quad posn='0 -4 0' sizen='$qw 3' action='$allaction' style='Bgs1InRace' substyle='$allbg'/>";
	$header .= "<quad posn='$qw -4 0' sizen='$qw 3' action='$speedaction' style='Bgs1InRace' substyle='$speedbg'/>";
	$header .= "<quad posn='$hw -4 0' sizen='$qw 3' action='$alpineaction' style='Bgs1InRace' substyle='$alpinebg'/>";
	$header .= "<quad posn='$tqw -4 0' sizen='$qw 3' action='$rallyaction' style='Bgs1InRace' substyle='$rallybg'/>";
	$header .= "<quad posn='0 -7 0' sizen='$qw 3' action='$stadiumaction' style='Bgs1InRace' substyle='$stadiumbg'/>";
	$header .= "<quad posn='$qw -7 0' sizen='$qw 3' action='$bayaction' style='Bgs1InRace' substyle='$baybg'/>";
	$header .= "<quad posn='$hw -7 0' sizen='$qw 3' action='$islandaction' style='Bgs1InRace' substyle='$islandbg'/>";
	$header .= "<quad posn='$tqw -7 0' sizen='$qw 3' action='$coastaction' style='Bgs1InRace' substyle='$coastbg'/>";
	$header .= "<label posn='".($qw/2)." -4.5 0' halign='center' textsize='2' text='Overall'/>";
	$header .= "<label posn='".(3*$qw/2)." -4.5 0' halign='center' textsize='2' text='Speed'/>";
	$header .= "<label posn='".(5*$qw/2)." -4.5 0' halign='center' textsize='2' text='Alpine'/>";
	$header .= "<label posn='".(7*$qw/2)." -4.5 0' halign='center' textsize='2' text='Rally'/>";
	$header .= "<label posn='".($qw/2)." -7.5 0' halign='center' textsize='2' text='Stadium'/>";
	$header .= "<label posn='".(3*$qw/2)." -7.5 0' halign='center' textsize='2' text='Bay'/>";
	$header .= "<label posn='".(5*$qw/2)." -7.5 0' halign='center' textsize='2' text='Island'/>";
	$header .= "<label posn='".(7*$qw/2)." -7.5 0' halign='center' textsize='2' text='Coast'/>";
	
	$header .= "<quad posn='0 -10 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$header .= "<label posn='".($ra/2)." -10.5 1' halign='center' textsize='2' text='Pos.'/>";	
	$header .= "<label posn='".($ra)." -10.5 1' halign='left' textsize='2' text='Name'/>";	
	$header .= "<label posn='".($ra+$na+$av/2)." -10.5 1' halign='center' textsize='2' text='Average'/>";	

	$detail = "<label posn='".($ra/2)." {POSN} 1' halign='center' textsize='2' text='{POS}.'/>";	
	$detail .= '<label posn="'.($ra).' {POSN} 1" halign="left" textsize="2" text="{NAME}"/>';	
	$detail .= "<label posn='".($ra+$na+$av/2)." {POSN}  1' halign='center' textsize='2' text='{AVERAGE}'/>";	

	if ($environment == "")
		$environment = "avg";
	$query = 'SELECT p.NickName, r.'.strtolower($environment).' FROM players p LEFT JOIN rs_rank r ON (p.Id=r.PlayerId) WHERE r.'.strtolower($environment).'!=0 ORDER BY r.'.strtolower($environment).' ASC LIMIT 100';
	$res = mysql_query($query);

	$player->msgs = array();
	$i = 1;
	$page = $header;
	$posn = -12;
	$cnt = 0;
	$p = 1;
	while ($row = mysql_fetch_row($res))
		{
		$posn -= 2;
		$cnt++;
		$nick = htmlspecialchars($row[0]);
		$s = $detail;
		$s = str_replace('{POSN}', $posn, $s);
		$s = str_replace('{POS}', $i, $s);
		$s = str_replace('{NAME}', $nick, $s);
		$s = str_replace('{AVERAGE}', $row[1]/10000, $s);
		$page .= $s;
		$i++;
		if ($cnt == $ppp)
			{
			$player->msgs[$p++] = $page;
			$cnt = 0;
			$posn = -12;
			$page = $header;
			}
		}
	mysql_free_result($res);
	
	if ($cnt > 0)
		$player->msgs[$p] = $page;
	if ($i == 1)
		$player->msgs[1] = $header;		
	
	$player->msgs['curpage'] = 1;

	show_multi_msg($player);
	}

/** recs - deactivate plugin.records.php
*****************************************/
function chat_recs(&$aseco, &$command)
	{
	global $manialinkstack;
	
	$player = $command['author'];

	$rpp = 26;

	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;
		
	$da = 15;
	$po = 10;
	$ti = 10;
	$dr = 45;
	$width = $da+$po+$ti+$dr;
	$height = $rpp*2+10;
	$hw = $width/2;
	$hh = $height/2;
	$player->msgsw=$width;
	$player->msgsh=$height;

	$header = "<?xml version='1.0' encoding='utf-8' ?><manialink id='110'><frame posn='-$hw $hh $manialinkstack'>";
	$header .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";
	$header .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$header .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="Records on '.$aseco->server->challenge->name.'"/>';
	$header .= "<quad posn='0 -4 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$header .= "<label posn='".($da)." -4.5 1' halign='right' textsize='2' text='Driven at'/>";	
	$header .= "<label posn='".($da+$po/2)." -4.5 1' halign='center' textsize='2' text='Pos.'/>";	
	$header .= "<label posn='".($da+$po+$ti/2)." -4.5 1' halign='center' textsize='2' text='Time'/>";	
	$header .= "<label posn='".($da+$po+$ti)." -4.5 1' halign='left' textsize='2' text='Driver'/>";	

	$detail = "<label posn='".($da)." {POSN} 1' halign='right' textsize='2' text='{DATE}'/>";	
	$detail .= "<label posn='".($da+$po/2)." {POSN} 1' halign='center' textsize='2' text='{POS}'/>";	
	$detail .= "<label posn='".($da+$po+$ti/2)." {POSN}  1' halign='center' textsize='2' text='{TIME}'/>";	
	$detail .= '<label posn="'.($da+$po+$ti).' {POSN} 1" halign="left" textsize="2" text="{NAME}"/>';

	$posn = -5;
	$player->msgs = array();
	$player->msgs['curpage'] = 0;
	$s = '';
	$ctr = 0;
	$msgs = 0;
	$list = $header;
	if ($aseco->server->records->count() > 0)
		{
		for ($i = 0; $i < $aseco->server->records->count(); $i++)
			{
			if($cur_record = $aseco->server->records->getRecord($i))
				{
				$posn -= 2;
				$cid=$cur_record->challenge->id;
				$pid=getPlayerIdFromLogin($cur_record->player->login);
				$myquery="SELECT DATE_FORMAT(Date, '%d.%m.%y, %H:%i') as mydate FROM records r WHERE ChallengeID=".$cid." AND PlayerID=".$pid;
				$myresult=mysql_query($myquery);
				$myrow=mysql_fetch_array($myresult);
				$mydate=$myrow['mydate'];
				mysql_free_result($myresult);

				$nick = htmlspecialchars($cur_record->player->nickname);
				$s = $detail;
				$s = str_replace('{POSN}', $posn, $s);
				$s = str_replace('{DATE}', "\$z".$mydate, $s);
				$s = str_replace('{POS}', "\$F00".($i+1).'.', $s);
				$s = str_replace('{TIME}', formatTime($cur_record->score), $s);
				$s = str_replace('{NAME}', "\$z".$nick , $s);
				$list .= $s;
				$ctr++;
				if ( $ctr == $rpp )
					{
					$posn = -5;
					$ctr = 0;
					$msgs++;
					$player->msgs[$msgs] = $list;
					$list = $header;
					}
      			}
    		}
   		if ( $s > '' )		// add if last batch exists
			{
			$player->msgs[$msgs+1] = $list;
			}
		if ( $s > '' || sizeof($player->msgs)>0 )
			{
			$player->msgs['curpage'] = 1;
			}
  		}
	else
		{
		$detail = "<label posn='$hw -8 1' halign='center' textsize='2' text='No Records'/>";	
		$player->msgs[1] = $header.$detail;
		$player->msgs['curpage'] = 1;
		}

	show_multi_msg($player);
	}

/** stats - deactivate plugin.stats.php
****************************************/
function chat_stats(&$aseco, &$command)
	{
	$player = $command['author'];
	displayStats($aseco, $player, $player);
	}

/** SEKTION FOR FUNKTIONS
*********************************************************************/

/** mistral_checklog
****************************************************************/
function mistral_checklog()
{
	clearstatcache();
	$size = filesize('aseco.log');
	if ($size > 1024*1024*100)
    {
    	echo "LOGFILESIZE EXCEEDED";
	  	die();
  	}
}

/** cleanupdb from broken stuff
*******************************/
function cleanupdb($aseco, $player) {
	$tracks = getOfflineTracks($aseco, $player);
	foreach($tracks as $track)
		{
		$id = $track['id'];
		$query = "DELETE FROM challenges where id=$id;";
		mysql_query($query);
		}

	$query = "DELETE FROM mistral_trackeval where ChallengeId=0;";
	mysql_query($query);
	$query = "DELETE FROM mistral_trackeval where PlayerId=0;";
	mysql_query($query);

	$query = "DELETE FROM challenges where uid=\"\";";
	mysql_query($query);
	$query = "DELETE FROM players where login=\"\";";
	mysql_query($query);

	$query = "DELETE FROM records where not exists (select id from players p where p.id=records.PlayerId);";
	mysql_query($query);
	$query = "DELETE FROM records where not exists (select id from challenges c where c.id=records.ChallengeId);";
	mysql_query($query);

	$query = "SELECT mid FROM mistral_chat ORDER BY mid DESC LIMIT 1;";
	$result=mysql_query($query);
	$row=mysql_fetch_row($result);
	$mid=$row[0]-5000;
	mysql_free_result($result);
	$query = "DELETE FROM mistral_chat WHERE mid<$mid;";
	mysql_query($query);
}

/** cleanupRecords
***************************/
function cleanupRecords($uid) {
 	global $maxrecs;
 
	$id = getTrackIDfromUID($uid);
	if ($id == "")
		return;
	
	$query = "SELECT score FROM records WHERE Challengeid=$id ORDER BY score ASC LIMIT $maxrecs";
  	$result = mysql_query($query);
  	if (!$result)
  		return;

  	if (mysql_num_rows($result) == $maxrecs) {
		$score = 100000;
	    while ($row = mysql_fetch_row($result)) {
			$score = $row[0];
		}
		$query = "DELETE FROM records WHERE score>=$score AND challengeid=$id AND rank>$maxrecs";
		mysql_query($query);
		mysql_free_result($result);
/*
		$deleted = mysql_affected_rows();
		if ($deleted > 0) {
			echo "Deleted $deleted records for track $id".CRLF;
		}
*/
	}	
}

/** sync wins with backup
*******************************/
function fixwins($aseco, $command)
	{
	global $manialinkstack;
	
	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;

	$dbtable="mistral_playerwins";

	$query="CREATE TABLE `".$dbtable."` (
		`PlayerId` mediumint(9) NOT NULL default '0',
		`Wins` mediumint(9) NOT NULL default '0',
		`Donation` mediumint(9) unsigned NOT NULL default '0',
		`Won` mediumint(9) unsigned NOT NULL default '0',
		`Jukebox` mediumint(9) unsigned NOT NULL default '0',
		PRIMARY KEY `PlayerId` (`PlayerId`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		mysql_query($query);

	$query="SELECT Id,Login,Wins from players order by Id;";
	$result=mysql_query($query);

	$error=0;
	$unchanged=0;
	$update=0;
	$new=0;
	$fix=0;

	for ($i=0; $i<mysql_num_rows($result); $i++)
		{
		$row=mysql_fetch_row($result);
		$PlayerId=$row[0];
		$Login=$row[1];
		$Wins=$row[2];
		$query="SELECT Wins from ".$dbtable." where PlayerId=".$PlayerId.";";
		$result2=mysql_query($query);

		// NOT IN TABLE - INSERT
		if (mysql_num_rows($result2)==0)
			{
			$query="INSERT into ".$dbtable." (PlayerId, Wins) VALUES (".$PlayerId.",".$Wins.");";
			if (!mysql_query($query))
				{
				$message = "Cannot insert player with Id ".$PlayerId."into ".$dbtable."";
				$aseco->addCall("ChatSendToLogin", array($message, $command['author']->login));
				$aseco->client->multiquery();
				$error++;
				}
			else
				$new++;
			}
		// ALREADY IN TABLE - CHECK AND UPDATE
		else
			{
			$row=mysql_fetch_row($result2);
			$oldWins=$row[0];
			// MORE WINS - OK - UPDATE
			if ($Wins>$oldWins)
				{
				$query="UPDATE ".$dbtable." SET Wins=".$Wins." WHERE PlayerID=".$PlayerId.";";
				if (!mysql_query($query))
					{
					$message = "Cannot update player with Id ".$PlayerId."in ".$dbtable."";
					$aseco->addCall("ChatSendToLogin", array($message, $command['author']->login));
					$aseco->client->multiquery();
					$error++;
					}
				else
					$update++;
				}
			// LESS WINS - FIX!!!
			elseif ($Wins<$oldWins)
				{
				$message = "> '$Login' ($PlayerId): old=$oldWins; now=$Wins (fixed)";
				$aseco->addCall("ChatSendServerMessageToLogin", array($message, $command['author']->login));
				$aseco->client->multiquery();
				$newWins=$Wins+$oldWins;
				$query="UPDATE ".$dbtable." SET Wins=".$newWins." WHERE PlayerID=".$PlayerId.";";
				if (!mysql_query($query))
					{
					$message = "Cannot update player with Id ".$PlayerId." in ".$dbtable."";
					$aseco->addCall("ChatSendToLogin", array($message, $command['author']->login));
					$aseco->client->multiquery();
					$error++;
					}
				else
					{
					$query="UPDATE players SET Wins=".$newWins." WHERE Id=".$PlayerId.";";
					if (!mysql_query($query))
						{
						$message = "Cannot update player with Id ".$PlayerId." in players";
						$aseco->addCall("ChatSendToLogin", array($message, $command['author']->login));
						$aseco->client->multiquery();
						$error++;
						}
					else
						$fix++;
					}
				}
			// SAME - DO NOTHING
			else
				$unchanged++;
			}
		mysql_free_result($result2);
		}
	mysql_free_result($result);

	$width = 28;
	$height = 25;
	$hw = $width/2;
	$hh = $height/2;

	$manialink = "<?xml version='1.0' encoding='utf-8' ?><manialink id='80'><frame posn='-$hw $hh $manialinkstack'>";
	$manialink .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";
		
	$manialink .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$manialink .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="Fixwins - Backup"/>';
	$manialink .= "<quad posn='0 -4 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='$hw -4.5 1' halign='center' textsize='2' text='Result of synchronistation'/>";	
	$manialink .= "<label posn='$hw -8 1' halign='center' autonewline='1' sizen='".($width-2)." $height' textsize='2' text='$unchanged players unchanged.\n$new new players added.\n$update players updated.\n$fix fixed entries.\n$error errors.'/>";

	$manialink .= 	"<label posn='$hw -21 1' halign='center' style='CardButtonSmall' action='12' text='Close'/>";

	$manialink .= "</frame></manialink>";
		
	$aseco->addcall('SendDisplayManialinkPageToLogin', array($command['author']->login, $manialink, 0, TRUE));
	}

/** raise stats for top5 position
**********************************
function player_raise ( $aseco, $playerId, $rank )
	{

	$query = "SELECT * FROM mistral_wins WHERE playerId=\"" . $playerId . "\";";
	$result = mysql_query($query);

	if (mysql_num_rows($result) == 0)
		{
		$query = "INSERT INTO mistral_wins VALUES ('" . $playerId . "', 0, 0, 0, 0, 0)";
		if (!mysql_query($query))
			{
			$aseco->console_text("Cannot insert player.");
			return;
			}
		$first=0;
		$second=0;
		$third=0;
		$fourth=0;
		$fifth=0;
		}
	else
		{
		$row = mysql_fetch_array($result);
		$first = $row['first'];
		$second = $row['second'];
		$third = $row['third'];
		$fourth = $row['fourth'];
		$fifth = $row['fifth'];
		}

	if ($rank == 1) 
		$first++;
	elseif ($rank == 2)
		$second++;
	elseif ($rank == 3)
		$third++;
	elseif ($rank == 4)
		$fourth++;
	elseif ($rank == 5)
		$fifth++;

	$query = "UPDATE mistral_wins SET first='" . $first .
		"', second='" . $second .
		"', third='" . $third .
		"', fourth='" . $fourth .
		"', fifth='" . $fifth .
		"' WHERE playerId='" . $playerId . "'";
	if (!mysql_query($query))
		$aseco->console_text("Cannot update player.");
	}
*/

/** Build MistralWins Table
****************************/
function buildMistralWins($aseco)
	{
	$minrecords = 10; /* Must be 5 at least */

	$aseco->addCall("AutoSaveReplays", array(FALSE));
	$aseco->console_text("|...Building mistral_wins");

	mistral_checklog();

	foreach($aseco->server->players->player_list as $player)
		{
		$lastjukebox = $player->mistal['lastjukebox'];
		if ($lastjukebox > 0)
			$player->mistal['lastjukebox'] = $lastjukebox-1;
		}


	$query = "DROP TABLE mistral_wins";
	mysql_query($query);

	$query = "CREATE TABLE `mistral_wins` (
		`playerId` mediumint(9) unsigned NOT NULL auto_increment,
		`first` int(10) unsigned NOT NULL default '0',
		`second` int(10) unsigned NOT NULL default '0',
		`third` int(10) unsigned NOT NULL default '0',
		`fourth` int(10) unsigned NOT NULL default '0',
		`fifth` int(10) unsigned NOT NULL default '0',
		PRIMARY KEY  (`playerId`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	if (!mysql_query($query))
		{
		$aseco->console_text("Cannot create table \"mistral_wins\"!.");
		return;
		}


	$query = "SELECT Id FROM challenges";
	$result = mysql_query($query);

	$players = array();
	while ($row = mysql_fetch_row($result))
		{
		$query = "SELECT PlayerId FROM records r WHERE ChallengeId=\"" . $row[0] . "\" ORDER BY Rank LIMIT " . $minrecords;
		$result2 = mysql_query($query);

		/* Only compute if $minrecords where driven on a track
		******/
		if (mysql_num_rows($result2) == $minrecords)
			{
			$row2 = mysql_fetch_row($result2);
			$players[$row2[0]]["first"]++;
			$players[$row2[0]]["id"] = $row2[0];
			$row2 = mysql_fetch_row($result2);
			$players[$row2[0]]["second"]++;
			$players[$row2[0]]["id"] = $row2[0];
			$row2 = mysql_fetch_row($result2);
			$players[$row2[0]]["third"]++;
			$players[$row2[0]]["id"] = $row2[0];
			$row2 = mysql_fetch_row($result2);
			$players[$row2[0]]["fourth"]++;
			$players[$row2[0]]["id"] = $row2[0];
			$row2 = mysql_fetch_row($result2);
			$players[$row2[0]]["fifth"]++;
			$players[$row2[0]]["id"] = $row2[0];
			}
		}

	foreach ($players as $player)
		{
		if (!isset($player["first"]))
			$player["first"] = 0;
		if (!isset($player["second"]))
			$player["second"] = 0;
		if (!isset($player["third"]))
			$player["third"] = 0;
		if (!isset($player["fourth"]))
			$player["fourth"] = 0;
		if (!isset($player["fifth"]))
			$player["fifth"] = 0;
		$query = "Insert into mistral_wins (PlayerId, first, second, third, fourth, fifth) VALUES (".$player["id"].",".$player["first"].",".$player["second"].",".$player["third"].",".$player["fourth"].",".$player["fifth"].")";
		if (!mysql_query($query))
			$aseco->console_text("Cannot insert player: $query");
		}

	$aseco->console_text("|...Done!");
	}

/** doMyLog
*****************/
function doMyLog($text)
	{
	global $logfile;

	$message = date("d.m. H:i")." ".$text;
	$message = stripColors($message);

	$query = "INSERT INTO mistral_chat (message) VALUES (".quotedString(utf8_encode($message)).")";
	mysql_query($query);
/*
	$logfile = fopen("chatlog.txt", "a+");
	fwrite($logfile, $message."\r\n");
	fclose($logfile);
*/
	}

// aseco.php -> newPlayer()
/**************************/
// logJoin($player_item->nickname, $player_item->login, $player_item->ip);
function logJoin($nickname, $login, $ip)
	{
	$message = "     ++++ ".$nickname." (".$login."@".$ip.") JOINED. ++++";
	$message = stripColors($message);
	doMyLog($message);
	}

// aseco.php -> playerDisconnect()
/**********************************/
// logLeave($player_item->nickname, $player_item->login, $player_item->ip);
function logLeave($nickname, $login, $ip)
	{
	$message = "     ---- ".$nickname." (".$login."@".$ip.") LEFT. ----";
	$message = stripColors($message);
	doMyLog($message);
	}

// onNewChallenge
/*////*************************/
function logChallenge($aseco, $challenge)
	{
 	$aseco->client->query("GetServerCoppers");
	$coppers = $aseco->client->getResponse();

	$playercount = count($aseco->server->players->player_list);
	
	$name = $challenge->name;
	$env = $challenge->environment;
	
	$message = "     **** $name ($env; $playercount players online; $coppers coppers) PLAYING. ****";
	$message = stripColors($message);
	doMyLog($message);
	}


/** WORD TRIGGER
*****************/
function checkChatForWords($aseco, $command)
	{
	global $trigger, $asecoadmin;
	
	$message = "" ;

	foreach ($trigger as $check)
		{
		if (stristr($command[2], $check[0]))
			{
			$message = $check[1];
			break;
			}
		}

	if (stristr($command[2], "uhrzeit"))
		$message = "The time is ".date("H:i - l, j.n.Y").".";

	if ( $message != "" )
		{
		$aseco->addCall("ChatSendServerMessage", array($message));
		}

	$loginid = $command[0];
	$login = $command[1];
	$nickname = getNicknameFromLogin($login);
	$message = $command[2];
	$logmsg = "";

	if ( substr(strtolower($message),0,8) == 'mistral?')
		{
		$aseco->addCall("ChatSendServerMessage", array('$l[http://www.google.com/search?hl=en&q=mistral&btnG=Google+Search]Click here$l if you have questions about "Mistral".'));		
		}


	if ($loginid != 0)
		{
		if ( !("/team" == substr($message, 0, 5) || "/tm" == substr($message, 0, 3)) )
			$logmsg = "[".$nickname."] ".$message."     - (".$login.")";
		}
	else
		{
		$prefix = "\$FC0[\$C00Admin";	
		if ( $prefix == substr($message, 0, strlen($prefix)) )
			$logmsg = $message."     - (CONSOLE)";
		else
			{
			$prefix = "\$FC0[\$00CAdmin";
			if ( $prefix == substr($message, 0, strlen($prefix)) )
				$logmsg = $message."     - (CONSOLE)";
			else
				{
				$prefix = "\$s\$0F0";
				if ( $prefix == substr($message, 0, strlen($prefix)) )
					$logmsg = "[Admin - Tweety] ".$message."     - (SERVERMANIA)";
				else
					{
					$prefix = "\$s\$0FF";
					if ( $prefix == substr($message, 0, strlen($prefix)) )
						$logmsg = "[Admin - Mistral] ".$message."     - (SERVERMANIA)";
					}
				}
			}
		}

	if ( $logmsg != "" )
		doMyLog($logmsg);
	}

/** OFFICIAL
****************/
function chat_official(&$aseco, $command)
	{
	$message = formatText('$i{1} {#emotic}typed "/official" - NOOBALARM!!!',
	$command['author']->nickname,
	$command['params']);
	$message = $aseco->formatColors($message);
  	$aseco->addCall("ChatSendServerMessage", array($message));
	}

/** WWOOWWOOWW
****************/
function chat_wow($aseco, $command)
	{
	$msg = '$z['.$command['author']->nickname.'$z] ';
	$msg .= "\$000.\$100.\$200.\$300.\$400w\$500w\$600w\$700w\$800W\$900W\$a00W\$b00W\$c00w\$d00w\$e00w\$f00w\$f00.\$f10. \$f20.\$f30.\$f40o\$f50o\$f60o\$f70o\$f80O\$f90O\$fa0O\$fb0O\$fc0o\$fd0o\$fe0o\$ff0o\$ff0.\$ff1. \$ff2.\$ff3.\$ff4w\$ff5w\$ff6w\$ff7w\$ff8W\$ff9W\$ffaW\$ffbW\$ffcw\$ffdw\$ffew\$fffw\$eff.\$dff. \$cff.\$bff.\$affo\$9ffo\$8ffo\$7ffo\$6ffO\$5ffO\$4ffO\$3ffO\$2ffo\$1ffo\$0ffo\$0fe.\$0fd. \$0fc.\$0fbw\$0faw\$0f9w\$0f8W\$0f7W\$0f6W\$0f5w\$0f4w\$0f3w\$0f2.\$0f1.\$0f0.";
	$aseco->addCall("ChatSendServerMessage", array($msg));
	}

/** HAPPY BIRTHDAY
****************/
function chat_hb($aseco, $command)
	{
	$admin = $command['author'];
	$player = $aseco->server->players->getPlayer($command['params']);

	if ( !isset($player) )
		{
		$message = formatText('{#server}>> ' . $player . ' is not a valid player login. Use /players to find the correct login.');
		$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $admin->login));
		}
	else
		{
		$message = formatText('$i{1} {#emotic}sings {2} {3}{#emotic}, happy birthday to you!',
		$command['author']->nickname,
		"\$000H\$100a\$200p\$300p\$400y \$500B\$600i\$700r\$800t\$900h\$a00d\$b00a\$c00y \$d00t\$e00o \$f00y\$f00o\$f10u\$f20, \$f30h\$f40a\$f50p\$f60p\$f70y \$f80b\$f90i\$fa0r\$fb0t\$fc0h\$fd0d\$fe0a\$ff0y \$ff0t\$ff1o \$ff2y\$ff3o\$ff4u\$ff5, \$ff6h\$ff7a\$ff8p\$ff9p\$ffay \$ffbb\$ffci\$ffdr\$ffet\$fffh\$effd\$dffa\$cffy \$bffd\$affe\$9ffa\$8ffr",
		$player->nickname);
		$message = $aseco->formatColors($message);
		$aseco->addCall("ChatSendServerMessage", array($message));
		}
	}
?>

