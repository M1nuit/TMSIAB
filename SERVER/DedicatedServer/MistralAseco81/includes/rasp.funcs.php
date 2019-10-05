<?php
// common functions for RASP 0.4.1 and above

function getAllChallenges($player, $wildcard, $env)
	{
	global $jb_buffer, $maxrecs, $manialinkstack, $aseco;

	$tpp = 18;

	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;

	$pid = getPlayerIdFromLogin($player->login);
	$ranksort = $player->mistral['Ranksort'];
	$datesort = $player->mistral['Datesort'];

	$ranklist = array();
	$datelist = array();

	$id = 5;
	$ra = 5;
	$rf = 17;
	$na = 33;
	$en = 13;
	$au = 13;
	$width = $id+$ra+$rf+$na+$en+$au;
	$height = $tpp*3+11;
	$hw = $width/2;
	$hh = $height/2;
	$player->msgsw=$width;
	$player->msgsh=$height;

	$header = "<?xml version='1.0' encoding='utf-8' ?><manialink id='100'><frame posn='-$hw $hh $manialinkstack'>";
	$header .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";
	$header .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$header .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="Server Tracks (click on any rank or date to change sort order)"/>';
	$header .= "<quad posn='0 -4 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$header .= "<label posn='".($id/2)." -4.5 1' halign='center' textsize='2' text='Id'/>";	
	$header .= "<label posn='".($id+$ra/2)." -4.5 1' halign='center' textsize='2' text='Rank'/>";	
	$header .= "<label posn='".($id+$ra+$rf/2)." -4.5 1' halign='center' textsize='2' text='Record from'/>";	
	$header .= "<label posn='".($id+$ra+$rf+$na/2)." -4.5 1' halign='center' textsize='2' text='Name (click for jukebox)'/>";	
	$header .= "<label posn='".($id+$ra+$rf+$na+$en/2)." -4.5 1' halign='center' textsize='2' text='Environment'/>";	
	$header .= "<label posn='".($id+$ra+$rf+$na+$en+$au/2)." -4.5 1' halign='center' textsize='2' text='Author'/>";	

	$detail = "<label posn='1 {POSN+} 1' textsize='2' text='{TRACKID}'/>";
	$detail .= "<quad posn='".($id)." {POSN} 0' sizen='$ra 3' action='30025' style='Bgs1InRace' substyle='NavButton'/>";
	$detail .= "<label posn='".($id+$ra/2)." {POSN+} 1' halign='center' textsize='2' text='{TRACKRANK}'/>";
	$detail .= "<quad posn='".($id+$ra)." {POSN} 0' sizen='$rf 3' action='30042' style='Bgs1InRace' substyle='NavButton'/>";
	$detail .= "<label posn='".($id+$ra+$rf/2)." {POSN+} 1' halign='center' textsize='2' text='{RECDATE}'/>";
	$detail .= "<quad posn='".($id+$ra+$rf)." {POSN} 0' sizen='$na 3' action='{TRACKNUM}' style='Bgs1InRace' substyle='NavButton'/>";
	$detail .= '<label posn="'.($id+$ra+$rf+1).' {POSN+} 1" textsize="2" text="{TRACKNAME}"/>';
	$detail .= "<quad posn='".($id+$ra+$rf+$na)." {POSN} 0' sizen='$en 3' action='{ENVACTION}' style='Bgs1InRace' substyle='NavButton'/>";
	$detail .= "<label posn='".($id+$ra+$rf+$na+$en/2)." {POSN+} 1' halign='center' textsize='2' text='{TRACKENV}'/>";
	$detail .= '<label posn="'.($width-1).' {POSN+} 1" halign="right" textsize="2" text="{TRACKAUTHOR}"/>';

	$newlist = array();
	$done = false;
	$i = 0;
	while (!$done)
		{
		$tracks = $aseco->client->addCall('GetChallengeList', array(300, $i));
  		if (!$aseco->client->multiquery())
			{
    		trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
  			}
		else
			{
			$tlist = array();
			$response = $aseco->client->getResponse();
			if (sizeof($response[$tracks][0]) > 0)
				{
				foreach ($response[$tracks][0] as $trow)
					{
					$cid = getTrackIDfromUID($trow['UId']);
					$trackrank = 9999;
					$recdate = "0000-00-00 00:00:00";
					$query = "SELECT rank,date FROM records WHERE PlayerId=$pid AND ChallengeId=$cid;";
					$result = mysql_query($query);
					if ($result)
						{
						$row = mysql_fetch_row($result);
						if ($row[0] != 0)
							{
							$trackrank = $row[0];
							$recdate = $row[1];
							}
						mysql_free_result($result);
						}
					$trow['Rank'] = $trackrank;
					$trow['Date'] = $recdate;
					$newlist[] = $trow;
					$ranklist[] = $trackrank;
					$datelist[] = $recdate;
					}
				if ( sizeof($newlist) < 300 )
					{
					$done = true;
					}
				else
					{
					$i = $i + 300;
					}
				}
			else
				{
				$done = true;
				break;
				}
			}
		}

	if ($player->mistral['Tracksort'] == "rank")
		{
		if ($ranksort == 1)
			array_multisort($ranklist, SORT_DESC, $newlist);
		if ($ranksort == 2)
			array_multisort($ranklist, SORT_ASC, $newlist);
		}

	if ($player->mistral['Tracksort'] == "date")
		{
		if ($datesort == 1)
			array_multisort($datelist, SORT_ASC, $newlist);
		if ($datesort == 2)
			array_multisort($datelist, SORT_DESC, $newlist);
		}


	$player->tracklist = array();
	$player->msgs = array();
	$player->msgs['curpage'] = 0;

	$s = '';
	$tid = 1;
	$ctr = 0;
	$msgs = 0;

	$posn = -5;
	foreach ($newlist as $row)
		{
		$trackname = $row['Name']."\$z";
		$trackauthor = $row['Author'];
		$trackenv = $row['Environnement'];
		$trackuid = $row['UId'];
		$trackrank = $row['Rank'];
		
		if ($trackrank == 9999)
			$trackrank = '$FFF-----';
		elseif ($trackrank < $maxrecs/3)
			$trackrank = "\$8F8$trackrank.";
		elseif ($trackrank < 2*$maxrecs/3)
			$trackrank = "\$FF8$trackrank.";
		else
			$trackrank = "\$F88$trackrank.";
		
		if (in_array($trackuid, $jb_buffer))
			$trackname .= "\$n (played)\$z";
		
		if ( $wildcard == '*' )
			{
			$pos = 0;
			}
		else
			{
			$pos = stripos(stripColors($trackname), $wildcard);
			if ( $pos === false )
				{
				$pos = stripos($trackauthor, $wildcard);
				}
			}

		// env is a additive compare always, so /list xxx env:bay will give all bay tracks with xxx in track or author
		if ($env != '*' &&
			($wildcard == '*' || ( $wildcard != '*' && !($pos === false))))
			{
			$pos = stripos($trackenv, $env);
			}

		if (!($pos === false))
			{
			$posn -= 3;
			$trkarr = array();
			$stgout = $detail;
			$stgout = str_replace('{POSN}', $posn, $stgout);
			$stgout = str_replace('{POSN+}', $posn-0.5, $stgout);
			$stgout = str_replace('{TRACKID}', $tid, $stgout);
			$stgout = str_replace('{TRACKRANK}', $trackrank, $stgout);
			$stgout = str_replace('{RECDATE}', $row['Date'], $stgout);
			$stgout = str_replace('{TRACKNUM}', $tid+10000, $stgout);
			$stgout = str_replace('{TRACKNAME}', sub_maniacodes($trackname) . '  ', $stgout);
			$stgout = str_replace('{TRACKENV}', sub_maniacodes($trackenv) . '  ', $stgout);
			$envstring = sub_maniacodes($trackenv);
			switch ($envstring)
				{
				case "Stadium": $actionstring="15000"; break;
				case "Alpine": $actionstring="15001"; break;
				case "Speed": $actionstring="15002"; break;
				case "Bay": $actionstring="15003"; break;
				case "Rally": $actionstring="15004"; break;
				case "Coast": $actionstring="15005"; break;
				case "Island": $actionstring="15006"; break;
				default: $actionstring="12";
				}
			$stgout = str_replace('{ENVACTION}', $actionstring, $stgout);
			$stgout = str_replace('{TRACKAUTHOR}', sub_maniacodes($trackauthor), $stgout);
			$s .= $stgout;
			$tid++;
			$ctr++;
			if ( $ctr == $tpp )
				{
				$posn = -5;
				$ctr = 0;
				$msgs++;
				$s = $header . $s;
				$player->msgs[$msgs] = $s;
				$s = '';
				}
			$trkarr['name'] = $trackname;
			$trkarr['filename'] = $row['FileName'];
			$trkarr['uid'] = $row['UId'];
			$trkarr['environnement'] = $row['Environnement'];
			$player->tracklist[] = $trkarr;
			}
		}
	if ( $s != '' )		// add if last batch exists
		{
		$s = $header . $s;
		$player->msgs[$msgs+1] = $s;
		}
	if ( sizeof($player->msgs)>0 )
		{
		$player->msgs['curpage'] = 1;
		}
	}

