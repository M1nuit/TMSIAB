<?php
//* chat.player.php - Player Chat Commands
//* Version:   0.9.0
//* Coded by:  libero6
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('Chat', 'playerchat');
control::RegisterEvent('ManialinkPageAnswer', 'playerchat_manialinkPageAnswer');

function playerchat($control, $PlayerChat){
	//Get Infos
	$Command = explode(' ', $PlayerChat[2]);
	$control->client->query('GetDetailedPlayerInfo', $PlayerChat[1]);
	$CommandAuthor = $control->client->getResponse();
	
	//Commands
	if($Command[0]=='/players'){
		$control->show_playerlist($CommandAuthor['Login'], false, 0);
	}
 	elseif($Command[0]=='/afk'){
 	 $control->chat_with_nick('$6F3A$5F3w$5E2a$4E2y$3E2 $3D1f$2D1r$1D1o$1C0m$0C0 $0C0K$2C2e$4D4y$6D6b$8E8o$9E9a$BEBr$DFDd$FFF!', $CommandAuthor['Login']);
 	 $control->client->query('ForceSpectator', $CommandAuthor['Login'], 1);
	 $control->client->query('SendDisplayManialinkPageToLogin', $CommandAuthor['Login'], '<?xml version="1.0" encoding="UTF-8" ?>
	 <manialink id="79999">
	 <quad posn="0 -27 1" sizen="25 4" halign="center" style="Bgs1InRace" substyle="NavButtonBlink" action="79999" />
	 <label posn="0 -28 2" halign="center" style="TextPlayerCardName" text="$o$fffClick here to play!" action="79999" />
	 </manialink>', 0, false);
	 }
	elseif($Command[0]=='/lol'){
		$control->chat_with_nick('$F00L$F21o$F51o$F720$FA20$FC3O$FC3O$FA20$F820$F71o$F51o$F30L', $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/brb'){
		$control->chat_with_nick('$00FB$02Fe$03E $05ER$06Di$08Dg$09Ch$09Ct$3AD $5BDB$8CEa$ADEc$DEFk$FFF!', $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/gga'){
		$control->chat_with_nick('$CC0G$AC0o$8C0o$6C0d$4C0 $2C0G$0C0a$0C0m$3B0e$590 $880A$A60l$D50l$F30!', $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/fox'){
		$control->chat_with_nick('$o$09fFOX RulZzz', $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/libero'){
		$control->chat_with_nick('$o$06Fғox$FFF»$FFFﾚĬわe$06F®$FFFO', $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/cyril'){
		$control->chat_with_nick('$o$06Fғox$FFF» $h[ci]C$06fу$fffяιL', $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/help'){
		$control->chat_with_nick('', $CommandAuthor['Login']);
	}
	elseif($Command[0]=='/me'){
		$message = '';
		for($i = 1; isset($Command[$i]); $i++)
		{
			$message = $message.$Command[$i].' ';
		}
		$message = $control->rgb_decode($message);
		$newmessage = '';
		$color = '$06f';
		$mbegan = false;
		for($i2 = 0; true; $i2++)
		{
			if(substr($message, $i2, 1) == ' ' && $mbegan == false) {}
			else 
			{
				$newmessage = $newmessage.$color.substr($message, $i2, 1);
				$mbegan = true;
				if($color == '$06f') $color = '$fff';
			}
			if(substr($message, $i2, 1) == '') break;
		}
		$control->chat_message(htmlspecialchars('$i$fff'.$CommandAuthor['NickName'].'$z$i$s$06f  '.$newmessage));
	}
}

function playerchat_manialinkPageAnswer($control, $ManialinkPageAnswer){
    if($ManialinkPageAnswer[2] == '79999'){
	    $control->client->query('ForceSpectator', $ManialinkPageAnswer[1], 2);
		$control->close_ml(79999, $ManialinkPageAnswer[1]);
	}
}
?>