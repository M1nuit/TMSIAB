<?php
//* plugin.simple_buttons.php - Adds buttons very easy
//* Version:   0.9.0
//* Coded by:  cyrilw
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('StartUp', 'simple_buttons');
control::RegisterEvent('BeginChallenge', 'simple_buttons_bc');
control::RegisterEvent('EndChallenge', 'simple_buttons_ec');
control::RegisterEvent('PlayerConnect', 'simple_buttons');

function simple_buttons($control){
	if(file_exists('plugin.simple_buttons.xml')){
		$xml = @simplexml_load_file('plugin.simple_buttons.xml');
		
		$simplebuttons = array();
		$sb_curr_id = 0;
		$expl = '||xx||';
		while(isset($xml->size[$sb_curr_id])){
			$sb_size = $xml->size[$sb_curr_id];
			$sb_pos = $xml->pos[$sb_curr_id];
			$sb_image = $xml->image[$sb_curr_id];
			$sb_imagefocus = $xml->imagefocus[$sb_curr_id];
			$sb_linkstyle = $xml->linkstyle[$sb_curr_id];
			$sb_link = $xml->link[$sb_curr_id];
			if($sb_linkstyle=='') $sb_linkstyle = 'manialink';
			if($sb_imagefocus!=='') $sb_imagefocus = $sb_imagefocus;
			else $sb_imagefocus = '';
			$simplebuttons[] = '<quad posn="'.$sb_pos.' 10" sizen="'.$sb_size.'" image="'.$sb_image.'" imagefocus="'.$sb_imagefocus.'" '.$sb_linkstyle.'="'.$sb_link.'"/>';
			$sb_curr_id++;
		}
		$sb_curr_id = 0;
		$sb_buttons = '';
		while(isset($simplebuttons[$sb_curr_id])){
			$sb_buttons = $sb_buttons.$simplebuttons[$sb_curr_id];
			$sb_curr_id++;
		}
		$control->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="3000">
		'.$sb_buttons.'
		</manialink>', 0, false);
	
	}
	else die('Can\'t open \'plugin.simple_buttons.xml\'!!');
}

function simple_buttons_bc($control, $challdata){
	simple_buttons($control);
}

function simple_buttons_ec($control, $challdata){
	$control->close_ml(3000, '');
}



?>