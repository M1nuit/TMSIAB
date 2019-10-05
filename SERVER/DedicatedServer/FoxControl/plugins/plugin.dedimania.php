<?php
//* plugin.dedimania.php - Dedimania
//* Version:   0.8.0
//* Coded by:  cyrilw
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('StartUp', 'dedi_startup');
control::RegisterEvent('BeginChallenge', 'dedi_beginChallenge');
control::RegisterEvent('EverySecond', 'dedi_update');
control::RegisterEvent('EndChallenge', 'dedi_endChallenge');

require_once('include/GbxRemote.response.php');
require_once('include/web_access.php');
require_once('include/xmlrpc_db_access.php');


global $_Dedimania_recs_updated;
$_Dedimania_recs_updated = false;

function databaseIsError(&$error){
	if(is_string($error) && strlen($error)>0)
		return true;
	if(is_array($error) && count($error)>0)
		return true;
	return false;
}

function dedi_startup($control){
	global $settings, $_Dedimania, $_Dedimania_webaccess, $_Dedimania_recs, $_Dedimania_recs_updated;
	//Set dedimania settings
	$_Dedimania = array();
	$_Dedimania_recs = array();
	$control->client->query('GetGameInfos');
	$response = $control->client->getResponse();
	$_Dedimania['login'] = trim($settings['ServerLogin']);
	$_Dedimania['password'] = trim($settings['ServerPW']);
	$_Dedimania['version'] = FOXC_VERSION;
	$_Dedimania['tool'] = 'FoxControl';
	$_Dedimania['refresh'] = 240;
	$_Dedimania['timeOut'] = 1800;
	$_Dedimania['Url'] = 'http://dedimania.net:8002/Dedimania';
	$control->client->query('GetServerPackMask');
	$_Dedimania['PackMask'] = $control->client->getResponse();
	if($_Dedimania['PackMask'] == 'Stadium') $_Dedimania['game'] = 'TMNF';
	else $_Dedimania['game'] = 'TMUF';
	$control->client->query('GetServerName');
	$_Dedimania['ServerName'] = $control->client->getResponse();
	$control->client->query('GetServerComment');
	$_Dedimania['ServerComment'] = $control->client->getResponse();
	$_Dedimania['nation'] = $settings['Nation'];
	$_Dedimania_webaccess = new Webaccess();
	$_Dedimania_recs_updated = false;
	$_Dedimania['StartingUp'] = true;
	
	console('******** (Dedimania) ********');
	dedi_connect();
	console('-------- (Dedimania) --------');
	
	dedi_beginChallenge($control, '');
	
	$_Dedimania['lastSent'] = time();
	
}

function dedi_connect(){ //Connect to dedimania
	global $settings, $_Dedimania, $_Dedimania_webaccess;
	console('* Try connection on '.$_Dedimania['Url'].' ...');
	$xmlrpcdb = new XmlrpcDB($_Dedimania_webaccess,$_Dedimania['Url'],$_Dedimania['game'],$_Dedimania['login'],$_Dedimania['password'],$_Dedimania['tool'],$_Dedimania['version'],$_Dedimania['nation'],$_Dedimania['PackMask']);
			
	$response = $xmlrpcdb->RequestWait('dedimania.ValidateAccount');
		
	if($response===false){
		die('!!!!!! Error bad database response !\n  !!!!!!');
	}
	elseif(isset($response['Data']['params']['Status'])  && $response['Data']['params']['Status']){
		console('Connection and status ok !');
		$_Dedimania['XmlrpcDB'] = $xmlrpcdb;
		$_Dedimania['News'] = $response['Data']['params']['Messages'];
		$_Dedimania['Events'] = array();
		if(isset($response['Data']['errors']) && databaseIsError($response['Data']['errors'])) 
			console("!!!!!! ... with some authenticate warning: ",$response['Data']['errors']);
	}
	elseif(isset($response['Data']['errors'])){	
		console("!!!!!! Connection Error !!!!!! \n".$response['Data']['errors']."\n  !!!!!!");	
	}
	elseif(!isset($response['Code'])){
		console("!!!!!! Error no database response (".$url.")\n  !!!!!!");	
	}
	else{
		console("!!!!!! Error bad database response or contents (".$response['Code'].",".$response['Reason'].")\n  !!!!!!");
	}
}

