<?php
/*****************************************************************************************/
/*
/* plugin.teamranking.php v 1.11 (c) 2006 by H. Sawade
/*
/* Tested With ASECO 0.61b, RASP 1.2a, plugin.localdatabase.php
/*
/* Basic configuration:
/* - $teamtable: name of database table to hold the teams
/* - $membertable: name of database table to hold teammembers
/* - $show_rank_auto: teamrank automatically shown on trackchange/playerjoin
/* - $hide_rank_no_team: no "You are no member of any registered team" when rank is shown.
/* - $precision: must be between 0 and 4. Number of decimals of rank when displayed.
/* 
/* New commands:
/* /team
/* /topteams
/* /teamrank
/* /tm alias for /team msg
/*
/* Howto:
/* - create a team (/team create ...)
/* 	shortname should be clantag or similar short identifier
/*	adminpass will be needed for team management
/*	playerpass will be needed for other players to join the team
/* 
/* - give the team a "nice", long name if you want to (/team rename ...)
/* 
/* - let other players join your team (they need to know shortname and playerpassword)
/* 
/* - chat with your team mates (/team msg ...)
/* 
/* What it does:
/*	The plugin will compute the averages of all team members as a team average.
/* 	Therefor it will use the already existing information from the RASP database.
/*  Teams with only one member get an average of 100 (no team yet).
/*  Teams with no members become deleted on new challenge.
/*****************************************************************************************/

global $teamtable, $membertable, $show_rank_auto, $hide_rank_no_team, $precision, $teamenvironment;

$teamtable="teams";
$membertable="teammembers";

Aseco::registerEvent('onStartup', 'startTeamScript');
Aseco::registerEvent('onNewChallenge', 'calculateTeamRanks');
Aseco::registerEvent('onNewChallenge', 'showTeamRankAll');
Aseco::registerEvent('onPlayerConnect', 'playerConnectTeam');
Aseco::addChatCommand('team', 'Manage teams /team for help');
Aseco::addChatCommand("topteams", "Displays the top 15 ranked teams");
Aseco::addChatCommand("teamrank", "Displays your current team rank");
Aseco::addChatCommand("tm", "alias for /team msg");
Aseco::registerEvent('onPlayerServerMessageAnswer', 'mistralTeamresponse');

/** Manialink response
***************************/
function mistralTeamresponse($aseco, $answer)
	{
	global $teamtable, $teamenvironment;

	$player = $aseco->server->players->getPlayer($answer[1]);
	$i = $answer[2];
	
	if ($i<40000 || $i>=40100)
		return;
	$i-=40000;

	$query = "SELECT Teamname FROM ".$teamtable." ORDER BY $teamenvironment ASC LIMIT $i";
	$result = mysql_query($query);
	for ($j=0; $j<$i; $j++)
		$row = mysql_fetch_row($result);
	$teamname = $row[0];
	mysql_free_result($result);
	
	$command['author'] = $player;
	$command['params'] = "list $teamname";
	chat_team($aseco, $command);
	}

