<?php
/**
 * Chat plugin.
 * Vote for a track and display current score of it.
 */

Aseco::addChatCommand("karma", "displays karma for the current track");
Aseco::addChatCommand("++", "increases karma for the current track");
Aseco::addChatCommand("--", "decreases karma for the current track");
Aseco::registerEvent("onChat", "check4Karma");

function check4Karma(&$aseco, &$command) {
	if ($command[2] == "++")
		{
		KarmaVote($aseco, $command, 1);
		rasp_karma($aseco->server->challenge);
		}
	elseif ( $command[2] == "--" )
		{
		KarmaVote($aseco, $command, -1);
		rasp_karma($aseco->server->challenge);
		}
}

function KarmaVote(&$aseco, &$command, $vote) {
	$login_id = $command['author']->login;
	$aseco->console_text("Registering vote....");
	$query = "SELECT Id FROM players WHERE Login='" . $login_id . "'";
	$res = mysql_query($query);
	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_row($res);
		$pid = $row[0];
	} else {
		$aseco->console_text("no player!");
	 	return;
	}
	mysql_free_result($res);

	$query2 = "SELECT Id, Score FROM rs_karma WHERE PlayerId=" . $pid . " AND ChallengeId=" . $aseco->server->challenge->id;
	$res2 = mysql_query($query2);
	if (mysql_num_rows($res2) > 0) {
		$row = mysql_fetch_object($res2);
		if ($row->Score == $vote) {
			$message = $aseco->formatColors("{#highlite} You have already voted for this track");
			$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login_id));
			$aseco->client->multiquery();
		} else {
			$query3 = "UPDATE rs_karma SET Score=" . $vote . " WHERE Id=".$row->Id;
			mysql_query($query3);
			if (mysql_affected_rows() < 1) {
				$msg = $aseco->formatColors("{#highlite} Vote Failed!");
				$aseco->addCall('ChatSendServerMessageToLogin', array($msg, $login_id));
				$aseco->client->multiquery();
			} else {
				$msg = $aseco->formatColors("{#highlite} Vote Successful!");
				$aseco->addCall('ChatSendServerMessageToLogin', array($msg, $login_id));
				$aseco->client->multiquery();
				chat_karma($aseco, $command);
			}
		}
	} else {
		$query3 = "INSERT INTO rs_karma (Score, PlayerId, ChallengeId) VALUES ($vote, ".$pid.", ".$aseco->server->challenge->id.")";
		mysql_query($query3);
		if (mysql_affected_rows() < 1) {
			$msg = $aseco->formatColors("{#highlite} Vote Failed!");
			$aseco->addCall('ChatSendServerMessageToLogin', array($msg, $login_id));
			$aseco->client->multiquery();
		} else {
			$msg = $aseco->formatColors("{#highlite} Vote Successful!");
			$aseco->addCall('ChatSendServerMessageToLogin', array($msg, $login_id));
			$aseco->client->multiquery();
			chat_karma($aseco, $command);
		}
	}
	mysql_free_result($res2);
}

function displayKarmaVote($votestring, &$command)
	{
	global $aseco;
	$nick = $command['author']->nickname;
	if ( $votestring == "++" )
		{
		$karma = "good";
		}
	else
		{
		$karma = "bad";
		}
	$message = formatText("$nick thinks this track is $karma ({#highlite}/$votestring)");
	$message = $aseco->formatColors($message);
	$aseco->addCall('ChatSendServerMessage', array($message));
	$aseco->client->multiquery();
	}  //  displayKarmaVote

function chat_plusplus(&$aseco, &$command)
	{
	KarmaVote($aseco, $command, 1);
	//	displayKarmaVote("++", $command);
	}  //  chat_plusplus

function chat_dashdash($aseco, $command)
	{
	KarmaVote($aseco, $command, -1);
	//	displayKarmaVote("--", $command);
	}  //  chat_plusplus

function rasp_karma(&$challenge) {
	global $aseco;

	$karma = getKarma($challenge);
	// replace parameters ...
	$message = formatText("{#interact}Current Track Karma: {#highlite}{1}", $karma);

	// replace colors ...
	$message = $aseco->formatColors($message);

	// send the message ...
	$aseco->addCall('ChatSendServerMessage', array($message));
	$aseco->client->multiquery();
}

function chat_karma(&$aseco, &$command) {
	$karma = getKarma($aseco->server->challenge);

	// replace parameters ...
	$message = formatText("{#interact}Current Track Karma: {#highlite}{1}", $karma);

	// replace colors ...
	$message = $aseco->formatColors($message);

	// send the message ...
	$aseco->addCall('ChatSendServerMessageToLogin', array($message, $command['author']->login));
	$aseco->client->multiquery();
}


function getKarma(&$challenge) {
	global $aseco;
	$query = "SELECT sum(score) as karma FROM rs_karma WHERE ChallengeId=" . $challenge->id;
	$res = mysql_query($query);
	if ( mysql_num_rows($res) == 1 )
		{
		$row = mysql_fetch_row($res);
		$karma = $row[0];
		}
	else
		{
		$karma = 0;
		}

	if ( !isset($karma) )
		{
		$karma = 0;
		}
	mysql_free_result($res);
	return $karma;
}
?>
