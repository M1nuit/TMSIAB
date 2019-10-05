<?php
Aseco::addChatCommand('pi', 'Display your player info (Manialink HUD)');
Aseco::addChatCommand('ranks', 'Display your ranking in all environments');
Aseco::registerEvent('onNewChallenge', 'displayAllPlayerInfo');
Aseco::registerEvent('onPlayerConnect', 'displayPlayerInfoInit');
Aseco::registerEvent('onPlayerServerMessageAnswer', 'mistralRedisplay');

function mistralRedisplay($aseco, $answer)
	{
	global $manialinkstack;
	
	$player = $aseco->server->players->getPlayer($answer[1]);
	$i = $answer[2];
	
	switch ($i)
		{
		// Close player admin
		case 20001:
		// Close popup
		case 12:
			$manialinkstack = $manialinkstack-3;
			$player->mistral['displayPlayerInfo']=true;
			displayPlayerInfo($aseco, $player);
			break;
		// Jukebox
		case 30001:
			$command['author'] = $player;
			chat_list($aseco, $command);
			break;
		// Jukebox - nofinish
		case 30002:
			$command['author'] = $player;
			$command['params'] = "nofinish";
			chat_list($aseco, $command);
			break;
		// Statistic
		case 30003:
			displayStats($aseco, $player, $player);
			break;
		// Recs
		case 30004:
			$command['author'] = $player;
			chat_recs($aseco, $command);
			break;
		// Top - All
		case 30005:
			$command['author'] = $player;
			chat_top($aseco, $command);
			break;
		// Top Teams - All
		case 30006:
			$command['author'] = $player;
			chat_topteams($aseco, $command);
			break;
		// Next
		case 30007:
			$command['author'] = $player;
			$command['params'] = "next";
			chat_admin($aseco, $command);
			displayPlayerInfo($aseco, $player);
			break;
		// Restart
		case 30008:
			$command['author'] = $player;
			$command['params'] = "restart";
			chat_admin($aseco, $command);
			displayPlayerInfo($aseco, $player);
			break;
		// Close PlayerInfo
		case 30009:
			$aseco->addcall('SendHideManialinkPageToLogin', array($player->login));
			$player->mistral['displayPlayerInfo']=false;
			displayPlayerInfo($aseco, $player);
			break;
		// Clear Jukebox
		case 30010:
			$command['author'] = $player;
			$command['params'] = "clearjukebox";
			chat_admin($aseco, $command);
			// UPDATE PLAYERINFO MANIALINK
			displayAllPlayerInfo($aseco, 0, TRUE);
			break;
		// CleanupDB
		case 30011:
			$command['author'] = $player;
			$command['params'] = "cleanupdb";
			chat_admin($aseco, $command);
			displayPlayerInfo($aseco, $player);
			break;
		// Player admin
		case 30012:
			$command['author'] = $player;
			chat_pa($aseco, $command);
			break;
		// Donation
		case 30013:
			mistralDonationWindow($aseco, $player);
			break;
		// Donate 10
		case 30014:
			displayPlayerInfo($aseco, $player);
			mistralDonate($aseco, $player, 10);
			break;
		// Donate 20
		case 30015:
			displayPlayerInfo($aseco, $player);
			mistralDonate($aseco, $player, 20);
			break;
		// Donate 50
		case 30016:
			displayPlayerInfo($aseco, $player);
			mistralDonate($aseco, $player, 50);
			break;
		// Donate 100
		case 30017:
			displayPlayerInfo($aseco, $player);
			mistralDonate($aseco, $player, 100);
			break;
		// Donate 200
		case 30018:
			displayPlayerInfo($aseco, $player);
			mistralDonate($aseco, $player, 200);
			break;
		// Donate 500
		case 30019:
			displayPlayerInfo($aseco, $player);
			mistralDonate($aseco, $player, 500);
			break;
		// Donate 1000
		case 30020:
			displayPlayerInfo($aseco, $player);
			mistralDonate($aseco, $player, 1000);
			break;
		// Donate 2000
		case 30021:
			displayPlayerInfo($aseco, $player);
			mistralDonate($aseco, $player, 2000);
			break;
		// Donate 5000
		case 30022:
			displayPlayerInfo($aseco, $player);
			mistralDonate($aseco, $player, 5000);
			break;
		// Donate 10000
		case 30023:
			displayPlayerInfo($aseco, $player);
			mistralDonate($aseco, $player, 10000);
			break;
		// Easteregg
		case 30024:
			mistralShowEasteregg($aseco, $player);
			break;
		// Change sort order of tracklist - RANK
		case 30025:
			if ($player->mistral['Tracksort'] != "rank")
				{
				$player->mistral['Tracksort'] = "rank";
				if ($player->mistral['Ranksort'] == 0)
					$player->mistral['Ranksort'] = 1;
				}
			else
				{
				$sort = $player->mistral['Ranksort'];
				$sort++;
				if ($sort > 2)
					$sort=0;
				$player->mistral['Ranksort'] = $sort;
				}
			$command['author'] = $player;
			chat_list($aseco, $command);
			break;
		// jukebox list
		case 30026:
			$command['author'] = $player;
			$command['params'] = "list";
			chat_jukebox($aseco, $command);
			displayPlayerInfo($aseco, $player);
			break;
		// Top - Speed
		case 30027:
			$command['author'] = $player;
			chat_top($aseco, $command, "Speed");
			break;
		// Top - Alpine
		case 30028:
			$command['author'] = $player;
			chat_top($aseco, $command, "Alpine");
			break;
		// Top - Rally
		case 30029:
			$command['author'] = $player;
			chat_top($aseco, $command, "Rally");
			break;
		// Top - Bay
		case 30030:
			$command['author'] = $player;
			chat_top($aseco, $command, "Bay");
			break;
		// Top - Island
		case 30031:
			$command['author'] = $player;
			chat_top($aseco, $command, "Island");
			break;
		// Top - Coast
		case 30032:
			$command['author'] = $player;
			chat_top($aseco, $command, "Coast");
			break;
		// Top - Stadium
		case 30033:
			$command['author'] = $player;
			chat_top($aseco, $command, "Stadium");
			break;
		// Ranks
		case 30034:
			$command['author'] = $player;
			chat_ranks($aseco, $command);
			break;
		// Topteams - Speed
		case 30035:
			$command['author'] = $player;
			chat_topteams($aseco, $command, "Speed");
			break;
		// Topteams - Alpine
		case 30036:
			$command['author'] = $player;
			chat_topteams($aseco, $command, "Alpine");
			break;
		// Topteams - Rally
		case 30037:
			$command['author'] = $player;
			chat_topteams($aseco, $command, "Rally");
			break;
		// Topteams - Bay
		case 30038:
			$command['author'] = $player;
			chat_topteams($aseco, $command, "Bay");
			break;
		// Topteams - Island
		case 30039:
			$command['author'] = $player;
			chat_topteams($aseco, $command, "Island");
			break;
		// Topteams - Coast
		case 30040:
			$command['author'] = $player;
			chat_topteams($aseco, $command, "Coast");
			break;
		// Top - Stadium
		case 30041:
			$command['author'] = $player;
			chat_topteams($aseco, $command, "Stadium");
			break;
		// Change sort order of tracklist - DATE
		case 30042:
			if ($player->mistral['Tracksort'] != "date")
				{
				$player->mistral['Tracksort'] = "date";
				if ($player->mistral['Datesort'] == 0)
					$player->mistral['Datesort'] = 1;
				}
			else
				{
				$sort = $player->mistral['Datesort'];
				$sort++;
				if ($sort > 2)
					$sort=0;
				$player->mistral['Datesort'] = $sort;
				}
			$command['author'] = $player;
			chat_list($aseco, $command);
			break;
		// 30045-30049 FOR TRACKEVALUATION!
		default: 
			break;
		}
	}

