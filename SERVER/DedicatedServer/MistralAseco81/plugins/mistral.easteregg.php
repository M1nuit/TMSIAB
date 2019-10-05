<?php
function mistralShowEasteregg($aseco, $player)
	{
	global $manialinkstack;
	
	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;
	
	$query = "SELECT mid from mistral_chat order by mid desc limit 1";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$allchat = floor(($row[0])*2/3);
	mysql_free_result($result);

	$query = "SELECT id from challenges order by id desc limit 1";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$alltracks = $row[0];
	mysql_free_result($result);

	$query = "SELECT sum(wins) from players";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$allraces = $row[0];
	mysql_free_result($result);

	$query = "SELECT sum(timeplayed) FROM players p";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$alltimeplayed = round($row[0]/60/60/24/365, 2);
	mysql_free_result($result);

	$query = "select nation from players group by nation";
	$result = mysql_query($query);
	$allnations = mysql_num_rows($result)-1;
	mysql_free_result($result);

	$query = "SELECT challengeid,playerid,score,date FROM records r order by date asc limit 1";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$orectrackid = $row[0];
	$orecplayerid = $row[1];
	$orecscore = $row[2];
	$orecdate = $row[3];
	mysql_free_result($result);

	$query = "SELECT name,author FROM challenges c where id=$orectrackid";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$orectrackname = $row[0];
	$orecauthor = $row[1];
	mysql_free_result($result);

	$query = "select nickname,nation from players where id=$orecplayerid";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$orecname = $row[0];
	$orecnation = $row[1];
	mysql_free_result($result);

	$query = "SELECT id from records where challengeid=$orectrackid and (score<$orecscore or (score=$orecscore and date<='$orecdate'))";
	$result = mysql_query($query);
	$orecpos = mysql_num_rows($result);
	mysql_free_result($result);

	$orecsec = floor($orecscore/1000);
	$orechun = ($orecscore-1000*$orecsec)/10;
	$orecmin = floor($orecsec/60);
	$orecsec = $orecsec-60*$orecmin;

	$width = 60;
	$height = 47;
	$wr = $width-2;
	$hw = $width/2;
	$hwt = $width/3;
	$hwr = $hw-1;
	$hh = $height/2;
	

	$message = "<?xml version='1.0' encoding='utf-8' ?><manialink id='20'><frame posn='-$hw $hh $manialinkstack'>";
	$message .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";

	$message .= "<quad posn='1 -1 0' sizen='$wr 8' style='Bgs1InRace' substyle='BgListLine'/>";
	$message .= "<label posn='$hw -2 1' halign='center' textsize='3' text='\$F00Congratulations'/>";
	$message .= "<label posn='$hw -5 1' halign='center' textsize='3' text='\$000You found \$888Mi\$88Fstr\$888al\$000&apos;s easter egg!'/>";

	$message .= "<quad posn='1 -9 0' sizen='$wr 4' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$message .= "<label posn='$hw -10 1' halign='center' textsize='2' text='\$FF0Some useless information:'/>";
	
	$message .= "<label posn='$hwt -14 1' halign='right' textsize='2' text='\$0F0Overall time played:'/>";
	$message .= "<label posn='$hwt -14 1' halign='left' textsize='2' text=' $alltimeplayed years'/>";
	$message .= "<label posn='$hwt -17 1' halign='right' textsize='2' text='\$0F0Overall races:'/>";
	$message .= "<label posn='$hwt -17 1' halign='left' textsize='2' text=' $allraces'/>";
	$message .= "<label posn='$hwt -20 1' halign='right' textsize='2' text='\$0F0Overall tracks online:'/>";
	$message .= "<label posn='$hwt -20 1' halign='left' textsize='2' text=' $alltracks'/>";
	$message .= "<label posn='$hwt -23 1' halign='right' textsize='2' text='\$0F0Overall chat lines:'/>";
	$message .= "<label posn='$hwt -23 1' halign='left' textsize='2' text=' $allchat'/>";
	$message .= "<label posn='$hwt -26 1' halign='right' textsize='2' text='\$0F0Overall nations:'/>";
	$message .= "<label posn='$hwt -26 1' halign='left' textsize='2' text=' $allnations'/>";
	$message .= "<label posn='$hwt -29 1' halign='right' textsize='2' text='\$0F0Oldest record from:'/>";
	$message .= '<label posn="'.$hwt.' -29 1" halign="left" textsize="2" text=" '.sub_maniacodes($orecname).'$z ('.$orecnation.')"/>';
	$message .= "<label posn='$hwt -32 1' halign='right' textsize='2' text='\$0F0Oldest record on:'/>";
	$message .= '<label posn="'.$hwt.' -32 1" halign="left" textsize="2" text=" '.sub_maniacodes($orectrackname).'$z (by '.$orecauthor.'$z)"/>';
	$message .= "<label posn='$hwt -35 1' halign='right' textsize='2' text='\$0F0Oldest record data:'/>";
	$message .= "<label posn='$hwt -35 1' halign='left' textsize='2' text=' $orecmin:$orecsec.$orechun ($orecpos.) on $orecdate'/>";

	$message .= "<quad posn='1 -38 0' sizen='$wr 4' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$message .= "<label posn='$hw -39 1' halign='center' textsize='2' text='\$FF0Please don&apos;t tell others where to find the easter egg'/>";

	$message .= "<label posn='$hw -43 0' halign='center' style='CardButtonSmall' text='Close' action='12'/>";

	$message .= "</frame></manialink>";

	$aseco->addcall('SendDisplayManialinkPageToLogin', array($player->login, $message, 0, TRUE));
	}
?>

