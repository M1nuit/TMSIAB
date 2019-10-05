<?php

// FoxControl
// Copyright 2010 - 2011 by FoxRace, http://www.fox-control.de

//* foxcontrol.window.php - Window class
//* Version:   0.8.3
//* Coded by:  cyrilw
//* Copyright: FoxRace, http://www.fox-control.de
global $fc_window;
$fc_window = array();
$fc_window['PlayerButtons'] = array();
$fc_window['ButtonToPlayer'] = array();
$fc_window['Uses'] = -1;
$fc_window['Functions'] = array();

class window
{
	public function init() //init function of the window. call this init function when you create a new window
	{
		global $fc_window;
		$fc_window['Content'] = array();
		$fc_window['TextAlign'] = 'left';
		$fc_window['Close'] = true;
		$fc_window['Title'] = 'Fox Control';
		$fc_window['SizeX'] = '40';
		$fc_window['SizeY'] = '20';
		$fc_window['PosY'] = '30';
		$fc_window['Buttons'] = array();
		$fc_window['ButtonsAutoWidth'] = false;
		$fc_window['UseButtons'] = false;
		$fc_window['Target'] = '';
		$fc_window['UseCode'] = false;
		$fc_window['Code'] = '';
		$fc_window['DynamicHeight'] = true;
		$fc_window['Table'] = false;
		$fc_window['TableLink'] = array('Style' => 'BgsPlayerCard', 'SubStyle' => 'BgCard');
		$fc_window['Uses'] = $fc_window['Uses'] + 1;
		$fc_window['ButtonToPlayer'][$fc_window['Uses']] = array();
	}
	public function title($title) //set the title of the window
	{
		global $fc_window;
		$fc_window['Title'] = $title;
	}
	public function close($close) //set the close box (true or false)
	{
		global $fc_window;
		$fc_window['Close'] = $close;
	}
	public function content($content) //write the content
	{
		global $fc_window;
		$fc_window['Content'][] = str_replace("\n", "", $content);
	}
	public function code($code) //Use own code, but the foxcontrol window design. the 0 pos is in the left top corner of the window
	{
		global $fc_window;
		$fc_window['Code'] = $code;
		$fc_window['UseCode'] = true;
	}
	public function textAlign($align) //Set the text align
	{
		global $fc_window;
		$fc_window['TextAlign'] = $align;
	}
	public function posY($y) //Set the Y position
	{
		global $fc_window;
		$fc_window['PosY'] = $y;
	}
	public function size($x, $y) //set the size
	{
		global $fc_window;
		if(trim($x) !== '') $fc_window['SizeX'] = $x;
		if(trim($y) !== '') $fc_window['SizeY'] = $y;
		if(trim($y) !== '') $fc_window['DynamicHeight'] = false;
	}
	public function addButton($name, $size, $close) //add buttons
	{
		global $fc_window;
		$fc_window['Buttons'][] = array('text' => $name, 'size' => $size, 'close' => $close);
		$fc_window['UseButtons'] = true;
	}
	public function target($target) //Set the button target (when clicking)
	{
		global $fc_window;
		$fc_window['Target'] = $target;
	}
	public function buttonsAutoWidth($boolean) //Set the auto width of the buttons
	{
		global $fc_window;
		$fc_window['ButtonsAutoWidth'] = $boolean;
	}
	public function displayAsTable($boolean) //Display the content as table
	{
		global $fc_window;
		$fc_window['Table'] = $boolean;
	}
	public function tableLinkStyle($style, $substyle) //Set the link style of the table
	{
		global $fc_window;
		if(trim($style) !== '') $fc_window['TableLink']['Style'] = $style;
		if(trim($substyle) !== '') $fc_window['TableLink']['SubStyle'] = $substyle;
	}
	public function show($player) //display window
	{
		global $control, $fc_window;
		//Set target to players
		if(trim($fc_window['Target']) !== '') $fc_window['Functions'][$player] = $fc_window['Target'];
		
		//Calculate dynamic height if selected
		if($fc_window['DynamicHeight'] == true)
		{
			$fc_window['SizeY'] = $this->calculateHeight();
		}
		//Calculate window position, size, etc
		$ml_code_y = $fc_window['PosY'];
		$ml_code_x = $fc_window['SizeX'];
		$title_x = 0 - $ml_code_x / 2 + 1.5;
		$title_y = $ml_code_y - 0.75;
		$title_bg_x = -1.25;
		$title_bg_width = $ml_code_x - 3;
		if($fc_window['Close'] == false){
			$title_bg_width = $ml_code_x - 0.75;
			$title_bg_x = 0;
		}
		$close_x = $ml_code_x / 2 - 3;
		$content_x = 0 - $ml_code_x / 2 + 2;
		$content_y = $ml_code_y - 3.5;
		$content_width = $ml_code_x - 4;
		
		//Create window header code
		$ml_code = '';
		
		//Create content code
		if($fc_window['UseCode'] == true) //Code
		{
			$ml_code .= '<frame posn="'.(-$ml_code_x/2).' '.($ml_code_y-2).' 2">'.$fc_window['Code'].'</frame>';
		}
		elseif($fc_window['Table'] == true) //Table
		{
			$ml_code .= $this->createTable();
		}
		else
		{
			$i = 0;
			while(isset($fc_window['Content'][$i]))
			{
				$content_mly = $content_y - $i * 2.5;
				if($fc_window['TextAlign'] == 'center' || $fc_window['TextAlign'] == 'Center') $ml_code .= '<label posn="'.($content_x+$content_width/2).' '.$content_mly.' 5" sizen="'.$content_width.' 2" halign="center" textsize="2" text="'.$fc_window['Content'][$i].'" autonewline="0"/>';
				else $ml_code .= '<label posn="'.$content_x.' '.$content_mly.' 5" sizen="'.$content_width.' 2" textsize="2" text="'.$fc_window['Content'][$i].'" autonewline="0"/>';
				$i++;
			}
		}
		
		$ml_display_code = '<quad posn="0 '.$ml_code_y.' 1" sizen="'.$fc_window['SizeX'].' '.$fc_window['SizeY'].'" halign="center" style="Bgs1InRace" substyle="NavButtonBlink"/>
		<quad posn="0 '.$ml_code_y.' 0" sizen="'.$fc_window['SizeX'].' '.$fc_window['SizeY'].'" halign="center" style="Bgs1InRace" substyle="BgList"/>
		<quad posn="'.$title_bg_x.' '.($ml_code_y - 0.4).' 3" sizen="'.$title_bg_width.' 2.5" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>
		<label posn="'.$title_x.' '.$title_y.' 4" textsize="2" text="$o$FFF'.$fc_window['Title'].'"/>';
		if($fc_window['Close'] == true) $ml_display_code .= '<quad posn="'.$close_x.' '.($ml_code_y - 0.4).' 4" sizen="2.5 2.5" style="BgsPlayerCard" substyle="BgActivePlayerScore"/>';
		if($fc_window['Close'] == true) $ml_display_code .= '<quad posn="'.$close_x.' '.($ml_code_y - 0.4).' 5" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close" action="10000"/>';
		$ml_display_code .= $ml_code;
		
		if($fc_window['UseButtons'] == true) $ml_display_code .= $this->createButtonsCode();
		$fc_window['PlayerButtons'][$fc_window['Uses']] = $fc_window['Buttons'];
		$fc_window['ButtonToPlayer'][$fc_window['Uses']] = $player;
		
		//Display manialink
		if(trim($player)!=='')
		{
			$control->display_manialink_to_login($ml_display_code, 10000, 0, 0, $player);
		}
		else
		{
			$control->display_manialink($ml_display_code, 10000, 0, 0);
		}
	}
	public function closeWindow($player)
	{
		//Close Manialink when a player clicked on the closebox or a plugin called it
		global $control;
		if(trim($player)!=='') //to 1 player
		{
			$control->close_ml(10000, $player);
		}
		else //to all players
		{
			$control->close_ml(10000, '');
		}
	}
	public function mlAnswer($data)
	{
		if($data[2] == 10000) $this->closeWindow($data[1]);
		else $this->buttonPressed($data);
	}
	
