<?php
Aseco::registerEvent("onPlayerIncoherence", "myCleanBanlist");

/** Get Top Donators
***************************/
function getTopDonators()
{
 	global $partner;
 	
 	$partnerid = getPlayerIdFromLogin($partner);
 	
	$donators=array();

	$query = "SELECT playerid, donation FROM mistral_playerwins where playerid!=$partnerid order by donation desc limit 10;";
	$result = mysql_query($query);
	$i = 0;
	while ($row = mysql_fetch_row($result))
	{
		$donators[$i]->id = $row[0];
		$donators[$i]->donation = $row[1];
		$i++;
	}
	mysql_free_result($result);
	
	return $donators;	
}

/** Get Top Winner
***************************/
function getTopWinner()
{
 	global $partner;
 	
 	$partnerid = getPlayerIdFromLogin($partner);
	 
	$winner=array();

	$query = "SELECT playerid, won FROM mistral_playerwins where playerid!=$partnerid order by won desc limit 10;";
	$result = mysql_query($query);
	$i = 0;
	while ($row = mysql_fetch_row($result))
	{
		$winner[$i]->id = $row[0];
		$winner[$i]->won = $row[1];
		$i++;	
	}
	mysql_free_result($result);
	
	return $winner;	
}

/** Clean Banlist
***************************/
function myCleanBanlist($aseco, $command)
{
	$login = $command[1];
	
	$message = "{#server}>> Server detected incoherence of $login (automatic ban) - script will clear banlist";
	$message = $aseco->formatColors($message);
	$aseco->addCall(ChatSendServerMessage, array($message));
	$aseco->addCall(CleanBanList, array());
}

/** get Coppers Won
***************************/
function getWon($id)
{
	$won = 0;

	$query = "SELECT won from mistral_playerwins where PlayerId=$id;";
	if ($result = mysql_query($query))
		{
		$row = mysql_fetch_row($result);
		$won = $row[0];
		mysql_free_result($result);
		}
	
	return $won;
}

/** get Jukebox fee
***************************/
function getJukebox($id)
{
	$jukebox = 0;

	$query = "SELECT jukebox from mistral_playerwins where PlayerId=$id;";
	if ($result = mysql_query($query))
		{
		$row = mysql_fetch_row($result);
		$jukebox = $row[0];
		mysql_free_result($result);
		}
	
	return $jukebox;
}

/** get Donation
***************************/
function getDonation($id)
{
	$donation = 0;

	$query = "SELECT donation from mistral_playerwins where PlayerId=$id;";
	if ($result = mysql_query($query))
		{
		$row = mysql_fetch_row($result);
		$donation = $row[0];
		mysql_free_result($result);
		}
	
	return $donation;
}

