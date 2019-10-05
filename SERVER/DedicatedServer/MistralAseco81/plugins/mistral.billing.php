<?php
Aseco::registerEvent('onBillUpdated', 'mistralBillUpdated');
Aseco::registerEvent("onNewChallenge", "mistralBillNewChallenge");
Aseco::registerEvent('onPlayerFinish', 'mistralAddFinish');
Aseco::addChatCommand('donate', 'donates coppers for the lottery');

global $bills, $payee, $partner, $minwin, $maxwin, $finishminplayers, $winpercentage, $finishlist, $onlineminplayers, $tracksintransaction, $servername;

$bills = array();
$tracksintransaction = array();
$finishlist = array();

// add jukebox fee to db overall for player
function mistralAddJukebox($login, $fee)
{
	if ($login == "")
		return;

	$dbid = getPlayerIdFromLogin($login);

	// bah - quick and dirty to ensure player is included in backup table
	$query="INSERT into mistral_playerwins (PlayerId, Wins, Donation, Won, Jukebox) VALUES ($dbid, 0, 0, 0, 0);";
	mysql_query($query);
	
	$query = "update mistral_playerwins set jukebox=jukebox+$fee where playerid=$dbid;";
	mysql_query($query);
}

// add donation to db overall for player
function mistralAddDonation($login, $donation)
{
	if ($login == "")
		return;
		
	$dbid = getPlayerIdFromLogin($login);

	// bah - quick and dirty to ensure player is included in backup table
	$query="INSERT into mistral_playerwins (PlayerId, Wins, Donation, Won, Jukebox) VALUES ($dbid, 0, 0, 0, 0);";
	mysql_query($query);
	
	$query = "update mistral_playerwins set donation=donation+$donation where playerid=$dbid;";
	mysql_query($query);
}

// add won coppers to db overall for player
function mistralAddWon($login, $won)
{
	if ($login == "")
		return;

	$dbid = getPlayerIdFromLogin($login);

	// bah - quick and dirty to ensure player is included in backup table
	$query="INSERT into mistral_playerwins (PlayerId, Wins, Donation, Won, Jukebox) VALUES ($dbid, 0, 0, 0, 0);";
	mysql_query($query);
	
	$query = "update mistral_playerwins set won=won+$won where playerid=$dbid;";
	mysql_query($query);
}

// /donate <coppers> command
function chat_donate($aseco, $command)
{
	$player = $command['author'];
	$param =$command['params'];
	
	// check if parameter is correct ...
	if (!is_numeric($param))
	{
    	$message = $aseco->formatColors('{#error}Use "/donate <number>" to donate coppers for the lottery.');
    	$aseco->addCall("ChatSendToLogin", array($message, $player->login));
	    return;
	}

	// check if parameter is too long ...
	if (strlen($param)>7)
	{
    	$message = $aseco->formatColors('{#error}In your dreams.');
    	$aseco->addCall("ChatSendToLogin", array($message, $player->login));
	    return;
	}

	$donation = $param+1-1;
	
	if ($donation<1)
	{
    	$message = $aseco->formatColors('{#error}Nice try. Please donate only positive values.');
    	$aseco->addCall("ChatSendToLogin", array($message, $player->login));
	    return;
	}
  	mistralDonate($aseco, $player, $donation);
}

// just add player to list - if player finishes more than once he has higher chances (often in the list)
function mistralAddFinish($aseco, $finish_item)
{
 	global $finishlist;
 	
	if ($finish_item->score > 0)
		$finishlist[] = $finish_item->player;	
}

function mistralDonate($aseco, $player, $coppers)
{
	mistralSendBill($aseco, $player, $coppers, "Donate");
}