function getChallengesByKarma(&$player, $karmaval) {
	global $aseco;
	$newlist = array();
	$done = false;
	$i = 0;
	$sql = 'select uid, sum(score) as karma from challenges, rs_karma where challenges.id=rs_karma.challengeid group by uid order by karma ';
	$order = 'desc';

	$header = "<?xml version='1.0' encoding='utf-8' ?>
<manialink posx='0.5' posy='0.55'>
  <type>default</type>
  <format textsize='2'/>
  <background bgcolor='222E' bgborderx='0.03' bgbordery='0.03'/>
  <line><cell width='0.94'><text halign='center'>- Server Tracks by Karma -</text></cell></line>
	<line height='.04'>
	<cell width='0.06' bgcolor='888E'><text halign='right'>Id</text></cell>
	<cell width='0.52' bgcolor='888E'><text>  Name</text></cell>
	<cell width='0.25' bgcolor='888E'><text>Author</text></cell>
	<cell width='0.11' bgcolor='888E'><text>Karma</text></cell>
	</line>
	";

	$detail = "<line><cell width='0.06'><text halign='right'>{TRACKID}</text></cell><cell width='0.52'><text>  {TRACKNAME}</text></cell><cell width='0.25'><text>{TRACKAUTHOR}</text></cell><cell width='0.11'><text>{TRACKKARMA}</text></cell></line>" . CRLF;


	if ( $karmaval < 0 )
		{
		$order = 'asc';
		}
	$sql .= $order . ', challenges.name';
	$result = mysql_query($sql);

	if ( mysql_num_rows($result) == 0 )
		{
		mysql_free_result($result);
		return;
		}

	while (!$done) {
		$tracks = $aseco->client->addCall('GetChallengeList', array(300, $i));
  		if (!$aseco->client->multiquery()) {
    		trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
  		} else {
			$tlist = array();
			$response = $aseco->client->getResponse();
			if (sizeof($response[$tracks][0]) > 0) {
				foreach ($response[$tracks][0] as $trow) {
					$newlist[$trow['UId']] = $trow;
				}
				$i = $i + 300;
			} else {
				$done = true;
				break;
			}
		}
	}

	$player->tracklist = array();
	$player->msgs = array();
	$player->msgs['curpage'] = 0;
	$s = '';
	$tid = 1;
	$ctr = 0;
	$msgs = 0;
	while($dbrow = mysql_fetch_array($result))
		{
		if ( ($karmaval < 0 && $dbrow[1] > $karmaval ) || ($karmaval > 0 && $dbrow[1] < $karmaval ))
			{
			break;
			}

		$row = $newlist[$dbrow[0]];		// indexed by uid
		if ( array_key_exists($dbrow[0], $newlist) )		// does the uid exist in the current server track list?
			{
			$trkarr = array();
			$trackname = stripcolors($row['Name']);
			$stgout = $detail;
			$stgout = str_replace('{TRACKID}', '' . $tid . '. ', $stgout);
			$stgout = str_replace('{TRACKNAME}', sub_maniacodes($trackname), $stgout);
			$stgout = str_replace('{TRACKAUTHOR}', sub_maniacodes($row['Author']), $stgout);
			$stgout = str_replace('{TRACKKARMA}', $karmaval, $stgout);
			$s .= $stgout;
			$tid++;
			$ctr++;
			if ( $ctr == 20 )
				{
				$ctr = 0;
				$msgs++;
				$s = $header . $s;
				$player->msgs[$msgs] = $s;
				$s = '';
				}
			$trkarr['name'] = $trackname;
			$trkarr['filename'] = $row['FileName'];
			$trkarr['uid'] = $row['UId'];
			$player->tracklist[] = $trkarr;
			}
		}
	if ( $s > '' )		// add if last batch exists
		{
		$s = $header . $s;
		$player->msgs[$msgs+1] = $s;
		}
	if ( $s > '' || sizeof($player->msgs)>0 )
		{
		$player->msgs['curpage'] = 1;
		}

	mysql_free_result($result);
	}	// end admin_getChallengesByKarma($karmaval)