	//Private functions
	private function calculateHeight()
	{
		global $fc_window;
		$height = 3.5 + count($fc_window['Content']) * 2.5;
		if(isset($fc_window['Buttons'][0])) $height = $height + 3;
		return $height;
	}
	private function createButtonsCode()
	{
		global $fc_window;
		$bc = '<quad posn="0 '.($fc_window['PosY'] - $this->calculateHeight() + 2.6).' 6" sizen="'.($fc_window['SizeX'] - 0.2).' 0.2" halign="center" bgcolor="fffc"/>';
		$buttons = count($fc_window['Buttons']);
		//Auto width
		if($fc_window['ButtonsAutoWidth'] == true)
		{
			$button_width = ($fc_window['SizeX'] - 2) / $buttons;
			$button_pos = ($fc_window['SizeX'] - 2) / 2;
			$button_pos = 0 - $button_pos;
			for($i = 0; isset($fc_window['Buttons'][$i]); $i++)
			{
				if(trim($fc_window['Buttons'][$i]['text']) !== '')
				{
					$bc .= '<quad posn="'.$button_pos.' '.($fc_window['PosY'] - $this->calculateHeight() + 2.4).' 4" sizen="'.$button_width.' 2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
					$bc .= '<quad posn="'.$button_pos.' '.($fc_window['PosY'] - $this->calculateHeight() + 2.4).' 6" sizen="'.$button_width.' 2" style="BgsPlayerCard" substyle="BgCardSystem" action="'.(10000 + $i + 1).'"/>';
					$bc .= '<label posn="'.($button_pos + ($button_width / 2)).' '.($fc_window['PosY'] - $this->calculateHeight() + 2.5).' 8" sizen="'.$button_width.' 2.5" textsize="2" text="$o'.$fc_window['Buttons'][$i]['text'].'" halign="center"/>';
				}
				$button_pos = $button_pos + $button_width;
			}
		}
		//no auto width
		else
		{
			$buttons_width = 0;
			for($i = 0; isset($fc_window['Buttons'][$i]); $i++)
			{
				$buttons_width = $buttons_width + $fc_window['Buttons'][$i]['size'];
			}
			$buttons_pos = 0 - ($buttons_width / 2);
			for($i = 0; isset($fc_window['Buttons'][$i]); $i++)
			{
				if(trim($fc_window['Buttons'][$i]['text']) !== '')
				{
					$bc .= '<quad posn="'.$buttons_pos.' '.($fc_window['PosY'] - $this->calculateHeight() + 2.4).' 4" sizen="'.$fc_window['Buttons'][$i]['size'].' 2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
					$bc .= '<quad posn="'.$buttons_pos.' '.($fc_window['PosY'] - $this->calculateHeight() + 2.4).' 6" sizen="'.$fc_window['Buttons'][$i]['size'].' 2" style="BgsPlayerCard" substyle="BgCardSystem" action="'.(10000 + $i + 1).'"/>';
					$bc .= '<label posn="'.($buttons_pos + ($fc_window['Buttons'][$i]['size'] / 2)).' '.($fc_window['PosY'] - $this->calculateHeight() + 2.5).' 8" sizen="'.$fc_window['Buttons'][$i]['size'].' 2.5" textsize="2" text="$o'.$fc_window['Buttons'][$i]['text'].'" halign="center"/>';
				}
				$buttons_pos = $buttons_pos + $fc_window['Buttons'][$i]['size'];
			}
		}
		return $bc;
	}
	private function buttonPressed($data)
	{
		global $fc_window, $control;
		$data[2] = $data[2] - 10000;
		$showId = '';
		for($i = count($fc_window['ButtonToPlayer']) - 1; $i >= 0; $i--)
		{
			if($fc_window['ButtonToPlayer'][$i] == $data[1])
			{
				$showId = $i;
				break;
			}
		}
		if($showId !== '')
		{
			$data[3] = $fc_window['PlayerButtons'][$showId][$data[2] - 1]['text'];
			if($fc_window['PlayerButtons'][$showId][$data[2] - 1]['close'] == true)
			{
				$this->closeWindow($data[1]);
			}
			else
			{
				if(isset($fc_window['Functions'][$data[1]])){
					if(function_exists($fc_window['Functions'][$data[1]])) $fc_window['Functions'][$data[1]]($control, $data);
					else console('Function '.$fc_window['Functions'][$data[1]].' doesn\'t exists!');
				}
				else console('Can\'t find target for '.$data[1].'!');
			}
		}
	}
	private function createTable()
	{
		global $fc_window;
		$table = '';
		$posy = $fc_window['PosY'] - 3.5;
		$sizey = 0;
		for($i = 0; isset($fc_window['Content'][$i]); $i++) //For every line
		{
			$td = explode('<td', $fc_window['Content'][$i]);
			$posx = ($fc_window['SizeX'] - 2) / 2;
			$posx = 0 - $posx;
			for($tdi = 0; isset($td[$tdi]); $tdi++) //For every td
			{
				$width = '';
				$c = 0;
				$width_began = false;
				$is_link = false;
				$align_center = false;
				$link = '';
				$content = '';
				if(strpos($td[$tdi], 'width') !== false)
				{
					$widthStart = strpos($td[$tdi], 'width') + 7;
					for($ci = $widthStart; true; $ci++)
					{
						if(substr($td[$tdi], $ci, 1) == '\'') break;
						elseif(substr($td[$tdi], $ci, 1) == '"') break;
						elseif(substr($td[$tdi], $ci, 1) == ' ') break;
						else $width .= substr($td[$tdi], $ci, 1);
					}
				}
				if(strpos($td[$tdi], 'id=') !== false)
				{
					$idStartPos = strpos($td[$tdi], 'id=') + 4;
					for($ci = $idStartPos; true; $ci++)
					{
						if(substr($td[$tdi], $ci, 1) == '\'') break;
						elseif(substr($td[$tdi], $ci, 1) == '"') break;
						elseif(substr($td[$tdi], $ci, 1) == ' ') break;
						else $link .= substr($td[$tdi], $ci, 1);
					}
				}
				if(strpos($td[$tdi], 'align="center"') !== false || strpos($td[$tdi], 'align=\'center\'') !== false) $align_center = true;
				$text = $td[$tdi];
				$text = str_replace(' width="'.$width.'">', '', $text);
				$text = str_replace('width="'.$width.'">', '', $text);
				$text = str_replace(' width="'.$width.'"/>', '', $text);
				$text = str_replace(' width="'.$width.'" ', '', $text);
				$text = str_replace(' width="'.$width.'"', '', $text);
				$text = str_replace(' width=\''.$width.'\'>', '', $text);
				$text = str_replace(' width=\''.$width.'\'/>', '', $text);
				$text = str_replace(' width=\''.$width.'\' ', '', $text);
				$text = str_replace(' width=\''.$width.'\'', '', $text);
				$text = str_replace(' align="center">', '', $text);
				$text = str_replace('align="center">', '', $text);
				$text = str_replace(' align="center"/>', '', $text);
				$text = str_replace(' align="center" ', '', $text);
				$text = str_replace(' align="center"', '', $text);
				$text = str_replace(' align=\'center\'>', '', $text);
				$text = str_replace(' align=\'center\'/>', '', $text);
				$text = str_replace(' align=\'center\' ', '', $text);
				$text = str_replace(' align=\'center\'', '', $text);
				$text = str_replace('id="'.$link.'">', '', $text);
				$text = str_replace('id="'.$link.'"/>', '', $text);
				$text = str_replace('id="'.$link.'" ', '', $text);
				$text = str_replace('id="'.$link.'"', '', $text);
				$text = str_replace('id=\''.$link.'\'>', '', $text);
				$text = str_replace('id=\''.$link.'\'/>', '', $text);
				$text = str_replace('id=\''.$link.'\' ', '', $text);
				$text = str_replace('id=\''.$link.'\'', '', $text);
				$text = str_replace('</td>', '', $text);
				if($align_center == true) $table .= '<label posn="'.($posx+(($width)/2)).' '.$posy.' 4" sizen="'.($width - 0.5).' 2" textsize="2" halign="center" text="$fff'.$text.'"/>';
				else $table .= '<label posn="'.$posx.' '.$posy.' 4" sizen="'.($width - 0.5).' 2" textsize="2" text="$fff'.$text.'"/>';
				if(trim($link) !== '') $table .= '<quad posn="'.$posx.' '.$posy.' 4" sizen="'.$width.' 2.25" style="'.$fc_window['TableLink']['Style'].'" substyle="'.$fc_window['TableLink']['SubStyle'].'" action="'.$link.'"/>';
				$posx = $posx + $width;
			}
			$posy = $posy - 2.5;
			$sizey = $sizey + 2.5;
		}
		return $table;
	}
}
?>