<?php
/************************************************************************************
/* 
/* ASECO plugin to evaluate tracks
/* 
/* (C) 2007 by Mistral
/* 
/************************************************************************************/
Aseco::registerEvent("onEndRace", "trackevalEndRace");
Aseco::registerEvent("onNewChallenge", "trackevalNewChallenge");
Aseco::registerEvent("onPlayerFinish", "trackevalFinish");
Aseco::registerEvent('onPlayerServerMessageAnswer', 'trackevalResponse');

global $eval_threshold, $track2eval, $trackkeep, $trackdontcare, $trackdelete, $tracknotmyenv, $trackevaluating, $keepcount, $dontcarecount, $deletecount, $notmyenvcount;

// dont touch:
$track2eval = '';		// uid of last track
$trackkeep = -1;
$trackdontcare = -2;
$trackdelete = -3;
$tracknotmyenv = -4;
$trackevaluating = array();

function getEvalCount($id, $val)
	{
	$ret = 0;
	
	$query = "SELECT COUNT(Eval) FROM mistral_trackeval WHERE Eval=$val AND ChallengeId=$id;";
	$result = mysql_query($query);
	if ($result)
		{
		$row = mysql_fetch_row($result);
		$ret = $row[0];
		mysql_free_result($result);
		}
	
	return $ret;
	}

// manialink response from track evalution
function trackevalResponse($aseco, $answer)
	{
	global $trackevaluating, $trackkeep, $trackdontcare, $trackdelete, $tracknotmyenv, $eval_threshold;
	
	$i = $answer[2];
	if ($i<30045)
		return;
	if ($i>30049)
		return;

	$login = $answer[1];
	$uid = $trackevaluating[$login];
	unset($trackevaluating[$login]);
	
	switch ($i)
		{
		// not my environment
		case 30045:
			setTrackeval($aseco, $uid, $login, $tracknotmyenv);
			break;
		// keep it
		case 30046:
			setTrackeval($aseco, $uid, $login, $trackkeep);
			break;
		// i dont care
		case 30047:
			setTrackeval($aseco, $uid, $login, $trackdontcare);
			break;
		// remove it
		case 30048:
			setTrackeval($aseco, $uid, $login, $trackdelete);
			break;
		case 30049:
			setTrackeval($aseco, $aseco->server->challenge->uid, $login, $eval_threshold+1);
			break;
		default: 
			break;
		}

	$player = $aseco->server->players->getPlayer($login);
	$player->mistral['displayPlayerInfo']=true;
	displayPlayerInfo($aseco, $player);
	}

// ask for evaluation
function trackevalNewChallenge($aseco, $challenge)
	{
 	global $track2eval, $trackkeep, $trackdontcare, $trackdelete, $tracknotmyenv, $eval_threshold, $trackevaluating, $keepcount, $dontcarecount, $deletecount, $notmyenvcount;
 	
 	// Calculate values for CURRENT challenge
 	$uid = $aseco->server->challenge->uid;
	$id = getTrackIDfromUID($uid);

	$keepcount = getEvalCount($id, $trackkeep);
	$dontcarecount = getEvalCount($id, $trackdontcare);
	$deletecount = getEvalCount($id, $trackdelete);
	$notmyenvcount = getEvalCount($id, $tracknotmyenv);

	// Evaluate PREVIOUS challenge
	$uid = $track2eval;
	$name = getTracknameFromUId($uid);

	if ($uid == '')
		return;

	$manialink = "<?xml version='1.0' encoding='utf-8' ?>
			<manialink posx='0.4' posy='0.12'>
			<type>default</type>
			<format textsize='1'/>
			<background bgcolor='222E' bgborderx='0.03' bgbordery='0.03'/>
			<line><cell width='0.8' bgcolor='8888'><text halign='center' textsize='2'>Evaluate ".sub_maniacodes($name)."</text></cell></line>
			<line height='.02'><cell></cell></line>";
	$manialink .= "<line><cell><text halign='center' textcolor='FFFF'>You finished the previous track at least $eval_threshold times now.\n\nPlease tell us what you think about the track. Please don't tell us to delete the track,\njust because you don't like the environment - be fair.</text></cell></line><line height='.02'><cell></cell></line>";
	$manialink .= "<line height='.04'><cell width='0.8' bgcolor='008E'><text halign='center' action='30045'>Not my favorite environment</text></cell></line><line height='.01'><cell></cell></line>";
	$manialink .= "<line height='.04'>
		<cell width='0.2' bgcolor='080E'><text halign='center' action='30046'>Keep it</text></cell>
		<cell width='0.1'></cell>
		<cell width='0.2' bgcolor='880E'><text halign='center' action='30047'>I don't care</text></cell>
		<cell width='0.1'></cell>
		<cell width='0.2' bgcolor='800E'><text halign='center' action='30048'>Remove it</text></cell>
		</line><line height='.01'><cell></cell></line>";
	$manialink .= "<line height='.04'><cell width='0.8' bgcolor='888E'><text halign='center' action='12'>Ask me again next time</text></cell></line>";
	$manialink .= "</manialink>";

	foreach($aseco->server->players->player_list as $player)
		{
		$login = $player->login;
		$eval = getTrackeval($aseco, $uid, $login);
		if ($eval >= $eval_threshold)
			{
			$player->mistral['displayPlayerInfo']=false;
			$trackevaluating[$login] = $uid;			
			$aseco->addcall('SendDisplayManialinkPageToLogin', array($login, $manialink, 0, TRUE));		
			}
		}
	}

// get current evaluation
function getTrackeval($aseco, $uid, $login)
	{
	$cid = getTrackIDfromUID($uid);
	$pid = getPlayerIdFromLogin($login);

	$query = "SELECT Eval FROM mistral_trackeval WHERE ChallengeId=$cid AND PlayerId=$pid;";
	$result = mysql_query($query);
	if (mysql_num_rows($result) == 0)
		{
		$eval = 0;
		}
	else
		{
		$row = mysql_fetch_row($result);
		$eval = $row[0];
		}
	mysql_free_result($result);
	
	return $eval;
	}

// set current evaluation
function setTrackeval($aseco, $uid, $login, $eval)
	{
	$cid = getTrackIDfromUID($uid);
	$pid = getPlayerIdFromLogin($login);

	$query="UPDATE mistral_trackeval SET Eval=$eval WHERE ChallengeId=$cid and PlayerId=$pid";
	if (!mysql_query($query))
		{
		$aseco->console("Cannot update entry: $query");
		}
	if (mysql_affected_rows() == 0)
		{
		$query = "INSERT INTO mistral_trackeval (ChallengeId, PlayerId, Eval) VALUES ($cid,$pid,$eval);";
		if (!mysql_query($query))
			{
			$aseco->console("Cannot create entry: $query");
			}
		}
	}

// Remember UID of last track
function trackevalEndRace($aseco, $challenge)
	{
	global $track2eval;
	$track2eval = $aseco->server->challenge->uid;
	}

// Increase finishcount if not evaluated already (>=0)
function trackevalFinish($aseco, $finish_item)
	{
	if ($finish_item->score == 0)
		return;

	$uid = $aseco->server->challenge->uid;
	$login = $finish_item->player->login;

	$eval = getTrackeval($aseco, $uid, $login);
	if ($eval < 0)
		return;
	
	setTrackeval($aseco, $uid, $login, $eval+1);
	$player = $aseco->server->players->getPlayer($login);
	displayPlayerInfo($aseco, $player);
	}

?>