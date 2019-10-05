<?php
/**
 * Writes a logfile of all outputted messages.
 */
function doLog($text) {
  global $logfile;
  
  $logfile = fopen('logfile.txt', 'a+');
  fwrite($logfile, $text);
  fclose ($logfile);
  
}


/**
 * Puts an element onto a specific position into an array.
 * Keeps original size.
 */
function insertArrayElement(&$array, $value, $pos) {

  // get current size ...
  $size = count($array);

  // if position is in array range ...
  if ($pos < 0 && $pos > $size) {
    return false;
  }

  // shift values below ...
  for ($i = $size-1; $i >= $pos; $i--) {
    $array[$i+1] = $array[$i];
  }

  // now put in the new element.
  $array[$pos] = $value;
  return true;
}


/**
 * Moves an element from one position to the other.
 * All items between are shifted down.
 */
function moveArrayElement(&$array, $from, $to) {

  // get current size ...
  $size = count($array);

  // destination and source have to be among the array borders!
  if ($from < 0 && $from > $size && $to < 0 && $to > $size) {
    return false;
  }

  // backup the element we have to move ...
  $moving_element = $array[$from];

  if ($from > $to) {

    // shift values between downwards ...
    for ($i = $from-1; $i >= $to; $i--) {
      $array[$i+1] = $array[$i];
    }
  } else {

    // not needed yet ...
    return false;
  }

  // now put in the element which was to move ...
  $array[$to] = $moving_element;
  return true;
}


/**
 * Formats a string from the format ssssms
 * into the format mmm:ss:ms.
 */
function formatTime($MwTime, $msec = true)
	{
	if ($MwTime == -1)
  		{
		return '???';
		}
	else
		{
    	$minutes = floor($MwTime/(1000*60));
    	$seconds = floor(($MwTime-$minutes*60*1000)/1000);
    	$mseconds = substr($MwTime,strlen($MwTime)-3,2);
    	if ($msec)
			{
			$tm = sprintf('%02d:%02d.%02d', $minutes, $seconds, $mseconds);
    		}
		else
			{
			$tm = sprintf('%02d:%02d', $minutes, $seconds);
			}
		}
	if (substr($tm, 0, 1) == '0')
		{
		$tm = substr($tm, 1);
		}
	return $tm;
	}

/**
 * Formats a string from the format ssssmss
 * into the format hh:mm:ss.ms
 */