function mistralDonationWindow($aseco, $player)
{
	global $manialinkstack;
	
	if ($player->login == "")
		return;

	$manialinkstack += 3;
	if ($manialinkstack > 20)
		$manialinkstack = -30;

	$width = 90;
	$height = 55;
	$hw = $width/2;
	$tw = $width/3;
	$ttw = 2*$tw;
	$hh = $height/2;

	$manialink = "<?xml version='1.0' encoding='utf-8' ?><manialink id='50'><frame posn='-$hw $hh $manialinkstack'>";

	$manialink .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";

	$manialink .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
	$manialink .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="Donations"/>';
	$manialink .= "<quad posn='0 -4 0' sizen='$tw 5' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='".($tw/2)." -4.5 1' halign='center' textsize='2' text='Top Donators'/>";
	$manialink .= "<label posn='".($tw/2)." -7 1' halign='center' textsize='1' text='Thank you!'/>";
	$manialink .= "<quad posn='$tw -4 0' sizen='$tw 5' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='".(3*$tw/2)." -4.5 1' halign='center' textsize='2' text='Make a donation'/>";
	$manialink .= "<label posn='".(3*$tw/2)." -7 1' halign='center' textsize='1' text='to server for lottery'/>";
	$manialink .= "<quad posn='$ttw -4 0' sizen='$tw 5' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='".(5*$tw/2)." -4.5 1' halign='center' textsize='2' text='Top Winners'/>";	
	$manialink .= "<label posn='".(5*$tw/2)." -7 1' halign='center' textsize='1' text='Congratulations!'/>";

	$donation = array();
	$donation[0]->text = '10 coppers';
	$donation[0]->action = 30014;
	$donation[1]->text = '20 coppers';
	$donation[1]->action = 30015;
	$donation[2]->text = '50 coppers';
	$donation[2]->action = 30016;
	$donation[3]->text = '100 coppers';
	$donation[3]->action = 30017;
	$donation[4]->text = '200 coppers';
	$donation[4]->action = 30018;
	$donation[5]->text = '500 coppers';
	$donation[5]->action = 30019;
	$donation[6]->text = '1000 coppers';
	$donation[6]->action = 30020;
	$donation[7]->text = '2000 coppers';
	$donation[7]->action = 30021;
	$donation[8]->text = '5000 coppers';
	$donation[8]->action = 30022;
	$donation[9]->text = '10000 coppers';
	$donation[9]->action = 30023;
	
	$winners = getTopWinner();
	$donators = getTopDonators();
	
	$query = "SELECT sum(won) from mistral_playerwins;";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$oc = $row[0];
	$oc = number_format($oc, 0, ',', '.');
	mysql_free_result($result);
	
	$query = "SELECT sum(jukebox) from mistral_playerwins;";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$oj = $row[0];
	$oj = number_format($oj, 0, ',', '.');
	mysql_free_result($result);
	
	// content
	$posn = -10;
	for ($i=0; $i<10; $i++)
	{
		$don = number_format($donators[$i]->donation, 0, ',', '.');
		$won = number_format($winners[$i]->won, 0, ',', '.');
		
		$manialink .= "<quad posn='1 $posn 0' sizen='".($tw-1)." 3' style='BgsPlayerCard' substyle='BgRacePlayerName'/>";
		$manialink .= '<label posn="'.($tw/2).' '.($posn-0.5).' 1" halign="center" textsize="2" text="'.htmlspecialchars(getNicknameFromId($donators[$i]->id)).' $z('.$don.')"/>';
		
		$manialink .= "<label posn='$hw $posn 1' halign='center' action='".$donation[$i]->action."' style='CardButtonSmall' text='".$donation[$i]->text."'/>";

		$manialink .= "<quad posn='".(2*$tw)." $posn 0' sizen='".($tw-1)." 3' style='BgsPlayerCard' substyle='BgRacePlayerName'/>";
		$manialink .= '<label posn="'.(5*$tw/2).' '.($posn-0.5).' 1" halign="center" textsize="2" text="'.htmlspecialchars(getNicknameFromId($winners[$i]->id)).' $z('.$won.')"/>';
	
	/*	
		$clink .= "<line height='0.05'>";
		$clink .= "<cell width='0.5' bgcolor='888E'><text halign='center'>".."\$z\$000 ($don)</text></cell>";
		$clink .= "<cell width='0.02'><text></text></cell>";
		$clink .= "<cell width='0.3' bgcolor='222E'><text halign='center' action='""'>".."</text></cell>";	
		$clink .= "<cell width='0.02'><text></text></cell>";
		$clink .= "<cell width='0.5' bgcolor='888E'><text halign='center'>".htmlspecialchars(getNicknameFromId($winners[$i]->id))."\$z\$000 ($won)</text></cell>";
		$clink .= "</line>";
		$clink .= "<line height='0.01'><cell width='1.34'><text></text></cell></line>";	
		*/
		$posn-=4;
	}

	$posn--;
	
	$manialink .= "<quad posn='0 ".($posn+1)." 0' sizen='$tw 5' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='".($tw/2)." ".($posn-0.5)." 1' halign='center' textsize='2' text='Overall jukebox fee: $oj'/>";
	$manialink .= "<label posn='$hw $posn 0' halign='center' style='CardButtonSmall' text='Close' action='12'/>";
	$manialink .= "<quad posn='".(2*$tw)." ".($posn+1)." 0' sizen='$tw 5' style='Bgs1InRace' substyle='BgTitle3_2'/>";
	$manialink .= "<label posn='".(5*$tw/2)." ".($posn-0.5)." 1' halign='center' textsize='2' text='Overall coppers won: $oc'/>";

	$manialink .= "</frame></manialink>";

	$aseco->addcall('SendDisplayManialinkPageToLogin', array($player->login, $manialink, 0, TRUE));
}

