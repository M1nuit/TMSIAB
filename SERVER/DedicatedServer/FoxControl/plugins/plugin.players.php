<?php
//* plugin.players.php - Playerlist
//* Version:   0.9.0
//* Coded by:  cyrilw
//* Copyright: FoxRace, http://www.fox-control.de

//This plugin require chat.admin.php !!
if(!isset($p_starting_id) OR $p_starting_id=='') $p_starting_id = 0;
if(!isset($p_admin_rights) OR $p_admin_rights=='') $p_admin_rights = false;
		$this->client->query('GetPlayerList', 300, 0);
		$player_list = $this->client->getResponse();

		
		if($p_admin_rights==false){
			$curr_pid = $p_starting_id;
			$curr_y = '20';
			$playerarray = array();
			while(isset($player_list[$curr_pid])){
				
				$curr_pdata = $player_list[$curr_pid];
				$curr_nick = $curr_pdata['NickName'];
				$curr_login = $curr_pdata['Login'];
				$curr_ladder = $curr_pdata['LadderRanking'];
				
				$url = "http://fox-control.de/~skpfox/scripts/get_data.php?login=".trim($curr_login).""; 
				$file = fopen($url, "rb");
				$pl_content = stream_get_contents($file);
				$file = fclose($file);
				$pl_content = explode('{expl}', $pl_content);
				$playerarray[] = '<label posn="-34 '.$curr_y.' 4" text="'.htmlspecialchars($curr_nick).'" sizen="15 2" textsize="2"/>
				<label posn="-13 '.$curr_y.' 4" text="'.htmlspecialchars($curr_login).'" sizen="10 2" textsize="2"/>
				<label posn="1 '.$curr_y.' 4" text="'.$curr_ladder.'" sizen="10 2" textsize="2"/>
				<label posn="12 '.$curr_y.' 4" text="SKP:" textsize="2"/>
				<label posn="16 '.$curr_y.' 4" text="'.$pl_content[0].'" textsize="2" sizen="15 2"/>
				<label posn="23 '.$curr_y.' 4" text="LVL:" textsize="2"/>
				<label posn="26.5 '.$curr_y.' 4" text="'.$pl_content[1].'" textsize="2" sizen="15 2"/>
				<quad posn="-3.25 '.$curr_y.' 4" sizen="3 2.5" style="Icons128x128_1" substyle="LadderPoints"/>';
				if($curr_pid==13) break;
				$curr_pid++;
				$curr_y = $curr_y-2.5;
			}
			
			$count = count($playerarray);
			if($count>13){
			    $playerarraycount = 13;
			}else{
			    $playerarraycount = $count;
			}
			
			$playerarraydata = '';
			$playerarraydata .= '<?xml version="1.0" encoding="UTF-8" ?>
			                     <manialink id="10">
			                         <quad posn="0 5 1" sizen="70 41" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
			                         <quad posn="0 5 0" sizen="70 41" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
			                         <quad posn="0 24.5 3" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
			                         <label posn="-34 24.25 4" textsize="2" text="$o$09fCurrent Players:"/>
			                         <quad posn="31.75 24.5 4" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="11"/>';
			
			for($run=0; $run<$playerarraycount; $run++){
			    $playerarraydata .= $playerarray[$run];
			}
			
			if($count>13){
			    $nextarrow = '<quad posn="31.75 -12 20" sizen="3 3" style="Icons64x64_1" substyle="ArrowNext" action="5252"/>';
			}else{
			    $nextarrow = '';
			}
			
			$playerarraydata .= $nextarrow;
			$playerarraydata .= '</manialink>';
			
			$this->client->query('SendDisplayManialinkPageToLogin', $p_show_to_login, $playerarraydata, 0, false);
		}
		elseif($p_admin_rights==true){
			
			$curr_pid = $p_starting_id;
			$curr_y = '20';
			$playerlist_mlid = '11';
			$playerarray = array();
			$this->client->query('GetIgnoreList', 1000, 0);
			$ignore_list = $this->client->getResponse();
			while(isset($player_list[$curr_pid])){
				
				$curr_ml_id = $playerlist_mlid+$curr_pid;
				$curr_pdata = $player_list[$curr_pid];
				$curr_nick = $curr_pdata['NickName'];
				$curr_login = $curr_pdata['Login'];
				$curr_kick_id = 250+$curr_pid;
				$curr_ignore_id = 0;
				$curr_warn_id = 49749+$curr_pid;
				$curr_ban_id = 50000+$curr_pid;
				$curr_y_2 = $curr_y-0.25;
				$player_in_ignore_list = false;
				
				while(isset($ignore_list[$curr_ignore_id])){
					if($ignore_list[$curr_ignore_id]['Login'] == trim($curr_login)){
						$player_in_ignore_list = true;
						break;
					}
					$curr_ignore_id++;
				}
				if($player_in_ignore_list==true){
					$curr_ignore_text = 'UnIgnore';
				}
				else{
					$curr_ignore_text = 'Ignore';
				}
				$curr_ignore_id = 500+$curr_pid;
				
				$playerarray[] = '<label posn="-34 '.$curr_y.' 4" text="'.htmlspecialchars($curr_nick).'" sizen="15 2" textsize="2"/>
				<label posn="-13 '.$curr_y.' 4" text="'.htmlspecialchars($curr_login).'" sizen="10 2" textsize="2"/>
				<quad posn="0 '.$curr_y.' 4" sizen="7 2.5" style="Bgs1" substyle="NavButtonBlink" action="'.$curr_kick_id.'"/>
				<label posn="3.5 '.$curr_y_2.' 4" sizen="7 2.5" halign="center" style="TextPlayerCardName" textsize="2" text="$fff$oKick" action="'.$curr_kick_id.'"/>
				<quad posn="8 '.$curr_y.' 4" sizen="7 2.5" style="Bgs1" substyle="NavButtonBlink" action="'.$curr_ignore_id.'"/>
				<label posn="11.5 '.$curr_y_2.' 4" sizen="7 2.5" halign="center" style="TextPlayerCardName" textsize="2" text="$fff$o'.$curr_ignore_text.'" action="'.$curr_ignore_id.'"/>
				<quad posn="16 '.$curr_y.' 4" sizen="7 2.5" style="Bgs1" substyle="NavButtonBlink" action="'.$curr_warn_id.'"/>
				<label posn="19.5 '.$curr_y_2.' 4" sizen="7 2.5" halign="center" style="TextPlayerCardName" textsize="2" text="$fff$oWarn" action="'.$curr_warn_id.'"/>
				<quad posn="24 '.$curr_y.' 4" sizen="7 2.5" style="Bgs1" substyle="NavButtonBlink" action="'.$curr_ban_id.'"/>
				<label posn="27.5 '.$curr_y_2.' 4" sizen="7 2.5" halign="center" style="TextPlayerCardName" textsize="2" text="$fff$oBan" action="'.$curr_ban_id.'"/>';
				if($curr_pid==13) break;
				$curr_pid++;
				$curr_y = $curr_y-2.5;
			}
			
			$count = count($playerarray);
			if($count>13){
			    $playerarraycount = 13;
			}else{
			    $playerarraycount = $count;
			}
			
			$playerarraydata = '';
			$playerarraydata .= '<?xml version="1.0" encoding="UTF-8" ?>
			                     <manialink id="10">
			                         <quad posn="0 5 1" sizen="70 41" valign="center" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
			                         <quad posn="0 5 0" sizen="70 41" valign="center" halign="center" style="Bgs1InRace" substyle="BgList"/>
			                         <quad posn="0 24.5 3" sizen="69 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
			                         <label posn="-34 24.25 4" textsize="2" text="$o$09fCurrent Players:"/>
			                         <quad posn="31.75 24.5 4" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="11"/>';
			
			for($run=0; $run<$playerarraycount; $run++){
			    $playerarraydata .= $playerarray[$run];
			}
			
			if($count>13){
			    $nextarrow = '<quad posn="31.75 -12 20" sizen="3 3" style="Icons64x64_1" substyle="ArrowNext" action="5252"/>';
			}else{
			    $nextarrow = '';
			}
			
			$playerarraydata .= $nextarrow;
			$playerarraydata .= '</manialink>';
			
			$this->client->query('SendDisplayManialinkPageToLogin', $p_show_to_login, $playerarraydata, 0, false);
		}
		
?>