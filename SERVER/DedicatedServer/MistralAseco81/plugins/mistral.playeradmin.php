<?php
Aseco::registerEvent('onPlayerServerMessageAnswer', 'mistral_message_answer');
Aseco::addChatCommand('pa', 'Player administration (Admins only)');

global $pa_page, $pa_perpage, $pa_list, $pa_nick, $pa_dark, $blacklist, $guestlist;

function chat_pa($aseco, $command)
	{
	global $pa_page, $pa_list, $pa_nick, $pa_dark;
	$pa_page = 1;
	$pa_list=array();
	$pa_nick=true;
	$pa_dark=true;
	
	$admin = $command['author'];
	
	if (!$aseco->isAdmin($admin->login))
		{
		$aseco->console($admin->login . ' tried to player administration (no permission!)');
		$aseco->addCall('ChatSendToLogin', array($aseco->formatColors('{#error}You have to be in admin list to do that!'), $admin->login));
		$aseco->client->multiquery();
		return false;
		}

	$aseco->client->query('GetPlayerList', 100, 0);
	$players = $aseco->client->getResponse();

	$aseco->client->query('GetGuestList', 100, 0);
	$guests = $aseco->client->getResponse();
	
	$aseco->client->query('GetIgnoreList', 100, 0);
	$ignored = $aseco->client->getResponse();

	foreach ($players as $player)
		{
		$player['Nickname'] = getNicknameFromLogin($player['Login']);
		$isguest = false;
		foreach ($guests as $guest)
			{
			if ($player['Login'] == $guest['Login'])
				$isguest = true; 
			}
		$player['IsGuest'] = $isguest;
		$isignored = false;
		foreach ($ignored as $ignore)
			{
			if ($player['Login'] == $ignore['Login'])
				$isignored = true;
			}
		$player['IsIgnored'] = $isignored;
		$pa_list[] = $player;
		}

	mistral_pa_display($aseco, $admin->login);
	return true;
	}

