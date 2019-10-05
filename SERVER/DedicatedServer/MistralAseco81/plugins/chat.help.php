<?php
/**
 * Chat plugin.
 * Displays help for public chat commands.
 */

Aseco::addChatCommand('help', 'shows help');

function chat_help(&$aseco, &$command) {

	display_help($command['author']);
}
?>