function chat_ranks($aseco, $command)
	{
	global $maxrecs, $manialinkstack;
	
	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;
	 
	$player = $command['author'];
	$login = $player->login;
	$pid = getPlayerIdFromLogin($login);
	
	$width = 65;
	$height = 37;
	$hw = $width/2;
	$tw = $width/3;
	$ttw = 2*$tw;
	$hh = $height/2;

	$manialink = "<?xml version='1.0' encoding='utf-8' ?><manialink id='40'><frame posn='-$hw $hh $manialinkstack'>";

	$manialink .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";

	$manialink .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$manialink .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="Your server ranking"/>';
	$manialink .= "<quad posn='0 -4 0' sizen='$tw 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='".($tw/2)." -4.5 1' halign='center' textsize='2' text='Environment'/>";
	$manialink .= "<quad posn='$tw -4 0' sizen='$tw 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='".(3*$tw/2)." -4.5 1' halign='center' textsize='2' text='Ranking'/>";
	$manialink .= "<quad posn='$ttw -4 0' sizen='$tw 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='".(5*$tw/2)." -4.5 1' halign='center' textsize='2' text='Average'/>";	

	$environments = array ("Overall", "Speed", "Alpine", "Rally", "Bay", "Island", "Coast", "Stadium");
	$i = 1;
	$posn = -7.5;
	foreach ($environments as $environment)
		{
		$envcolumn = 'avg';
		$bg = '800E';
		if ($environment != "Overall")
			{
			$envcolumn = strtolower($environment);
			$i++;
			$bg = $i.$i.$i.'E';
			}
		
		
		$rank = "No rank";
		$average = $maxrecs;
		$query = 'SELECT '.$envcolumn.' FROM rs_rank WHERE playerID=' . $pid . ' ORDER BY '.$envcolumn.' ASC';
		$res = mysql_query($query);
		if (mysql_num_rows($res) > 0)
			{
			$row = mysql_fetch_row($res);
			$average = $row[0]/10000;
			$query2 = 'SELECT count(playerId) FROM rs_rank WHERE '.$envcolumn.'>0 and '.$envcolumn.' <' . $row[0];
			$res2 = mysql_query($query2);
			$row = mysql_fetch_row($res2);
			$rank = $row[0]+1;
			mysql_free_result($res2);
			}
		mysql_free_result($res);

		$query = 'SELECT count(playerId) FROM rs_rank where '.$envcolumn.'>0';
		$res = mysql_query($query);
		$row = mysql_fetch_row($res);
		$ranked = $row[0];
		mysql_free_result($res);
		
		$manialink .= "<quad bgcolor='$bg' posn='0.5 $posn 0' sizen='".($width-1)." 3'/>";
		$manialink .= "<label posn='".($tw/2)." ".($posn-0.5)." 1' halign='center' textsize='2' text='$environment'/>";
		$manialink .= "<label posn='".(3*$tw/2)." ".($posn-0.5)." 1' halign='center' textsize='2' text='$rank (of $ranked)'/>";
		$manialink .= "<label posn='".(5*$tw/2)." ".($posn-0.5)." 1' halign='center' textsize='2' text='$average'/>";

		$posn -= 3;
		}
	
	$posn--;
	$manialink .= "<label posn='$hw $posn 0' halign='center' style='CardButtonSmall' text='Close' action='12'/>";

	$manialink .= "</frame></manialink>";
		
	$aseco->addcall('SendDisplayManialinkPageToLogin', array($login, $manialink, 0, TRUE));		
	}