function dedi_update($control){
	global $_Dedimania, $_Dedimania_webaccess;
	
	// check for valid connection
	if (isset($_Dedimania['XmlrpcDB'])) {
		// refresh DB every 4 mins after last DB update
		if ($_Dedimania['lastSent'] + $_Dedimania['refresh'] < time()){
			//dedimania_announce();
		}

		if ($_Dedimania['XmlrpcDB']->isBad()) {
			// retry after 30 mins of bad state
			if ($_Dedimania['XmlrpcDB']->badTime() > $_Dedimania['timeOut']) {
				console('Dedimania retry to send after '.round($_Dedimania['timeOut']/60).' minutes...');
				$_Dedimania['XmlrpcDB']->retry();
			}
		} else {
			$response = $_Dedimania['XmlrpcDB']->sendRequests();
			if (!$response) {
				console('Dedimania has consecutive connection errors!');
			}
		}
	}
	
	$read = array();
	$write = null;
	$except = null;
	$_Dedimania_webaccess->select($read, $write, $except, 0);
}

function dedi_getnextUID($control){
	//Get the next 5 UIDs
	$control->client->query('GetNextChallengeIndex');
	$next = $control->client->getResponse();
	$control->client->query('GetChallengeList', 5, $next);
	$track = $control->client->getResponse();
	$next = $track[0]['UId'];
	$next .= '/'.$track[1]['UId'];
	$next .= '/'.$track[2]['UId'];
	$next .= '/'.$track[3]['UId'];
	$next .= '/'.$track[4]['UId'];
	return $next;
}

function dedi_serverinfos($control){
	$numplayers = 0;
	$numspecs = 0;
	$control->client->query('GetPlayerList', 300, 0);
	$pl = $control->client->getResponse();
	$id = 0;
	while(isset($pl[$id])){
		$numplayers++;
		$id++;
	}

	// get current server options
	$control->client->query('GetServerOptions');
	$options = $control->client->getResponse();

	$serverinfo = array('SrvName' => $options['Name'],
	                    'Comment' => $options['Comment'],
	                    'Private' => ($options['Password'] != ''),
	                    'SrvIP' => '',
	                    'SrvPort' => 0,
	                    'XmlrpcPort' => 0,
	                    'NumPlayers' => $numplayers,
	                    'MaxPlayers' => $options['CurrentMaxPlayers'],
	                    'NumSpecs' => $numspecs,
	                    'MaxSpecs' => $options['CurrentMaxSpectators'],
	                    'LadderMode' => $options['CurrentLadderMode'],
	                    'NextFiveUID' => dedi_getnextUID($control)
	                   );
	return $serverinfo;
}

function dedi_players($control) {
	//get all players
	$players = array();
	$control->client->query('GetPlayerList', 300, 0);
	$pl = $control->client->getResponse();
	$id = 0;
	while(isset($pl[$id])){
		$pinfo = dedi_playerinfo($control, $pl[$id]);
		if ($pinfo !== false)
			$players[] = $pinfo;
		$id++;
	}
	return $players;
}

function dedi_playerinfo($control, $player){
	//get the infos of a player
	$control->client->query('GetDetailedPlayerInfo', $player['Login']);
	$info = $control->client->getResponse();
	$nation = explode('|', $info['Path']);
	$nation = $nation[1];

	return array('Login' => $info['Login'],
		     'Nation' => $nation,
		     'TeamName' => $info['LadderStats']['TeamName'],
		     'TeamId' => -1,
		     'IsSpec' => $info['IsSpectator'],
		     'Ranking' => $info['LadderStats']['PlayerRankings'][0]['Ranking'],
		     'IsOff' => $info['IsInOfficialMode']
		     );
}