function mistralBillPayed($aseco, $command)
{
	global $bills;
	
	$billId = $command[0];
	
	$player = $bills[$billId]->player;
	$action = $bills[$billId]->action;
	$price = $bills[$billId]->price;
	
	switch ($action)
	{
		case "JBadd":	$jid = $bills[$billId]->param;
						mistralJBadd($aseco, $player, $jid);
						mistralAddJukebox($player->login, $price);
						break;
		case "Donate":	if ($price < 10)
							$who = "You";
						else
							$who = $player->nickname;
						$message = "{#server}>> $who\$z\$8F8\$s made a donation of $price coppers. Thank You!";
						if ($price < 10)
							$aseco->addCall("ChatSendServerMessageToLogin", array($aseco->formatColors($message), $player->login));
						else
							$aseco->addCall("ChatSendServerMessage", array($aseco->formatColors($message)));
						mistralAddDonation($player->login, $price);
						break;
		default: break;
	}
}

function mistralBillNewChallenge($aseco, $challenge)
{
 	global $finishminplayers, $finishlist, $onlineminplayers;
 	
	$pcount=count($finishlist);
	$ocount=count($aseco->server->players->player_list);
	if ($pcount<$finishminplayers || $ocount<$onlineminplayers)
		{
		return;
		}

	// get a random player from the finishlist	
	$player = $finishlist[array_rand($finishlist)];
	payLottery($aseco, $player->login, $player->nickname);

	// reset list after lottery
	$dummy = array();
	$finishlist = $dummy;
}

// TrackMania.BillUpdated(int BillId, int State, string StateName, int TransactionId);
function mistralBillUpdated($aseco, $command)
{
	global $bills, $tracksintransaction;
	
	$billId = $command[0];
	$state = $command[1];
	$stateName = $command[2];
	$player = $bills[$billId]->player;

	$action = $bills[$billId]->action;
	if ($action=="JBadd")
		$jid = $bills[$billId]->param;
		
	switch ($state)
	{
	 	// Payed
		case 4:	mistralBillPayed($aseco, $command);
				unset($bills[$billId]);
				if ($action=="JBadd")
					unset($tracksintransaction[$jid]);
				break;
		// Refused
		case 5: unset($bills[$billId]);
				if ($action=="JBadd")
					unset($tracksintransaction[$jid]);
				break;
		// Error
		case 6:	$message = "{#server}> Transaction failed - $stateName";
				$message = $aseco->formatColors($message);
				if ($player->login == "")
					$aseco->addCall("ChatSendServerMessage", array($message));
				else
					$aseco->addCall("ChatSendServerMessageToLogin", array($message, $player->login));
				unset($bills[$billId]);
				if ($action=="JBadd")
					unset($tracksintransaction[$jid]);
				break;
		default: break;
	}
}