function getChallengesNoFinish(&$player) {
	global $aseco, $jb_buffer, $manialinkstack;
	
	$newlist = array();
	$done = false;
	$i = 0;
	$player->tracklist = array();
	$player->msgs = array();
	$sql = 'select uid from challenges where id not in (select distinct challengeID from records,players where records.playerID=players.id AND players.login=' . quotedString($player->login) . ') order by `name`';
	$result = mysql_query($sql);

	if ( mysql_num_rows($result) == 0 )
		{
		mysql_free_result($result);
		return;
		}

	while (!$done) {
		$tracks = $aseco->client->addCall('GetChallengeList', array(300, $i));
  		if (!$aseco->client->multiquery()) {
    		trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
  		} else {
			$tlist = array();
			$response = $aseco->client->getResponse();
			if (sizeof($response[$tracks][0]) > 0) {
				foreach ($response[$tracks][0] as $trow) {
					$newlist[$trow['UId']] = $trow;
				}
				$i = $i + 300;
			} else {
				$done = true;
				break;
			}
		}
	}

	$tpp = 18;

	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;

	$id = 7;
	$na = 37;
	$en = 17;
	$au = 17;
	$width = $id+$na+$en+$au;
	$height = $tpp*3+11;
	$hw = $width/2;
	$hh = $height/2;
	$player->msgsw=$width;
	$player->msgsh=$height;

	$header = "<?xml version='1.0' encoding='utf-8' ?><manialink id='100'><frame posn='-$hw $hh $manialinkstack'>";
	$header .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";
	$header .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$header .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="Tracks without records"/>';
	$header .= "<quad posn='0 -4 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$header .= "<label posn='".($id/2)." -4.5 1' halign='center' textsize='2' text='Id'/>";	
	$header .= "<label posn='".($id+$na/2)." -4.5 1' halign='center' textsize='2' text='Name (click for jukebox)'/>";	
	$header .= "<label posn='".($id+$na+$en/2)." -4.5 1' halign='center' textsize='2' text='Environment'/>";	
	$header .= "<label posn='".($id+$na+$en+$au/2)." -4.5 1' halign='center' textsize='2' text='Author'/>";	

	$detail = "<label posn='1 {POSN+} 1' textsize='2' text='{TRACKID}'/>";
	$detail .= "<quad posn='".($id)." {POSN} 0' sizen='$ra 3' action='30025' style='Bgs1InRace' substyle='NavButton'/>";
	$detail .= "<quad posn='".($id)." {POSN} 0' sizen='$na 3' action='{TRACKNUM}' style='Bgs1InRace' substyle='NavButton'/>";
	$detail .= '<label posn="'.($id+1).' {POSN+} 1" textsize="2" text="{TRACKNAME}"/>';
	$detail .= "<label posn='".($id+$na+$en/2)." {POSN+} 1' halign='center' textsize='2' text='{TRACKENV}'/>";
	$detail .= '<label posn="'.($width-1).' {POSN+} 1" halign="right" textsize="2" text="{TRACKAUTHOR}"/>';

	$player->msgs['curpage'] = 0;

	$s = '';
	$tid = 1;
	$ctr = 0;
	$msgs = 0;
	$posn = -5;
	while($dbrow = mysql_fetch_array($result))
		{

		$row = $newlist[$dbrow[0]];		// indexed by uid
		if ( array_key_exists($dbrow[0], $newlist) )		// does the uid exist in the current server track list?
			{
			$posn -= 3;
			$trkarr = array();
			$stgout = $detail;
			$trackname = $row['Name']."\$z";
			$trackenv = $row['Environnement'];

			$trackuid = $dbrow[0];
			if (in_array($trackuid, $jb_buffer))
				$trackname .= "\$n (played)\$z";

			$stgout = str_replace('{POSN}', $posn, $stgout);
			$stgout = str_replace('{POSN+}', $posn-0.5, $stgout);
			$stgout = str_replace('{TRACKID}', '' . $tid . '. ', $stgout);
			$stgout = str_replace('{TRACKNUM}', '' . $tid+10000 . '. ', $stgout);
			$stgout = str_replace('{TRACKNAME}', sub_maniacodes($trackname), $stgout);
			$stgout = str_replace('{TRACKENV}', sub_maniacodes($trackenv) . '  ', $stgout);
			$stgout = str_replace('{TRACKAUTHOR}', sub_maniacodes($row['Author']), $stgout);
			$s .= $stgout;
			$tid++;
			$ctr++;
			if ( $ctr == $tpp )
				{
				$posn = -5;
				$color = 6;
				$ctr = 0;
				$msgs++;
				$s = $header . $s;
				$player->msgs[$msgs] = $s;
				$s = '';
				}
			$trkarr['name'] = $trackname;
			$trkarr['filename'] = $row['FileName'];
			$trkarr['uid'] = $row['UId'];
			$trkarr['environnement'] = $row['Environnement'];
			$player->tracklist[] = $trkarr;
			}
		}
	if ( $s > '' )		// add if last batch exists
		{
		$s = $header . $s;
		$player->msgs[$msgs+1] = $s;
		}
	if ( $s > '' || sizeof($player->msgs)>0 )
		{
		$player->msgs['curpage'] = 1;
		}

	mysql_free_result($result);
	}	// end admin_getChallengesByKarma($karmaval)

