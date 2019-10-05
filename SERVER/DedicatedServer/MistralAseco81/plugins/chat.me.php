<?php
/**
 * Chat plugin.
 * Will build a chat message starting with the player's login.
 */

Aseco::addChatCommand("me", "can be used to express emotions");

function chat_me(&$aseco, $command) {
  	
	$player=$command['author'];
	$login=$player->login;

	$aseco->client->query('GetIgnoreList', 100, 0);
	$ignored = $aseco->client->getResponse();

	foreach ($ignored as $ignore)
		{
		if ($ignore['Login'] == $login)
			{
			$message = '{#server}> You are ignored.';
			$message = $aseco->formatColors($message);
			$aseco->addCall("ChatSendServerMessageToLogin", array($message, $login));
			return;
			}
		}
  
  // replace parameters ...
  $message = formatText('$i{1} $z$i{#emotic}{2}',
  $command['author']->nickname,
  $command['params']);
  
  // replace colors ...
  $message = $aseco->formatColors($message);
  
  // send the message ...
  $aseco->addCall("ChatSendServerMessage", array($message));
}
?>