function dedi_beginChallenge($control, $ChallengeInfo){
	global $_Dedimania, $_Dedimania_recs_updated;
	$_Dedimania_recs_updated = false;
	console('Get Dedimania-Records..');
	$control->client->query('GetCurrentGameInfo');
	$GameInfos = $control->client->getResponse();
	$serverInfos = dedi_serverinfos($control);
	$players = dedi_players($control);
	$i = 0;
	//Check if the connection isn't bad
	if(isset($_Dedimania['XmlrpcDB']) AND !$_Dedimania['XmlrpcDB']->isBad()){
		$control->client->query('GetCurrentChallengeInfo');
		$ChallengeInfo = $control->client->getResponse();
		$callback = array('dedimania_beginchallenge_cb', $ChallengeInfo, $control); 
		$_Dedimania['XmlrpcDB']->addRequest($callback, 
			'dedimania.CurrentChallenge',
			$ChallengeInfo['UId'],
			$ChallengeInfo['Name'],
			$ChallengeInfo['Environnement'],
			$ChallengeInfo['Author'],
			$_Dedimania['game'],
			$GameInfos['GameMode'],
			$serverInfos,
			30,
			$players);
			console('Dedimania-Request sent! UId: '.$ChallengeInfo['UId']);
	}
	else console('DEDIMANIA ERROR!! BAD CONNECTION!');
	$_Dedimania['lastSent'] = time();
}

function dedimania_beginchallenge_cb($response, $challenge, $control) {
	//handle response
	global $_Dedimania, $_Dedimania_recs, $_Dedimania_recs_updated;
	$_Dedimania_recs = array();
	$_Dedimania['Challenge'] = $response['Data']['params'];
	console('Dedimania-Request received! TTR: '.$response['Data']['TTR'].' '.$response['Headers']['accept-ranges'][0].':'.$response['Headers']['content-length'][0]);
	$id = 0;
	while(isset($response['Data']['params']['Records'][$id])){
		$crec = $response['Data']['params']['Records'][$id];
		$_Dedimania_recs[] = array('Login' => $crec['Login'], 'Nick' => $crec['NickName'], 'Time' => $crec['Best'], 'Rank' => $crec['Rank']);
		$id++;
	}
	$_Dedimania_recs_updated = true;
	if($_Dedimania['StartingUp'] == false) {
		if(count($_Dedimania_recs) > 0) $control->client->query('ChatSendServerMessage', '$fff1. $0b0Dedimania record: $fff'.formattime($_Dedimania_recs[0]['Time']).'$0b0 by $fff'.$_Dedimania_recs[0]['Nick']);
		else $control->chat_message('$0b0No Dedimania records on this challenge!');
	} else $_Dedimania['StartingUp'] = false;
}

function dedi_endChallenge($control, $cbdata){
	global $_Dedimania, $_Dedimania_recs;
	$ChallengeInfo = $cbdata[1];
	$control->client->query('GetCurrentGameInfo');
	$GameInfos = $control->client->getResponse();
	console('Sending dedimania records..');
	$ranking = $cbdata[0];
	$dediranking = array();
	$i = 0;
	while(isset($ranking[$i]))
	{
		if($ranking[$i]['BestTime'] <= 0) break;
		$dediranking[] = array('Login' => (string) $ranking[$i]['Login'], 'Best' => (int) $ranking[$i]['BestTime'], 'Checks' => $ranking[$i]['BestCheckpoints']);
		$i++;
	}
	//Check if the connection isn't bad
	if(isset($_Dedimania['XmlrpcDB']) AND !$_Dedimania['XmlrpcDB']->isBad()){
	$callback = array('dedi_endChallenge_cb', $ChallengeInfo); 
	$_Dedimania['XmlrpcDB']->addRequest($callback, 
			'dedimania.ChallengeRaceTimes',
			$ChallengeInfo['UId'],
			$ChallengeInfo['Name'],
			$ChallengeInfo['Environnement'],
			$ChallengeInfo['Author'],
			$_Dedimania['game'],
			$GameInfos['GameMode'],
			0,
			30,
			$dediranking);
	}
	else console('DEDIMANIA ERROR!! BAD CONNECTION!');
}

function dedi_endChallenge_cb($response, $ChallengeInfo){
	console('Dedimania records succesfull sent!');
}

?>