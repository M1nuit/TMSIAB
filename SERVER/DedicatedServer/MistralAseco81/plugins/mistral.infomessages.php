<?php
Aseco::registerEvent("onEndRace", "mistral_infomessage");

global $mi_messages;

function mistral_infomessage($aseco,$command)
	{
	global $mi_messages;
	
	$message = '$z$s>> [$f00INFO$z$s] $F88';
	$message.=$mi_messages[array_rand($mi_messages)];
	$aseco->addCall("ChatSendServerMessage", array($message));	
	}
?>