<?php

//Aseco::addChatCommand("msg", "Sends a private message to a specified user login");
//Aseco::addChatCommand("hi", "Sends a hi message to everyone");
//Aseco::addChatCommand("bye", "Sends a bye message to everyone");
//Aseco::addChatCommand("lol", "Sends a lol message to everyone");
Aseco::addChatCommand("lool", "Sends a lool message to everyone");
//Aseco::addChatCommand("gg", "Sends a gg message to everyone");

/*
function chat_msg(&$aseco, &$command) {
	$command['params'] = explode(" ", $command['params'], 2);
	$m = '{#error}-pm-$z['.$command['author']->nickname.'$z] {#interact}'.$command['params'][1];
	$msg = $aseco->formatColors($m);
	$aseco->addCall('ChatSendServerMessageToLogin', array($msg, $command['params'][0]));
	$aseco->addCall('ChatSendServerMessageToLogin', array($msg, $command['author']->login));
}

function chat_hi(&$aseco, &$command) {
	if (strlen($command['params']) > 0) {
		$m = '$z['.$command['author']->nickname.'$z] {#interact}Hello '.$command['params']."!";
		$msg = $aseco->formatColors($m);
	} else {
		$m = '$z['.$command['author']->nickname.'$z] {#interact}Hello All !';
		$msg = $aseco->formatColors($m);
	}
	$aseco->addCall('ChatSendServerMessage', array($msg));
}

function chat_bye(&$aseco, &$command) {
	if (strlen($command['params']) > 0) {
		$m = '$z['.$command['author']->nickname.'$z] {#interact}Bye '.$command['params']."!";
		$msg = $aseco->formatColors($m);
	} else {
		$m = '$z['.$command['author']->nickname.'$z] {#interact}Bye All !';
		$msg = $aseco->formatColors($m);
	}
	$aseco->addCall('ChatSendServerMessage', array($msg));
}

function chat_lol(&$aseco, &$command) {
	$m = '$z['.$command['author']->nickname.'$z] {#interact}LoL !';
	$msg = $aseco->formatColors($m);
	$aseco->addCall('ChatSendServerMessage', array($msg));
}

function chat_gg(&$aseco, &$command) {
	if (strlen($command['params']) > 0) {
		$m = '$z['.$command['author']->nickname.'$z] {#interact}Good Game '.$command['params']."!";
		$msg = $aseco->formatColors($m);
	} else {
		$m = '$z['.$command['author']->nickname.'$z] {#interact}Good Game All !';
		$msg = $aseco->formatColors($m);
	}
	$aseco->addCall('ChatSendServerMessage', array($msg));
}

*/
function chat_lool(&$aseco, &$command) {
	$m = '$z['.$command['author']->nickname.'$z] {#interact}LooOOooL !';
	$msg = $aseco->formatColors($m);
	$aseco->addCall('ChatSendServerMessage', array($msg));
}
?>
