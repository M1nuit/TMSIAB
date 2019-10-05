<?php
/**
 * Chat plugin.
 * Vote for a track and display current score of it.
 */

Aseco::addChatCommand("vote", "votes for a track, requires parameter (0-10)");
Aseco::addChatCommand("score", "displays score of a track");

function chat_vote($aseco, $command) {
  
  // check if parameter is correct ...
  if (is_numeric($command['params']) && $command['params'] >= 0 && $command['params'] <= 10) {
    $score = $command['params'];
  } else {
    
    // replace colors ...
    $message = $aseco->formatColors("{#error}You have to apply a numeric parameter (0-10)");
    
    // send the message ...
    $aseco->addCall("ChatSendToLogin", array($message, $command['author']->login));
    return;
  }
  
  // make sure player hasn't voted alredy ...
  if ($command['author']->hasvoted) {
    
    // replace colors ...
    $message = $aseco->formatColors("{#error}You voted already for this track");
    
    // send the message ...
    $aseco->addCall("ChatSendToLogin", array($message, $command['author']->login));
    return;
  }
  
  // react on the vote ...
  $aseco->server->challenge->score += $score;
  $aseco->server->challenge->votes++;
  $command['author']->hasvoted = true;
  
  // replace parameters ...
  $message = formatText("{#interact}You have voted this track with a {#highlite}{1}",
  $score);
  
  // replace colors ...
  $message = $aseco->formatColors($message);
  
  // send the message ...
  $aseco->addCall("ChatSendToLogin", array($message, $command['author']->login));

  // release a new Event ...
  $aseco->releaseEvent("onPlayerVote", $command);
}

function chat_score($aseco, $command) {
  
  // calculate average score of the current track ...
  if ($aseco->server->challenge->votes && $aseco->server->challenge->score) {
    $avg_score = $aseco->server->challenge->score/$aseco->server->challenge->votes;
  } else {
    $avg_score = 0;
  }
  
  // replace parameters ...
  $message = formatText("{#interact}Current track has an avarage score of {#highlite}{1}",
  $avg_score);
  
  // replace colors ...
  $message = $aseco->formatColors($message);
  
  // send the message ...
  $aseco->addCall("ChatSendToLogin", array($message, $command['author']->login));
}
?>