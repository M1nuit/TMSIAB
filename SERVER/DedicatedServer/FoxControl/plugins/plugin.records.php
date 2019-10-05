<?php
//* plugin.records.php - Records
//* Version:   0.9.0
//* Coded by:  cyrilw && libero6
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('StartUp', 'records_startup');
control::RegisterEvent('BeginChallenge', 'records_newchallenge');
control::RegisterEvent('EndChallenge', 'records_score');
control::RegisterEvent('PlayerConnect', 'records_playerconnect');
control::RegisterEvent('PlayerFinish', 'records_playerfinish');
control::RegisterEvent('ManialinkPageAnswer', 'records_mlanswer');
control::RegisterEvent('EverySecond', 'records_dedi_es');

// manialink id 130-150
// action 150 -

global $records_dedi_displayed;
$records_dedi_displayed = true;

//Format the time of the records
function formattime($time_to_format){

	//FORMAT TIME
	$formatedtime_minutes = floor($time_to_format/60000);
	$formatedtime_seconds = floor(($time_to_format - $formatedtime_minutes*60*1000)/1000);
	$formatedtime_hseconds = substr($time_to_format, strlen($time_to_format)-3, 2);
	$formatedtime = sprintf('%02d:%02d.%02d', $formatedtime_minutes, $formatedtime_seconds, $formatedtime_hseconds);
	if($formatedtime_minutes<'0') $formatedtime = '???';
	return $formatedtime;

}

//Displays all local records
function records_display_locals($control){
	global $db, $settings;

	/***********************************
	***********LOCAL RECORDS************
	***********************************/
	$control->client->query('GetCurrentChallengeInfo');
	$records_challenge_info = $control->client->getResponse();
	$localrecords_y = '19';
	$localrecords_id = '1';
	$recs_local = '';
	$recs_local = array();
	
	$id = 0;
	while(true){
		$sql = "SELECT * FROM `records` WHERE challengeid = '".$records_challenge_info['UId']."' ORDER BY time ASC, date ASC LIMIT ".$id.", 1";
		$mysql = mysqli_query($db, $sql);
		if($localrecords_list = $mysql->fetch_object()){
			$sql = "SELECT * FROM `players` WHERE playerlogin = '".$localrecords_list->playerlogin."'";
			$mysql = mysqli_query($db, $sql);
			if($localrecs_playerdata = $mysql->fetch_object()) $localrecs_playernick = $localrecs_playerdata->nickname;
			else $localrecs_playernick = $localrecords_list->nickname;
			$localrecs_playernick = str_replace('$o', '', $localrecs_playernick);
			$localrecs_playernick = str_replace('$w', '', $localrecs_playernick);
			$localrecs_playernick = str_replace('$i', '', $localrecs_playernick);
			$recs_local[] = array('Login' => $localrecords_list->playerlogin, 'NickName' => $localrecs_playernick, 'Time' => formattime($localrecords_list->time), 'Rank' => $id + 1); 
			$id++;
		}
		else break;
	}
	
	$control->client->query('GetPlayerList', 300, 0);
	$players = $control->client->getResponse();
	$recs_local_window_code = '';
	
	$cid = 0;
	while(isset($players[$cid])){
		$cid2 = 0;
		$player_rank = -5;
		while(isset($recs_local[$cid2])){
			if($recs_local[$cid2]['Login']==$players[$cid]['Login']){
				$player_rank = $cid2;
				break;
			}
			else $cid2++;
		}
		
		if($player_rank <= 8 OR !isset($recs_local[11])){
			if($player_rank < 0) $player_rank_y = '100';
			else{
				if($player_rank==0) $player_rank_y = '19';
				else{
					$player_rank_y = $player_rank*2;
					$player_rank_y = 19 - $player_rank_y;
					if($player_rank >= 3) $player_rank_y--;
				}
			}
			$player_rank = 7;
		}
		else{
			$player_rank_y = '4';
		}
		
		$x = -62.1;
		$y = 19;
		$z = 3;
		
		$recs_local_window_code .= '<?xml version="1.0" encoding="UTF-8" ?>';
		$recs_local_window_code .= '<manialink id="131">';
		for($run = 0; true; $run++){
			$rank = $run + 1;
			if(!isset($recs_local[$run])) break;
			if($rank <= 3){
				$recs_icon = '';
				if($rank == 1){
					$recs_icon = 'First';
				}
				elseif($rank == 2){
				    $recs_icon = 'Second';
				}
				elseif($rank == 3){
				    $recs_icon = 'Third';
				}
				$recs_local_window_code .= '<quad posn="'.($x + 0.3).' '.$y.' '.$z.'" sizen="2 2" style="Icons64x64_1" halign="right" substyle="'.$recs_icon.'"/>';
				$recs_local_window_code .= '<label posn="'.($x + 0.5).' '.($y-0.2).' '.$z.'" sizen="4 2" textsize="1" scale="0.9" text="$fff'.$recs_local[$run]['Time'].'"/>';
				$recs_local_window_code .= '<label posn="'.($x+4.5).' '.$y.' '.$z.'" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars(stripslashes($recs_local[$run]['NickName'])).'"/>';
			}
			else{	
				$rank = ($player_rank - 4) + ($run - 2);
				$recs_local_window_code .= '<label posn="'.$x.' '.($y-1.2).' '.$z.'" sizen="2 2" halign="right" textsize="1" text="$o$09f'.$recs_local[$rank - 1]['Rank'].'"/>';
				$recs_local_window_code .= '<label posn="'.($x + 0.4).' '.($y-1.2).' '.$z.'" sizen="4 2" textsize="1" scale="0.9" text="$fff'.$recs_local[$rank - 1]['Time'].'"/>';
				$recs_local_window_code .= '<label posn="'.($x+4.5).' '.($y-1).' '.$z.'" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars(stripslashes($recs_local[$rank - 1]['NickName'])).'"/>';
			}
			$y = $y-2;
			if(!isset($recs_local[$rank]) || $run >= 11) break;
		}
		$recs_local_window_code .= '<quad posn="-65 '.$player_rank_y.' 2.9" sizen="18 2" style="BgsPlayerCard" substyle="BgRacePlayerName"/><quad posn="-49 '.$player_rank_y.' 3" sizen="2 2" style="Icons128x128_1" substyle="Solo"/>';
		$recs_local_window_code .= '</manialink>';
		
		$control->client->query('SendDisplayManialinkPageToLogin', $players[$cid]['Login'], $recs_local_window_code, 0, False);
		
		$recs_local_window_code = '';
		
		$cid++;
	}
}

