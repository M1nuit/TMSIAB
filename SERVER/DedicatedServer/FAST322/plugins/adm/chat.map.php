<?php
////////////////////////////////////////////////////////////////
//¤
// File:      FAST 3.1 (First Automatic Server for Trackmania)
// Web:       
// Date:      13.05.2008
// Author:    Gilles Masson
// 
////////////////////////////////////////////////////////////////

registerCommand('map','/map name : load map.txt matchsettings map file.',true);


//------------------------------------------
// Laps Commands
//------------------------------------------
function chat_map($author, $login, $params){
	global $_debug,$_GameInfos,$_NextGameInfos,$doInfosNext,$_ServerOptions;
	global $_response,$_response_error;
	global $_ChallengeList;

	// verify if author is in admin list
	if(!verifyAdmin($login))
		return;

	// response
	if($_response || $_response_error) {
		//console("chat_map response received !");
		if($_response_error)
			$msg = localeText(null,'server_message')."Error(".$_response_error['faultCode']."): ".localeText(null,'interact').$_response_error['faultString']." !";
		else{
			$msg = '';
			if(is_array($_response)){
				$sep = '';
				foreach($_response as $key => $val){
					$msg .= $sep.$key.'=';
					if(is_array($val)){
						$sep2 = '{';
						foreach($val as $key2 => $val2){
							$msg .= $sep2.$key2.'='.$val2;
							$sep2 = ',';
						}
						$msg .= '}';
					}else
						$msg .= $val;
					$sep = ',';
				}
			}else
				$msg .= $_response;
			$msg = localeText(null,'server_message')."Result: ".localeText(null,'interact').stripColors($msg);
			if(is_numeric($_response) && ($_response+0)>0)
				$msg .= ' maps loaded. Wait next map or use /adm next.';
		}
		addCall(null,'ChatSendToLogin', $msg, $login);
		
		// map command
	}elseif(isset($params[0])){
		if(strpos($params[0],'.txt')!==false)
			$matchsetting = $params[0];
		else
			$matchsetting = $params[0].'.txt';
		$msg = localeText(null,'server_message').localeText(null,'interact')
			."Try to load '$matchsetting' matchsettings file...";
		addCall(null,'ChatSendToLogin', $msg, $login);


		$action = array('CB'=>array('chat_map',func_get_args()),'Login'=>$login);
		addCall($action,'LoadMatchSettings', $matchsetting);

		// help
	}else{
		$msg = localeText(null,'server_message') . localeText(null,'interact')
			.'/map name : load map.txt matchsettings map file (need a /adm next, or finish current map).';
		// send message to user who wrote command
		addCall(null,'ChatSendToLogin', $msg, $login);
	}

}

?>
