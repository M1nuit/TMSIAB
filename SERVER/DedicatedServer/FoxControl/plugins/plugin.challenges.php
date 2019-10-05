<?php

//* plugin.challenges.php - Challengelist
//* Version:   0.9.0
//* Coded by:  cyrilw
//* Copyright: FoxRace, http://www.fox-control.de

//MANIALINKID + ACTION: 998-2000

control::RegisterEvent('ManialinkPageAnswer', 'challenges_display');
control::RegisterEvent('Chat', 'challenges_chat');
control::RegisterEvent('StartUp', 'write_challenges');
control::RegisterEvent('BeginChallenge', 'challenges_begin');

global $challenges, $chall_users;
$chall_users = array();

function write_challenges($control){
	global $challenges;
	$challenges = array();
	$control->client->query('GetChallengeList', 1000, 0);
	$challenge_list = $control->client->getResponse();
	$curr_id = 0;
	while(isset($challenge_list[$curr_id])){
		$challenges[] = array('Name' => $challenge_list[$curr_id]['Name'], 'FileName' => $challenge_list[$curr_id]['FileName'], 'Author' => $challenge_list[$curr_id]['Author'], 'Environnement' => $challenge_list[$curr_id]['Environnement']);
		$curr_id++;
	}
	
}

function challenges_begin($control, $var){
	write_challenges($control);
}

function chall_pages($control, $data)
{
	global $chall_users;
	if($data[2] == 1) $chall_users[$data[1]] = 0; // <<
	elseif($data[2] == 2 && $chall_users[$data[1]] > 0) $chall_users[$data[1]]--; // <
	elseif($data[2] == 6) $chall_users[$data[1]]++; // >
	elseif($data[2] == 7)
	{
		global $challenges;
		$chall_users[$data[1]] = floor(count($challenges) / 25);
	}
	$send_var = array(0 => '', 1 => $data[1], 2 => 999);
	challenges_display($control, $send_var);
}

function challenges_display($control, $ManialinkPageAnswer){
	global $challenges, $jukebox;
	if($ManialinkPageAnswer[2]>=999 AND $ManialinkPageAnswer[2]<=2050){
	
		
		
		global $chall_users;
		$challenge_page_id = $chall_users[$ManialinkPageAnswer[1]];
		$challenge_page_id = $challenge_page_id*25;
		$challenge_page_id_number_2 = $challenge_page_id-25;
		if(isset($PlayerChat[1])) $control->close_ml(102, $PlayerChat[1]);
		
		if($ManialinkPageAnswer[2]>=1000 AND $ManialinkPageAnswer[2]<=2050){
			$jukedchallengex = $ManialinkPageAnswer[2]-1000;			
			$jukedchallenge = $challenges[$jukedchallengex];
			
			jukebox_jukeChallenge($control, $jukedchallenge['FileName'], $ManialinkPageAnswer[1], true);
		}
		else{
			$curr_challid = $chall_users[$ManialinkPageAnswer[1]] * 25;
			if(isset($challenges[$curr_challid - 25])) $chall_prev_page = true;
			else $chall_prev_page = false;
			if(isset($challenges[$curr_challid + 25])) $chall_next_page = true;
			else $chall_next_page = false;
			
			//Include window class
			global $window;
			$window->init();
			$window->title('$09fChallenges');
			$window->displayAsTable(true);
			$window->size(70, '');
			$window->posY('40');
			$window->target('chall_pages');
			if($chall_prev_page == true){
				$window->addButton('<<<', '7', false);
				$window->addButton('<', '7', false);
			}
			else
			{
				$window->addButton('', '7', false);
				$window->addButton('', '7', false);
			}
			$window->addButton('', '15.5', false);
			$window->addButton('Close', '10', true);
			$window->addButton('', '15.5', false);
			if($chall_next_page == true){
				$window->addButton('>', '7', false);
				$window->addButton('>>>', '7', false);
			}
			else
			{
				$window->addButton('', '7', false);
				$window->addButton('', '7', false);
			}
			
			$chall_code = '';
			for($i = 0; isset($challenges[$curr_challid]) && $i <= 24; $i++)
			{
				$chall_ml_id = 1000 + $curr_challid;
				if($control->pluginIsActive('plugin.jukebox.php') == true) $window->content('<td width="3">'.($curr_challid + 1).'</td><td width="30" id="'.$chall_ml_id.'">'.htmlspecialchars($challenges[$curr_challid]['Name']).'</td><td width="3"/><td width="15">'.htmlspecialchars($challenges[$curr_challid]['Author']).'</td><td width="3"/><td width="15">'.$challenges[$curr_challid]['Environnement'].'</td>');
				else $window->content('<td width="3">'.($curr_challid + 1).'</td><td width="30">'.htmlspecialchars($challenges[$curr_challid]['Name']).'</td><td width="3"/><td width="15">'.htmlspecialchars($challenges[$curr_challid]['Author']).'</td><td width="3"/><td width="15">'.$challenges[$curr_challid]['Environnement'].'</td>');
				$curr_challid++;
			}
			
			$window->show($ManialinkPageAnswer[1]);
		}
	}
	elseif($ManialinkPageAnswer[2]==998){
		$control->close_ml(1000, $ManialinkPageAnswer[1]);
		$control->close_ml(999, $ManialinkPageAnswer[1]);
	}

}

function challenges_chat($control, $PlayerChat){
	if(trim($PlayerChat[2])=='/list' OR trim($PlayerChat[2]=='/challenges')){
		$send_var = array(0 => '', 1 => $PlayerChat[1], 2 => 999);
		global $chall_users;
		$chall_users[$PlayerChat[1]] = 0;
		challenges_display($control, $send_var);
	}
}









?>