//Displays all live records
function records_display_live($control){

	/*****************************
	******   LIVE RANKING   ******
	*****************************/
	$control->client->query('GetCurrentRanking', 15, 0);
	$current_ranking = $control->client->getResponse();
	$recs_rank_id = 0;
	$recs_explode = '||xx||';
	$recs_live = array();
	while(isset($current_ranking[$recs_rank_id]) AND trim($current_ranking[$recs_rank_id]['BestTime'])>0){
		$current_rank_data = $current_ranking[$recs_rank_id];
		$recs_rank_id_2 = $recs_rank_id+1;
		$live_ctime = formattime($current_rank_data['BestTime']);
		$recs_live[] = array('Login' => $current_rank_data['Login'], 'NickName' => $current_rank_data['NickName'], 'Time' => $live_ctime, 'Rank' => $recs_rank_id_2);
		$recs_rank_id++;
	}
	
	$count = count($recs_live);
	if($count>14){
	    $recs_live_fields = 14;
	}else{
	    $recs_live_fields = $count;
	}
	
    $x = 48.825;
	$y = 8;
	$z = 3;
	
	$recs_live_data = '';
	$recs_live_data .= '<?xml version="1.0" encoding="UTF-8" ?>';
	$recs_live_data .= '<manialink id="151">';
	
	for($run=0; $run<$recs_live_fields; $run++){
           $rank = $run+1;
			if($rank<4){
			    if($rank == 1){
			        $recs_icon = 'First';
					$recs_live_data .= '<quad posn="'.$x.' '.$y.' '.$z.'" sizen="2 2" style="Icons64x64_1" substyle="'.$recs_icon.'"/>';
					$recs_live_data .= '<label posn="'.($x+1.75).' '.($y-0.2).' '.$z.'" sizen="4 2" textsize="1" scale="0.9" text="$fff'.$recs_live[$run]['Time'].'"/>';
		            $recs_live_data .= '<label posn="'.($x+6.5).' '.$y.' '.$z.'" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars(stripslashes($recs_live[$run]['NickName'])).'"/>';
				}
				if($rank == 2){
				    $recs_icon = 'Second';
					$recs_live_data .= '<quad posn="'.$x.' '.$y.' '.$z.'" sizen="2 2" style="Icons64x64_1" substyle="'.$recs_icon.'"/>';
					$recs_live_data .= '<label posn="'.($x+1.75).' '.($y-0.2).' '.$z.'" sizen="4 2" textsize="1" scale="0.9" text="$fff'.$recs_live[$run]['Time'].'"/>';
		            $recs_live_data .= '<label posn="'.($x+6.5).' '.$y.' '.$z.'" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars(stripslashes($recs_live[$run]['NickName'])).'"/>';
				}
				if($rank == 3){
				    $recs_icon = 'Third';
					$recs_live_data .= '<quad posn="'.$x.' '.$y.' '.$z.'" sizen="2 2" style="Icons64x64_1" substyle="'.$recs_icon.'"/>';
					$recs_live_data .= '<label posn="'.($x+1.75).' '.($y-0.2).' '.$z.'" sizen="4 2" textsize="1" scale="0.9" text="$fff'.$recs_live[$run]['Time'].'"/>';
		            $recs_live_data .= '<label posn="'.($x+6.5).' '.$y.' '.$z.'" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars(stripslashes($recs_live[$run]['NickName'])).'"/>';
				}
			}else{	
		        $recs_live_data .= '<label posn="'.($x+1.5).' '.($y-1.2).' '.$z.'" sizen="2 2" halign="right" textsize="1" text="$o$09f'.$recs_live[$run]['Rank'].'"/>';
		        $recs_live_data .= '<label posn="'.($x+1.75).' '.($y-1.2).' '.$z.'" sizen="4 2" textsize="1" scale="0.9" text="$fff'.$recs_live[$run]['Time'].'"/>';
		        $recs_live_data .= '<label posn="'.($x+6.5).' '.($y-1).' '.$z.'" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars(stripslashes($recs_live[$run]['NickName'])).'"/>';
		    }
		    $y = $y-2;
	}
	
	$recs_live_data .= '</manialink>';
	
	$control->client->query('SendDisplayManialinkPage', $recs_live_data, 0, False);

}

