<?php
/************************************************************************************
/* 
/* ASECO plugin to kick idle players
/* 
/* (C) 2007 by Mistral
/* 
/************************************************************************************/
Aseco::registerEvent("onEndRace", "kickIdlePlayers");
Aseco::registerEvent("onPlayerConnect", "kickIdleInit");
Aseco::registerEvent("onNewChallenge", "kickIdleNewChallenge");
Aseco::registerEvent("onChat", "kickIdleChat");
Aseco::registerEvent("onCheckpoint", "kickIdleCheckpoint");
Aseco::registerEvent("onPlayerFinish", "kickIdleFinish");

global $resetOnChat, $resetOnCheckpoint, $resetOnFinish, $idlekickStart, $kickAfter, $debug;

// dont touch:
$idlekickStart = true;
$debug = false;

function kickIdleCheckpoint($aseco, $data)
	{
 	global $resetOnCheckpoint, $debug;
 	
	$login = $data[1];
	
	if (!$resetOnCheckpoint)
		return;
		
	$player = $aseco->server->players->getPlayer($login);
	$player->mistral['idleCount']=0;
	if ($debug)
		$aseco->console_text("Idlekick: ".$player->login." reset on checkpoint");
	
	}

function kickIdleFinish($aseco, $finish_item)
	{
 	global $resetOnFinish, $debug;
 	
	$player = $finish_item->player;
		
	if (!$resetOnFinish)
		return;
		
	$player->mistral['idleCount']=0;
	if ($debug)
		$aseco->console_text("Idlekick: ".$player->login." reset on finish");
	}

function kickIdleChat($aseco, $data)
	{
 	global $resetOnChat, $debug;
 	
 	$id = $data[0];
 	if ($id == 0)
 		return;
 		
	$login = $data[1];
	
	if (!$resetOnChat)
		return;
		
	$player = $aseco->server->players->getPlayer($login);
	$player->mistral['idleCount']=0;
	if ($debug)
		$aseco->console_text("Idlekick: ".$player->login." reset on chat");
	}

// NewChallenge
function kickIdleNewChallenge($aseco, $challenge)
	{
 	global $idlekickStart, $debug;
 	
	if ($idlekickStart)
		{
		$idlekickStart = false;
		if ($debug)
			$aseco->console_text("Idlekick: idlekickStart set to false.");
		foreach($aseco->server->players->player_list as $player)
			kickIdleInit($aseco, $player);
		return;
		}
	
	foreach($aseco->server->players->player_list as $player)
		{
		$player->mistral['idleCount'] = $player->mistral['idleCount']+1;
		if ($debug)
			$aseco->console_text("Idlekick: ".$player->login." set to ".$player->mistral['idleCount']);
		}
	}

function kickIdleInit($aseco, $player)
	{
 	global $debug;
 	
	$player->mistral['idleCount']=0;
	if ($debug)
		$aseco->console_text("Idlekick: ".$player->login." initialised with 0");
	}

// EndRace
function kickIdlePlayers($aseco,$challenge)
	{
	global $kickAfter, $debug;

	$aseco->client->query('GetGuestList', 100, 0);
	$guests = $aseco->client->getResponse();

	foreach($aseco->server->players->player_list as $player)
		{
		if ($player->mistral['idleCount'] >= $kickAfter)
			{
			$isguest = false;
			foreach ($guests as $guest)
				{
				if ($player->login == $guest['Login'])
					{
					$aseco->console("Idlekick: ".$player->login." is on guestlist - not kicked.");
					$isguest = true;
					}
				}
			if (!$isguest)
				{
				$message = formatText("{#server}>> Idlekick {1}\$z{#server} after {2} challenges!", $player->nickname, $kickAfter);
				$message = $aseco->formatColors($message);
				$aseco->addCall("ChatSendServerMessage", array($message));
				$aseco->addCall("Kick", array($player->login));
				
				$aseco->console("Idlekick: ".$player->login." after $kickAfter challenges without action.");
				}
			}
		elseif ($debug)
			$aseco->console("Idlekick: ".$player->login." current value is ".$player->mistral['idleCount']);

		}
	}
?>