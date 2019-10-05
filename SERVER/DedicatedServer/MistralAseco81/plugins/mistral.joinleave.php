<?php

Aseco::registerEvent("onPlayerConnect", "playerjoinmsg");
Aseco::registerEvent("onPlayerDisconnect", "playerleavemsg");

global $adminonline, $voteratio;

$adminonline=false;

function setRatio($aseco)
	{
	global $adminonline, $voteratio;

	if ($adminonline)
		{
		$ratio = 100;
		$message = formatText("{#server}>> Admin entered! Votes became automatically disabled.");
		}
	else
		{
		$ratio = $voteratio;
		$message = formatText("{#server}>> Last admin left! Callvote ratio automatically set to {1}%.", $ratio);
		}
		
	$serverratio = (double)$ratio;
	$serverratio = $serverratio/100; 

	// tell the server to set the callvote ratio
	$aseco->addCall("SetCallVoteRatio",array($serverratio));

	// replace colors ...
	$message = $aseco->formatColors($message);
    
	// send chat message ...
	$aseco->addCall(ChatSendServerMessage, array($message));
	}

function playerjoinmsg($aseco, $player_item)
	{
	global $adminonline;

	$zone = $player_item->zone;
	$nickname = $player_item->nickname;
	$player_item->mistral['lastjukebox'] = 0;

	$aseco->client->query('GetDetailedPlayerInfo', $player_item->login);
	$playerinfo = $aseco->client->getResponse();
	$rank = $playerinfo['LadderStats']['PlayerRankings']['0']['Ranking'];
//	$rank = str_replace(' ', '$n $m', number_format($rank, 0, ' ', ' '));
 
	$message=$nickname.'$z$s{#message} ($FFF'.$rank.'{#message} - $FFF'.$zone.'{#message}) has joined.';
	$message=$aseco->formatColors($message);
	$aseco->addCall("ChatSendServerMessage", array($message));
	
/*
	if (!$adminonline)
		{
		$aseco->client->query('GetPlayerList', 100, 0);
		$players = $aseco->client->getResponse();

		foreach ($players as $player)
			{
			$login = $player['Login'];
			if ($aseco->isAdmin($login))
				{
				$adminonline = true;
				setRatio($aseco);
				$aseco->console("Admin($login) entered. Voting disabled.");
				break;
				}
			}
		}
*/

	logJoin($nickname, $player_item->login, $zone); 
	}

function playerleavemsg($aseco, $player_item)
	{
	global $adminonline;
	 
 	if ($player_item->nickname)
 		{
		if ($player_item->nickname == '')
			return;
		}
	else
		return;

	$nickname = $player_item->nickname;
	$zone = $player_item->zone;

	$message=$nickname.'$z$s{#message} has left.';

/*
	if ($adminonline)
		{
		$aseco->client->query('GetPlayerList', 100, 0);
		$players = $aseco->client->getResponse();

		$stillonline = false;
		foreach ($players as $player)
			{
			$login = $player['Login'];
			if ($aseco->isAdmin($login))
				{
				$stillonline=true;
				}
			}
		if (!$stillonline)
			{
			$adminonline = false;
			setRatio($aseco);
			$aseco->console("Last admin($player_item->login) left. Voting enabled.");
			}
		}
*/

	logLeave($nickname, $player_item->login, $zone);
	$aseco->client->multiquery();
	
	$aseco->client->query('GetPlayerList', 100, 0);
	$players = $aseco->client->getResponse();

	foreach ($players as $player)
		{
		if ($player_item->login==$player['Login'])
			{
			$message.=' (disconnected/autokick)';
			$aseco->addCall('Kick', array($player_item->login));
			}
		}
		
	if ($player_item->mistral['quit'] != "quit")
		{
		$message=$aseco->formatColors($message);
		$aseco->addCall("ChatSendServerMessage", array($message));
		}
	}
?>