//Displays all dedimania records
function records_display_dedi($control){
	global $_Dedimania_recs, $records_dedi_displayed;
	
	$control->client->query('GetPlayerList', 300, 0);
	$players = $control->client->getResponse();
	
	$id = 0;
	while(isset($_Dedimania_recs[$id])){
		$id++;
	}
	$_Dedimania_recs_num = $id - 1;
	
	$cid = 0;
	while(isset($players[$cid])){
		$cid2 = 0;
		$player_rank = '-';
		while(isset($_Dedimania_recs[$cid2])){
			if($_Dedimania_recs[$cid2]['Login']==$players[$cid]['Login']){
				$player_rank = $cid2;
				break;
			}
			$cid2++;
		}
		if($player_rank=='-' OR $player_rank <= 5){
			$ind1 = $_Dedimania_recs_num - 1;
			$ind2 = $_Dedimania_recs_num;
			if($ind1 < 3){
				$ind1 = 100;
				$ind2 = 100;
			}
		}
		else{
			$ind1 = $player_rank - 1;
			$ind2 = $player_rank;
		}
		
		$recs_dedi_window_code = '<?xml version="1.0" encoding="UTF-8" ?>';
		$recs_dedi_window_code .= '<manialink id="141">';
		//1.
		if(isset($_Dedimania_recs[0])){
			$recs_dedi_window_code .= '<label posn="-62.2 -12.5 3" sizen="2 2" halign="right" textsize="1" text="$o$09f'.$_Dedimania_recs[0]['Rank'].'"/>';
			$recs_dedi_window_code .= '<label posn="-61.7 -12.5 3" sizen="4 2" textsize="1" scale="0.9" text="$fff'.formattime($_Dedimania_recs[0]['Time']).'"/>';
			$recs_dedi_window_code .= '<label posn="-57.6 -12.3 3" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars($_Dedimania_recs[0]['Nick']).'"/>';
			//2.
			if(isset($_Dedimania_recs[1])){
				$recs_dedi_window_code .= '<label posn="-62.2 -14.5 3" sizen="2 2" halign="right" textsize="1" text="$o$09f'.$_Dedimania_recs[1]['Rank'].'"/>';
				$recs_dedi_window_code .= '<label posn="-61.7 -14.5 3" sizen="4 2" textsize="1" scale="0.9" text="$fff'.formattime($_Dedimania_recs[1]['Time']).'"/>';
				$recs_dedi_window_code .= '<label posn="-57.6 -14.3 3" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars($_Dedimania_recs[1]['Nick']).'"/>';
				//3.
				if(isset($_Dedimania_recs[2])){
					$recs_dedi_window_code .= '<label posn="-62.2 -16.5 3" sizen="2 2" halign="right" textsize="1" text="$o$09f'.$_Dedimania_recs[2]['Rank'].'"/>';
					$recs_dedi_window_code .= '<label posn="-61.7 -16.5 3" sizen="4 2" textsize="1" scale="0.9" text="$fff'.formattime($_Dedimania_recs[2]['Time']).'"/>';
					$recs_dedi_window_code .= '<label posn="-57.6 -16.3 3" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars($_Dedimania_recs[2]['Nick']).'"/>';
					//Individual - 1
					if(isset($_Dedimania_recs[$ind1])){
						$recs_dedi_window_code .= '<label posn="-62.1 -19 3" sizen="2 2" halign="right" textsize="1" text="$o$09f'.$_Dedimania_recs[$ind1]['Rank'].'"/>';
						$recs_dedi_window_code .= '<label posn="-61.6 -19 3" sizen="4 2" textsize="1" scale="0.9" text="$fff'.formattime($_Dedimania_recs[$ind1]['Time']).'"/>';
						$recs_dedi_window_code .= '<label posn="-57.5 -18.8 3" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars($_Dedimania_recs[$ind1]['Nick']).'"/>';
					}
					//Individual
					if(isset($_Dedimania_recs[$ind2])){
						$recs_dedi_window_code .= '<label posn="-62.1 -21 3" sizen="2 2" halign="right" textsize="1" text="$o$09f'.$_Dedimania_recs[$ind2]['Rank'].'"/>';
						$recs_dedi_window_code .= '<label posn="-61.6 -21 3" sizen="4 2" textsize="1" scale="0.9" text="$fff'.formattime($_Dedimania_recs[$ind2]['Time']).'"/>';
						$recs_dedi_window_code .= '<label posn="-57.5 -20.8 3" sizen="8.5 2" textsize="1" text="$fff'.htmlspecialchars($_Dedimania_recs[$ind2]['Nick']).'"/>';
					}
				}
			}
		}
		$recs_dedi_window_code .= '</manialink>';
		$control->client->query('SendDisplayManialinkPageToLogin', $players[$cid]['Login'], $recs_dedi_window_code, 0, false);
		$cid++;
	}
	
	
}

function records_dedi_es($control){
	global $_Dedimania_recs_updated;
	if($_Dedimania_recs_updated == true){
		records_display_dedi($control);
		$_Dedimania_recs_updated = false;
	}
}

