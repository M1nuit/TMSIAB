<?php
//* plugin.scorepanel.php - Scorepanel
//* Version:   0.8.0
//* Coded by:  cyrilw && libero6
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('EndChallenge', 'scores_endchallenge');
control::RegisterEvent('BeginChallenge', 'scores_beginchallenge');
control::RegisterEvent('ManialinkPageAnswer', 'scores_mlpageanswer');

function scores_endchallenge($control, $end_chall_data){
	global $db, $scoretable, $st_feld;
	
	$ec_curr_id = 0;;
	$scoretable = '';
	$scoretable = array();
	
	while(isset($end_chall_data[0][$ec_curr_id])){
        $scoretable[] = $end_chall_data[0][$ec_curr_id];
		$ec_curr_id++;
	}
			
	$st_currid = 0;
	$st_posnx = -26.1;
	$st_posny = 29;
	$st_feld = array();
	
	while(isset($scoretable[$st_currid])){
		$st_ptime = formattime($scoretable[$st_currid]['BestTime']);
		$url = "http://fox-control.de/~skpfox/scripts/get_data.php?login=".trim($scoretable[$st_currid]['Login']).""; 
		$file = fopen($url, "rb");
		$skill_data_p = stream_get_contents($file);
		$file = fclose($file);
		$skill_data_p = explode('{expl}', $skill_data_p);
		$control->client->query('GetDetailedPlayerInfo', $scoretable[$st_currid]['Login']);
		$detpinfo = $control->client->getResponse();
		$playerrank = $st_currid + 1;
		$st_feld[$st_currid] = '<frame posn="[xx] [yy] 2">
		<quad posn="0 0 2" sizen="26 10" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		<label posn="-12.2 4 3" sizen="2 2" textsize="4" text="$o$09f'.$playerrank.'"/>
		<label posn="-3.9 4 3" sizen="15 2" textsize="2" text="'.htmlspecialchars($scoretable[$st_currid]['NickName']).'"/>
		<label posn="-11.7 -1 3" sizen="10 2" textsize="2" text="$o$fff'.$st_ptime.'"/>
		<quad posn="-9.9 4.25 3" sizen="5 5" image="tmtp://'.$detpinfo['Avatar']['FileName'].'"/>
		<label posn="12.6 0 3" sizen="10 2" textsize="2" halign="right" text="SKP: '.$skill_data_p[0].'"/>
		<label posn="12.6 -2 3" sizen="10 2" textsize="2" halign="right" text="LVL: '.$skill_data_p[1].'"/>
	    </frame>';
		$st_currid++;
	}
	
	//LOAD LOCAL RECORDS AND WRITE THEM IN A ARRAY

	/***********************************
	***********LOCAL RECORDS************
	***********************************/
	$control->client->query('GetCurrentChallengeInfo');
	$scp_challenge_info = $control->client->getResponse();
	$scp_local = '';
	$scp_local = array();
	$scp_explode = '||xx||';
	$scp_curr_id = 0;
	while(true){
		$sql = "SELECT * FROM `records` WHERE challengeid = '".$scp_challenge_info['UId']."' ORDER BY time ASC LIMIT ".$scp_curr_id.", 1";
		$mysql = mysqli_query($db, $sql);
		if($localrecords_list = $mysql->fetch_object()){
			$sql = "SELECT * FROM `players` WHERE playerlogin = '".$localrecords_list->playerlogin."'";
			$mysql = mysqli_query($db, $sql);
			if($localrecs_playerdata = $mysql->fetch_object()) $localrecs_playernick = $localrecs_playerdata->nickname;
			else $localrecs_playernick = $localrecords_list->nickname;
		
			//FORMAT TIME
			$time_to_format = $localrecords_list->time;
			$formatedtime_minutes = floor($time_to_format/(1000*60));
			$formatedtime_seconds = floor(($time_to_format - $formatedtime_minutes*60*1000)/1000);
			$formatedtime_hseconds = substr($time_to_format, strlen($time_to_format)-3, 2);
			$formatedtime = sprintf('%02d:%02d.%02d', $formatedtime_minutes, $formatedtime_seconds, $formatedtime_hseconds);
		
			$scp_local[$scp_curr_id]['NickName'] = $localrecs_playernick;
			$scp_local[$scp_curr_id]['PlayerLogin'] = $localrecords_list->playerlogin;
			$scp_local[$scp_curr_id]['Time'] = $formatedtime;
			$scp_local[$scp_curr_id]['Rank'] = $scp_curr_id+1;
		
			$scp_curr_id++;
		}
		else break;	
	}
		
	$scp_curr_id = 0;
	while($scp_curr_id<=30){
		if(!isset($scp_local[$scp_curr_id])){
			$scp_local[$scp_curr_id]['NickName'] = '';
			$scp_local[$scp_curr_id]['PlayerLogin'] = '';
			$scp_local[$scp_curr_id]['Time'] = '';
			$scp_local[$scp_curr_id]['Rank'] = '';
		}
		$scp_curr_id++;
	}
	
	//Dedimania
	global $_Dedimania_recs;
	$scoretable_dedimania = '';
	$d_id = 0;
	while(isset($_Dedimania_recs[$d_id])){
		$d_y = 9 - $d_id * 2;
		$p_id = $d_id + 1;
		$scoretable_dedimania .= '<label posn="45 '.$d_y.' 5" textsize="0" text="$o$09f'.$p_id.'"/>';
		$scoretable_dedimania .= '<label posn="47.25 '.$d_y.' 5" textsize="0" sizen="4.5 2" text="'.formattime($_Dedimania_recs[$d_id]['Time']).'"/>';
		$scoretable_dedimania .= '<label posn="51 '.$d_y.' 5" textsize="1" sizen="13 2" text="'.htmlspecialchars($_Dedimania_recs[$d_id]['Nick']).'"/>';
		$d_id++;
		if($d_id>=17) break;
	}
	
	//SKP RANKLIST
	$url = "http://fox-control.de/~skpfox/scripts/get_data.php?record=show&show=Top5"; 
	$file = fopen($url, "rb");
	$skp_content = stream_get_contents($file);
	$file = fclose($file);
	$skp_content = explode('{expl}', $skp_content);
	$scoretable_skp = '<label posn="-64 45.25 12" sizen="5 2" textsize="1" text="'.$skp_content[0].'"/>
	<label posn="-58.5 45.25 12" sizen="13 45 12" textsize="1" text="'.htmlspecialchars(stripslashes(str_replace('{leer}', ' ', $skp_content[1]))).'"/>
	<label posn="-64 43.25 12" sizen="5 2" textsize="1" text="'.$skp_content[2].'"/>
	<label posn="-58.5 43.25 12" sizen="13 45 12" textsize="1" text="'.htmlspecialchars(stripslashes(str_replace('{leer}', ' ', $skp_content[3]))).'"/>
	<label posn="-64 41.25 12" sizen="5 2" textsize="1" text="'.$skp_content[4].'"/>
	<label posn="-58.5 41.25 12" sizen="13 45 12" textsize="1" text="'.htmlspecialchars(stripslashes(str_replace('{leer}', ' ', $skp_content[5]))).'"/>
	<label posn="-64 39.25 12" sizen="5 2" textsize="1" text="'.$skp_content[6].'"/>
	<label posn="-58.5 39.25 12" sizen="13 45 12" textsize="1" text="'.htmlspecialchars(stripslashes(str_replace('{leer}', ' ', $skp_content[7]))).'"/>
	<label posn="-64 37.25 12" sizen="5 2" textsize="1" text="'.$skp_content[8].'"/>
	<label posn="-58.5 37.25 12" sizen="13 45 12" textsize="1" text="'.htmlspecialchars(stripslashes(str_replace('{leer}', ' ', $skp_content[9]))).'"/>';
	
	//DONATES
	$sql = "SELECT * FROM `players` ORDER BY donations DESC LIMIT 0, 1";
	$mysql = mysqli_query($db, $sql);
	if($don_data = $mysql->fetch_object() AND $don_data->donations!=='0') $dons_1 = array('NickName' => $don_data->nickname, 'Dons' => $don_data->donations);
	else $dons_1 = array('NickName' => '', 'Dons' => '');
	$sql = "SELECT * FROM `players` ORDER BY donations DESC LIMIT 1, 1";
	$mysql = mysqli_query($db, $sql);
	if($don_data = $mysql->fetch_object() AND $don_data->donations!=='0') $dons_2 = array('NickName' => $don_data->nickname, 'Dons' => $don_data->donations);
	else $dons_2 = array('NickName' => '', 'Dons' => '');
	$sql = "SELECT * FROM `players` ORDER BY donations DESC LIMIT 2, 1";
	$mysql = mysqli_query($db, $sql);
	if($don_data = $mysql->fetch_object() AND $don_data->donations!=='0') $dons_3 = array('NickName' => $don_data->nickname, 'Dons' => $don_data->donations);
	else $dons_3 = array('NickName' => '', 'Dons' => '');
	$sql = "SELECT * FROM `players` ORDER BY donations DESC LIMIT 3, 1";
	$mysql = mysqli_query($db, $sql);
	if($don_data = $mysql->fetch_object() AND $don_data->donations!=='0') $dons_4 = array('NickName' => $don_data->nickname, 'Dons' => $don_data->donations);
	else $dons_4 = array('NickName' => '', 'Dons' => '');
	$sql = "SELECT * FROM `players` ORDER BY donations DESC LIMIT 4, 1";
	$mysql = mysqli_query($db, $sql);
	if($don_data = $mysql->fetch_object() AND $don_data->donations!=='0') $dons_5 = array('NickName' => $don_data->nickname, 'Dons' => $don_data->donations);
	else $dons_5 = array('NickName' => '', 'Dons' => '');
	$scoretable_dons = '<label posn="-44 45.25 12" sizen="4.5 2" textsize="1" text="'.$dons_1['Dons'].'"/>
	<label posn="-38.75 45.25 12" sizen="13 2" textsize="1" text="'.htmlspecialchars($dons_1['NickName']).'"/>
	<label posn="-44 43.25 12" sizen="4.5 2" textsize="1" text="'.$dons_2['Dons'].'"/>
	<label posn="-38.75 43.25 12" sizen="13 2" textsize="1" text="'.htmlspecialchars($dons_2['NickName']).'"/>
	<label posn="-44 41.25 12" sizen="4.5 2" textsize="1" text="'.$dons_3['Dons'].'"/>
	<label posn="-38.75 41.25 12" sizen="13 2" textsize="1" text="'.htmlspecialchars($dons_3['NickName']).'"/>
	<label posn="-44 39.25 12" sizen="4.5 2" textsize="1" text="'.$dons_4['Dons'].'"/>
	<label posn="-38.75 39.25 12" sizen="13 2" textsize="1" text="'.htmlspecialchars($dons_4['NickName']).'"/>
	<label posn="-44 37.25 12" sizen="4.5 2" textsize="1" text="'.$dons_5['Dons'].'"/>
	<label posn="-38.75 37.25 12" sizen="13 2" textsize="1" text="'.htmlspecialchars($dons_5['NickName']).'"/>';
	
	//BEST TRACKS
	$control->client->query('GetCurrentChallengeInfo');
	$st_challinfo = $control->client->getResponse();
	$besttracks = array();
	$sql = "SELECT * FROM `karma` WHERE playerlogin = 'root' ORDER BY vote DESC LIMIT 0, 1";
	$mysql = mysqli_query($db, $sql);
	if($track_01 = $mysql->fetch_object()){
		$besttracks[1] = array('Name' => $track_01->challengename, 'Karma' => $track_01->vote);
	}
	else{
		$besttracks[1] = array('Name' => '', 'Karma' => '');
	}
	$sql = "SELECT * FROM `karma` WHERE playerlogin = 'root' ORDER BY vote DESC LIMIT 1, 1";
	$mysql = mysqli_query($db, $sql);
	if($track_02 = $mysql->fetch_object()){
		$besttracks[2] = array('Name' => $track_02->challengename, 'Karma' => $track_02->vote);
	}
	else{
		$besttracks[2] = array('Name' => '', 'Karma' => '');
	}
	$sql = "SELECT * FROM `karma` WHERE playerlogin = 'root' ORDER BY vote DESC LIMIT 2, 1";
	$mysql = mysqli_query($db, $sql);
	if($track_03 = $mysql->fetch_object()){
		$besttracks[3] = array('Name' => $track_03->challengename, 'Karma' => $track_03->vote);
	}
	else{
		$besttracks[3] = array('Name' => '', 'Karma' => '');
	}
	$sql = "SELECT * FROM `karma` WHERE playerlogin = 'root' ORDER BY vote DESC LIMIT 3, 1";
	$mysql = mysqli_query($db, $sql);
	if($track_04 = $mysql->fetch_object()){
		$besttracks[4] = array('Name' => $track_04->challengename, 'Karma' => $track_04->vote);
	}
	else{
		$besttracks[4] = array('Name' => '', 'Karma' => '');
	}
	$sql = "SELECT * FROM `karma` WHERE playerlogin = 'root' ORDER BY vote DESC LIMIT 4, 1";
	$mysql = mysqli_query($db, $sql);
	if($track_05 = $mysql->fetch_object()){
		$besttracks[5] = array('Name' => $track_05->challengename, 'Karma' => $track_05->vote);
	}
	else{
		$besttracks[5] = array('Name' => '', 'Karma' => '');
	}
	
	$scoretable_besttracks = '<label posn="-4 45.25 12" sizen="4.5 2" textsize="1" text="$o$09f'.$besttracks[1]['Karma'].'"/>
	<label posn="0 45.25 12" sizen="15 2" textsize="1" text="'.htmlspecialchars(stripslashes($besttracks[1]['Name'])).'"/>
	<label posn="-4 43.25 12" sizen="4.5 2" textsize="1" text="$o$09f'.$besttracks[2]['Karma'].'"/>
	<label posn="0 43.25 12" sizen="15 2" textsize="1" text="'.htmlspecialchars(stripslashes($besttracks[2]['Name'])).'"/>
	<label posn="-4 41.25 12" sizen="4.5 2" textsize="1" text="$o$09f'.$besttracks[3]['Karma'].'"/>
	<label posn="0 41.25 12" sizen="15 2" textsize="1" text="'.htmlspecialchars(stripslashes($besttracks[3]['Name'])).'"/>
	<label posn="-4 39.25 12" sizen="4.5 2" textsize="1" text="$o$09f'.$besttracks[4]['Karma'].'"/>
	<label posn="0 39.25 12" sizen="15 2" textsize="1" text="'.htmlspecialchars(stripslashes($besttracks[4]['Name'])).'"/>
	<label posn="-4 37.25 12" sizen="4.5 2" textsize="1" text="$o$09f'.$besttracks[5]['Karma'].'"/>
	<label posn="0 37.25 12" sizen="15 2" textsize="1" text="'.htmlspecialchars(stripslashes($besttracks[5]['Name'])).'"/>';
	
	
	//ACTIVE
	$active = array();
	$sql = "SELECT * FROM `players` ORDER BY timeplayed DESC LIMIT 0, 1";
	$mysql = mysqli_query($db, $sql);
	if($player_01 = $mysql->fetch_object() AND $player_01->timeplayed!=='0'){
		$active[1] = array('NickName' => stripslashes($player_01->nickname), 'Time' => $control->formattime_hour($player_01->timeplayed));
	}
	else{
		$active[1] = array('NickName' => '', 'Time' => '');
	}
	$sql = "SELECT * FROM `players` ORDER BY timeplayed DESC LIMIT 1, 1";
	$mysql = mysqli_query($db, $sql);
	if($player_02 = $mysql->fetch_object() AND $player_02->timeplayed!=='0'){
		$active[2] = array('NickName' => stripslashes($player_02->nickname), 'Time' => $control->formattime_hour($player_02->timeplayed));
	}
	else{
		$active[2] = array('NickName' => '', 'Time' => '');
	}
	$sql = "SELECT * FROM `players` ORDER BY timeplayed DESC LIMIT 2, 1";
	$mysql = mysqli_query($db, $sql);
	if($player_03 = $mysql->fetch_object() AND $player_03->timeplayed!=='0'){
		$active[3] = array('NickName' => stripslashes($player_03->nickname), 'Time' => $control->formattime_hour($player_03->timeplayed));
	}
	else{
		$active[3] = array('NickName' => '', 'Time' => '');
	}
	$sql = "SELECT * FROM `players` ORDER BY timeplayed DESC LIMIT 3, 1";
	$mysql = mysqli_query($db, $sql);
	if($player_04 = $mysql->fetch_object() AND $player_04->timeplayed!=='0'){
		$active[4] = array('NickName' => stripslashes($player_04->nickname), 'Time' => $control->formattime_hour($player_04->timeplayed));
	}
	else{
		$active[4] = array('NickName' => '', 'Time' => '');
	}
	$sql = "SELECT * FROM `players` ORDER BY timeplayed DESC LIMIT 4, 1";
	$mysql = mysqli_query($db, $sql);
	if($player_05 = $mysql->fetch_object() AND $player_05->timeplayed!=='0'){
		$active[5] = array('NickName' => stripslashes($player_05->nickname), 'Time' => $control->formattime_hour($player_05->timeplayed));
	}
	else{
		$active[5] = array('NickName' => '', 'Time' => '');
	}
	$scoretable_mostactive = '<label posn="-23.75 45.25 12" sizen="4.5 2" textsize="1" text="$o$09f'.$active[1]['Time'].'"/>
	<label posn="-19 45.25 12" sizen="15 2" textsize="1" text="'.htmlspecialchars($active[1]['NickName']).'"/>
	<label posn="-23.75 43.25 12" sizen="4.5 2" textsize="1" text="$o$09f'.$active[2]['Time'].'"/>
	<label posn="-19 43.25 12" sizen="15 2" textsize="1" text="'.htmlspecialchars($active[2]['NickName']).'"/>
	<label posn="-23.75 41.25 12" sizen="4.5 2" textsize="1" text="$o$09f'.$active[3]['Time'].'"/>
	<label posn="-19 41.25 12" sizen="15 2" textsize="1" text="'.htmlspecialchars($active[3]['NickName']).'"/>
	<label posn="-23.75 39.25 12" sizen="4.5 2" textsize="1" text="$o$09f'.$active[4]['Time'].'"/>
	<label posn="-19 39.25 12" sizen="15 2" textsize="1" text="'.htmlspecialchars($active[4]['NickName']).'"/>
	<label posn="-23.75 37.25 12" sizen="4.5 2" textsize="1" text="$o$09f'.$active[5]['Time'].'"/>
	<label posn="-19 37.25 12" sizen="15 2" textsize="1" text="'.htmlspecialchars($active[5]['NickName']).'"/>';
	
	//Best SKP Servers
	$url = "http://fox-control.de/~skpfox/scripts/get_data.php?record=servers"; 
	$file = fopen($url, "rb");
	$skp_servers = stream_get_contents($file);
	$file = fclose($file);
	$skp_servers = explode('{expl}', $skp_servers);
	$scoretable_bestservers = '';
	if(isset($skp_servers[1])){
		$scoretable_bestservers .= '<label posn="16 45 12" sizen="5 2" scale="0.6" text="'.$skp_servers[1].'"/>';
		$scoretable_bestservers .= '<label posn="21.5 45 12" sizen="18 2" scale="0.6" text="'.htmlspecialchars($skp_servers[0]).'"/>';
		if(isset($skp_servers[3])){
			$scoretable_bestservers .= '<label posn="16 43 12" sizen="5 2" scale="0.6" text="'.$skp_servers[3].'"/>';
			$scoretable_bestservers .= '<label posn="21.5 43 12" sizen="18 2" scale="0.6" text="'.htmlspecialchars($skp_servers[2]).'"/>';
			if(isset($skp_servers[5])){
				$scoretable_bestservers .= '<label posn="16 41 12" sizen="5 2" scale="0.6" text="'.$skp_servers[5].'"/>';
				$scoretable_bestservers .= '<label posn="21.5 41 12" sizen="18 2" scale="0.6" text="'.htmlspecialchars($skp_servers[4]).'"/>';
				if(isset($skp_servers[7])){
					$scoretable_bestservers .= '<label posn="16 39 12" sizen="5 2" scale="0.6" text="'.$skp_servers[7].'"/>';
					$scoretable_bestservers .= '<label posn="21.5 39 12" sizen="18 2" scale="0.6" text="'.htmlspecialchars($skp_servers[6]).'"/>';
					if(isset($skp_servers[9])){
						$scoretable_bestservers .= '<label posn="16 37 12" sizen="5 2" scale="0.6" text="'.$skp_servers[9].'"/>';
						$scoretable_bestservers .= '<label posn="21.5 37 12" sizen="18 2" scale="0.6" text="'.htmlspecialchars($skp_servers[8]).'"/>';
					}
				}
			}
		}
	}
	
	//Next track
	$control->client->query('GetNextChallengeInfo');
	$nca = $control->client->getResponse();
	$scoretable_nexttrack = '<label posn="0 0 0" scale="0.6" sizen="35 2" text="$o$fffName:$z '.$nca['Name'].'"/>
	<label posn="0 -2 0" scale="0.6" sizen="35 2" text="$o$fffAuthor:$z '.$nca['Author'].'"/>
	<label posn="0 -4 0" scale="0.6" sizen="35 2" text="$o$fffEnvironnement:$z '.$nca['Environnement'].'"/>
	<label posn="0 -6 0" scale="0.6" sizen="35 2" text="$o$fffMood:$z '.$nca['Mood'].'"/>
	<label posn="0 -8 0" scale="0.6" sizen="35 2" text="$o$fffAuthorTime:$z '.formattime($nca['AuthorTime']).'"/>';
	
	$scoretable_locals = '';
	
	$count = count($scp_local);
	if($count > 24){
	    $fields = 24;
	}else{
	    $fields = $count;
	}
	
	$scoretable_locals .= '<?xml version="1.0" encoding="utf-8" ?>
	                   <manialink id="2070">
	                       <quad posn="0 5 1" sizen="80 60" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
	                       <quad posn="-64.5 28.5 10" sizen="20 53.5" style="Bgs1InRace" substyle="NavButton" />
	                       <quad posn="-64.5 28.5 10" sizen="20 53.5" style="Bgs1InRace" substyle="NavButton" />
	                       <quad posn="-66.75 28 11" sizen="21.8 3" style="Bgs1InRace" substyle="BgTitle3" />
	                       <label posn="-62 27.5 12" scale="0.7" text="$o$FFFLocal Records" />';
	
    $x = -63.75;
	$y = 24.5;
	$z = 13;
	
	for($run=0; $run<$fields; $run++){
	    $scoretable_locals .= '<label posn="'.$x.' '.$y.' '.$z.'" sizen="1 2" textsize="0" text="$09f$o'.$scp_local[$run]['Rank'].'"/>';
	    $scoretable_locals .= '<label posn="'.($x+1.25).' '.$y.' '.$z.'" sizen="4 2" textsize="0" text="$fff'.$scp_local[$run]['Time'].'"/>';
	    $scoretable_locals .= '<label posn="'.($x+4.75).' '.($y+0.5).' '.$z.'" sizen="13.5 2" textsize="1" text="$fff'.htmlspecialchars(stripslashes($scp_local[$run]['NickName'])).'"/>';
	
	    $y = $y-2;
	}
	
	$control->client->query('SendDisplayManialinkPage',
	'
	'.$scoretable_locals.'
	<quad posn="44.5 13 10" sizen="20 38" style="Bgs1InRace" substyle="NavButton" />
	<quad posn="44.5 13 10" sizen="20 38" style="Bgs1InRace" substyle="NavButton" />
	<quad posn="45 12.5 11" sizen="21.8 3" style="Bgs1InRace" substyle="BgTitle3" /> 
	<label posn="46 12 12" scale="0.7" text="$o$FFFDedimania Records" />
	'.$scoretable_dedimania.'
	
	<quad posn="-64.5 51 10" sizen="20 15.6" style="Bgs1InRace" substyle="BgTitle2" />
	<quad posn="-66.75 48 11" sizen="21.8 3" style="Bgs1InRace" substyle="BgTitle3" />
	<label posn="-54.5 47.5 12" scale="0.7" halign="center" text="$o$FFFSKP Ranklist" />
	'.$scoretable_skp.'
	
	<quad posn="-44.5 51 10" sizen="20 15.6" style="Bgs1InRace" substyle="BgTitle2" />
	<quad posn="-44 48 11" sizen="19 3" style="Bgs1InRace" substyle="BgTitle3" />
	<label posn="-34.5 47.5 12" scale="0.7" halign="center" text="$o$FFFTop Donators" />
	'.$scoretable_dons.'
	
	<quad posn="-24.5 51 10" sizen="20 15.6" style="Bgs1InRace" substyle="BgTitle2" />
	<quad posn="-24 48 11" sizen="19 3" style="Bgs1InRace" substyle="BgTitle3" />
	<label posn="-14.5 47.5 12" scale="0.7" halign="center" text="$o$FFFMost active" />
	'.$scoretable_mostactive.'
	
	<quad posn="-4.5 51 10" sizen="20 15.6" style="Bgs1InRace" substyle="BgTitle2" />
	<quad posn="-4 48 11" sizen="19 3" style="Bgs1InRace" substyle="BgTitle3" />
	<label posn="5.5 47.5 12" scale="0.7" halign="center" text="$o$FFFBest Tracks" />
	'.$scoretable_besttracks.'
	
	<quad posn="15.5 51 10" sizen="20 15.6" style="Bgs1InRace" substyle="BgTitle2" />
	<quad posn="16 48 11" sizen="19 3" style="Bgs1InRace" substyle="BgTitle3" />
	<label posn="25.5 47.5 12" scale="0.7" halign="center" text="$o$FFFBest Servers (SKP)" />
	'.$scoretable_bestservers.'
	
	<quad posn="35.5 51 10" sizen="40 15.6" style="Bgs1InRace" substyle="BgTitle2" />
	<quad posn="36 48 11" sizen="39 3" style="Bgs1InRace" substyle="BgTitle3" />
	<label posn="39 47.5 12" scale="0.7" text="$o$FFFNext Track" />
	<frame posn="36.5 45 12">
	'.$scoretable_nexttrack.'
	</frame>
	</manialink>', 0, false);
	
	if(isset($st_feld[15])) $nextarrow = '<quad posn="35 -21 20" sizen="3 3" style="Icons64x64_1" substyle="ArrowNext" action="2073"/>';
	else $nextarrow = '';

	$fields_ml = '';
	
	$count = count($st_feld);
	if($count>14){
	    $fields = 14;
	}else{
	    $fields = $count;
	}
	
	$x = -26;
	$y = 29;
	
	for($run=0; $run<$fields; $run++){
	    if($x>26 AND $y == 29){
		    $x = -26;
			$y = 18;
		}
		if($x>26 AND $y == 18){
		    $x = -26;
			$y = 7;
		}
		if($x>26 AND $y == 7){
		    $x = -26;
			$y = -4;
		}
		if($x>26 AND $y == -4){
		    $x = -26;
			$y = -15;
		}
	
	    $fields_ml .= str_replace('[xx]', $x, str_replace('[yy]', $y, $st_feld[$run]));

		$x = $x + 26;
	}
	
	$control->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="utf-8" ?>
	<manialink id="2071">
	'.$fields_ml.'
	'.$nextarrow.'
	</manialink>', 0, false);
	$end_chall_data = array();

}

function scores_beginchallenge($control, $calldata){
    global $scoretable;
	$control->close_ml(2070, '');
	$control->close_ml(2071, '');
	$control->close_ml(2072, '');
	
	$id = 0;
	while(isset($scoretable[$id])){
	    $control->client->query('GetDetailedPlayerInfo', $scoretable[$id]['Login']);
		$detpinfo = $control->client->getResponse();
		
	    $control->client->query('ChatSendServerMessageToLogin', '$f00-> You gained $fff'.$detpinfo['LadderStats']['LastMatchScore'].' $f00LadderPoints last round!', $scoretable[$id]['Login']);
		
		$id++;
	}
}
function scores_mlpageanswer($control, $ManialinkPageAnswer){
	global $st_feld;
	if($ManialinkPageAnswer[2] >= 2073 AND $ManialinkPageAnswer[2]<= 2090){
		$id = $ManialinkPageAnswer[2] - 2072;
		$id = $id * 15;
		$nextid = $ManialinkPageAnswer[2] + 1;
		$previd = $ManialinkPageAnswer[2] - 1;
		if(isset($st_feld[$id+15])) $nextarrow = '<quad posn="35 -21 20" sizen="3 3" style="Icons64x64_1" substyle="ArrowNext" action="'.$nextid.'"/>';
		else $nextarrow = '';
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="utf-8" ?>
		<manialink id="2071">
		'.str_replace('[xx]', '-26.1', str_replace('[yy]', '29', $st_feld[$id])).'
		'.str_replace('[xx]', '-0.1', str_replace('[yy]', '29', $st_feld[$id + 1])).'
		'.str_replace('[xx]', '26.2', str_replace('[yy]', '29', $st_feld[$id + 2])).'
		'.str_replace('[xx]', '-26.1', str_replace('[yy]', '18', $st_feld[$id + 3])).'
		'.str_replace('[xx]', '0.1', str_replace('[yy]', '18', $st_feld[$id + 4])).'
		'.str_replace('[xx]', '26.2', str_replace('[yy]', '19', $st_feld[$id + 5])).'
		'.str_replace('[xx]', '-26.1', str_replace('[yy]', '7', $st_feld[$id + 6])).'
		'.str_replace('[xx]', '0.1', str_replace('[yy]', '7', $st_feld[$id + 7])).'
		'.str_replace('[xx]', '26.2', str_replace('[yy]', '7', $st_feld[$id + 8])).'
		'.str_replace('[xx]', '-26.1', str_replace('[yy]', '-4', $st_feld[$id + 9])).'
		'.str_replace('[xx]', '0.1', str_replace('[yy]', '-4', $st_feld[$id + 10])).'
		'.str_replace('[xx]', '26.2', str_replace('[yy]', '-4', $st_feld[$id + 11])).'
		'.str_replace('[xx]', '-26.1', str_replace('[yy]', '-15', $st_feld[$id + 12])).'
		'.str_replace('[xx]', '0.1', str_replace('[yy]', '-15', $st_feld[$id + 13])).'
		'.str_replace('[xx]', '26.2', str_replace('[yy]', '-15', $st_feld[$id + 14])).'
		'.$nextarrow.'
		<quad posn="-38 -21 20" sizen="3 3" style="Icons64x64_1" substyle="ArrowPrev" action="'.$previd.'"/>
		</manialink>', 0, false);
	}
	elseif($ManialinkPageAnswer[2]==2072){
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="utf-8" ?>
		<manialink id="2071">
		'.str_replace('[xx]', '-26.1', str_replace('[yy]', '29', $st_feld[0])).'
		'.str_replace('[xx]', '-0.1', str_replace('[yy]', '29', $st_feld[1])).'
		'.str_replace('[xx]', '26.2', str_replace('[yy]', '29', $st_feld[2])).'
		'.str_replace('[xx]', '-26.1', str_replace('[yy]', '18', $st_feld[3])).'
		'.str_replace('[xx]', '0.1', str_replace('[yy]', '18', $st_feld[4])).'
		'.str_replace('[xx]', '26.2', str_replace('[yy]', '19', $st_feld[5])).'
		'.str_replace('[xx]', '-26.1', str_replace('[yy]', '7', $st_feld[6])).'
		'.str_replace('[xx]', '0.1', str_replace('[yy]', '7', $st_feld[7])).'
		'.str_replace('[xx]', '26.2', str_replace('[yy]', '7', $st_feld[8])).'
		'.str_replace('[xx]', '-26.1', str_replace('[yy]', '-4', $st_feld[9])).'
		'.str_replace('[xx]', '0.1', str_replace('[yy]', '-4', $st_feld[10])).'
		'.str_replace('[xx]', '26.2', str_replace('[yy]', '-4', $st_feld[11])).'
		'.str_replace('[xx]', '-26.1', str_replace('[yy]', '-15', $st_feld[12])).'
		'.str_replace('[xx]', '0.1', str_replace('[yy]', '-15', $st_feld[13])).'
		'.str_replace('[xx]', '26.2', str_replace('[yy]', '-15', $st_feld[14])).'
		<quad posn="35 -21 20" sizen="3 3" style="Icons64x64_1" substyle="ArrowNext" action="2073"/>
		</manialink>', 0, false);
	}
}
?>