function mistralSendBill($aseco, $player, $price, $action, $param = null)
{
 	global $bills, $payee, $tracksintransaction, $servername;
 	
	if ($player->login == "")
		return;

	$nickname = $player->nickname;
	$login = $player->login;
	
	switch ($action)
	{
		case "JBadd":	$jid = $param;
						if ($tracksintransaction[$jid]==1)
							{
							$aseco->addCall("ChatSendServerMessageToLogin", array("> Sorry, track already in transaction by another player.", $login));
							return;
							}
						$tracksintransaction[$jid]=1;
						$trackname = $player->tracklist[$jid]['name'];
						$message = "add $trackname\$z to Jukebox";
						break;
		case "Donate":	$message = "Donate $price coppers to $servername\$z";
						break;
		default:		$aseco->addCall("ChatSendServerMessageToLogin", array("> No such billing action.", $login));
						return;
	}

	settype($price,'integer');
	$aseco->client->query("SendBill", $login, $price, $message, $payee);
	$billId=$aseco->client->getResponse();
	$bills[$billId]->player = $player;
	$bills[$billId]->action = $action;
	$bills[$billId]->price = $price;
	$bills[$billId]->param = $param;
}

function payLottery($aseco, $login, $nickname)
{
 	global $minwin, $maxwin, $partner, $winpercentage, $finishlist, $servername;
 	
	if ($login == "")
		return;

	$aseco->client->query("GetServerCoppers");
	$coppers=$aseco->client->getResponse();

	$win = $coppers/100*$winpercentage;
	if ($win<$minwin)
		$win = $minwin;
	if ($win>$maxwin)
		$win = $maxwin;
	$loan = $win/4;

	$lottery = false;
	$low = "";
	// >20*win+loan - PLAY
	if ($coppers>20*($win+$loan))
	{
		$lottery=true;
	}
	// >10*win+loan - 50% chance to play
	elseif ($coppers>10*($win+$loan))
	{
	 	$low = " \$n(Coppers getting low)";
		$lucky = rand(1,2);
		if ($lucky==1)
		{
			$lottery=true;
		}
		else
		{
			$message = "\$0F0Sorry, no lottery this time.$low";			
		}
	}
	// if enough available - 25% chance to play
	elseif ($coppers>($win+$loan))
	{
	 	$low = " \$n(Server out of coppers soon)";
		$lucky = rand(1,4);
		if ($lucky==1)
		{
			$lottery=true;
		}
		else
		{
			$message = "\$0F0Sorry, no lottery this time.$low";			
		}
	}
	// else - no lottery
	else
	{
		$message = "\$0F0Sorry, no lottery. \$n(Server out of coppers - wait for donations or people using the jukebox)";
	}

	if (!$lottery)
		{
		$aseco->addCall("ChatSendServerMessage", array($message));
		return;
		}
	
	$pcount=count($finishlist);
	
	$left=$coppers-$win-$loan;
	
	settype($win,'integer');
	settype($loan,'integer');
	$aseco->addCall("Pay", array($login, $win, "You won in the $servername\$z lottery!"));
	$aseco->addCall("Pay", array($partner, $loan, "Server has $left coppers left. $nickname \$zwon $win coppers."));
	mistralAddWon($login, $win);
	$message = "\$0F0LOTTERY ($pcount finishs)!!! \$FFF$nickname\$z\$0F0\$s got $win coppers! Congratulations!".$low;
	$aseco->addCall("ChatSendServerMessage", array($message));
}
?>