//Displays the windows at the startup phase of foxcontrol
function records_startup($control){
	global $db;

	records_display_locals($control);
	records_display_live($control);
	records_display_dedi($control);
	
	$newline = "\n";
	require('plugins/records.config.php');

	$control->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
	<manialink id="143">
	<quad posn="-58 23 1" sizen="19 30" halign="center" style="Bgs1InRace" substyle="NavButton" />
	<quad posn="-58.2 20 2" sizen="19 8" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
	<quad posn="-58.2 22.7 2" sizen="19 3" halign="center" style="Bgs1InRace"substyle="NavButtonBlink" />
	<label posn="-56.5 22 3" scale="0.6" halign="center" text="$o'.$localrecords.'" />
       <quad posn="-58.2 21 0" sizen="19 3" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
       <quad posn="-63 22.2 4" sizen="2.3 2.3" halign="center" style="Icons64x64_1" substyle="OfficialRace" />
       <quad posn="-51.4 -5.7 5" sizen="3 3" haling="center" valign="center" image="http://www.fox-control.de/fcimages/shutright.png" action="3000"/>
	<quad posn="-65 -5.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
	<label posn="-62.5 -5.6 3" text="$o$FFF<<<" scale="0.4" style="TextButtonBig" action="157" />
	</manialink>

       <manialink id="144">
       <quad posn="-58 -8 1" sizen="19 16" halign="center" style="Bgs1InRace" substyle="NavButton" />
	<quad posn="-58.2 -11 2" sizen="19 8" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
	<quad posn="-58.2 -8.3 2" sizen="19 3" halign="center" style="Bgs1InRace"substyle="NavButtonBlink" />
	<label posn="-56.5 -9 3" scale="0.6" halign="center" text="$o'.$dedimania.'" />
       <quad posn="-58.2 -10 0" sizen="19 3" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
       <quad posn="-62.7 -8.4 4" sizen="2.5 2.5" halign="center" style="Icons64x64_1" substyle="ToolLeague1" />
       <quad posn="-51.4 -22.7 5" sizen="3 3" haling="center" valign="center" image="http://www.fox-control.de/fcimages/shutright.png" action="3001" />
	<quad posn="-65 -22.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
	<label posn="-62.5 -22.6 3" text="$o$FFF<<<" scale="0.4" style="TextButtonBig" action="158"/>
	</manialink>

       <manialink id="145">
	<quad posn="58 12 1" sizen="19 35" halign="center" style="Bgs1InRace" substyle="NavButton" />
	<quad posn="58.2 9 2" sizen="19 8" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
	<quad posn="58.2 11.7 2" sizen="19 3" halign="center" style="Bgs1InRace"substyle="NavButtonBlink" />
	<label posn="58.2 11 3" scale="0.6" halign="center" text="$o'.$liverecords.'" />
       <quad posn="58.2 10 0" sizen="19 3" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
	<quad posn="52 11.5 4" sizen="2.5 2.5" halign="center" style="Icons64x64_1" substyle="RestartRace" />
	<quad posn="48.4 -21.7 5" sizen="3 3" haling="center" valign="center" image="http://www.fox-control.de/fcimages/shutleft.png" action="3002"/>
	<quad posn="58 -21.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
	<label posn="60 -21.6 3" text="$o$FFF>>>" scale="0.4" style="TextButtonBig" action="159" />

	</manialink>', 0, False);	

	
	
	
	
	
	
}

function records_score($control, $calldata){
	$control->close_ml(131, '');
	$control->close_ml(141, '');
	$control->close_ml(151, '');
	$control->close_ml(143, '');
	$control->close_ml(144, '');
	$control->close_ml(145, '');
}

//Displays the windows when a new challenge starts
function records_newchallenge($control){
	global $db;
	
	$control->client->query('GetCurrentChallengeInfo');
	$records_challenge_info = $control->client->getResponse();
	
	$newline = "\n";
	require('plugins/records.config.php');
	$control->close_ml(131, '');
	$control->close_ml(151, '');
	$control->close_ml(143, '');
	$control->close_ml(144, '');
	$control->close_ml(145, '');
	
	records_startup($control);
	
	//Chat Message
	$sql = "SELECT * FROM `records` WHERE challengeid = '".$records_challenge_info['UId']."' ORDER BY time ASC";
	$mysql = mysqli_query($db, $sql);
	if($localrecs_nextrecs = $mysql->fetch_object()){
		$sql = "SELECT * FROM `players` WHERE playerlogin = '".$localrecs_nextrecs->playerlogin."'";
		$mysql = mysqli_query($db, $sql);
		if($localrecs_nextrecs_player = $mysql->fetch_object()) $localrecs_nextrecs_nick = $localrecs_nextrecs_player->nickname;
		else $localrecs_nextrecs_nick = $localrecs_nextrecs->nickname;
		
		//FORMAT TIME
		$time_to_format = $localrecs_nextrecs->time;
		$formatedtime_minutes = floor($time_to_format/(1000*60));
		$formatedtime_seconds = floor(($time_to_format - $formatedtime_minutes*60*1000)/1000);
		$formatedtime_hseconds = substr($time_to_format, strlen($time_to_format)-3, 2);
		$formatedtime = sprintf('%02d:%02d.%02d', $formatedtime_minutes, $formatedtime_seconds, $formatedtime_hseconds);
		
	$control->chat_message($color_newchallenge.'New challenge: $fff'.$records_challenge_info['Name'].'$z$s '.$color_newchallenge.'by $fff'.$records_challenge_info['Author']);
	//$newline.'$z$s$fff1. '.$color_newchallenge.'local $fff'.$formatedtime.$color_newchallenge.' by $fff'.$localrecs_nextrecs_nick
	}
	else $control->chat_message($color_newchallenge.'New challenge: $fff'.$records_challenge_info['Name'].'$z$s '.$color_newchallenge.'by $fff'.$records_challenge_info['Author'].$newline.$color_newchallenge);
	console('New Challenge: '.$records_challenge_info['Name']);
}