/** nextRestartML
***************************/
function adminBarML()
	{
	global $keepcount, $dontcarecount, $deletecount, $notmyenvcount;

	$deepth = -40;

	$abs = "<frame posn='-10.5 -38 $deepth'>";
	$bar = "<quad posn='0 0 -1' sizen='21 4' style='BgsPlayerCard' substyle='BgActivePlayerCard'/>";
	$restart = "<quad posn='1 -0.5 0' sizen='3 3' style='Icons64x64_1' substyle='Refresh' action='30008'/>";
	$clearjb = "<quad posn='5 -0.5 0' sizen='3 3' style='Icons64x64_1' substyle='QuitRace' action='30010'/>";
	$admin = "<quad posn='9 -0.5 0' sizen='3 3' style='Icons128x128_1' substyle='Buddies' action='30012'/>";
	$cands = "<quad posn='13 -0.5 0' sizen='3 3' style='Icons128x128_1' substyle='Save' action='30011'/>";
	$next =  "<quad posn='17 -0.5 0' sizen='3 3' style='Icons64x64_1' substyle='ArrowNext' action='30007'/>";
	$fe = "</frame>";
	
	$adminbar = $abs.$bar.$restart.$clearjb.$admin.$cands.$next.$fe;
	
	$tes = "<frame posn='54 -32 $deepth'>";
	$border = "<quad posn='0 0 -1' sizen='10 8' style='Bgs1InRace' substyle='BgWindow2'/>";
	$keep = "<quad posn='1 -1 0' sizen='8 1.5' style='Bgs1InRace' substyle='BgTitle3_4'/>".
			"<label posn='5 -1 1' halign='center' textsize='1' sizen='8 1.5' text='\$000$keepcount'/>";
	$dontcare = "<quad posn='1 -2.5 0' sizen='8 1.5' style='Bgs1InRace' substyle='BgTitle3_1'/>".
			"<label posn='5 -2.5 1' halign='center' textsize='1' sizen='8 1.5' text='\$000$dontcarecount'/>";
	$notmyenv = "<quad posn='1 -4 0' sizen='8 1.5' style='Bgs1InRace' substyle='BgTitle3_3'/>".
			"<label posn='5 -4 1' halign='center' textsize='1' sizen='8 1.5' text='\$000$notmyenvcount'/>";
	$delete = "<quad posn='2 -5.5 0' sizen='6 1.5' bgcolor='8008'/>".
			"<label posn='5 -5.5 1' halign='center' textsize='1' sizen='8 1.5' text='\$000$deletecount'/>";
	$trackeval = $tes.$border.$keep.$dontcare.$notmyenv.$delete.$fe;
	
	return $adminbar.$trackeval;
	}

/** displayStats
***************************/
function displayStats($aseco, $showplayer, $theplayer)
	{
	global $manialinkstack;
	
	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;
	
	$login=$theplayer->login;
	$nickname=$theplayer->nickname;
	
	$first=0;
	$second=0;
	$third=0;
	$fourth=0;
	$fifth=0;

	$time=$theplayer->getTimePlayed();
	$days = floor($time/86400);
	$time = $time - ($days*86400);
	$hours = floor($time/3600);
	$time = $time - ($hours*3600);
	$min = floor($time/60);

	$playerid = getPlayerIdFromLogin($login);
	$query = "SELECT * FROM mistral_wins WHERE playerId='" . $playerid . "'";
	$result = mysql_query($query);
	if (mysql_num_rows($result) != 0)
		{
		$row = mysql_fetch_array($result);
		$first = $row['first'];
		$second = $row['second'];
		$third = $row['third'];
		$fourth = $row['fourth'];
		$fifth = $row['fifth'];
		}

	$nick = htmlspecialchars($nickname);

	$width = 70;
	$height = 27;
	$hw = $width/2;
	$hhw = $width/4;
	$hhwl = $hhw-3;
	$hwr = $hw+$hw/3;
	$hh = $height/2;
	
	$manialink = "<?xml version='1.0' encoding='utf-8' ?><manialink id='30'><frame posn='-$hw $hh $manialinkstack'>";

	$manialink .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";

	$manialink .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$manialink .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="Stats for '.sub_maniacodes($nick).'"/>';
	$manialink .= "<quad posn='0 -4 0' sizen='$hw 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='$hhw -4.5 1' halign='center' textsize='2' text='General'/>";
	$manialink .= "<quad posn='$hw -4 0' sizen='$hw 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='".($hw+$hhw)." -4.5 1' halign='center' textsize='2' text='Top 5 Records'/>";
	$manialink .= "<label posn='$hhwl -8 0' halign='right' textsize='2' text='\$0F0Races won: '/>";
	$manialink .= "<label posn='$hhwl -11 0' halign='right' textsize='2' text='\$0F0Time played: '/>";
	$manialink .= "<label posn='$hhwl -14 0' halign='right' textsize='2' text='\$0F0Jukebox paid: '/>";
	$manialink .= "<label posn='$hhwl -17 0' halign='right' textsize='2' text='\$0F0Coppers won: '/>";
	$manialink .= "<label posn='$hhwl -20 0' halign='right' textsize='2' text='\$0F0Donation made: '/>";
	$manialink .= "<label posn='$hhwl -8 0' halign='left' textsize='2' text='".$theplayer->getWins()."'/>";
	$manialink .= "<label posn='$hhwl -11 0' halign='left' textsize='2' text='$days days, $hours:$min hours'/>";
	$manialink .= "<label posn='$hhwl -14 0' halign='left' textsize='2' text='".getJukebox($playerid)."'/>";
	$manialink .= "<label posn='$hhwl -17 0' halign='left' textsize='2' text='".getWon($playerid)."'/>";
	$manialink .= "<label posn='$hhwl -20 0' halign='left' textsize='2' text='".getDonation($playerid)."'/>";
	$manialink .= "<label posn='$hwr -8 0' halign='left' textsize='2' text='\$0F0track records.'/>";
	$manialink .= "<label posn='$hwr -11 0' halign='left' textsize='2' text='\$0F0second positions.'/>";
	$manialink .= "<label posn='$hwr -14 0' halign='left' textsize='2' text='\$0F0third positions.'/>";
	$manialink .= "<label posn='$hwr -17 0' halign='left' textsize='2' text='\$0F0fourth positions.'/>";
	$manialink .= "<label posn='$hwr -20 0' halign='left' textsize='2' text='\$0F0fifth positions.'/>";
	$manialink .= "<label posn='$hwr -8 0' halign='right' textsize='2' text='$first '/>";
	$manialink .= "<label posn='$hwr -11 0' halign='right' textsize='2' text='$second '/>";
	$manialink .= "<label posn='$hwr -14 0' halign='right' textsize='2' text='$third '/>";
	$manialink .= "<label posn='$hwr -17 0' halign='right' textsize='2' text='$fourth '/>";
	$manialink .= "<label posn='$hwr -20 0' halign='right' textsize='2' text='$fifth '/>";
	$manialink .= "<label posn='$hw -23 0' halign='center' style='CardButtonSmall' text='Close' action='12'/>";

	$manialink .= "</frame></manialink>";
	
	$aseco->addcall('SendDisplayManialinkPageToLogin', array($showplayer->login, $manialink, 0, TRUE));
	}