function formatTimeH($MwTime, $msec = true) {
  if ($MwTime == -1) {
		return '???';
  } else {
	$mseconds = substr($MwTime, strlen($MwTime)-3, 2);
	$MwTime = substr($MwTime, 0, strlen($MwTime)-3);
	$hours = floor($MwTime / 3600);
	$MwTime = $MwTime - ($hours * 3600);
    $minutes = floor($MwTime / 60);
	$MwTime = $MwTime - ($minutes * 60);
    $seconds = floor($MwTime);
    if ($msec) {
      return sprintf('%02d:%02d:%02d.%02d', $hours, $minutes, $seconds, $mseconds);
    } else {
      return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
  }
}


/**
 * Strips colors from TM color strings.
 * "$af0Brat$s$fffwurst" will become "Bratwurst".
 */
function stripColors($str){
  $cr = "\r";

  // replace $ with \r (unused char in nicks), because ereg don't like to work with $
  // note: strip all \r's because they'll be made to $ if they weren't removed...
  $str2 = str_replace('$', $cr, str_replace($cr, '', $str) );

  // rereplace double dollars with one dollar
  $str2 = str_replace($cr . $cr, '$', $str2);

  // replace "$" having 3 [0-9a-fA-F] or 1 not "$" after
  // add a char at begining because ereg rule will use the char before found "$"...
  //$str2 = ereg_replace("\r([0-9a-foA-FO][0-9a-fA-F][0-9a-fA-F]|[^\r])", "", "a".$str2);

  $str2 = ereg_replace($cr . '[0-9a-fA-F][0-9a-zA-Z][0-9a-zA-Z]', '', $str2);



  // have to do this in two steps, first remove the $xxx's then do the single char ones
  $str2 = ereg_replace($cr . '[obfiswnmgzOBFISWNMGZ]', '', $str2);

  // remove first char, and back to real $
  $str2 = str_replace($cr, '$', $str2);
  return $str2;
}


/**
 * Formats a text.
 * replaces parameters in the text which are marked with {n}
 */
function formatText($text) {

  // get all function's parameters ...
  $args = func_get_args();

  // first parameter is the text to format ...
  $text = array_shift($args);

  // further parameters will be replaced in the text ...
  $parameters = $args;

  $i = 0;
  // replace all parameters in the text ...
  foreach($parameters as $parameter) {
    $i++;
    $text = str_replace('{'.$i.'}', $parameter, $text);
  }

  // and return the modified one.
  return $text;
}

/**
 * Make String for SQL use that single quoted & got special chars replaced
 *
 */
function quotedString(&$str_in)
	{
	return '\'' . mysql_real_escape_string($str_in) . '\'';
	}  //  quotedString


function popup_msg(&$login, &$msg, $timeout=0)		// if $login is blank, send to everyone.
	{
	global $aseco;
	
	$player = $aseco->server->players->getPlayer($login);
	
	$msg = sub_maniacodes($msg);		// make sure you don't have any calls to this if you're using the popup_msg function
	$msgwrapper = "<?xml version='1.0' encoding='utf-8' ?>
<manialink posx='0.5' posy='0.35'>
  <type>default</type>
  <format textsize='2'/>
  <background bgcolor='222C' bgborderx='0.03' bgbordery='0.03'/>
  <line><cell width='0.94'><text>$msg</text></cell></line>
  <line height='0.04'><cell width='0.94'><text></text></cell></line>
  <line><cell width='0.94'><text halign='center' action='12' textcolor='FFFF'>Close</text></cell></line>
</manialink>";

	if ( $login > '' )
		{
		$aseco->addcall('SendDisplayManialinkPageToLogin', array($login, $msgwrapper, $timeout, TRUE));
		}
	else
		{
		$aseco->addcall('SendDisplayManialinkPage', array($msgwrapper, $timeout, TRUE));
		}
	if (!$aseco->client->multiquery())
		{
		$errmsg = '[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage();
		trigger_error($errmsg);
		$fp = fopen('failed manialink.xml', 'w');
		fwrite($fp, $errmsg . CRLF);
		fwrite($fp, $msg . CRLF);
		fclose($fp);
		}
	}  //  popup_msg

function display_help(&$player, $show_admin = false)
	{
	global $aseco;
	$header = "<?xml version='1.0' encoding='utf-8' ?>
<manialink posx='0.5' posy='0.65'>
<type>default</type>
<format textsize='2'/>
<background bgcolor='222C' bgborderx='0.03' bgbordery='0.03'/>
<line><cell width='0.94'><text halign='center'>";

	if ( $show_admin )
		{
		$header .= '/admin ';
		$padchrs = '...';
		}
  $header .= "Chat Commands</text></cell></line>
		<line height='.04'><cell width='0.22' bgcolor='888E'><text>CMD</text></cell><cell width='0.72' bgcolor='888E'><text>  Info</text></cell></line>";

	$detail = "<line><cell width='0.22'><text>\$f00{CMDNAME}</text></cell><cell width='0.72'><text>  {HELP}</text></cell></line>" . CRLF;

	$player->msgs = array();
	$msgs = 0;
	$cmdcount = 0;
	$stgout = '';

	foreach ($aseco->chat_commands as $chat_command)
		{
		if ($chat_command->isadmin == $show_admin)
			{
			$s = str_replace('{CMDNAME}', $padchrs . $chat_command->name, $detail);
			$s = str_replace('{HELP}', sub_maniacodes($chat_command->help), $s);
			$stgout .= CRLF . $s;
			$cmdcount++;

			if ( $cmdcount > 25 )
				{
				$msgs++;
				$player->msgs[$msgs] = $header . $stgout;
				$stgout = '';
				$cmdcount = 0;
				}
			}
		}

	if ( $cmdcount > 0 )
		{
		$msgs++;
		$player->msgs[$msgs] = $header . $stgout;
		}

	if ( count($player->msgs) > 0 )
		{
	  	$player->msgs['curpage'] = 1;
		show_multi_msg($player);
		}
	else
		{
		$aseco->addCall('ChatSendToLogin',
			array($aseco->formatColors('{#error}No Chat Commands Found!'), $player->login));
		$aseco->client->multiquery();
		}
	}  //  display_help()


function sub_maniacodes(&$str2fix)
	{
	return htmlspecialchars($str2fix);
	/* Sorry Cay, but I tried using htmlentites, actually had a player with a name that made the whole popup window fail with the following error:
	   Can't do/get SendDisplayManialinkPageToLogin !  Error -510: UTF-8 sequence too short
	   falling back to what I know works
	*/
	if ( strpos($str2fix, '<') == 0 && strpos($str2fix, '&') == 0)
		{
		return $str2fix;
		}
	$str2fix = str_replace('&', '&amp;', $str2fix);
	$str2fix = str_replace('<', '&lt;', $str2fix);
//	$str2fix = str_replace('>', '&gt;', $str2fix);

	return $str2fix;

	}  //  sub_maniacodes

// $s$fff[ATP]$007$iÐ¼Î±Ä‘ÅžÎ¹Î±sÑ›
// CNGºStÞtXº


function show_multi_msg($player)
	{
	global $aseco;

	$curpage = $player->msgs['curpage'];
	$cnt = sizeof($player->msgs);
	if ( $cnt == 0 )
		{
		return;
		}
	$cnt--;		// count would include the curpage counter too, remove it
	$msg = $player->msgs[$curpage];

	// OLDSTLYE
	if ($player->msgsw == 0 || $player->msgsh == 0)
		{
		if ( $curpage < $cnt )		// pages exist after this one
			{
			$btn2 = '<text halign=\'left\' action=\'13\' textcolor=\'FFFF\'>Next</text>';
			}
		else
			{
			$btn2 = '<text></text>';
			}
		if ( $curpage > 1 )
			{
			$btn1 = '<text halign=\'right\' action=\'11\' textcolor=\'FFFF\'>Prev</text>';
			}
		else
			{
			$btn1 = '<text></text>';
			}
		$msg .= "<line height='.04'><cell><text></text></cell></line>
			<line><cell width='.40'>" . $btn1 . "</cell>
			<cell width='.14'><text halign='center' action='12' textcolor='FFFF'>Close</text></cell>
			<cell width='.40'>" . $btn2 . "</cell>
			</line>
			</manialink>";
		}
	// NEWSTYLE
	else
		{
		$width = $player->msgsw;
		$buttom = -$player->msgsh+3;
		if ( $curpage < $cnt )		// pages exist after this one
			{
			$msg .= "<label posn='$width $buttom 1' halign='right' action='13' style='CardButtonSmall' text='Next'/>";
			}
			$msg .= "<label posn='".($width/2)." $buttom 1'  halign='center' action='12' style='CardButtonSmall' text='Close'/>";
		if ( $curpage > 1 )
			{
			$msg .= "<label posn='0 $buttom 1' halign='left' action='11' style='CardButtonSmall' text='Previous'/>";
			}
		$msg .= "<quad posn='0 $buttom 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";

		$msg .= "</frame></manialink>";	
		}
	
	$aseco->addcall('SendDisplayManialinkPageToLogin', array($player->login, $msg, 0, TRUE));

	if (!$aseco->client->multiquery())
		{
		$errmsg = '[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage();
		trigger_error($errmsg);
		$fp = fopen('failed manialink.xml', 'w');
		fwrite($fp, $errmsg . CRLF);
		fwrite($fp, $msg . CRLF);
		fclose($fp);
		}

	}  //  show_multi_msg

function event_multi_message(&$aseco, &$answer) {
	global $admin_responses;
	$player = $aseco->server->players->getPlayer($answer[1]);
	$cnt = sizeof($player->msgs);
	$curpage = $player->msgs['curpage'];
	if ( $cnt == 0 )
		{
		return;
		}
	if ( $answer[2] >= 20000 ) // 20000-29999 Player Administration
		{
		return;
		}
	if ( $answer[2] == 13 && $curpage+1 < $cnt )
		{
		$curpage++;
		}
	elseif ( $answer[2] == 11 && $curpage-1 > 0 )
		{
		$curpage--;
		}
	elseif ( $answer[2] == 12 )
		{
		$player->msgs = array();
		$player->msgsw = 0;
		$player->msgsh = 0;
		return;
		}
	elseif ( $answer[2] >= 10000 && $answer[2] < 15000)
		{
		$tracknumber = $answer[2]%10000;
		$command=array('author'=>$player, 'params'=>$tracknumber);
		$player->msgs = array();
		chat_jukebox($aseco, $command);
		return;
		}
	elseif ( $answer[2] >= 15000 && $answer[2] < 20000)
		{
		switch ($answer[2])
			{
			case 15000: $environment="env:stadium"; break;
			case 15001: $environment="env:alpine"; break;
			case 15002: $environment="env:speed"; break;
			case 15003: $environment="env:bay"; break;
			case 15004: $environment="env:rally"; break;
			case 15005: $environment="env:coast"; break;
			case 15006: $environment="env:island"; break;
			}
		$command=array('author'=>$player, 'params'=>$environment);
		$player->msgs = array();
		chat_list($aseco, $command);
		return;
		}
	
	$player->msgs['curpage'] = $curpage;

	show_multi_msg($player);
	}



?>