//Displays the windows to the connected player
function records_playerconnect($control, $connectedplayer){ 
	global $db;
	
	$newline = "\n";
	require('plugins/records.config.php');
	
	$control->client->query('SendDisplayManialinkPageToLogin', $connectedplayer['Login'], '<?xml version="1.0" encoding="UTF-8" ?>
	<manialink id="143">
	<quad posn="-58 23 1" sizen="19 30" halign="center" style="Bgs1InRace" substyle="NavButton" />
	<quad posn="-58.2 20 2" sizen="19 8" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
	<quad posn="-58.2 22.7 2" sizen="19 3" halign="center" style="Bgs1InRace"substyle="NavButtonBlink" />
	<label posn="-56.5 22 3" scale="0.6" halign="center" text="$o'.$localrecords.'" />
       <quad posn="-58.2 21 0" sizen="19 3" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
       <quad posn="-63 22.2 4" sizen="2.3 2.3" halign="center" style="Icons64x64_1" substyle="OfficialRace" />
       <quad posn="-51.4 -5.7 5" sizen="3 3" haling="center" valign="center" image="http://www.fox-control.de/fcimages/shutright.png" action="3000" />
	<quad posn="-65 -5.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
	<label posn="-62.5 -5.6 3" text="$o$FFF<<<" scale="0.4" style="TextButtonBig" action="157" />
	</manialink>

       <manialink id="144">
       <quad posn="-58 -8 1" sizen="19 16" halign="center" style="Bgs1InRace" substyle="NavButton" />
	<quad posn="-58.2 -11 2" sizen="19 8" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
	<quad posn="-58.2 -8.3 2" sizen="19 3" halign="center" style="Bgs1InRace"substyle="NavButtonBlink" />
	<label posn="-56.5 -9 3" scale="0.6" halign="center" text="$o'.$dedimania.'" />
       <quad posn="-58.2 -10 0" sizen="19 3" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
       <quad posn="-62.7 -8.4 4" sizen="2.5 2.5" halign="center" style="Icons64x64_1" substyle="ToolLeague1" />
       <quad posn="-51.4 -22.7 5" sizen="3 3" haling="center" valign="center" image="http://www.fox-control.de/fcimages/shutright.png" action="3001"/>
	<quad posn="-65 -22.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
	<label posn="-62.5 -22.6 3" text="$o$FFF<<<" scale="0.4" style="TextButtonBig" action="158"/>
	</manialink>

       <manialink id="145">
	<quad posn="58 12 1" sizen="19 35" halign="center" style="Bgs1InRace" substyle="NavButton" />
	<quad posn="58.2 9 2" sizen="19 8" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
	<quad posn="58.2 11.7 2" sizen="19 3" halign="center" style="Bgs1InRace"substyle="NavButtonBlink" />
	<label posn="58.2 11 3" scale="0.6" halign="center" text="$o'.$liverecords.'" />
       <quad posn="58.2 10 0" sizen="19 3" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
	<quad posn="52 11.5 4" sizen="2.5 2.5" halign="center" style="Icons64x64_1" substyle="RestartRace" />
	<quad posn="48.4 -21.7 5" sizen="3 3" haling="center" valign="center" image="http://www.fox-control.de/fcimages/shutleft.png" action="3002" />
	<quad posn="58 -21.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
	<label posn="60 -21.6 3" text="$o$FFF>>>" scale="0.4" style="TextButtonBig" action="159" />

	</manialink>', 0, False);	
	
	
	
	records_display_locals($control);
	records_display_live($control);
	records_display_dedi($control);
	
}

function recs_dediUpdateArray($control, $player, $time) {
	global $_Dedimania_recs, $_Dedimania_recs_updated;
	$newArray = array();
	$newrecset = false;
	$oldFound = false;
	for($i = 0; true; $i++) {
		if($_Dedimania_recs[$i]['Time'] <= $time && count($_Dedimania_recs[$i]) > 0) {
			console('[1]');
			if(isset($_Dedimania_recs[$i]) == true) $newArray[] = $_Dedimania_recs[$i];
		} else if($newrecset == false) {
			console('[2]');
			$control->client->query('GetDetailedPlayerInfo', $player);
			$playerData = $control->client->getResponse();
			if(count($newArray) > 0) $newArray[] = array('Login' => $player, 'Nick' => $playerData['NickName'], 'Time' => $time, 'Rank' => $newArray[count($newArray)-1]['Rank'] + 1);
			else $newArray[] = array('Login' => $player, 'Nick' => $playerData['NickName'], 'Time' => $time, 'Rank' => 1);
			$newrecset = true;
		} else if($oldFound == false){
			console('[3]');
			if(isset($_Dedimania_recs[$i-1]) == true){
				console($_Dedimania_recs[$i-1]['Login'].'   ||||||||||    '.$player);
				if($_Dedimania_recs[$i-1]['Login'] == $player) $oldFound = true;
				else $newArray[] = array('Login' => $_Dedimania_recs[$i-1]['Login'], 'Nick' => $_Dedimania_recs[$i-1]['Nick'], 'Time' => $_Dedimania_recs[$i-1]['Time'], 'Rank' => $newArray[count($newArray)-1]['Rank'] + 1);
			} else break;
		} else {
			console('[4]');
			if(isset($_Dedimania_recs[$i-1]) == true) $newArray[] = array('Login' => $_Dedimania_recs[$i-1]['Login'], 'Nick' => $_Dedimania_recs[$i-1]['Nick'], 'Time' => $_Dedimania_recs[$i-1]['Time'], 'Rank' => $newArray[count($newArray)-1]['Rank'] + 1);
			else break;
		}
	}
	$_Dedimania_recs = $newArray;
	$_Dedimania_recs_updated = true;
}

function recs_dediCheck($control, $player, $time)
{
	global $_Dedimania_recs;
	$dediplayer = false;
	$deditime = 0;
	$playerHasDedi = false;
	$playerHadDedi = false;
	$playerDediRank = 0;
	$playerRecEqualed = false;
	for($i = 0; $i < count($_Dedimania_recs); $i++) {
		if($_Dedimania_recs[$i]['Login'] == $player) {
			$playerHadDedi = true;
			if($_Dedimania_recs[$i]['Time'] > $time) {
				$playerHasDedi = true;
				$playerDediRank = $i;
			} else if ($_Dedimania_recs[$i]['Time'] == $time) {
				$playerRecEqualed = true;
				$playerDediRank = $i;
			}
			break;
		}
		if($_Dedimania_recs[$i]['Time'] > $time) {
			$playerHasDedi = true;
			$playerDediRank = $i;
			break;
		}
	}
	if(count($_Dedimania_recs) < 1 && $playerHasDedi == false && $playerHadDedi == false) {
		$playerHasDedi = true;
		$playerDediRank = 0;
	} else if(count($_Dedimania_recs) < 30 && $playerHasDedi == false && $playerHadDedi == false) {
		$playerHasDedi = true;
		$playerDediRank = count($_Dedimania_recs);
	}
	if($playerHasDedi == true && $playerRecEqualed == false) recs_dediUpdateArray($control, $player, $time);
	$return = array('hasDedi' => $playerHasDedi, 'rank' => $playerDediRank, 'hadDedi' => $playerHadDedi, 'recEqualed' => $playerRecEqualed);
	return $return;
}

///Player finish///
function records_playerfinish($control, $PlayerFinish){ //insert a new record to the database

	if($PlayerFinish[2]>0){
		global $db, $_Dedimania_recs;

		/******************************/
		/****write the local recors****/
		/******************************/
		$control->client->query('GetCurrentChallengeInfo');
		$records_challenge_info = $control->client->getResponse();
		$newline = "\n";
		require('plugins/records.config.php');
		if(trim($PlayerFinish[2])!=='0' AND trim($PlayerFinish[2])!=='' AND isset($PlayerFinish[2])){
			$control->client->query('GetDetailedPlayerInfo', $PlayerFinish[1]);
			$player_data = $control->client->getResponse();
			$sql = "SELECT * FROM `records` WHERE challengeid = '".$records_challenge_info['UId']."' AND playerlogin = '".$PlayerFinish[1]."'";
			$mysql = mysqli_query($db, $sql);
			if(!$records_time = $mysql->fetch_object()){
				$sql = "SELECT * FROM `players` WHERE playerlogin = '".trim($player_data['Login'])."'";
				$mysql = mysqli_query($db, $sql);
				if(!$mysql->fetch_object()){
					$sql = "INSERT INTO `players` (id, playerlogin, nickname, lastconnect) VALUES ('', '".$player_data['Login']."', '".mysqli_real_escape_string($db, $player_data['NickName'])."', '".time()."')";
					$mysql = mysqli_query($db, $sql);
				}
				$sql = "INSERT INTO `records` (challengeid, playerlogin, nickname, time, date) VALUES ('".$records_challenge_info['UId']."', '".$PlayerFinish[1]."', '".mysqli_real_escape_string($db, $player_data['NickName'])."', '".$PlayerFinish[2]."', '".date('Y.m.d H:i:s')."')";
				$mysql = mysqli_query($db, $sql);
				
				//FORMAT TIME
				$time_to_format = $PlayerFinish[2];
				$formatedtime_minutes = floor($time_to_format/(1000*60));
				$formatedtime_seconds = floor(($time_to_format - $formatedtime_minutes*60*1000)/1000);
				$formatedtime_hseconds = substr($time_to_format, strlen($time_to_format)-3, 2);
				$formatedtime = sprintf('%02d:%02d.%02d', $formatedtime_minutes, $formatedtime_seconds, $formatedtime_hseconds);
				
				$sql = "SELECT * FROM `records` WHERE challengeid = '".$records_challenge_info['UId']."' ORDER BY time ASC";
				$mysql = mysqli_query($db, $sql);
				$localrecs_new_rank = '1';
				while($localrecs_new_rank2 = $mysql->fetch_object()){
					if($localrecs_new_rank2->playerlogin==$PlayerFinish[1]) break;
					$localrecs_new_rank++;
				}
				
				$control->chat_message($player_data['NickName'].'$z$s '.$color_newlocal.'claimed the $fff'.$localrecs_new_rank.'. '.$color_newlocal.'local record! Time: $fff'.$formatedtime);
			}
			elseif($records_time->time == $PlayerFinish[2]){
				
				$sql = "SELECT * FROM `records` WHERE challengeid = '".$records_challenge_info['UId']."' ORDER BY time ASC";
				$mysql = mysqli_query($db, $sql);
				$localrecs_new_rank = '1';
				while($localrecs_new_rank2 = $mysql->fetch_object()){
					if($localrecs_new_rank2->playerlogin==$PlayerFinish[1]) break;
					$localrecs_new_rank++;
				}
				
				$formatedtime = formattime($records_time->time);
				
				$control->chat_message($player_data['NickName'].'$z$s '.$color_newlocal.'equaled his/her $fff'.$localrecs_new_rank.' '.$color_newlocal.'local record! Time: $fff'.$formatedtime);
			}
			elseif($records_time->time > $PlayerFinish[2]){
				$sql = "UPDATE `records` SET time = '".$PlayerFinish[2]."' WHERE challengeid = '".$records_challenge_info['UId']."' AND playerlogin = '".$PlayerFinish[1]."'";
				$mysql = mysqli_query($db, $sql);
				$sql = "UPDATE `records` SET date = '".date('Y.m.d H:i:s')."' WHERE challengeid = '".$records_challenge_info['UId']."' AND playerlogin = '".$PlayerFinish[1]."'";
				$mysql = mysqli_query($db, $sql);
				
				$formatedtime = formattime($PlayerFinish[2]);
				
				$sql = "SELECT * FROM `records` WHERE challengeid = '".$records_challenge_info['UId']."' ORDER BY time ASC";
				$mysql = mysqli_query($db, $sql);
				$localrecs_new_rank = '1';
				while($localrecs_new_rank2 = $mysql->fetch_object()){
				if($localrecs_new_rank2->playerlogin==$PlayerFinish[1]) break;
				$localrecs_new_rank++;
				}
				
				$recs_new_time_2 = str_replace('00:', '', formattime($records_time->time - $PlayerFinish[2]));
				
				$control->chat_message($player_data['NickName'].'$z$s '.$color_newlocal.'claimed the $fff'.$localrecs_new_rank.'. '.$color_newlocal.'local record! Time: $fff'.$formatedtime.'$z $s$n$fff(- '.$recs_new_time_2.')');
				$control->console($player_data['Login'].' claimed the '.$localrecs_new_rank.' local record! Time: '.$formatedtime);
			}
			$records_nextrefresh = true;
		}
		
		/**********************************/
		/****write the dedimania recors****/
		/**********************************/
		
		$color_newdedi = $color_newlocal;
		
		$login = $PlayerFinish[1];
		$time = $PlayerFinish[2];
		$control->client->query('GetDetailedPlayerInfo', $login);
		$player_data = $control->client->getResponse();
		
		$newdedi = recs_dediCheck($control, $login, $time);
		if($newdedi['hasDedi'] == true) {
			$dediTime = $control->formattime($time);
			if($newdedi['recEqualed'] == true) $control->chat_message($player_data['NickName'].'$z$s '.$color_newdedi.'equaled his/her $fff'.($newdedi['rank'] + 1).'. '.$color_newdedi.'dedimania record! Time: $fff'.$dediTime);
			else $control->chat_message($player_data['NickName'].'$z$s '.$color_newdedi.'claimed the $fff'.($newdedi['rank'] + 1).'. '.$color_newdedi.'dedimania record! Time: $fff'.$dediTime);
		}
		
		records_startup($control);
	}
}

function records_mlanswer($control, $ManialinkPageAnswer){
	require('plugins/records.config.php');
	global $db, $_Dedimania_recs;
	if($ManialinkPageAnswer[2]=='160'){
		
		$recs_local_more_code = '<?xml version="1.0" encoding="UTF-8" ?>';
		$recs_local_more_code .= '<manialink id="143">';
		$recs_local_more_code .= '<quad posn="-58 23 1" sizen="19 30" halign="center" style="Bgs1InRace" substyle="NavButton" />';
		$recs_local_more_code .= '<quad posn="-58.2 20 2" sizen="19 8" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" />';
		$recs_local_more_code .= '<quad posn="-58.2 22.7 2" sizen="19 3" halign="center" style="Bgs1InRace"substyle="NavButtonBlink" />';
		$recs_local_more_code .= '<label posn="-56.5 22 3" scale="0.6" halign="center" text="$o'.$localrecords.'" />';
		$recs_local_more_code .= '<quad posn="-58.2 21 0" sizen="19 3" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />';
		$recs_local_more_code .= '<quad posn="-63 22.2 4" sizen="2.3 2.3" halign="center" style="Icons64x64_1" substyle="OfficialRace" />';
		$recs_local_more_code .= '<quad posn="-51.4 -5.7 5" sizen="3 3" haling="center" valign="center" image="http://www.fox-control.de/fcimages/shutright.png" action="3000" />';
		$recs_local_more_code .= '<quad posn="-65 -5.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />';
		$recs_local_more_code .= '<label posn="-62.5 -5.6 3" text="$o$FFF<<<" scale="0.4" style="TextButtonBig" action="157" />';
		$recs_local_more_code .= '</manialink>';
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], $recs_local_more_code, 0, False);
		records_display_locals($control);
	}
	 
	elseif($ManialinkPageAnswer[2]=='161'){
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
		</manialink>
		<manialink id="144">
		<quad posn="-58 -8 1" sizen="19 16" halign="center" style="Bgs1InRace" substyle="NavButton" />
		<quad posn="-58.2 -11 2" sizen="19 8" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		<quad posn="-58.2 -8.3 2" sizen="19 3" halign="center" style="Bgs1InRace"substyle="NavButtonBlink" />
		<label posn="-56.5 -9 3" scale="0.6" halign="center" text="$o'.$dedimania.'" />
		<quad posn="-58.2 -10 0" sizen="19 3" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		<quad posn="-62.7 -8.4 4" sizen="2.5 2.5" halign="center" style="Icons64x64_1" substyle="ToolLeague1" />
		<quad posn="-51.4 -22.7 5" sizen="3 3" haling="center" valign="center" image="http://www.fox-control.de/fcimages/shutright.png" action="3001" />
		<quad posn="-65 -22.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
		<label posn="-62.5 -22.6 3" text="$o$FFF<<<" scale="0.4" style="TextButtonBig" action="158"/>
		</manialink>', 0, False);
		records_display_dedi($control);
	}

	elseif($ManialinkPageAnswer[2]=='162'){
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
		
		</manialink>
		<manialink id="145">
		<quad posn="58 12 1" sizen="19 35" halign="center" style="Bgs1InRace" substyle="NavButton" />
		<quad posn="58.2 9 2" sizen="19 8" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		<quad posn="58.2 11.7 2" sizen="19 3" halign="center" style="Bgs1InRace"substyle="NavButtonBlink" />
		<label posn="58.2 11 3" scale="0.6" halign="center" text="$o'.$liverecords.'" />
		<quad posn="58.2 10 0" sizen="19 3" halign="center" valign="center" style="Bgs1InRace" substyle="NavButtonBlink" />
		<quad posn="52 11.5 4" sizen="2.5 2.5" halign="center" style="Icons64x64_1" substyle="RestartRace" />
		<quad posn="48.4 -21.7 5" sizen="3 3" haling="center" valign="center" image="http://www.fox-control.de/fcimages/shutleft.png" action="3002" />
		<quad posn="58 -21.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
		<label posn="60 -21.6 3" text="$o$FFF>>>" scale="0.4" style="TextButtonBig" action="159" />
		</manialink>', 0, False);
		records_display_live($control);
	}
		/***********************************
		********Tabellen ausblenden*********
		***********************************/
	elseif($ManialinkPageAnswer[2]=='157'){
		$control->close_ml(131, $ManialinkPageAnswer[1]);
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="143">
		<quad posn="-65 -5.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
		<label posn="-62.5 -5.6 3" text="$o$FFF>>>" scale="0.4" style="TextButtonBig" action="160" />
		</manialink>', 0, False);
	}
	elseif($ManialinkPageAnswer[2]=='158'){
		$control->close_ml(141, $ManialinkPageAnswer[1]);
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="144">
		<quad posn="-65 -22.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
		<label posn="-62.5 -22.6 3" text="$o$FFF>>>" scale="0.4" style="TextButtonBig" action="161"/>
		</manialink>', 0, False);
	}
	elseif($ManialinkPageAnswer[2]=='159'){
		$control->close_ml(151, $ManialinkPageAnswer[1]);
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="145">
		<quad posn="58 -21.4 2" sizen="7 1.5" style="Bgs1InRace" substyle="NavButtonBlink" />
		<label posn="60 -21.6 3" text="$o$FFF<<<" scale="0.4" style="TextButtonBig" action="162" />
		</manialink>', 0, False);
	}
	
	//Advanced Local records
	elseif($ManialinkPageAnswer[2]=='3000'){
		$control->client->query('GetCurrentChallengeInfo');
		$records_challenge_info = $control->client->getResponse();
		
		$recs_local_more_code = '<?xml version="1.0" encoding="UTF-8" ?>';
		$recs_local_more_code .= '<manialink id="999">';
		$recs_local_more_code .= '<quad posn="0 12 1" sizen="70 57" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>';
		$recs_local_more_code .= '<quad posn="0 12 0" sizen="70 57" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>';
		$recs_local_more_code .= '<quad posn="0 39.5 3" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>';
		$recs_local_more_code .= '<label posn="-34 39.25 4" textsize="2" text="$o$FFFLocal Records:"/>';
		$recs_local_more_code .= '<quad posn="31.75 39.5 4" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="998"/>';
		
		$id = 0;
		while(true){
			$sql = "SELECT * FROM `records` WHERE challengeid = '".$records_challenge_info['UId']."' ORDER BY time ASC, date ASC LIMIT ".$id.", 1";
			$mysql = mysqli_query($db, $sql);
			if($localrecords_list = $mysql->fetch_object()){
				$y = 36.5 - $id * 1.75;
				$sql = "SELECT * FROM `players` WHERE playerlogin = '".$localrecords_list->playerlogin."'";
				$mysql = mysqli_query($db, $sql);
				if($localrecs_playerdata = $mysql->fetch_object()) $localrecs_playernick = $localrecs_playerdata->nickname;
				else $localrecs_playernick = $localrecords_list->nickname;
				$recs_local_date = explode(' ', $localrecords_list->date);
				$recs_local_date = str_replace('-', '.', $recs_local_date[0]);
				$recs_local_more_rank = $id + 1;
				$recs_local_more_code .= '<label posn="-34 '.$y.' 5" textsize="1" text="$o$09f'.$recs_local_more_rank.'"/>';
				$recs_local_more_code .= '<label posn="-31 '.$y.' 5" textsize="1" sizen="20 2" text="'.formattime($localrecords_list->time).'"/>';
				$recs_local_more_code .= '<label posn="-24 '.$y.' 5" textsize="1" sizen="20 2" text="'.htmlspecialchars($localrecs_playernick).'"/>';
				$recs_local_more_code .= '<label posn="-2 '.$y.' 5" textsize="1" sizen="10 2" text="'.$localrecords_list->playerlogin.'"/>';
				$recs_local_more_code .= '<label posn="13 '.$y.' 5" textsize="1" sizen="15 2" text="'.$recs_local_date.'"/>';
				$id++;
			}
			else break;
			if($id >= 25) break;
		}
		
		$recs_local_more_code .= '</manialink>';
		
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], $recs_local_more_code, 0, false);
	}
	//Advanced Dedimania records
	elseif($ManialinkPageAnswer[2]=='3001'){
		$recs_dedi_more_code = '<?xml version="1.0" encoding="UTF-8" ?>';
		$recs_dedi_more_code .= '<manialink id="999">';
		$recs_dedi_more_code .= '<quad posn="0 12 1" sizen="70 57" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>';
		$recs_dedi_more_code .= '<quad posn="0 12 0" sizen="70 57" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>';
		$recs_dedi_more_code .= '<quad posn="0 39.5 3" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>';
		$recs_dedi_more_code .= '<label posn="-34 39.25 4" textsize="2" text="$o$FFFDedimania Records:"/>';
		$recs_dedi_more_code .= '<quad posn="31.75 39.5 4" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="998"/>';
		$id = 0;
		while(isset($_Dedimania_recs[$id])){
			$y = 36.5 - $id * 1.75;
			$recs_dedi_more_code .= '<label posn="-34 '.$y.' 5" textsize="1" text="$o$09f'.$_Dedimania_recs[$id]['Rank'].'"/>';
			$recs_dedi_more_code .= '<label posn="-31 '.$y.' 5" textsize="1" text="'.formattime($_Dedimania_recs[$id]['Time']).'"/>';
			$recs_dedi_more_code .= '<label posn="-24 '.$y.' 5" textsize="1" sizen="20 2" text="'.htmlspecialchars($_Dedimania_recs[$id]['Nick']).'"/>';
			$recs_dedi_more_code .= '<label posn="-2 '.$y.' 5" textsize="1" sizen="10 2" text="'.$_Dedimania_recs[$id]['Login'].'"/>';
			$id++;
		}
		$recs_dedi_more_code .= '</manialink>';
		
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], $recs_dedi_more_code, 0, false);
	}
	elseif($ManialinkPageAnswer[2]=='3002'){
		$control->client->query('SendDisplayManialinkPageToLogin', $ManialinkPageAnswer[1], '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="999">
		<quad posn="0 12 1" sizen="70 57" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<quad posn="0 12 0" sizen="70 57" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
		<quad posn="0 39.5 3" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
		<label posn="-34 39.25 4" textsize="2" text="$o$FFFLive Records:"/>
		<quad posn="31.75 39.5 4" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="998"/>
		</manialink>', 0, false);
	}
}
?>

