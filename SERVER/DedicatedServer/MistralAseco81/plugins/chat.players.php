<?php
/**
 * Chat plugin.
 * Show list of players in a popup.
 */

Aseco::addChatCommand('players', 'displays list of nick/logons');

function chat_players(&$aseco, &$command) {
	$header = "<?xml version='1.0' encoding='utf-8' ?>
<manialink posx='0.5' posy='0.35'>
  <type>default</type>
  <format textsize='2'/>
  <background bgcolor='222E' bgborderx='0.03' bgbordery='0.03'/>
  <line><cell width='0.94'><text halign='center'>- Player Data -</text></cell></line>
<line height='.04'>
<cell width='0.47' bgcolor='888E'><text halign='right'>Login</text></cell>
<cell width='0.47' bgcolor='888E'><text>  NickName</text></cell></line>
";

	$detail = "<line>
	<cell width='0.47'><text halign='right'>{LOGIN}</text></cell>
	<cell width='0.47'><text>  {NICKNAME}</text></cell></line>
";
	$msg = '';
	$player = $command['author'];
	$command['params'] = explode(' ', $command['params']);

	$cmdcount = count($command['params']);
	$ctr1 = 0;
	$msgnum = 1;
	$wildcard = $command['params'][0];
	$player->msgs = array();		// clear out the message list before you do anything
	foreach($aseco->server->players->player_list as $pl)
		{
		if ( strlen($command['params'][0]) == 0 ||
			 stripos(stripcolors($pl->nickname), $wildcard) > 0 ||
			 stripos($pl->login, $wildcard) > 0)
			{
			$s = $detail;
			$s = str_replace('{LOGIN}', $pl->login, $s);
			$s = str_replace('{NICKNAME}', sub_maniacodes($pl->nickname), $s);
			$ctr1++;
			$msg .= $s;
			if ( $ctr1 > 24 )
				{
				$ctr1 = 0;
				$player->msgs[$msgnum] = $header . $msg;
				$msgnum++;
				$msg = '';
				}
			}
		}
	if ( strlen($msg) > 0 )
		{
		$ctr1 = 0;
		$player->msgs[$msgnum] = $header . $msg;
		$msgnum++;
		}
	$player->msgs['curpage'] = 1;
	show_multi_msg($player);

//	$fp = fopen('players.xml', 'w');
//	fwrite($fp, $player->msgs[1]);
//	fclose($fp);
  	}
?>