function mistral_pa_display($aseco, $admin)
	{
	global $pa_page, $pa_perpage, $pa_list, $pa_nick, $pa_dark, $bad_players, $manialinkstack;
	
	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;
	
	$start = ($pa_page-1)*$pa_perpage;
	$count = sizeof($pa_list);
	$space = $pa_page*$pa_perpage;
	$end = $start+$pa_perpage-1;
	if ($end > $count-1)
		$end = $count-1;

	$nw = 26;
	$bww = 10;
	$gw = 10;
	$ww = 10;
	$iw = 10;
	$kw = 10;
	$baw = 10;
	$blw = 10;
	$width = $nw+$bww+$gw+$ww+$iw+$kw+$baw+$blw;
	$height = ($end-$start+1)*4+10;
	$hw = $width/2;
	$hh = $height/2;

	

	$manialink = "<?xml version='1.0' encoding='utf-8' ?><manialink id='60'><frame posn='-$hw $hh $manialinkstack'>";

	if ($pa_dark)
		$manialink .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";
	else
		{
		$manialink .= "<quad posn='0 0 -1.5' sizen='$width $height' style='Bgs1InRace' substyle='BgListLine'/>";
		$manialink .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow3'/>";
		}
		
	$manialink .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$manialink .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="$FFFMistral $88FPlayer $FFFAdministration"/>';
	$manialink .= "<quad posn='0 -4 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='".($nw+$bww/2)." -4.5 1' halign='center' textsize='2' text='BadW.'/>";	
	$manialink .= "<label posn='".($nw+$bww+$gw/2)." -4.5 1' halign='center' textsize='2' text='Guest'/>";	

	if ($pa_nick)
		$manialink .= 	"<label posn='0 -4 1' style='CardButtonSmall' action='20004' text='\$0F0Nickname\$z\$s/Login'/>";
	else
		$manialink .= 	"<label posn='0 -4 1' style='CardButtonSmall' action='20003' text='Nickname/\$0F0Login\$z\$s'/>";
		
	if ($pa_dark)
		$manialink .= 	"<label posn='".($width-26)." -4 1' style='CardButtonSmall' action='20005' text='Light'/>";
	else
		$manialink .= 	"<label posn='".($width-26)." -4 1' style='CardButtonSmall' action='20006' text='Dark'/>";

	$posn = -7;
	for($i=$start; $i<=$end; $i++ )
		{
		$player = $pa_list[$i];
		$nickname = $player['Nickname'];
		$isguest = $player['IsGuest'];
		$isignored = $player['IsIgnored'];
		$login = $player['Login'];

		$action = ($i+20700);
		if ($pa_nick)
			$content = htmlspecialchars($nickname);
		else
			$content = $login;
		
		$manialink .= "<quad posn='0 $posn 0' sizen='$nw 4' action='$action' style='Bgs1InRace' substyle='NavButton'/>";
		$manialink .= '<label posn="1 '.($posn-0.5).' 1" textsize="3" text="'.$content.'"/>';
		
		$bw = 0;
		if (in_array($login, $bad_players))
			{
			$bw = $bad_players[$login];
			if (!$bw)
				$bw = 0;
			}
		$manialink .= "<quad posn='$nw $posn 0' sizen='$bww 4' action='".($i+20800)."' style='Bgs1InRace' substyle='NavButton'/>";
		$manialink .= '<label posn="'.($nw+$bww/2).' '.($posn-0.5).' 1" halign="center" textsize="3" text="'.$bw.'"/>';

		$gt='$F00Add';
		if ($isguest)
			$gt='$0F0Remove';
		$manialink .= "<quad posn='".($nw+$bww)." $posn 0' sizen='$gw 4' action='".($i+20100)."' style='Bgs1InRace' substyle='NavButton'/>";
		$manialink .= '<label posn="'.($nw+$bww+$gw/2).' '.($posn-0.5).' 1" halign="center" textsize="3" text="'.$gt.'"/>';

		$manialink .= "<quad posn='".($nw+$bww+$gw)." $posn 0' sizen='$gw 4' action='".($i+20200)."' style='Bgs1InRace' substyle='NavButton'/>";
		$manialink .= '<label posn="'.($nw+$bww+$gw+$ww/2).' '.($posn-0.5).' 1" halign="center" textsize="3" text="$FF0Warn"/>';

		$ig='$F80Ignore';
		if ($isignored)
			$ig='$0F0Unignore';
		$manialink .= "<quad posn='".($nw+$bww+$gw+$ww)." $posn 0' sizen='$gw 4' action='".($i+20300)."' style='Bgs1InRace' substyle='NavButton'/>";
		$manialink .= '<label posn="'.($nw+$bww+$gw+$ww+$iw/2).' '.($posn-0.5).' 1" halign="center" textsize="3" text="'.$ig.'"/>';

		$manialink .= "<quad posn='".($nw+$bww+$gw+$ww+$iw)." $posn 0' sizen='$gw 4' action='".($i+20400)."' style='Bgs1InRace' substyle='NavButton'/>";
		$manialink .= '<label posn="'.($nw+$bww+$gw+$ww+$iw+$kw/2).' '.($posn-0.5).' 1" halign="center" textsize="3" text="$88FKick"/>';

		$manialink .= "<quad posn='".($nw+$bww+$gw+$ww+$iw+$kw)." $posn 0' sizen='$gw 4' action='".($i+20500)."' style='Bgs1InRace' substyle='NavButton'/>";
		$manialink .= '<label posn="'.($nw+$bww+$gw+$ww+$iw+$kw+$baw/2).' '.($posn-0.5).' 1" halign="center" textsize="3" text="$44FBan"/>';

		$manialink .= "<quad posn='".($nw+$bww+$gw+$ww+$iw+$kw+$baw)." $posn 0' sizen='$gw 4' action='".($i+20600)."' style='Bgs1InRace' substyle='NavButton'/>";
		$manialink .= '<label posn="'.($nw+$bww+$gw+$ww+$iw+$kw+$baw+$blw/2).' '.($posn-0.5).' 1" halign="center" textsize="3" text="$00FBlack"/>';
		
		$posn -=4;
		}

	$manialink .= "<quad posn='0 $posn 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	
	if ($pa_page > 1)
		$manialink .= 	"<label posn='0 $posn 1' style='CardButtonSmall' action='20000' text='Previous Page'/>";

	$manialink .= 	"<label posn='$hw $posn 1' halign='center' style='CardButtonSmall' action='20001' text='Close'/>";

	if ($space < $count)
		$manialink .= 	"<label posn='".($width-26)." $posn 1' style='CardButtonSmall' action='20002' text='Next Page'/>";
	
	$manialink .= "</frame></manialink>";
	
	$aseco->addcall('SendDisplayManialinkPageToLogin', array($admin, $manialink, 0, FALSE));
	}

