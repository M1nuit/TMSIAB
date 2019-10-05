<?php

Aseco::registerEvent("onChat", "mistral_badwords");

global $mistral_badwordsallowed, $bad_Words, $bad_players;

$bad_players = array();

function mistral_badwords($aseco,$command)
	{
	global $mistral_badwordsallowed, $bad_Words, $bad_players;

	$player = $aseco->server->players->getPlayer($command[1]);
	if (!$player)
		return;
	$login = $player->login;
	if ( !in_array($login, $bad_players) )
		{
		$bad_players[] = $login;
		$bad_players[$login] = 0;
		}
	
	$nick = $player->nickname;

	if($nick=='')
		{
		return;
		}

	$texte=$propre=stripColors($command[2]);
	$texte=strtolower($texte);
	$texte=str_replace("|_|","u",$texte);
	$texte=str_replace("|<","k",$texte);
	$texte=str_replace("@","a",$texte);
	$texte=str_replace("\$","s",$texte);
	$texte=str_replace("§","s",$texte);
	$texte=str_replace("!","i",$texte);
	$texte=str_replace("1","l",$texte);
	$texte=str_replace("3","e",$texte);
	$texte=str_replace("4","a",$texte);
	$texte=str_replace("5","s",$texte);
	$texte=str_replace("6","b",$texte);
	$texte=str_replace("7","t",$texte);
	$texte=str_replace("8","b",$texte);
	$texte=str_replace("9","g",$texte);
	$texte=str_replace("0","o",$texte);
	$sz = chr(227).chr(159);
	$texte=str_replace($sz,"ss",$texte);
	$texte=ereg_replace("(^/tm )","",$texte);
	$texte=ereg_replace("(^/team msg )","",$texte);
	$texte=ereg_replace("([^a-z ]+)","",$texte);
	$lettre="a";
	while($lettre<"z")
		{
		$texte=ereg_replace("[".$lettre."]{2,}",$lettre,$texte);
		$lettre++;
		}
	$texte=ereg_replace("( +)(.{1,1})( +)","\\2",$texte);
	$texte=ereg_replace("(^)(.{1,1})( +)","\\2",$texte);
	$texte=ereg_replace("( +)(.{1,1})($)","\\2",$texte);

	$max=$mistral_badwordsallowed;

	foreach($bad_Words As $mot)
		{
		if (ereg ("(^|[^a-z])".$mot."($|[^a-z])", $texte))
			{
			$bad_players[$login] = $bad_players[$login]+1;
			$message = '{#server}>> {#error}No badwords please!';
			if($bad_players[$login] > $max)
				{
				$max2=$max*2;
				$message .= ' {#message}['.$nick.'$z$s{#message} : ';
				$message .= '{#highlite}'.$bad_players[$login].'{#message}/{#highlite}'.$max2;
				$message .= ' {#error}to ban{#message}]';
				}
			else
				{
				$message .= ' {#message}['.$nick.'$z$s{#message} : ';
				$message .= '{#highlite}'.$bad_players[$login].'{#message}/{#highlite}'.$max;
				$message .= ' {#error}to kick{#message}]';
				}
			$message2 = '{#server}>> {#message}[{#highlite}"'.$mot.'"{#message} is a forbidden word].';
			$aseco->addCall("ChatSendServerMessageToLogin", array($aseco->formatColors($message2),$login));
			if($bad_players[$login] == 2*$max)
				{
				$message .=' {#message}({#error}banned{#message})';
				}
			if($bad_players[$login] == $max)
				{
				$message .=' {#message}({#error}kicked{#message})';
				}
			$aseco->addCall("ChatSendServerMessage", array($aseco->formatColors($message)));
			if($bad_players[$login] == 2*$max)
				{
				$aseco->addCall("Ban", array($login));
				$aseco->addCall("Kick", array($login));
				$bad_players[$login]=0;
				}
			if($bad_players[$login] == $max)
				{
				$aseco->addCall("Kick", array($login));
				}
			return;
			}
		}
	}

?>