function chat_pi($aseco, $command)
	{
	global $manialinkstack;
	
	$player = $command['author'];
	
	$manialinkstack = -30;
	$player->mistral['displayPlayerInfo']=true;
	$aseco->addcall('SendHideManialinkPageToLogin', array($player->login));
	displayPlayerInfo($aseco, $player);
	}

function displayAllPlayerInfo($aseco, $challenge=0, $newrec=FALSE)
	{
	global $ranked, $avglist, $rankmsglist;
	
	// New Challenge
	if ($challenge !=0 )
		{
		$query = 'SELECT count(playerId) FROM rs_rank';
		$res = mysql_query($query);
		$row = mysql_fetch_row($res);
		$ranked = $row[0];
		mysql_free_result($res);
		
		$avglist = array();
		$rankmsglist = array();
		}

/*
	$aseco->client->query('GetPlayerList', 100, 0);
	$response['playerlist'] = $aseco->client->getResponse();
	if (!empty($response['playerlist']))
		{
		foreach ($response['playerlist'] as $player)
			{
			$player_item = new Player($player);
			$login = $player_item->login;
			$player_verify = $aseco->server->players->getPlayer($login);
			if ($player_verify->login != $login)
				{
				$player_item->mistral['displayPlayerInfo']=true;
				ldb_playerConnect($aseco, $player_item);
				$aseco->server->players->addPlayer($player_item);
				playerjoinmsg($aseco, $player_item);
				$aseco->console("Fixed: Added player ".$player_item->login);
				}
			}
		}
*/
	
	foreach($aseco->server->players->player_list as $player)
		{
		displayPlayerInfo($aseco, $player, $challenge, $newrec);
		}
	}