// 20000-29999 supports maximum of 100 players
// 20000-20002 window control back, close, next
// 20100-20199 (un-)guest
// 20200-20299 warn
// 20300-20399 ignore
// 20400-20499 kick
// 20500-20599 ban
// 20600-20699 blacklist
// 20700-20799 player stats
function mistral_message_answer($aseco, $answer)
	{
	global $pa_page, $pa_list, $pa_nick, $pa_dark, $bad_players, $guestlist, $blacklist, $manialinkstack;
	$admin = $aseco->server->players->getPlayer($answer[1]);
	$i = $answer[2];

	if ($i < 20000)
		return;
	if ($i > 29999)	
		return;
		
	$i=$i-20000;
	$manialinkstack -= 3;
	
	// controls
	if ($i < 100)
		{
		switch ($i)
			{
			case 0: // previous page
			   	$pa_page -= 1;
			   	mistral_pa_display($aseco, $admin->login);
			   	return;
			   	break;
			case 1: // close
				$pa_list=array();
				$manialink = "<?xml version='1.0' encoding='utf-8' ?><manialink id='60'/>";
				$aseco->addcall('SendDisplayManialinkPageToLogin', array($admin->login, $manialink, 0, TRUE));
				return;
			   	break;
			case 2: // next page
			   	$pa_page += 1;
			   	mistral_pa_display($aseco, $admin->login);
			   	return;
			   	break;
			case 3: // nickname
				$pa_nick=true;
			   	mistral_pa_display($aseco, $admin->login);
			   	return;
			   	break;
			case 4: // login
				$pa_nick=false;
			   	mistral_pa_display($aseco, $admin->login);
			   	return;
			   	break;
			case 5: // light
				$pa_dark=false;
			   	mistral_pa_display($aseco, $admin->login);
			   	return;
			   	break;
			case 6: // dark
				$pa_dark=true;
			   	mistral_pa_display($aseco, $admin->login);
			   	return;
			   	break;
			case 7: // refresh
				$command['author']=$admin;
			   	chat_pa($aseco, $command);
			   	return;
			   	break;
			default:
				$aseco->console("Playeradmin: Undefined Control");
				return;
			}
		}
		
	$index=$i%100;
	$login = $pa_list[$index]['Login'];
	$nickname = $pa_list[$index]['Nickname'];
	$isguest = $pa_list[$index]['IsGuest'];
	$isignored = $pa_list[$index]['IsIgnored'];
	
	// (Un-)Guest
	if ($i < 200)
		{
		if ($isguest)
			{
			$aseco->addCall('RemoveGuest', array($login));
			$aseco->addCall('SaveGuestList', array($guestlist));
			$aseco->addCall('LoadGuestList', array($guestlist));
			$message = formatText('{#server}>> Admin removed {1} {#server}from guestlist!', $nickname);
			$pa_list[$index]['IsGuest']=false;
			}
		else
			{
			$aseco->addCall('AddGuest', array($login));
			$aseco->addCall('SaveGuestList', array($guestlist));
			$aseco->addCall('LoadGuestList', array($guestlist));
			$message = formatText('{#server}>> Admin added {1} {#server}to guestlist!', $nickname);
			$pa_list[$index]['IsGuest']=true;
			}
	
		$message = $aseco->formatColors($message);
		$aseco->addCall('ChatSendServerMessage', array($message));

	   	mistral_pa_display($aseco, $admin->login);
		return;
		}

	// Warn
	if ($i < 300)
		{
	   	mistral_pa_display($aseco, $admin->login);

		$manialinkstack += 3;
		if ($manialinkstack > 20)
			$manialinkstack = -30;

		$width = 28;
		$height = 30;
		$hw = $width/2;
		$hh = $height/2;

		$manialink = "<?xml version='1.0' encoding='utf-8' ?><manialink id='70'><frame posn='-$hw $hh $manialinkstack'>";

		$manialink .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";
		
		$manialink .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
		$manialink .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="$F00WARNING"/>';
		$manialink .= "<quad posn='0 -4 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
		$manialink .= "<label posn='$hw -4.5 1' halign='center' textsize='2' text='This is an administrative warning'/>";	
		$manialink .= "<label posn='$hw -8 1' halign='center' autonewline='1' sizen='".($width-2)." $height' textsize='2' text='You have done something against our server&apos;s policy. Not respecting other players, or using offensive language might result in a \$F00kick, or ban \$88Fthe next time\$z.\n\nThe server administrators.'/>";

		$manialink .= 	"<label posn='$hw -26 1' halign='center' style='CardButtonSmall' action='12' text='Close'/>";
		
		$manialink .= "</frame></manialink>";
		
		$aseco->addcall('SendDisplayManialinkPageToLogin', array($login, $manialink, 0, TRUE));

		$message = formatText('{#server}>> Admin warned {1}$z$s!', $nickname);
		$message = $aseco->formatColors($message);
		$aseco->addCall('ChatSendServerMessage', array($message));

		return;
		}

	// Ignore
	if ($i < 400)
		{
		if ($isignored)
			{
			$aseco->addCall('UnIgnore', array($login));
			$message = formatText('{#server}>> Admin removed {1} {#server}from ignorelist!', $nickname);
			$pa_list[$index]['IsIgnored']=false;
			}
		else
			{
			$aseco->addCall('Ignore', array($login));
			$message = formatText('{#server}>> Admin added {1} {#server}to ignorelist!', $nickname);
			$pa_list[$index]['IsIgnored']=true;
			}
	
		$message = $aseco->formatColors($message);
		$aseco->addCall('ChatSendServerMessage', array($message));

	   	mistral_pa_display($aseco, $admin->login);
		return;
		}
	
	// Kick
	if ($i < 500)
		{
		$aseco->addCall('Kick', array($login));
		$message = formatText('{#server}>> Admin kicked {1}{#server}!', $nickname);
		$message = $aseco->formatColors($message);
		$aseco->addCall('ChatSendServerMessage', array($message));

	   	mistral_pa_display($aseco, $admin->login);
		return;
		}

	// Ban
	if ($i < 600)
		{
		$aseco->addCall('Ban', array($login));
		$aseco->addCall('Kick', array($login));
		$message = formatText('{#server}>> Admin banned {1}{#server}!', $nickname);
		$message = $aseco->formatColors($message);
		$aseco->addCall('ChatSendServerMessage', array($message));

	   	mistral_pa_display($aseco, $admin->login);
		return;
		}

	// Blacklist
	if ($i < 700)
		{
		$aseco->addCall('BlackList', array($login));
		$aseco->addCall('SaveBlackList', array($blacklist));
		$aseco->addCall('LoadBlackList', array($blacklist));
		$aseco->addCall('Kick', array($login));
		$message = formatText('{#server}>> Admin blacklisted {1}{#server}!', $nickname);
		$message = $aseco->formatColors($message);
		$aseco->addCall('ChatSendServerMessage', array($message));

	   	mistral_pa_display($aseco, $admin->login);
		return;
		}
	
	// Stats
	if ($i < 800)
		{
		$theplayer = $aseco->server->players->getPlayer($login);
	   	mistral_pa_display($aseco, $admin->login);
		displayStats($aseco, $admin, $theplayer);
		return;
		}
	
	// Reset Badwords
	if ($i < 900)
		{
		$bad_players[$login] = 0;

	   	mistral_pa_display($aseco, $admin->login);
		return;
		}
	}
?>