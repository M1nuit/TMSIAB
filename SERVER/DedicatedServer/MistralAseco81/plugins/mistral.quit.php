<?php
Aseco::addChatCommand("quit", "quit the game, leaving a message");

function chat_quit($aseco, $command)
	{
  	$message = $command['params'];
	$player = $command['author'];
	$player->mistral['quit'] = "quit";
	$login = $player->login;
	$nickname = $player->nickname;

	$message=$nickname.'$z$s{#message} left the building: '.$message;
	$message=$aseco->formatColors($message);
	$aseco->addCall("ChatSendServerMessage", array($message));
	$aseco->addCall("Kick", array($login));
	}
?>