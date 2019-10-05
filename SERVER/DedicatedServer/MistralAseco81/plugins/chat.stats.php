<?php
/**
 * Chat plugin.
 * Displays TOP records of the currently played track.
 */

Aseco::addChatCommand("stats", "displays statistics of current player");

function chat_stats(&$aseco, &$command)
	{
  // showing stats for TMN
    $stats = "\$333Stats for " . $command['author']->nickname . "\$333\n\n";
	$stats .= "Server Date: " . strftime("%b %d, %Y") . "\n";
	$stats .= "Server Time: " . strftime("%I:%M:%S%p") . "\n";
    $stats .= "Time Played: ".formatTimeH($command['author']->getTimePlayed()."000", false)."\n";
    $stats .= "Races Won:   ".$command['author']->getWins();
	popup_msg($command['author']->login, $stats);
	}
?>