/** onStartup->startTeamScript
***************************/
function startTeamScript(&$aseco)
	{
	global $teamtable, $membertable, $show_rank_auto, $hide_rank_no_team, $precision, $maxrecs;

	if ($precision<0)
		$precision=0;
	if ($precision>4)
		$precision=4;

	$query="CREATE TABLE `".$teamtable."` (
		`Id` mediumint(9) NOT NULL auto_increment,
		`Teamname` varchar(50) NOT NULL default '',
		`TeamDisplayname` varchar(50) NOT NULL default '',
		`Adminpass` varchar(50) NOT NULL default '',
		`Playerpass` varchar(50) NOT NULL default '',
		`Avg` INTEGER UNSIGNED NOT NULL default '".($maxrecs*10000)."',
		PRIMARY KEY  (`Id`),
		UNIQUE KEY `Teamname` (`Teamname`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	mysql_query($query);

	$query="ALTER TABLE `$teamtable`
		ADD `Speed` INTEGER UNSIGNED NOT NULL default '".($maxrecs*10000)."',
		ADD `Alpine` INTEGER UNSIGNED NOT NULL default '".($maxrecs*10000)."',
		ADD `Rally` INTEGER UNSIGNED NOT NULL default '".($maxrecs*10000)."',
		ADD `Bay` INTEGER UNSIGNED NOT NULL default '".($maxrecs*10000)."',
		ADD `Island` INTEGER UNSIGNED NOT NULL default '".($maxrecs*10000)."',
		ADD `Coast` INTEGER UNSIGNED NOT NULL default '".($maxrecs*10000)."',
		ADD `Stadium` INTEGER UNSIGNED NOT NULL default '".($maxrecs*10000)."'";
	mysql_query($query);
	
	$query="CREATE TABLE `".$membertable."` (
		`PlayerId` mediumint(9) NOT NULL,
		`TeamId` mediumint(9) NOT NULL,
		PRIMARY KEY  (`PlayerId`),
		INDEX `TeamId` (`TeamId`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";
	mysql_query($query);
	}

/** /team msg alias
***************************/
function chat_tm($aseco, $command)
	{
	$command['params']="msg ".$command['params'];
	chat_team($aseco, $command);
	}

/** playerConnectTeam
***************************/
function playerConnectTeam($aseco, $user)
	{
	global $show_rank_auto;

	if ($show_rank_auto)
		showTeamRank($aseco, $user->login);
	}

/** showTeamRankAll
***************************/
function showTeamRankAll($aseco, $challenge)
	{
	global $show_rank_auto;

	if ($show_rank_auto)
		foreach($aseco->server->players->player_list as $user)
			showTeamRank($aseco, $user->login);
	}

/** showTeamRank
***************************/
function showTeamRank($aseco, $login)
	{
	global $rasp, $teamtable, $membertable, $hide_rank_no_team, $precision, $maxrecs;

	$teamname = getTeamname($login);
	// NO TEAMMEMBER
	if ( $teamname == "" )
		{
		if ($hide_rank_no_team)
			return;
		$message = "{#server}>{#record} You are no member of any registered team on this server";
		$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
		return;
		}

	$average = getTeamAverage($teamname);

	$maxaverage = $maxrecs*10000;
	$query = "SELECT count(Id) FROM ".$teamtable." WHERE Avg<$maxaverage";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$teams = $row[0];
	mysql_free_result($result);

	$query = "SELECT count(Id) FROM ".$teamtable." WHERE Avg<".$average;
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$position = $row[0]+1;
	mysql_free_result($result);

	$average = round($average/10000, $precision);
	$message = "{#server}>{#record} Your team's server rank is {#highlite}".$position."{#record}/{#highlite}".$teams."{#record} Average : {#highlite}".$average;
	$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
	}

/** /teamrank command
***************************/
function chat_teamrank($aseco, $command)
	{
	showTeamRank($aseco, $command['author']->login);
	}

/** showTeamMembers (Multipage)
***************************/
function showTeamMembers($player, $teamname)
{
	global $membertable, $precision, $manialinkstack;
	
	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;

	$ppp=15;

	$teamid = getTeamId($teamname);
	
	$query = "SELECT t.PlayerId,p.NickName,r.avg,avg IS NULL AS isnull FROM players p, $membertable t left join rs_rank r on (t.playerid=r.playerid) where t.teamid=".$teamid." and p.id=t.playerid order by isnull, r.avg";
	$result = mysql_query($query);

	$id = 10;
	$na = 55;
	$av = 15;
	$width = $id+$na+$av;
	$height = $ppp*2+12;
	$hw = $width/2;
	$hh = $height/2;
	$qw = $width/4;
	$tqw = 3*$width/4;
	$player->msgsw=$width;
	$player->msgsh=$height;

	$header = "<?xml version='1.0' encoding='utf-8' ?><manialink id='140'><frame posn='-$hw $hh $manialinkstack'>";
	$header .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";
	$header .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$header .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="'.htmlspecialchars(getTeamDisplayname($teamname)).'"/>';

	$header .= "<quad posn='0 -4 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$header .= "<label posn='".($id/2)." -4.5 1' halign='center' textsize='2' text='ID'/>";	
	$header .= "<label posn='".($id)." -4.5 1' halign='left' textsize='2' text='Name'/>";	
	$header .= "<label posn='".($id+$na+$av/2)." -4.5 1' halign='center' textsize='2' text='Average'/>";	

	$detail = '<label posn="'.($id/2).' {POSN} 1" halign="center" textsize="2" text="{ID}"/>';	
	$detail .= '<label posn="'.($id+1).' {POSN} 1" halign="left" textsize="2" text="{NAME}"/>';	
	$detail .= "<label posn='".($id+$na+$av/2)." {POSN}  1' halign='center' textsize='2' text='{AVERAGE}'/>";
	
	$page=1;
	$line=0;
	$content = $header;
	$player->msgs = array();
	$player->msgs['curpage'] = 0;
	$posn = -6;
	while ($row = mysql_fetch_row($result))
		{
		$posn -= 2;
		$playerid = $row[0];
		$nickname = $row[1];
		$average = round($row[2]/10000, $precision);
		if ($average == 0)
			$average = "---";
		$s = $detail;
		$s = str_replace('{POSN}', $posn, $s);
		$s = str_replace('{ID}', $playerid, $s);
		$s = str_replace('{NAME}', htmlspecialchars($nickname), $s);
		$s = str_replace('{AVERAGE}', $average, $s);
		$content.=$s;
		$line++;
		if ($line == $ppp)
			{
			$posn = -6;
			$line=0;
			$player->msgs[$page] = $content;
			$content=$header;
			$page++;
			$player->msgs['curpage'] = 1;
			}
		}
	mysql_free_result($result);
	if ($line > 0)
		{
		$player->msgs[$page] = $content;
		$player->msgs['curpage'] = 1;
		}
	show_multi_msg($player);
}

/** /topteams command (Multipage)
***************************/
function chat_topteams($aseco, $command, $environment="")
	{
	global $rasp, $teamtable, $membertable, $precision, $teamenvironment, $manialinkstack;

	$tpp = 21;

	$player = $command['author'];

	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;

	if ($environment == "")
		$environment = "Avg";

	$teamenvironment = $environment;

	$allaction = 30006;
	$speedaction = 30035;
	$alpineaction = 30036;
	$rallyaction = 30037;
	$bayaction = 30038;
	$islandaction = 30039;
	$coastaction = 30040;
	$stadiumaction = 30041;

	$allbg = "BgTitle3_4";
	$speedbg = "BgTitle3_4";
	$alpinebg = "BgTitle3_4";
	$rallybg = "BgTitle3_4";
	$stadiumbg = "BgTitle3_4";
	$baybg = "BgTitle3_4";
	$islandbg = "BgTitle3_4";
	$coastbg = "BgTitle3_4";
	
	if ($environment == "Avg")
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

	$po = 5;
	$na = 20;
	$ln = 55;
	$av = 15;
	$width = $po+$na+$ln+$av;
	$height = $tpp*2+18;
	$hw = $width/2;
	$hh = $height/2;
	$qw = $width/4;
	$tqw = 3*$width/4;
	$player->msgsw=$width;
	$player->msgsh=$height;

	$header = "<?xml version='1.0' encoding='utf-8' ?><manialink id='130'><frame posn='-$hw $hh $manialinkstack'>";
	$header .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";
	$header .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$header .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="Top Teams"/>';

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
	$header .= "<label posn='".($po/2)." -10.5 1' halign='center' textsize='2' text='Pos.'/>";	
	$header .= "<label posn='".($po+$na/2)." -10.5 1' halign='center' textsize='2' text='Name'/>";	
	$header .= "<label posn='".($po+$na)." -10.5 1' halign='left' textsize='2' text='Longname (click for members)'/>";	
	$header .= "<label posn='".($po+$na+$ln+$av/2)." -10.5 1' halign='center' textsize='2' text='Average'/>";	

	$detail = '<label posn="'.($po/2).' {POSN} 1" halign="center" textsize="2" text="{POS}."/>';	
	$detail .= '<label posn="'.($po+$na/2).' {POSN} 1" halign="center" textsize="2" text="{NAME}"/>';	
	$detail .= '<quad posn="'.($po+$na).' {POSN} 1" sizen="'.$ln.' 2" action="{ACTION}" style="Bgs1InRace" substyle="NavButton"/>';	
	$detail .= '<label posn="'.($po+$na+1).' {POSN} 1" halign="left" textsize="2" text="{LONGNAME}"/>';	
	$detail .= "<label posn='".($po+$na+$ln+$av/2)." {POSN}  1' halign='center' textsize='2' text='{AVERAGE}'/>";	

	$query = "SELECT Teamname, $environment FROM ".$teamtable." ORDER BY $environment ASC";
	$result = mysql_query($query);

	$page=1;
	$line=0;
	$content = $header;
	$player->msgs = array();
	$player->msgs['curpage'] = 0;
	$i = 1;
	$posn = -12;
	while ($row = mysql_fetch_row($result))
		{
		$posn -= 2;
		$teamname = $row[0];
		$teamid = getTeamId($teamname);
		$query = "SELECT count(PlayerId) FROM ".$membertable." WHERE TeamId=".$teamid;
		$result2 = mysql_query($query);
		$row2 = mysql_fetch_row($result2);
		$members = $row2[0];
		mysql_free_result($result2);
		$average = round($row[1]/10000, $precision);
		$s = $detail;
		$s = str_replace('{POS}', $i, $s);
		$s = str_replace('{POSN}', $posn, $s);
		$s = str_replace('{ACTION}', $i+40000, $s);
		$s = str_replace('{NAME}', htmlspecialchars($teamname), $s);
		$s = str_replace('{LONGNAME}', htmlspecialchars(getTeamDisplayname($teamname))."\$z ($members members)", $s);
		$s = str_replace('{AVERAGE}', $average, $s);
		$content .= $s;
		$line++;
		if ($line == $tpp)
			{
			$line=0;
			$posn = -12;
			$player->msgs[$page] = $content;
			$player->msgs['curpage'] = 1;
			$content = $header;
			$page++;
			}
		$i++;
		}
	mysql_free_result($result);
	if ($line > 0)
		{
		$player->msgs[$page] = $content;
		$player->msgs['curpage'] = 1;
		}
	show_multi_msg($player);
	}

/** onNewChallenge->calculateTeamRanks
***************************/
function calculateTeamRanks($aseco, $challenge)
	{
	global $teamtable, $membertable, $maxrecs;

	$aseco->console_text("|...Calculating team ranks");
	$query = "SELECT Id FROM ".$teamtable;
	$result = mysql_query($query);
	// FOR EACH TEAM
	while ($row = mysql_fetch_row($result))
		{
		$teamid = $row[0];
		$members=0;
		$query = "SELECT count(PlayerId) FROM ".$membertable." WHERE TeamId=".$teamid;
		$result2 = mysql_query($query);
		$row2 = mysql_fetch_row($result2);
		$members = $row2[0];
		mysql_free_result($result2);
		
		if ($members == 0)
			{
			$query = "DELETE FROM teams where id=".$teamid;
			mysql_query($query);
			}
		else
			{
			$environments = array("Avg", "Speed", "Rally", "Alpine", "Coast", "Bay", "Island", "Stadium");
			
			foreach ($environments as $environment)
				{
				$envcolumn = strtolower($environment);

				if ($members == 1)
					$value=$maxrecs*10000;
				else
					{
					$query = "SELECT round(sum(l.$envcolumn)/count(l.$envcolumn)) FROM (SELECT r.$envcolumn FROM $membertable t, rs_rank r where t.playerid=r.playerid and t.teamid=$teamid and $envcolumn>0 order by $envcolumn asc limit 5) l";
					$result2 = mysql_query($query);
					$row = mysql_fetch_row($result2);
					$value = $row[0];
					mysql_free_result($result2);
					if ($value == 0)
						$value = $maxrecs*10000;
					}
				$query = "UPDATE ".$teamtable." SET $environment=".$value." WHERE Id=".$teamid;
				mysql_query($query);	
				}		
			}
		}
	mysql_free_result($result);
	$aseco->console_text("|...Done!");
	}

/** getTeamid from Teamname
***************************/
function getTeamId($teamname)
	{
	global $teamtable, $membertable;

	$value = 0;
	$query = 'SELECT Id from '.$teamtable.' WHERE Teamname="'.$teamname.'"';
	$result = mysql_query($query);
  	if (mysql_num_rows($result) > 0)
		{
	    	$row = mysql_fetch_row($result);
    		$value = $row[0];
  		}
	mysql_free_result($result);
	return $value;
  	}

/** getTeamDisplayname from Teamname
***************************/
function getTeamDisplayname($teamname)
	{
	global $teamtable, $membertable;

	$value = $teamname;
	$query = 'SELECT TeamDisplayname from '.$teamtable.' WHERE Teamname="'.$teamname.'"';
	$result = mysql_query($query);
  	if (mysql_num_rows($result) > 0)
		{
	    	$row = mysql_fetch_row($result);
    		$value = $row[0];
  		}
	mysql_free_result($result);
	return $value;
  	}

/** getTeamAverage from Teamname
***************************/
function getTeamAverage($teamname)
	{
	global $teamtable, $membertable;

	$value = 0;
	$query = 'SELECT Avg from '.$teamtable.' WHERE Teamname="'.$teamname.'"';
	$result = mysql_query($query);
  	if (mysql_num_rows($result) > 0)
		{
	    	$row = mysql_fetch_row($result);
    		$value = $row[0];
  		}
	mysql_free_result($result);
	return $value;
  	}

/** getTeamname from Login
***************************/
function getTeamname($login)
	{
	global $teamtable, $membertable;
	
	$teamname = '';
	$query = 'SELECT TeamId FROM '.$membertable.' WHERE PlayerId='.getPlayerIdFromLogin($login);
	$result = mysql_query($query);
	// ALREADY MEMBER OF A TEAM
	if (mysql_num_rows($result) != 0)
		{
		$row = mysql_fetch_row($result);
		$teamid = $row[0];
		$query = 'SELECT Teamname FROM '.$teamtable.' WHERE Id='.$teamid;
		$result2 = mysql_query($query);
		$row = mysql_fetch_row($result2);
		$teamname = $row[0];
		mysql_free_result($result2);
		}
	mysql_free_result($result);
	return $teamname;
	}

/** getTeamAdminpass from Teamname
***************************/
function getTeamAdminpass($teamname)
	{
	global $teamtable, $membertable;

	$adminpass = '';
	$query = 'SELECT Adminpass FROM '.$teamtable.' WHERE Teamname="'.$teamname.'"';
	$result = mysql_query($query);
	if (mysql_num_rows($result) != 0)
		{
		$row = mysql_fetch_row($result);
		$adminpass = $row[0];
		}
	mysql_free_result($result);
	return $adminpass;
	}

/** getTeamPlayerpass from Teamname
***************************/
function getTeamPlayerpass($teamname)
	{
	global $teamtable, $membertable;

	$playerpass = '';
	$query = 'SELECT playerpass FROM '.$teamtable.' WHERE Teamname="'.$teamname.'"';
	$result = mysql_query($query);
	if (mysql_num_rows($result) != 0)
		{
		$row = mysql_fetch_row($result);
		$playerpass = $row[0];
		}
	mysql_free_result($result);
	return $playerpass;
	}

/** /team command
***************************/
function chat_team($aseco, $command)
	{
	global $teamtable, $membertable, $precision;

	$user = $command['author'];
	$login = $user->login;

	if ($login=="")
		return;

	$arguments = explode (" ", $command['params']);
	
	if (sizeof($arguments) == 1)
		if ($arguments[0] == "")
			{
			$teamname = getTeamname($login);
			$teamdisplayname = getTeamDisplayname($teamname);
			if ($teamname == "")
				$teamname = "no team";
			else
				$teamname = $teamdisplayname."\$z (".$teamname."\$z)";
			$message="You are member of ".$teamname."\n\n".
				"      \$F88/team create\$z <Shortname> <Adminpass> <Playerpass>\n".
				"      \$F88/team join\$z <Shortname> <Playerpass>\n".
				"      \$F88/team msg\$z <Message to your team>\n".
				"      \$F88/team leave\$z\n".
				"      \$F88/team list\$z [<Shortname>]\n".
				"      \$F88/team rename\$z <Adminpass> <Longname>\n".
				"      \$F88/team kick\$z <Adminpass> <PlayerId>\n".
				"      \$F88/team playerpass\$z <Adminpass> <New Playerpass>\n".
				"      \$F88/team adminpass\$z <Old Adminpass> <New Adminpass>\n\n".
				"      \$88FExample: \$8F8/team create MyT theadmin joinin\$z\n".
				"                \$8F8MyT\$z - shortname of team to create\n".
				"                \$8F8theadmin\$z - set adminpassword of team\n".
				"                \$8F8joinin\$z - set playerpassword of team (needed to join)\n".
				"      \$88FHint:\$z \$BBBShortname\$z should be something short, like your \$BBBClantag\$z.\n".
				"                 You can change the Displayname later with \$BBB/team rename\$z\n".
				"      \$88FRelated Commands: \$BBB/topteams /teamrank";
			popup_msg($login, $message);
			return;
			}
	/* CREATE
	 ***********************/
	if ($arguments[0] == "create")
		{
		if (sizeof($arguments) != 4)
			{
			$message="{#server}>> Usage: /team create <teamname> <adminpass> <playerpass>";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$teamname = $arguments[1];
		$adminpass = $arguments[2];
		$playerpass = $arguments[3];

		$query="INSERT INTO ".$teamtable." (Teamname, TeamDisplayname, Adminpass, Playerpass) VALUES (".
			quotedString($teamname).",".quotedString($teamname).",'".$adminpass."','".$playerpass."')";
		if (!mysql_query($query))
			{
			$message="{#server}>> Team \"".$teamname."\" already exists";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$message=$aseco->formatColors("{#server}>> ".$user->nickname."{#server} created team \"".$teamname."\"");
		$aseco->addCall('ChatSendServerMessage',array($message));
		$arguments=array("join", $teamname, $playerpass);
		}
	/* JOIN
	 ***********************/
	if ($arguments[0] == "join")
		{
		if (sizeof($arguments) != 3)
			{
			$message = "{#server}>> Usage: /team join <teamname> <playerpass>";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$teamname = getTeamname($login);
		// ALREADY MEMBER OF A TEAM
		if ( $teamname != "" )
			{
			$message = "{#server}>> You are already member of \"".getTeamDisplayname($teamname)."{#server}\" (".$teamname."). Leave that team first";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// JOIN TEAM
		$teamname = $arguments[1];
		$playerpass = $arguments[2];
		// WRONG PASSWORD
		if ( $playerpass != getTeamPlayerpass($teamname) )
			{
			$message = "{#server}>> Password incorrect for team \"".$teamname."\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// OK - JOIN
		$teamid = getTeamId($teamname);
		if ($teamid == 0)
			{
			$message = "{#server}>> Failed to get TeamId for \"".$teamname."\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$playerid = getPlayerIdFromLogin($login);
		if ($playerid == 0)
			{
			$message = "{#server}>> Failed to get PlayerId for \"".$login."\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$query = 'INSERT into '.$membertable.' (PlayerId,TeamId) VALUES ('.$playerid.','.$teamid.')';
		// SUCCESSFUL
		if ( mysql_query($query) )
			{
			$message=$aseco->formatColors("{#server}>> ".$user->nickname."{#server} joined team \"".getTeamDisplayname($teamname)."\"");
			$aseco->addCall('ChatSendServerMessage',array($message));
			calculateTeamRanks($aseco, "");
			return;
			}
		// FAILED
		$message = "{#server}>> Failed to join \"".$teamname."\"";
		$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
		return;
		}
	/* LEAVE
	 ***********************/
	if ($arguments[0] == "leave")
		{
		if (sizeof($arguments) != 1)
			{
			$message = "{#server}>> Usage: /team leave";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$teamname = getTeamname($login);
		// NOT MEMBER OF A TEAM
		if ( $teamname == "" )
			{
			$message = "{#server}>> You are not member of any team";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// LEAVE TEAM
		$playerid = getPlayerIdFromLogin($login);
		if ($playerid == 0)
			{
			$message = "{#server}>> Failed to get PlayerId for \"".$login."\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$query = "DELETE FROM ".$membertable." WHERE PlayerId=".$playerid;
		// SUCCESS
		if ( mysql_query($query) )
			{
			$message=$aseco->formatColors("{#server}>> ".$user->nickname."{#server} left team \"".getTeamDisplayname($teamname)."\"");
			$aseco->addCall('ChatSendServerMessage',array($message));
			calculateTeamRanks($aseco, "");
			return;
			}
		// FAILED
		$message = "{#server}>> Failed to leave \"".$teamname."\"";
		$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
		return;
		}
	/* LIST
	 ***********************/
	if ($arguments[0] == "list")
		{
		if (sizeof($arguments) > 2)
			{
			$message = "{#server}>> Usage: /team list [shortname]";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		if (sizeof($arguments) == 1)
			{
			$teamname = getTeamname($login);
			// NOT MEMBER OF A TEAM
			if ( $teamname == "" )
				{
				$message = "{#server}>> You are not member of any team. Try \"/team list <shortname>\"";
				$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
				return;
				}
			}
		else
			$teamname = $arguments[1];

		$teamid = getTeamId($teamname);
		if ($teamid == 0)
			{
			$message = "{#server}>> there is no team \"".$teamname."{#server}\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		showTeamMembers($user, $teamname);
		return;
		}
	/* RENAME
	 ***********************/
	if ($arguments[0] == "rename")
		{
		if (sizeof($arguments) < 3)
			{
			$message = "{#server}>> Usage: /team rename <adminpass> <longname>";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$adminpass = $arguments[1];
		$longname = "";
		for ($i = 2; $i < sizeof($arguments); $i++)
			{
			if ( $i == (sizeof($arguments)-1))
				$longname .= $arguments[$i];
			else
				$longname .= $arguments[$i]." ";
			}
		$teamname = getTeamname($login);
		// NOT MEMBER OF A TEAM
		if ( $teamname == "" )
			{
			$message = "{#server}>> You are not member of any team";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$teamid = getTeamId($teamname);
		if ($teamid == 0)
			{
			$message = "{#server}>> there is no team \"".$teamname."{#server}\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// WRONG PASSWORD
		if ( $adminpass != getTeamAdminpass($teamname) )
			{
			$message = "{#server}>> Password incorrect for team \"".getTeamDisplayname($teamname)."\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// OK - CHANGE LONGNAME
		$query = "UPDATE ".$teamtable." SET TeamDisplayname=".quotedString($longname)." WHERE Id=".$teamid;
		if ( mysql_query($query) )
			{
			$message=$aseco->formatColors("{#server}>> ".$user->nickname."{#server} set teamname to \"".getTeamDisplayname($teamname)."{#server}\"");
			$aseco->addCall('ChatSendServerMessage',array($message));
			return;
			}
		// FAILED
		$message = "{#server}>> Failed to set \"".$teamname."{#server}\" as new teamname";
		$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
		return;
		}
	/* PLAYERPASS
	 ***********************/
	if ($arguments[0] == "playerpass")
		{
		if (sizeof($arguments) != 3)
			{
			$message = "{#server}>> Usage: /team playerpass <adminpass> <newplayerpass>";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$adminpass = $arguments[1];
		$playerpass = $arguments[2];
		$teamname = getTeamname($login);
		// NOT MEMBER OF A TEAM
		if ( $teamname == "" )
			{
			$message = "{#server}>> You are not member of any team";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$teamid = getTeamId($teamname);
		if ($teamid == 0)
			{
			$message = "{#server}>> there is no team \"".$teamname."{#server}\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// WRONG PASSWORD
		if ( $adminpass != getTeamAdminpass($teamname) )
			{
			$message = "{#server}>> Password incorrect for team \"".getTeamDisplayname($teamname)."\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// OK - CHANGE PLAYERPASS
		$query = "UPDATE ".$teamtable." SET Playerpass='".$playerpass."' WHERE Id=".$teamid;
		if ( mysql_query($query) )
			{
			$message=$aseco->formatColors("{#server}>> ".$user->nickname."{#server} set new player password for \"".getTeamDisplayname($teamname)."{#server}\"");
			$aseco->addCall('ChatSendServerMessage',array($message));
			return;
			}
		// FAILED
		$message = "{#server}>> Failed to set player password for \"".$teamname."{#server}\"";
		$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
		return;
		}
	/* ADMINPASS
	 ***********************/
	if ($arguments[0] == "adminpass")
		{
		if (sizeof($arguments) != 3)
			{
			$message = "{#server}>> Usage: /team adminpass <oldadminpass> <newadminpass>";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$adminpass = $arguments[1];
		$newadminpass = $arguments[2];
		$teamname = getTeamname($login);
		// NOT MEMBER OF A TEAM
		if ( $teamname == "" )
			{
			$message = "{#server}>> You are not member of any team";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$teamid = getTeamId($teamname);
		if ($teamid == 0)
			{
			$message = "{#server}>> there is no team \"".$teamname."{#server}\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// WRONG PASSWORD
		if ( $adminpass != getTeamAdminpass($teamname) )
			{
			$message = "{#server}>> Password incorrect for team \"".getTeamDisplayname($teamname)."\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// OK - CHANGE ADMINPASS
		$query = "UPDATE ".$teamtable." SET Adminpass='".$newadminpass."' WHERE Id=".$teamid;
		if ( mysql_query($query) )
			{
			$message=$aseco->formatColors("{#server}>> ".$user->nickname."{#server} set new admin password for \"".getTeamDisplayname($teamname)."{#server}\"");
			$aseco->addCall('ChatSendServerMessage',array($message));
			return;
			}
		// FAILED
		$message = "{#server}>> Failed to set admin password for \"".$teamname."{#server}\"";
		$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
		return;
		}
	/* KICK
	 ***********************/
	if ($arguments[0] == "kick")
		{
		if (sizeof($arguments) != 3)
			{
			$message = "{#server}>> Usage: /team kick <adminpass> <playerid> #see \"/team list\" for playerid";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$adminpass = $arguments[1];
		$playerid = $arguments[2];
		$teamname = getTeamname($login);
		// NOT MEMBER OF A TEAM
		if ( $teamname == "" )
			{
			$message = "{#server}>> You are not member of any team";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$teamid = getTeamId($teamname);
		if ($teamid == 0)
			{
			$message = "{#server}>> there is no team \"".$teamname."{#server}\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// WRONG PASSWORD
		if ( $adminpass != getTeamAdminpass($teamname) )
			{
			$message = "{#server}>> Password incorrect for team \"".getTeamDisplayname($teamname)."\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		// OK - KICK
		$query = "DELETE FROM ".$membertable." WHERE PlayerId=".$playerid;
		if ( mysql_query($query) )
			{
			$message=$aseco->formatColors("{#server}>> ".$user->nickname."{#server} kicked ".getNicknameFromId($playerid)."{#server} from \"".getTeamDisplayname($teamname)."{#server}\"");
			$aseco->addCall('ChatSendServerMessage',array($message));
			calculateTeamRanks($aseco, "");
			return;
			}
		// FAILED
		$message = "{#server}>> Failed to kick player with id ".$playerid." (".getNicknameFromId($playerid)."{#server}) from \"".getTeamDisplayname($teamname)."{#server}\"";
		$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
		return;
		}
	/* MSG
	 ***********************/
	if ($arguments[0] == "msg")
		{
		if (sizeof($arguments) < 2)
			{
			$message = "{#server}>> Usage: /team msg <message>";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$adminpass = $arguments[1];
		$teammessage = "";
		for ($i = 1; $i < sizeof($arguments); $i++)
			{
			if ( $i == (sizeof($arguments)-1))
				$teammessage .= $arguments[$i];
			else
				$teammessage .= $arguments[$i]." ";
			}
		$teamname = getTeamname($login);
		// NOT MEMBER OF A TEAM
		if ( $teamname == "" )
			{
			$message = "{#server}>> You are not member of any team";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$teamid = getTeamId($teamname);
		if ($teamid == 0)
			{
			$message = "{#server}>> there is no team \"".$teamname."{#server}\"";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$query = "SELECT p.login FROM $membertable t, players p where t.playerid=p.id and t.teamid=".$teamid;
		$result = mysql_query($query);
		$message = "\$FFF[{#server}".$user->nickname."\$z\$s\$FFF:Team] ".$teammessage;
		while ($row = mysql_fetch_row($result))
			{
			$login = $row[0];
			foreach($aseco->server->players->player_list as $online)
				{
				if ($online->login == $login)
					{
					$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
					}
				}
			}
		return;
		}
	/* ADD (admin)
	 ***********************/
	if ($arguments[0] == "add" && $aseco->isAdmin($login))
		{
		if (sizeof($arguments) != 3)
			{
			$message = "{#server}>> Usage: /team add <player> <teamname>";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$playerlogin = $arguments[1];
		$theplayer = $aseco->server->players->getPlayer($playerlogin);
		$teamname = $arguments[2];
		$playerpass = getTeamPlayerpass($teamname);
		
		$thecommand['author'] = $theplayer;
		$thecommand['params'] = "join $teamname $playerpass";

		chat_team($aseco, $thecommand);
		return;
		}
	/* REMOVE (admin)
	 ***********************/
	if ($arguments[0] == "remove" && $aseco->isAdmin($login))
		{
		if (sizeof($arguments) != 2)
			{
			$message = "{#server}>> Usage: /team remove <player>";
			$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
			return;
			}
		$playerlogin = $arguments[1];
		$theplayer = $aseco->server->players->getPlayer($playerlogin);
		
		$thecommand['author'] = $theplayer;
		$thecommand['params'] = "leave";
		
		chat_team($aseco, $thecommand);
		return;
		}
	/* WRONG ARGUMENT
	 ***********************/
	$message="{#server}>> Unknown command \"/team ".$arguments[0]."\". Type \"/team\" for help";
	$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors($message), $login));
	return;
	}
?>