/** getOfflineTracks
***************************/
function getOfflineTracks($aseco, $player)
	{
 	$tracks = array();
 	
	getAllChallenges($player, "*", "*");

	$query = "DROP TABLE mistral_tracks;";
	mysql_query($query);
	$query = "CREATE TABLE `mistral_tracks` (
			`Id` mediumint(9) NOT NULL auto_increment,
			`Uid` varchar(27) NOT NULL default '',
			`Name` varchar(100) NOT NULL default '',
			PRIMARY KEY  (`Id`),
			UNIQUE KEY `Uid` (`Uid`)
			) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";
	if (!mysql_query($query))
		{
		$aseco->console_text("Cannot create table \"mistral_tracks\"!.");
		return $tracks;
		}

	foreach ($player->tracklist as $row)
		{
		$uid = $row['uid'];
		$name = $row['name'];
		$query = "INSERT INTO mistral_tracks (Uid, Name) VALUES (".quotedString($uid).",".quotedString($name).");";
		if (!mysql_query($query))
			{
			$aseco->console_text("Cannot insert track into 'mistral_tracks': UId='".$uid."'; Name='".$name."'");
			return $tracks;
			}
		cleanupRecords($uid);
		}
		
	$query = "SELECT id, name FROM challenges where uid not in (select uid from mistral_tracks);";
	$result = mysql_query($query);
	while ($track = mysql_fetch_array($result))
		$tracks[]=$track;
	mysql_free_result($result);
	$query = "DROP TABLE mistral_tracks;";
	mysql_query($query);
	return $tracks;
	}

/** getNickname from Login
***************************/
function getNicknameFromLogin($login) {
  	$query = "SELECT NickName FROM players WHERE Login='" . $login . "'";
  	$result = mysql_query($query);
  	if (mysql_num_rows($result) > 0) {
	    	$row = mysql_fetch_row($result);
    		return $row[0];
  	} else {
    		return "unknown";
  	}
}