/*
 all strings are length followed by data of exact length (no trailing null)

 0x4A long coppers
 0x67 string id
 4 unknown bytes
 string environment
 4 unknown bytes
 string author
 string trackname


 */

function GetGBXString($handle)
	{
	$data = fread($handle, 4);
	$result = unpack('Vlen', $data);
	if ( ($result['len'] > 256) || ($result['len'] < 1))
		{
//		echo 'Len=' . $result['len'] . CRLF;
		return 'read error';
		}
	$data = fread($handle, $result['len']);
	return $data;
	}  //  GetGBXString

function GetChallengeData($filename, $rtnvotes)
	{
	global $aseco, $tmxvoteratio;
	$ret = array();
	if ( !file_exists($filename) )
		{
		$ret['name'] = 'file not found';
		$ret['votes'] = 500;
		return $ret;
		}
	if ( $rtnvotes )
		{
		$players = $aseco->client->addCall('GetPlayerList', array(50,0));
		if (!$aseco->client->multiquery())
			{
			trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
			$response = array();
			$ret['name'] = 'failed to get players';
			$ret['votes'] = 500;
			return $ret;
			}
		else
			{
			$response = $aseco->client->getResponse();
			$nbplrs = sizeof($response[$players][0]);
			$ret['votes'] = floor($nbplrs * $tmxvoteratio);
			if ($ret['votes'] < 1)
				{
				$ret['votes']++;
				}
			if ( $aseco->debug )
				{
				$ret['votes'] = 1;
				}
			}
		}

	$handle = fopen($filename, 'rb');
	fseek($handle, 0x00, SEEK_SET);
	$data = fread($handle, 3);
	if ( $data == 'GBX' )
		{
		fseek($handle, 0x11, SEEK_SET);
		$data = fread($handle, 4);
		$r = unpack('Vfilever', $data);
		if ( $r['filever'] == 5 )
			{
			$offset = 0x6F;
			}
		elseif ( $r['filever'] == 4 )
			{
			$offset = 0x63;
			}
		elseif ( $r['filever'] == 2 )
			{
			$offset = 0x43;
			}
//		fseek($handle, 0x4A + $offset, SEEK_SET);
//		$data = fread($handle, 4);
//		$r = unpack('coppers', $data);
//		$ret['coppers'] = $r['coppers'];
		fseek($handle, $offset, SEEK_SET);
		$ret['uid'] = GetGBXString($handle);
		fseek($handle, 0x04, SEEK_CUR);
		$ret['environment'] = GetGBXString($handle);
		fseek($handle, 0x04, SEEK_CUR);
		$ret['author'] = GetGBXString($handle);
		$ret['name'] = GetGBXString($handle);
		}
	else
		{
		$ret['votes'] = 500;
		$ret['name'] = 'Not a GBX file';
		}

	fclose($handle);
	return $ret;
	}  //  GetChallengeData($filename)

Aseco::registerEvent('onPlayerServerMessageAnswer', 'event_multi_message');		// RASP .4.1
?>
