<?php
//Plugin autoTime
//Changes Timelimit for TimeAttack dynamically depending on the next tracks authortime
//Written by ck|cyrus
//martin@die-webber.com
//www.chaoskrieger.com
//20.12.2006
//Some code snippets of jfreu plugin (tmu)
//Improved caluclation and added manialink display by Mistral

include_once ('includes/types.inc.php');
include_once ('includes/basic.inc.php');

Aseco::registerEvent("onEndRace", "autotimelimit");

global $tmxlinks, $setmultiplicator, $trackbase, $timebase, $setmintime, $setmaxtime;

function autotimelimit(&$aseco, $challenge)
{
	global $tmxlinks, $setmultiplicator, $trackbase, $timebase, $setmintime, $setmaxtime;

	$deepth = -40;

	############################## TMX INFO FOR THIS TRACK #########################
	$uid = $aseco->server->challenge->uid;
	$name = $aseco->server->challenge->name;

	$query = "SELECT TMXType,TMXId from challenges where uid='$uid'";
	if ($result = mysql_query($query))
		{
		$row = mysql_fetch_row($result);
		$tmxtype = $row[0];
		$tmxid = $row[1];
		mysql_free_result($result);	
		if ($tmxid != 0)
			{
			$show = '';
			if ($tmxtype == 'SM')
				{
				$tmxlink = "http://sharemania.eu/track.php?id=$tmxid";
				$show = "\$z\$l[$tmxlink]\$000download $name\$l\$z\$l[$tmxlink]\$000 from Sharemania\$l";
				}	
			if ($tmxtype == 'TMO' || $tmxtype == 'TMS' || $tmxtype == 'TMN' || $tmxtype == 'TMU' || $tmxtype == 'TMF')
				{
				$tmxlink = $tmxlinks[$tmxtype];
				$tmxlink = str_replace('{ID}', $tmxid, $tmxlink);			
				$show = "\$z\$l[$tmxlink]\$000download $name\$l\$z\$l[$tmxlink]\$000 from TMX\$l";
				}
			
			if ($show != '')
				{
				$link3 = "<frame posn='-30 -25 $deepth'>";
				$link3 .= '<label posn="30 0 0" halign="center" style="CardButtonMediumWide" text="'.sub_maniacodes($show).'"/>';
				$link3 .= "</frame>";
				}	}}
	else
		echo "Query failed: $query".CRLF;

	############################### TIMELIMIT FOR NEXT TRACK #######################
	$aseco->client->query('GetGameInfos');
	$GameInfos = $aseco->client->getResponse();
	$CurrentGameInfo = $GameInfos['CurrentGameInfos'];
	if($CurrentGameInfo['GameMode']!= 123456)
	{
		//timeattack
		$defaulttimelimit=$CurrentGameInfo['TimeAttackLimit'];

		$challenge = get_nexttrack($aseco);
		$newtime = $challenge->authortime/1000;
		$newtime = ($newtime - $trackbase) * $setmultiplicator + $timebase;
		$newtime *= 1000;
					
		if(empty($newtime))
		{
			$newtime=$defaulttimelimit;
			$aseco->console_text("Setting Default Time...");
		}
			
		//mintime , maxtime check
		if($newtime < ($setmintime*60*1000)) $newtime = $setmintime*60*1000;
		if($newtime > ($setmaxtime*60*1000)) $newtime = $setmaxtime*60*1000;
			
		settype($newtime,'integer');
		$aseco->client->addcall('SetTimeAttackLimit',array($newtime));
		$aseco->client->multiquery();
		$aseco->console_text("Set timelimit for \"".stripColors($challenge->name)."\":  ".$newtime." (Authortime: ".$challenge->authortime.")");
		$none = '';
		$newtime = floor($newtime/1000);
		$min = floor($newtime/60);
		$sec = $newtime - $min*60;

		$header = "<?xml version='1.0' encoding='utf-8' ?><manialink id=10>";

		$link1 = "<frame posn='-64 48 $deepth'>";
		$link1 .= "<quad posn='0 0 -2' sizen='50 15' style='Bgs1InRace' substyle='BgWindow1'/>";
		$link1 .= "<quad posn='0 0 -1' sizen='50 4' style='Bgs1InRace' substyle='BgTitle3'/>";
		$link1 .= '<label posn="25 -0.5 0" halign="center" textsize="3" text="$0F0Top5 Records $z'.sub_maniacodes($aseco->server->challenge->name).'"/>';
	
		$posn = -4;
		for ($i = 0; $i < 5; $i++)
		{
			if ($cur_record = $aseco->server->records->getRecord($i))
			{
			 	$pos = $i+1;
				$time = formatTime($cur_record->score);
				$name = $cur_record->player->nickname;
				$link1 .= "<label posn='5 $posn 0' halign='right' textsize='2' text='\$F00$pos\$z.'/>";
				$link1 .= "<label posn='10 $posn 0' halign='center' textsize='2' text='$F00$time'/>";
				$link1 .= '<label posn="15 '.$posn.' 0" halign="left" textsize="2" text="'.sub_maniacodes($name).'"/>';
				$posn -= 2.2;
			}
		}
				
		$link1 .= "</frame>";
	
		$link2 = "<frame posn='14 48 $deepth'>";
		$link2 .= "<quad posn='0 0 -2' sizen='50 15' style='Bgs1InRace' substyle='BgWindow1'/>";
		$link2 .= "<quad posn='0 0 -1' sizen='50 4' style='Bgs1InRace' substyle='BgTitle3'/>";
		$link2 .= '<label posn="25 -0.5 0" halign="center" textsize="3" text="$0F0Next Track Information"/>';
		$link2 .= "<label posn='14 -4 0' halign='right' textsize='2' text='\$88FName: '/>";
		$link2 .= '<label posn="14 -4 0" halign="left" textsize="2" text=" '.sub_maniacodes($challenge->name).'"/>';
		$link2 .= "<label posn='14 -6.2 0' halign='right' textsize='2' text='\$88FAuthor: '/>";
		$link2 .= '<label posn="14 -6.2 0" halign="left" textsize="2" text=" '.sub_maniacodes($challenge->author).'"/>';
		$link2 .= "<label posn='14 -8.4 0' halign='right' textsize='2' text='\$88FAuthortime: '/>";
		$link2 .= '<label posn="14 -8.4 0" halign="left" textsize="2" text=" '.($challenge->authortime/1000).'"/>';
		$link2 .= "<label posn='14 -10.6 0' halign='right' textsize='2' text='\$88FEnvironment: '/>";
		$link2 .= '<label posn="14 -10.6 0" halign="left" textsize="2" text=" '.$challenge->environment.'"/>';
		$link2 .= "<label posn='14 -12.8 0' halign='right' textsize='2' text='\$88FNew Timelimit: '/>";
		$link2 .= '<label posn="14 -12.8 0" halign="left" textsize="2" text=" '.$min.":".sprintf('%02d', $sec).'"/>';
		$link2 .= "</frame>";

		// Next + Restart Challenge
		$adminBar = adminBarML();

		$end.="</manialink>";
		
		$message=$header.$link1.$link2.$link3.$end;
		$adminmessage=$header.$link1.$link2.$link3.$adminBar.$end;

		foreach($aseco->server->players->player_list as $player)
			{
			if ($aseco->isAdmin($player->login))
				$aseco->addcall('SendDisplayManialinkPageToLogin', array($player->login, $adminmessage, 0, FALSE));
			else
				$aseco->addcall('SendDisplayManialinkPageToLogin', array($player->login, $message, 0, FALSE));
			}
	}
}
?>