/** getNation
****************************************************************/
function getNation($zone)
{
	$pos = strpos($zone, '|');
	if ($pos == false)
		return $zone;
	
	return substr($zone, 0, $pos);
}

/** getZone
****************************************************************/
function getZone($aseco, $login)
{
 	if ($login == "")
		return 'unknown';
 	
	if (!$aseco->client->query('GetDetailedPlayerInfo',$login))
		{
		$aseco->console("[Problem] client query failed (".$aseco->server->ip.":".$aseco->server->port.")");
		return 'query error';
		}
	$PlayerDetailedInfo = $aseco->client->getResponse();
	$path = $PlayerDetailedInfo['Path'];
	$zone = substr($path, strpos($path, '|')+1);
	return $zone;
}

/** getPlayerIdFromLogin from Login
***************************/
function getPlayerIdFromLogin($login)
	{
	$value = 0;
  	$query = "SELECT Id FROM players WHERE Login='" . $login . "'";
  	$result = mysql_query($query);
  	if (mysql_num_rows($result) > 0)
		{
	    	$row = mysql_fetch_row($result);
    		$value = $row[0];
  		}
	mysql_free_result($result);
	return $value;
	}

/** getNickname from PlayerId
***************************/
function getNicknameFromId($playerid)
	{
	$value = "";
  	$query = "SELECT NickName FROM players WHERE Id='" . $playerid . "'";
  	$result = mysql_query($query);
  	if (mysql_num_rows($result) > 0)
		{
	    	$row = mysql_fetch_row($result);
    		$value = $row[0];
	  	}
	mysql_free_result($result);
	return $value;
	}

/** getTrackID from UID
***************************/
function getTrackIDfromUID($uid) {
  	$query = "SELECT id FROM challenges WHERE Uid='$uid'";
  	$result = mysql_query($query);
  	if (!$result)
  		return "";
  	if (mysql_num_rows($result) > 0) {
	    	$row = mysql_fetch_row($result);
			$id = $row[0];
			mysql_free_result($result);
    		return $id;
  	} else {
    		return "";
  	}
}

/** getTrackName from UID
***************************/
function getTracknameFromUId($uid) {
  	$query = "SELECT name FROM challenges WHERE Uid='$uid'";
  	$result = mysql_query($query);
  	if (!$result)
  		return "unknown";
  	if (mysql_num_rows($result) > 0) {
	    	$row = mysql_fetch_row($result);
			$name = $row[0];
			mysql_free_result($result);
    		return $name;
  	} else {
    		return "unknown";
  	}
}

function getDummyChallenge()
{
	$challenge->authortime = 60000;
	$challenge->name = "Unknown (Query Error)";
	$challenge->author = "Unknown";
	$challenge->environment = "Unknown";
	return $challenge;
}

/** get_nexttrack
***************************/
function get_nexttrack(&$aseco)
{
	$aseco->client->multiquery();
	
	//Get Current Trackposition
	$aseco->client->query("GetCurrentChallengeIndex");
	$trackid = $aseco->client->getResponse();
	
	//Get Tracklist
	$aseco->client->addCall("GetChallengeList", array(2, $trackid));
	if (!$aseco->client->multiquery())
	{
		trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
		return getDummyChallenge();
	}
	$challengelistresponse = $aseco->client->getResponse();
	$nexttrack = $challengelistresponse[0][0][1];
	
	//Get Challenge Infos
	$aseco->client->addCall("GetChallengeInfo", array($nexttrack['FileName']));
	if (!$aseco->client->multiquery())
	{
		trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
		return getDummyChallenge();
	}
	$challengeinforesponse = $aseco->client->getResponse();
	$nextchallenge = new Challenge($challengeinforesponse[0][0]);	

	if ($nextchallenge->name == "" || $nextchallenge->author == "" || $nextchallenge->authortime == 0)
		return getDummyChallenge();

	return $nextchallenge;
}	
?>