function displayPlayerInfoInit($aseco, $player)
	{
	$player->mistral['displayPlayerInfo']=true;
	$aseco->addcall('SendHideManialinkPageToLogin', array($player->login));
	displayPlayerInfo($aseco, $player, $aseco->server->challenge);	
	}

function displayPlayerInfo($aseco, $player, $challenge=0, $newrec=FALSE)
	{
	global $maxrecs, $avglist, $rankmsglist, $ranked, $jukebox, $trackkeep, $trackdontcare, $trackdelete, $tracknotmyenv, $eval_threshold;
	
	$deepth = -40;
	
	if (!isset($player->login) || $player->login=="")
		return;
		
	$login = $player->login;
	$pid = getPlayerIdFromLogin($login);

	// Update
	if ($challenge == 0)
		{
		$uid = $aseco->server->challenge->uid;
		$cid = getTrackIDfromUID($uid);
		}
	// New Challenge
	else
		{
		$uid = $challenge->uid;
		$cid = getTrackIDfromUID($uid);
		$rankmsg = "No Rank";
		$average = $maxrecs;

		$query = 'SELECT avg FROM rs_rank WHERE playerID=' . $pid . ' ORDER BY avg ASC';
		$res = mysql_query($query);
		if (mysql_num_rows($res) > 0)
			{
			$row = mysql_fetch_array($res);
			$average = $row['avg']/10000;
			$query2 = 'SELECT count(playerId) FROM rs_rank WHERE avg>0 and avg <' . $row['avg'];
			$res2 = mysql_query($query2);
			$row = mysql_fetch_row($res2);
			$rankmsg = $row[0]+1;
			mysql_free_result($res2);
			}
		mysql_free_result($res);
		
		$avglist[$login] = $average;
		$rankmsglist[$login] = $rankmsg;
		}

	if (!$player->mistral['displayPlayerInfo'])
		return;

	$average = $avglist[$login];
	$rankmsg = $rankmsglist[$login];

	// Calculate Personal Best
	$found = false;
	
	$query = "SELECT DATE_FORMAT(Date, '%d.%m.%Y - %H:%i') as mydate,score FROM records WHERE playerID=$pid AND challengeID='$cid' ORDER BY score ASC, date ASC LIMIT 1";
	$res = mysql_query($query);
	if (mysql_num_rows($res) > 0)
		{
		$row = mysql_fetch_array($res);
		$ret['time'] = $row['score'];
		$ret['date'] = $row['mydate'];
		$found = true;
		}
	mysql_free_result($res);

	$limit = $maxrecs;
	if ($found == true)
		{
		$ret['rank'] = ">$maxrecs";
		
		for ($i = 0; $i < $maxrecs; $i++)
			{
			$rec = $aseco->server->records->getRecord($i);
			if ($rec->player->login == $login)
				{
				$ret['rank'] = $i + 1;
				$limit = $i;
				break;
				}
			}

		$pbmsg = "\$F88".formatTime($ret['time'])." \$0F0(\$F88".$ret['rank'].".\$0F0) ".$ret['date'];
		}
	else
		{
		$pbmsg = "No record";
		}
		
	// Calculate Time to beat
	$query = "SELECT score FROM records WHERE challengeid=$cid ORDER BY score ASC limit $limit";
	
	$result = mysql_query($query);
	if (!$result)
		echo "QUERY FAILURE: ".$query.CRLF;
	else
		$count = mysql_num_rows($result);
	if ($count == 0)
		{
		if (!$found)
			{
			$threshold = 0;
			$thresholdpos = 0;
			}
		else
			{
			$threshold = $ret['time'];
			$thresholdpos = 1;
			}
		}
	else
		{
		mysql_data_seek($result, $count-1);
		$row = mysql_fetch_row($result);
		$threshold = $row[0];
		$thresholdpos = $count;
		}
	mysql_free_result($result);

	$header = "<?xml version='1.0' encoding='utf-8' ?><manialink id='10'>";

	$eval = getTrackeval($aseco, $uid, $login);
	$trackeval = "<frame posn='50 25 $deepth'>";
	switch ($eval)
		{
		case $trackkeep:
			$trackeval .= "<quad posn='0 0 -1' sizen='15 2.5' action='30049' style='BgsPlayerCard' substyle='BgPlayerCardSmall'/>";
			$trackeval .= "<label posn='8 -0.25 0' sizen='12 2' halign='center' textsize='2' text='\$0F0Keep track'/>";
			break;
		case $trackdontcare:
			$trackeval .= "<quad posn='0 0 -1' sizen='15 2.5' action='30049' style='BgsPlayerCard' substyle='BgPlayerCardSmall'/>";
			$trackeval .= "<label posn='8 -0.25 0' sizen='12 2' halign='center' textsize='2' text='\$FF0Don&apos;t care'/>";
			break;
		case $trackdelete:
			$trackeval .= "<quad posn='0 0 -1' sizen='15 2.5' action='30049' style='BgsPlayerCard' substyle='BgPlayerCardSmall'/>";
			$trackeval .= "<label posn='8 -0.25 0' sizen='12 2' halign='center' textsize='2' text='\$F00Delete track'/>";
			break;
		case $tracknotmyenv:
			$trackeval .= "<quad posn='0 0 -1' sizen='15 2.5' action='30049' style='BgsPlayerCard' substyle='BgPlayerCardSmall'/>";
			$trackeval .= "<label posn='8 -0.25 0' sizen='12 2' halign='center' textsize='2' text='\$00FNot my env.'/>";
			break;
		default:
			$trackeval .= "<quad posn='0 0 -1' sizen='15 2.5' style='BgsPlayerCard' substyle='BgPlayerCardSmall'/>";
			if ($eval > $eval_threshold)
				$trackeval .= "<label posn='7 -0.25' halign='center' textsize='2' text='\$000Finishs: \$FFF>$eval_threshold'/>";
			else
				$trackeval .= "<label posn='7 -0.25' halign='center' textsize='2' text='\$000Finishs: \$FFF$eval'/>";
		}
		$trackeval .= "</frame>";

	$serverrank = "<frame posn='-64 29 $deepth'>";
	$serverrank .= "<label posn='-13 -0.1 0' action='30034' style='CardButtonSmall' text='a                 More'/>";
	$serverrank .= "<quad posn='-2 -3 -1' sizen='15 10' style='BgsPlayerCard' substyle='BgPlayerCardBig'/>";
	$serverrank .= "<label posn='7.5 -3.5 0' sizen='9 1.5' textsize='1.5' halign='center' text='\$000Time to beat'/>";
	$serverrank .= "<quad posn='0 -3.5 1' sizen='3 3' style='Icons128x32_1' substyle='RT_TimeAttack'/>";
	$serverrank .= "<label posn='7.5 -5 0' sizen='9 1.5' textsize='1.5' halign='center' text='\$FFF".formattime($threshold)." ($thresholdpos.)'/>";
	$serverrank .= "<label posn='7.5 -6.5 0' sizen='9 1.5' textsize='1.5' halign='center' text='\$000Server rank'/>";
	$serverrank .= "<quad posn='0 -6.6 1' sizen='3 3' style='Icons128x32_1' substyle='RT_Cup'/>";
	$serverrank .= "<label posn='7.5 -8 0' sizen='9 1.5' textsize='1.5' halign='center' text='\$FFF$rankmsg/$ranked'/>";
	$serverrank .= "<label posn='7.5 -9.5 0' sizen='9 1.5' textsize='1.5' halign='center' text='\$000Average'/>";
	$serverrank .= "<quad posn='0 -9.5 1' sizen='3 3' style='Icons128x32_1' substyle='RT_Laps'/>";
	$serverrank .= "<label posn='7.5 -11 0' sizen='9 1.5' textsize='1.5' halign='center' text='\$FFF$average'/>";
	$serverrank .= "</frame>";

	// Old Record
	$oldrec="";

	// no record in db yet
	if (!$found)
		{
		$player->mistral['oldtime'] = 0;
		$player->mistral['oldrank'] = 0;
		$player->mistral['oldertime'] = 0;
		$player->mistral['olderrank'] = 0;
		}

	// new track - init with rec from db
	if (!$newrec && $found)
		{
		$player->mistral['oldtime'] = $ret['time'];
		if ($ret['rank'] == ">$maxrecs")
			$player->mistral['oldrank'] = $maxrecs;
		else
			$player->mistral['oldrank'] = $ret['rank'];
		$player->mistral['oldertime'] = 0;
		$player->mistral['olderrank'] = 0;
		}

	// new record driven (maybe me)
	if ($newrec && $found)
		{
		// not me
		if ($player->mistral['oldtime'] != $ret['time'])
			{
			$player->mistral['oldertime'] = $player->mistral['oldtime'];
			$player->mistral['olderrank'] = $player->mistral['oldrank'];
			$player->mistral['oldtime'] = $ret['time'];
			if ($ret['rank'] == ">$maxrecs")
				$player->mistral['oldrank'] = $maxrecs;
			else
				$player->mistral['oldrank'] = $ret['rank'];
			}
		}
	
	$oldtime = $player->mistral['oldtime'];
	$oldrank = $player->mistral['oldrank'];
	$oldertime = $player->mistral['oldertime'];
	$olderrank = $player->mistral['olderrank'];
	if ($oldrank!=0 && $olderrank!=0)
		{
		$betterrank = $olderrank-$oldrank;
		$bettertime = abs(($oldertime-$oldtime)/1000);

		$oldrectext = "\$0F0New Record:\$z -$bettertime\$0F0. You \$z";
		if ($betterrank < 0)
			{
			$oldrectext .= "lost ".abs($betterrank)." ranks\$0F0.";
			}
		elseif ($betterrank == 0)
			{
			$oldrectext .= "kept your rank\$0F0.";
			}
		elseif ($betterrank > 0)
			{
			$oldrectext .= "gained $betterrank ranks\$0F0.";
			}
		$oldrec = "<frame posn='30 -29 $deepth'>";
		$oldrec .= "<quad sizen='35 3' style='BgsPlayerCard' substyle='BgPlayerCardSmall'/>";
		$oldrec .= "<label posn='17 -0.5 1' sizen='34 2' textsize='2' halign='center' text='$oldrectext'/>";
		$oldrec .= "</frame>";
		}

	// Next + Restart Challenge
	$adminBar = "";
	if ($aseco->isAdmin($login))
		$adminBar = adminBarML();

	// EASTEREGG
	$easteregg = "<quad posn='-64 -42 0' sizen='1.5 1.5' style='Icons64x64_1' substyle='LvlYellow' action='30024'/>";
	
	// Server Record
	$cur_record = new Record();
	$cur_record = $aseco->server->records->getRecord(0);
	if ($cur_record->score > 0)
		{
		$recmsg = formattime($cur_record->score)."\$0F0 by \$z".sub_maniacodes($cur_record->player->nickname);
		}
	else
		{
		$recmsg = "No record";
		}

	// Menü
	$menu = "<frame posn='-25 48 $deepth'>";
	$menu .= "<quad posn='0 2 -10' sizen='50 10' style='Bgs1InRace' substyle='BgWindow2'/>";
	
	$menu .= "<quad posn='0 1 1' sizen='4 4' action='30009' style='Icons64x64_1' substyle='QuitRace'/>";
	$jbs=sizeof($jukebox);
	$menu .= "<label posn='0.5 -2 1' textsize='0.5' text='\$000Jukebox:'/>";
	$menu .= "<label posn='2 -3.2 1' sizen='3 3' action='30026' halign='center' textsize='3' text='$jbs'/>";

	$menu .= "<label posn='27 -0.5 2' textsize='2' halign='center' text='\$0F0Personal Best:\$z $pbmsg'/>";
	$menu .= '<label posn="27 -3.5 2" textsize="2" halign="center" text="$0F0Record:$z '.$recmsg.'"/>';
	
	$menu .= "<quad posn='0 -6 -9' sizen='8 2' action='30001' style='Bgs1InRace' substyle='BgButton'/>";
	$menu .= "<quad posn='0 -5.5 2' sizen='3 3' action='30001' style='Icons64x64_1' substyle='ToolTree'/>";
	$menu .= "<label posn='3 -6.5 1' textsize='0.5' text='\$000Tracklist'/>";
	
	$menu .= "<quad posn='7 -6 -8' sizen='8 2' action='30002' style='Bgs1InRace' substyle='BgButton'/>";
	$menu .= "<quad posn='7 -5.5 2' sizen='3 3' action='30002' style='BgRaceScore2' substyle='SendScore'/>";
	$menu .= "<label posn='10 -6.5 1' textsize='0.5' text='\$000No Record'/>";
	
	$menu .= "<quad posn='14 -6 -7' sizen='8 2' action='30003' style='Bgs1InRace' substyle='BgButton'/>";
	$menu .= "<quad posn='14 -5.5 2' sizen='3 3' action='30003' style='Icons128x128_1' substyle='Statistics'/>";
	$menu .= "<label posn='17 -6.5 1' textsize='0.5' text='\$000Statistic'/>";
	
	$menu .= "<quad posn='21 -6 -6' sizen='8 2' action='30004' style='Bgs1InRace' substyle='BgButton'/>";
	$menu .= "<quad posn='21 -5.5 2' sizen='3 3' action='30004' style='BgRaceScore2' substyle='Podium'/>";
	$menu .= "<label posn='24 -6.5 1' textsize='0.5' text='\$000Records'/>";
	
	$menu .= "<quad posn='28 -6 -5' sizen='8 2' action='30005' style='Bgs1InRace' substyle='BgButton'/>";
	$menu .= "<quad posn='28 -5.5 2' sizen='3 3' action='30005' style='BgRaceScore2' substyle='LadderRank'/>";
	$menu .= "<label posn='31 -6.5 1' textsize='0.5' text='\$000TopPlayers'/>";
	
	$menu .= "<quad posn='35 -6 -4' sizen='8 2' action='30006' style='Bgs1InRace' substyle='BgButton'/>";
	$menu .= "<quad posn='35 -5.5 2' sizen='3 3' action='30006' style='Icons128x128_1' substyle='Credits'/>";
	$menu .= "<label posn='38 -6.5 1' textsize='0.5' text='\$000Top Teams'/>";
	
	$menu .= "<quad posn='42 -6 -3' sizen='8 2' action='30013' style='Bgs1InRace' substyle='BgButton'/>";
	$menu .= "<quad posn='42 -5.5 2' sizen='3 3' action='30013' style='Icons128x128_1' substyle='Coppers'/>";
	$menu .= "<label posn='45 -6.5 1' textsize='0.5' text='\$000Donation'/>";
	
	$menu .= "</frame>";

	$end.="</manialink>";

	$message=$header.$menu.$trackeval.$serverrank.$oldrec.$adminBar.$easteregg.$end;
	$aseco->addcall('SendDisplayManialinkPageToLogin', array($login, $message, 0, FALSE));
	}

?>