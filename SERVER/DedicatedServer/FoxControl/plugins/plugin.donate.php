<?php
//* plugin.donate.php - Donate
//* Version:   0.9.0
//* Coded by:  cyrilw && libero6
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('StartUp', 'donate_startup');
control::RegisterEvent('EndChallenge', 'donate_endchallenge');
control::RegisterEvent('BeginChallenge', 'donate_startup');
control::RegisterEvent('PlayerConnect', 'donate_startup');
control::RegisterEvent('ManialinkPageAnswer', 'donate_mlanswer');
control::RegisterEvent('BillUpdate', 'donate_bill');

global $bills, $donate1, $donate2, $donate3, $donate4, $donate5;
$bills = array();
$donate1 = 50;
$donate2 = 250;
$donate3 = 500;
$donate4 = 1000;
$donate5 = 3000;

function donate_startup($control){
	global $bills, $donate1, $donate2, $donate3, $donate4, $donate5;
	



	$control->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
	<manialink id="3010">
 	<quad posn="-62 50 1" sizen="7.5 5" style="Bgs1InRace" substyle="NavButtonBlink" />
 	<quad posn="-54 50 1" sizen="7.5 5" style="Bgs1InRace" substyle="NavButtonBlink" />
 	<quad posn="-46 50 1" sizen="7.5 5" style="Bgs1InRace" substyle="NavButtonBlink" />
 	<quad posn="-38 50 1" sizen="7.5 5" style="Bgs1InRace" substyle="NavButtonBlink" />
 	<quad posn="-30 50 1" sizen="7.5 5" style="Bgs1InRace" substyle="NavButtonBlink" />
 	<quad posn="-62 50 1" sizen="7.5 5" style="Bgs1InRace" substyle="NavButtonBlink" />
 	<quad posn="-54 50 1" sizen="7.5 5" style="Bgs1InRace" substyle="NavButtonBlink" />
 	<quad posn="-46 50 1" sizen="7.5 5" style="Bgs1InRace" substyle="NavButtonBlink" />
 	<quad posn="-38 50 1" sizen="7.5 5" style="Bgs1InRace" substyle="NavButtonBlink" />
 	<quad posn="-30 50 1" sizen="7.5 5" style="Bgs1InRace" substyle="NavButtonBlink" />
    <label posn="-58.25 46.75 2" scale="0.8" halign="center" valign="center" text="$o$fff'.$donate1.'" style="TextCardSmallScores2Rank" action="3011"/>
    <label posn="-50.25 46.75 2" scale="0.8" halign="center" valign="center" text="$o$fff'.$donate2.'" style="TextCardSmallScores2Rank" action="3012"/>
    <label posn="-42.25 46.75 2" scale="0.8" halign="center" valign="center" text="$o$fff'.$donate3.'" style="TextCardSmallScores2Rank" action="3013"/>
    <label posn="-34.25 46.75 2" scale="0.8" halign="center" valign="center" text="$o$fff'.$donate4.'" style="TextCardSmallScores2Rank" action="3014"/>
    <label posn="-26.25 46.75 2" scale="0.8" halign="center" valign="center" text="$o$fff'.$donate5.'" style="TextCardSmallScores2Rank" action="3015"/>
 	</manialink>', 0, False);
}

function donate_endchallenge($control, $donatedelete){
	$control->close_ml(3010, '');
}

function donate_mlanswer($control, $ManialinkPageAnswer){
	global $bills, $donate1, $donate2, $donate3, $donate4, $donate5;

	
	if($ManialinkPageAnswer[2]=='3011'){
		$control->client->query('SendBill', $ManialinkPageAnswer[1], $donate1, '$0f0Do you want donate $fff'.$donate1.'$0f0 Coppers?$z', '');
		$billid = $control->client->getResponse();
		$bills[] = array($ManialinkPageAnswer[1], $donate1, $billid);
	}
	elseif($ManialinkPageAnswer[2]=='3012'){
		$control->client->query('SendBill', $ManialinkPageAnswer[1], $donate2, '$0f0Do you want donate $fff'.$donate2.'$0f0 Coppers?$z', '');
		$billid = $control->client->getResponse();
		$bills[] = array($ManialinkPageAnswer[1], $donate2, $billid);
	}
	elseif($ManialinkPageAnswer[2]=='3013'){
		$control->client->query('SendBill', $ManialinkPageAnswer[1], $donate3, '$0f0Do you want donate $fff'.$donate3.'$0f0 Coppers?$z', '');
		$billid = $control->client->getResponse();
		$bills[] = array($ManialinkPageAnswer[1], $donate3, $billid);
	}
	elseif($ManialinkPageAnswer[2]=='3014'){
		$control->client->query('SendBill', $ManialinkPageAnswer[1], $donate4, '$0f0Do you want donate $fff'.$donate4.'$0f0 Coppers?$z', '');
		$billid = $control->client->getResponse();
		$bills[] = array($ManialinkPageAnswer[1], $donate4, $billid);
	}
	elseif($ManialinkPageAnswer[2]=='3015'){
		$control->client->query('SendBill', $ManialinkPageAnswer[1], $donate5, '$0f0Do you want donate $fff'.$donate5.'$0f0 Coppers?$z', '');
		$billid = $control->client->getResponse();
		$bills[] = array($ManialinkPageAnswer[1], $donate5, $billid);
	}
}

function donate_bill($control, $BillId){

	global $bills, $donate1, $donate2, $donate3, $donate4, $donate5;
	
	$billid = $BillId[0];
	
	$curr_id = 0;
	$billid_is_don = false;
	while(isset($bills[$curr_id])){
		$b_curr_data = $bills[$curr_id];
		if($b_curr_data[2]==$billid){
			$billid_is_don = true;
			break;
		}
		$curr_id++;
	}
	
	if($billid_is_don==true){
		$billarray = $bills[$curr_id];
	
		$billlogin = $billarray[0];
		$billcoppers = $billarray[1];
		$control->client->query('GetDetailedPlayerInfo', $billlogin);
		$billpdata = $control->client->getResponse();
	
	
		if($BillId[1]=='4'){
			global $db;
			$control->chat_message('$fff'.$billpdata['NickName'].'$z$s$o$0f0 donated $fff'.$billcoppers.'$0f0 Coppers! Thank you!');
			$sql = "SELECT * FROM `players` WHERE playerlogin = '".trim($billlogin)."'";
			if($mysql = mysqli_query($db, $sql)){
				if($donsdata = $mysql->fetch_object()){
					$dons = $donsdata->donations;
					$dons = $dons+$billcoppers;
					$sql = "UPDATE `players` SET donations = '".$dons."' WHERE playerlogin = '".trim($billlogin)."'";
					if($mysql = mysqli_query($db, $sql)){
						$updated = true;
					}
				}
			}
		}
		elseif($BillId[1]=='5'){
			$control->client->query('ChatSendServerMessageToLogin', '$f00->Transaction refused!', $billlogin);
		}
		elseif($BillId[1]=='6'){
			$control->client->query('ChatSendServerMessageToLogin', '$f00->Transaction error!', $billlogin);
		}
	}
}
?>