<?php
/* vim: set noexpandtab tabstop=2 softtabstop=2 shiftwidth=2: */

/**
 * Chat plugin.
 * Shows (file)names of current track's song & mod.
 * Created by Xymph
 *
 * Dependencies: none
 */

Aseco::addChatCommand('song', 'Shows filename of current track\'s song');
Aseco::addChatCommand('mod', 'Shows (file)name of current track\'s mod');

function chat_song($aseco, $command) {

	$player = $command['author'];

	// check for track's song
	if ($aseco->server->challenge->gbx->songfile) {
		$message = formatText($aseco->getChatMessage('SONG'),
		                      stripColors($aseco->server->challenge->name),
		                      $aseco->server->challenge->gbx->songfile);
		// use only first parameter
		$command['params'] = explode(' ', $command['params'], 2);
		if ((strtolower($command['params'][0]) == 'url' ||
		     strtolower($command['params'][0]) == 'loc') &&
		    $aseco->server->challenge->gbx->songurl) {
			$message .= LF . '{#highlite}$l[' . $aseco->server->challenge->gbx->songurl . ']' . $aseco->server->challenge->gbx->songurl . '$l';
		}
	} else {
		$message = '{#server}> {#error}No track song found!';
		if ($aseco->server->getGame() == 'TMF' && function_exists('chat_music'))
			$message .= '  Try {#highlite}$i /music current {#error}instead.';
	}
	// show chat message
	$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $player->login);
}  // chat_song

function chat_mod($aseco, $command) {

	$player = $command['author'];

	// check for track's mod
	if ($aseco->server->challenge->gbx->modname) {
		$message = formatText($aseco->getChatMessage('MOD'),
		                      stripColors($aseco->server->challenge->name),
		                      $aseco->server->challenge->gbx->modname,
		                      $aseco->server->challenge->gbx->modfile);
		// use only first parameter
		$command['params'] = explode(' ', $command['params'], 2);
		if ((strtolower($command['params'][0]) == 'url' ||
		     strtolower($command['params'][0]) == 'loc') &&
		    $aseco->server->challenge->gbx->modurl) {
			$message .= LF . '{#highlite}$l[' . $aseco->server->challenge->gbx->modurl . ']' . $aseco->server->challenge->gbx->modurl . '$l';
		}
	} else {
		$message = '{#server}> {#error}No track mod found!';
	}
	// show chat message
	$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $player->login);
}  // chat_mod
?>
