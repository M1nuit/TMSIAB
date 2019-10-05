<?php
////////////////////////////////////////////////////////////////
//¤
// File:      FAST 3.2 (First Automatic Server for Trackmania)
// Web:       
// Date:      21.06.2008
// Author:    Gilles Masson
// 
////////////////////////////////////////////////////////////////
//
// dependences: need manialinks plugin
//
// defined menus:  'menu.hud.times.menu'
// 
// 
// $_times_default = true; // times panel (contain records etc.)
// $_players[$login]['ML']['Show.times']

registerPlugin('ml_times',16);



//--------------------------------------------------------------
// ask refresh of times panel for all players 
//--------------------------------------------------------------
function ml_timesRefresh(){
	global $_players;
	foreach($_players as $login => &$pl){
		if(!is_string($login))
			$login = ''.$login;
		if($pl['ML']['Show.times'] && $pl['ML']['Show.ml_times']>0){
			if($pl['Status2']<2)
				ml_timesUpdateXml1($login,'refresh');
			else
				ml_timesUpdateXmlF($login,'refresh');
		}
	}
}


//--------------------------------------------------------------
// 
//--------------------------------------------------------------
function ml_timesAddTimesMod($name,$hook,$data,$priority=10){
	global $_ml_times_mods,$_ml_times_mods_list,$_ml_times_default_mod,$_players;
	if(function_exists($hook) && !isset($_ml_times_mods[$name])){
		$_ml_times_mods[$name] = array('Hook'=>$hook,'Data'=>$data,'Priority'=>$priority);
		uasort($_ml_times_mods,'ml_timesModPosCompare');
		$_ml_times_mods_list = array_reverse(array_keys($_ml_times_mods));

		$_ml_times_default_mod = reset($_ml_times_mods_list);
		foreach($_players as $login => &$player)
			$player['ML']['ml_times.mod'] = $_ml_times_default_mod;
		//debugPrint("ml_timesAddTimesMod ($name,$hook) - default=$_ml_times_default_mod - _ml_times_mods",$_ml_times_mods);

		ml_timesRefresh();
	}
}


//--------------------------------------------------------------
// 
//--------------------------------------------------------------
function ml_timesRemoveTimesMod($name){
	global $_ml_times_mods,$_ml_times_mods_list,$_ml_times_default_mod,$_players;
	if(isset($_ml_times_mods[$name])){
		unset($_ml_times_mods[$name]);
		uasort($_ml_times_mods,'ml_timesModPosCompare');
		$_ml_times_mods_list = array_reverse(array_keys($_ml_times_mods));
		
		$_ml_times_default_mod = reset($_ml_times_mods_list);
		foreach($_players as $login => &$player)
			$player['ML']['ml_times.mod'] = $_ml_times_default_mod;
		//debugPrint("ml_timesRemoveTimesMod ($name) - default=$_ml_times_default_mod - _ml_times_mods",$_ml_times_mods);

		ml_timesRefresh();
	}
}


// -----------------------------------
// compare function for uasort, return -1 if $a should be before $b
function ml_timesModPosCompare($a, $b){
	if($a['Priority']<=$b['Priority'])
		return -1;
	return 1;
}



//--------------------------------------------------------------
// Init :
//--------------------------------------------------------------
function ml_timesInit($event){
	global $_mldebug,$_ml_times_mods,$_ml_times_mods_list,$_ml_times_default_mod,$_times_default;
	if($_mldebug>3) console("ml_times.Event[$event]");

	$_ml_times_mods = array();
	$_ml_times_mods_list = array();
	$_ml_times_default_mod = '';

	if(!isset($_times_default))
		$_times_default = true;

	for($m=0;$m<6;$m++){
		manialinksAddAction('ml_times.1l'.$m);
		manialinksAddAction('ml_times.1r'.$m);
		manialinksAddAction('ml_times.F'.$m);
	}
	manialinksAddAction('ml_times.1l');
	manialinksAddAction('ml_times.1r');

	manialinksAddAction('ml_times.open');

	manialinksAddId('ml_times.1');
	manialinksAddId('ml_times.3');
	manialinksAddId('ml_times.F');
	manialinksAddId('ml_times.F2');

	ml_timesInitXmlStrings();

	//debugPrint("ml_timesInit - default=$_ml_times_default_mod - _ml_times_mods",$_ml_times_mods);
}


function ml_timesPlayerConnect($event,$login){
	global $_Game,$_players,$_ml_times_default_mod,$_GameInfos,$_times_default;
  if(!is_string($login))
    $login = ''.$login;
	if(!isset($_players[$login]['Relayed']) || $_players[$login]['Relayed'])
		return;
	//console("ml_times.Event[$event]('$login')");
	$pml = &$_players[$login]['ML'];

	$pml['ml_times.mod'] = $_ml_times_default_mod;

	if(!isset($pml['Show.times']))
		$pml['Show.times'] = $_times_default;

	if(!isset($pml['Show.ml_times']))
		$pml['Show.ml_times'] = 1;

	if(!isset($pml['Show.ml_times.1']))
		$pml['Show.ml_times.1'] = 1;

	if(!isset($pml['Show.ml_times.1.cols'][0]))
		$pml['Show.ml_times.1.cols'][0] = 1;
	if(!isset($pml['Show.ml_times.1.cols'][1]))
		$pml['Show.ml_times.1.cols'][1] = 3;
	
	// if team mode then hide times panel while playing
	if($_GameInfos['GameMode']==2 && $pml['Show.ml_times.1.cols'][0]>=0)
		$pml['Show.ml_times.1.cols'][0] = -1 - $pml['Show.ml_times.1.cols'][0];


	ml_timesPlayerStatus2Change('PlayerStatus2Change',$login,$_players[$login]['Status2']);
}


function ml_timesPlayerShowML($event,$login,$ShowML){
	global $_mldebug,$_players;
	if(!isset($_players[$login]['Relayed']) || $_players[$login]['Relayed'])
		return;
	if($ShowML && $_players[$login]['ML']['Show.times']){
		if($_players[$login]['Status2']<2){
			ml_timesUpdateXmlF($login,'hide');
			ml_timesUpdateXml1($login,'show');
		}else{
			ml_timesUpdateXml1($login,'hide');
			ml_timesUpdateXmlF($login,'show');
		}
	}else{
		ml_timesUpdateXml1($login,'hide');
		ml_timesUpdateXmlF($login,'hide');
	}
}


function ml_timesPlayerManialinkPageAnswer($event,$login,$answer,$action){
	global $_mldebug,$_Game,$_players,$_ml,$_ml_act,$_ml_times_mods_list;
  if(!is_string($login))
    $login = ''.$login;
	if(!isset($_players[$login]['Relayed']) || $_players[$login]['Relayed'])
		return;
	if(!isset($_players[$login]['ML']))
		return;
	$pml = &$_players[$login]['ML'];
	if($_mldebug>6) console("ml_times.Event[$event]('$login',$answer,$action)");
	$msg = localeText(null,'server_message').localeText(null,'interact');
	$state = ($_players[$login]['Status2']<1)?0:1;

	if($action=='Show.ml_times'){
		$pml['Show.ml_times'] = ($pml['Show.ml_times']>0)?0:1;
		ml_timesUpdateXml1($login,'hide');
		if($pml['Show.ml_times']>0){
			if($pml['Show.ml_times.1']>=0){
				ml_timesUpdateXml1($login,'show');
			}
		}
		$msg .= localeText($login,'ml_times.'.(($pml['Show.ml_times']>0)?'show':'hide'));
		addCall(null,'ChatSendToLogin', $msg, $login);
		ml_mainRefresh($login);

	}elseif($action=='ml_times.1l'){
		if($pml['Show.ml_times.1.cols'][$state]<6){
			$pml['Show.ml_times.1.cols'][$state]++;
			ml_timesUpdateXml1($login,'refresh');
		}

	}elseif($action=='ml_times.1r'){
		if($pml['Show.ml_times.1.cols'][$state]>0){
			$pml['Show.ml_times.1.cols'][$state]--;
			ml_timesUpdateXml1($login,'refresh');
		}

	}elseif($action=='ml_times.open'){
		$pml['Show.ml_times.1.cols'][$state] = -1 - $pml['Show.ml_times.1.cols'][$state];
		ml_timesUpdateXml1($login,'refresh');

	}else{
		foreach($_ml_times_mods_list as $m => $modname){
			if($pml['ml_times.mod']==$modname && $pml['Show.ml_times.1.cols'][$state]>0){

				if($action=='ml_times.1l'.$m){ // <<<
					if($pml['Show.ml_times.1.cols'][$state]<3){
						$pml['Show.ml_times.1.cols'][$state] = 3;
						ml_timesUpdateXml1($login,'refresh');
					}
						
				}elseif($action=='ml_times.1r'.$m){ // less
					if($pml['Show.ml_times.1.cols'][$state]>1)
						$pml['Show.ml_times.1.cols'][$state] = 1;
					else
						$pml['Show.ml_times.1.cols'][$state] = 0;
					ml_timesUpdateXml1($login,'refresh');
				}
					
			}elseif($action=='ml_times.1l'.$m){ // more
				$pml['ml_times.mod'] = $modname;
				if($pml['Show.ml_times.1.cols'][$state]<3){
					$pml['Show.ml_times.1.cols'][$state] = 3;
					ml_timesUpdateXml1($login,'refresh');
				}
					
				//$msg .= localeText($login,'ml_times.'.(($pml['Show.ml_times']>0)?'show':'hide'));
				//addCall(null,'ChatSendToLogin', $msg, $login);
					
			}elseif($action=='ml_times.1r'.$m){ // <<<
				$pml['ml_times.mod'] = $modname;
				if($pml['Show.ml_times.1.cols'][$state]<1){
					$pml['Show.ml_times.1.cols'][$state] = 1;
					ml_timesUpdateXml1($login,'refresh');
				}
					
				//$msg .= localeText($login,'ml_times.'.(($pml['Show.ml_times']>0)?'show':'hide'));
				//addCall(null,'ChatSendToLogin', $msg, $login);

			}elseif($action=='ml_times.F'.$m){ // result table
				$pml['ml_times.mod'] = $modname;
				ml_timesUpdateXmlF($login,'refresh');
			}
		}
	}
}


function ml_timesPlayerMenuBuild($event,$login){
	global $_mldebug,$_players;
	if(!isset($_players[$login]['Relayed']) || $_players[$login]['Relayed'])
		return;
	
	ml_menusAddItem($login, 'menu.hud', 'menu.hud.times', 
									array('Name'=>array(localeText($login,'menu.hud.times.on'),
																			localeText($login,'menu.hud.times.off')),
												'Type'=>'bool',
												'State'=>$_players[$login]['ML']['Show.times']));
	ml_menusAddItem($login, 'menu.hud', 'menu.hud.times.menu', 
									array('Name'=>localeText($login,'menu.hud.times'),
												'Menu'=>array('DefaultStyles'=>true,'Width'=>13,'Items'=>array()),
												'Show'=>$_players[$login]['ML']['Show.times']));
}


function ml_timesPlayerMenuAction($event,$login,$action,$state){
	global $_mldebug,$_players;
	//if($_mldebug>6) console("ml_times.Event[$event]('$login',$action,$state)");
	if(!isset($_players[$login]['Relayed']) || $_players[$login]['Relayed'])
		return;
	
	$msg = localeText(null,'server_message').localeText(null,'interact');
	if($action=='menu.hud.times'){
		$_players[$login]['ML']['Show.times'] = $state;
		if($state){
			ml_menusShowItem($login, 'menu.hud.times.menu');
			if($_players[$login]['Status2']<2)
				ml_timesUpdateXml1($login,'show');
			else
				ml_timesUpdateXmlF($login,'show');
			$msg .= localeText($login,'chat.hud.times.on');
		}else{
			ml_menusHideItem($login, 'menu.hud.times.menu');
			ml_timesUpdateXml1($login,'hide');
			ml_timesUpdateXmlF($login,'hide');
			$msg .= localeText($login,'chat.hud.times.off');
		}
		addCall(null,'ChatSendToLogin', $msg, $login);
	}
}


function ml_timesPlayerStatus2Change($event,$login,$status2){
	global $_mldebug,$_players;
	if(!isset($_players[$login]['Relayed']) || $_players[$login]['Relayed'])
		return;
	if(!$_players[$login]['ML']['ShowML'] || !$_players[$login]['ML']['Show.times'])
		return;
	if($_mldebug>4) console("ml_times.Event[$event]($login,$status2)");
	if($status2<2){
		ml_timesUpdateXmlF($login,'hide');
		ml_timesUpdateXml1($login,'show');
	}else{
		ml_timesUpdateXml1($login,'hide');
		ml_timesUpdateXmlF($login,'show');
	}
}



function	ml_timesInitXmlStrings(){
	global $_ml_act,$_ml_id,$_ml;

	$_ml['times_1_header'] = '<line height=\'0.027\'>'; // y=-0.528+0.027xlines
	$_ml['times_3_header'] = '<line height=\'0.027\'>'; // x=-0.670+0.334xcols

	$_ml['times_cell1_head'] = '<cell width=\'0.07\' bgcolor=\'1117\' textcolor=\'ffff\' textsize=\'1\'>'; 
	$_ml['times_cell1_title'] = '<text action=\'%d\'>$z$884 $s%s$z</text>';
	$_ml['times_cell1_text'] = '<text>%s$z</text>';

	$_ml['times_cell2_head'] = '<cell width=\'0.17\' bgcolor=\'1117\' textcolor=\'ffff\' textsize=\'1\'>'; 
	$_ml['times_cell2_title'] = '<text>$ff0$o%s$z</text>';
	$_ml['times_cell2_text'] = '<text>%s$z</text>';

	$_ml['times_cell3_head'] = '<cell width=\'0.09\' bgcolor=\'1117\' textcolor=\'ffff\' textsize=\'1\'>'; 
	$_ml['times_cell3_title'] = '<text action=\'%d\' halign=\'right\'>$z$884$s%s$z </text>';
	$_ml['times_cell3_text'] = '<text halign=\'right\'>%s$z</text>';

	$_ml['times_cell4_head'] = '<cell width=\'0.004\' bgcolor=\'223d\' textcolor=\'ffff\' textsize=\'1\'>'; 
	$_ml['times_cell4_title'] = '<text></text>';
	$_ml['times_cell4_text'] = '<text></text>';

	$_ml['times_cell_end'] = '</cell>';
	$_ml['times_end'] = '</line>';

	$_ml['times_arrows'] = '</line>'
		.'<line height=\'0.028\'>'
		.'<cell width=\'0.24\' textsize=\'1\'><text action=\''.$_ml_act['ml_times.1l'].'\' halign=\'right\'>$z$884$s%s &lt;&lt;%d$z </text></cell>'
		.'<cell width=\'0.09\' textsize=\'1\'><text action=\''.$_ml_act['ml_times.1r'].'\' halign=\'right\'>$z$884$s%d&gt;&gt;$z </text></cell>';
	$_ml['times_author'] = '</line>'
		.'<line height=\'0.028\'>'
		.'<cell width=\'0.24\' textsize=\'1\'><text halign=\'right\'>$ff0$o%s$z</text></cell>'
		.'<cell width=\'0.09\' textsize=\'1\'><text halign=\'right\'>$ff7%s$z</text></cell>';
	$_ml['times_end_open'] = '</line>'
		.'<line height=\'0.028\'>'
		.'<cell width=\'0.33\' textsize=\'1\'><text action=\''.$_ml_act['ml_times.open'].'\' halign=\'right\'>$z$s$884%s$z </text></cell>'
		.'</line>';


	$_ml['times_final_header'] = '<line height=\'0.028\'>';

	$_ml['times_final_colpad'] = '<cell width=\'0.04\' bgcolor=\'0007\'>'
		.'<text></text><text></text><text></text><text></text><text></text><text></text><text></text><text></text></cell>';

	$_ml['times_final_cell1'] = '<cell width=\'0.07\' bgcolor=\'0006\' textcolor=\'ffff\' textsize=\'1\'>%s</cell>'; 
	$_ml['times_final_cell1_elt'] = '<text>%s$z</text>'; 

	$_ml['times_final_cell2'] = '<cell width=\'0.19\' bgcolor=\'0006\' textcolor=\'ffff\' textsize=\'1\'>%s</cell>';
	$_ml['times_final_cell2_elt'] = '<text>%s$z</text>';

	$_ml['times_final_cell3'] = '<cell width=\'0.09\' bgcolor=\'0006\' textcolor=\'ffff\' textsize=\'1\'>%s</cell>';
	$_ml['times_final_cell3_elt'] = '<text halign=\'right\'>%s$z </text>';

	$_ml['times_final_title_header'] = '<line height=\'0.028\'>';

	$_ml['times_final_title_cell2'] = '<cell width=\'0.17\' bgcolor=\'0003\' textcolor=\'ffff\' textsize=\'1\'>'
		.'<text action=\''.$_ml_act['ml_times.F3'].'\' halign=\'center\'>%s$z</text>'
		.'<text action=\''.$_ml_act['ml_times.F2'].'\' halign=\'center\'>%s$z</text>'
		.'<text action=\''.$_ml_act['ml_times.F1'].'\' halign=\'center\'>%s$z</text>'
		.'<text action=\''.$_ml_act['ml_times.F0'].'\' halign=\'center\'>%s$z</text>'
		.'</cell>';

	$_ml['times_final_title_cell3'] = '<cell width=\'0.06\' bgcolor=\'0003\' textcolor=\'ffff\' textsize=\'1\'>'
		.'<text action=\''.$_ml_act['ml_times.F3'].'\'>%s$z</text>'
		.'<text action=\''.$_ml_act['ml_times.F2'].'\'>%s$z</text>'
		.'<text action=\''.$_ml_act['ml_times.F1'].'\'>%s$z</text>'
		.'<text action=\''.$_ml_act['ml_times.F0'].'\'>%s$z</text>'
		.'</cell>';

	$_ml['times_final_end'] = '</line>';

}


// action can be 'show', 'refresh', 'hide'
function ml_timesUpdateXml1($login,$action='show'){
	global $_mldebug,$_players,$_ml_act,$_ml,$_ml_times_mods,$_ml_times_mods_list,$_ChallengeInfo;
  if(!is_string($login))
    $login = ''.$login;
	if(!isset($_players[$login]['Relayed']) || $_players[$login]['Relayed'])
		return;
	if(!isset($_players[$login]['ML']))
		return;
	$pml = &$_players[$login]['ML'];
	$state = $_players[$login]['Status2'];

	if($_mldebug>6) console("ml_timesUpdateXml1({$login},{$action}):: state={$state}");

	// hide
	if($action=='hide' || $state>1){
		manialinksHide($login,'ml_times.1');
		manialinksHide($login,'ml_times.3');
		return;
	}
	// refresh only if opened
	if($action=='refresh' && !manialinksIsOpened($login,'ml_times.1')){
		if($_mldebug>8) console("ml_timesUpdateXml1({$login},{$action}):: ml_times.1 is not opened !");
		return;
	}
	if(!$_players[$login]['ML']['Show.times']){
		if(manialinksIsOpened($login,'ml_times.1')){
			manialinksHide($login,'ml_times.1');
			manialinksHide($login,'ml_times.3');
		}
		return;
	}

	//debugPrint("ml_timesBuildXml1 A- pml",$pml);

	$cols = $pml['Show.ml_times.1.cols'][$state];
	$lines = 0;
	$mdef = -1;
	$times3 = array();

	//debugPrint("ml_timesBuildXml1 - login=$login - state=$state",$state);

	if($cols>=0){
		// get times part values in $_players[$login]['ML']['ml_times.1'][] arrays
		$times1 = array();
		foreach($_ml_times_mods_list as $m => $modname){
			if($pml['ml_times.mod']!=$modname){
				$mod = &$_ml_times_mods[$modname];
				$times1[$m] = call_user_func($mod['Hook'],$login,$mod['Data'],2,2);
				if($times1[$m]===false)
					unset($times1[$m]);
				else{
					$lines += count($times1[$m]);
				}
			}else{
				$mdef = $m;
			}
		}
		// add default/selected part last
		if($mdef>=0){
			$mod = &$_ml_times_mods[$pml['ml_times.mod']];
			if($pml['Show.ml_times.1.cols'][$state]<=0){
				$times1[$mdef] = call_user_func($mod['Hook'],$login,$mod['Data'],2,2);
				if($times1[$mdef]===false)
					unset($times1[$mdef]);
				else{
					$lines += count($times1[$mdef]);
				}
				$cols = 0;
				
			}else{
				// get times part values in $_players[$login]['ML']['ml_times.3'] array
				$times3 = call_user_func($mod['Hook'],$login,$mod['Data'],6*$cols,6);
				
				if($times3!==false && isset($times3['Name'])){
					// copy last 6 to $times1[$mdef]
					$cols = floor((count($times3)-2)/6);
					for($i=0;$i<6;$i++){
						if(isset($times3[$cols*6+$i]))
							$times1[$mdef][$i] = $times3[$cols*6+$i];
						else
							$times1[$mdef][$i] = array('Pos'=>'','Name'=>'','Time'=>'');
					}
					$times1[$mdef]['Name'] = $times3['Name'];
					$lines += count($times1[$mdef]);
					
				}else
					$cols = 0;
			}
		}
	}

	$xml = sprintf($_ml['times_1_header'],(0.027*$lines-0.528));

	if($lines>0){
		$xml1 = $_ml['times_cell1_head'];
		$xml2 = $_ml['times_cell2_head'];
		$xml3 = $_ml['times_cell3_head'];
		foreach($times1 as $m => &$time1){
			$xml2 .= sprintf($_ml['times_cell2_title'],$time1['Name']);
			if($_ml_times_mods_list[$m]==$pml['ml_times.mod'] && $pml['Show.ml_times.1.cols'][$state]>0){
				$xml1 .= sprintf($_ml['times_cell1_title'],$_ml_act['ml_times.1l'.$m],'&lt;&lt;&lt;');
				$xml3 .= sprintf($_ml['times_cell3_title'],$_ml_act['ml_times.1r'.$m],localeText($login,'ml_times.less'));
			}else{
				$xml1 .= sprintf($_ml['times_cell1_title'],$_ml_act['ml_times.1l'.$m],'&lt;&lt;&lt;');
				$xml3 .= sprintf($_ml['times_cell3_title'],$_ml_act['ml_times.1r'.$m],localeText($login,'ml_times.more'));
			}
			for($i=0;isset($time1[$i]);$i++){
				$xml1 .= sprintf($_ml['times_cell1_text'],$time1[$i]['Pos']);
				$xml2 .= sprintf($_ml['times_cell2_text'],$time1[$i]['Name']);
				$xml3 .= sprintf($_ml['times_cell3_text'],$time1[$i]['Time']);
			}
		}
		//$xml1 .= $_ml['times_cell1_author'];
		//$xml2 .= sprintf($_ml['times_cell2_author'],localeText($login,'ml_times.author'));
		//$xml3 .= sprintf($_ml['times_cell3_author'],MwTimeToString($_ChallengeInfo['AuthorTime']));
		$xml1 .= $_ml['times_cell_end'];
		$xml2 .= $_ml['times_cell_end'];
		$xml3 .= $_ml['times_cell_end'];
		$xml .= $xml1.$xml2.$xml3;
		
		$xml .= sprintf($_ml['times_arrows'],
										($state>0?localeText($login,'ml_times.spec'):localeText($login,'ml_times.play')),
										$pml['Show.ml_times.1.cols'][$state],$pml['Show.ml_times.1.cols'][$state]);
	}
	if(!isset($_players[$login]['ML']['Show.mapinfo']) || !$_players[$login]['ML']['Show.mapinfo']){
		$xml .= sprintf($_ml['times_author'],
										localeText($login,'ml_times.author'),
										MwTimeToString($_ChallengeInfo['AuthorTime']));
	}
	$xml .= sprintf($_ml['times_end_open'],
									($cols>=0?localeText($login,'ml_times.close'):localeText($login,'ml_times.open')));

	//debugPrint("ml_timesBuildXml1 B- pml",$pml);
	//debugPrint("ml_timesBuildXml1 - times1",$times1);
	//console($xml);
	
	manialinksShow($login,'ml_times.1',$xml,-0.67,0.027*$lines-0.528);

	if($cols>0 && $times3!==false)
		ml_timesUpdateXml1times3($login,$times3,$cols);
	else
		manialinksHide($login,'ml_times.3');
}


function ml_timesUpdateXml1times3($login,&$times3,$cols){
	global $_mldebug,$_ml;
	if($cols<=0 || !isset($times3[0]))
		return ' ';
	
	$xml = sprintf($_ml['times_3_header'],(0.334*$cols-0.670)); // -0.670+0.334xcols

	for($col=0;$col<$cols;$col++){
		$xml1 = $_ml['times_cell1_head'];
		$xml2 = $_ml['times_cell2_head'];
		$xml3 = $_ml['times_cell3_head'];
		$xml4 = $_ml['times_cell4_head'];
		for($i=$col*6;($i<$col*6+6) && isset($times3[$i]);$i++){
			$xml1 .= sprintf($_ml['times_cell1_text'],$times3[$i]['Pos']);
			$xml2 .= sprintf($_ml['times_cell2_text'],$times3[$i]['Name']);
			$xml3 .= sprintf($_ml['times_cell3_text'],$times3[$i]['Time']);
			$xml4 .= $_ml['times_cell4_text'];
		}
		$xml1 .= $_ml['times_cell_end'];
		$xml2 .= $_ml['times_cell_end'];
		$xml3 .= $_ml['times_cell_end'];
		$xml4 .= $_ml['times_cell_end'];	

		$xml .= $xml1.$xml2.$xml3.$xml4;
	}

	$xml .= $_ml['times_end'];

	//debugPrint("ml_timesBuildXml1 - cols=$cols - xml",$xml);
	//debugPrint("ml_timesBuildXml1 - cols=$cols - times3",$times3);
	//console($xml);

	manialinksShow($login,'ml_times.3',$xml,(0.334*$cols-0.670),-0.366);
}



// action can be 'show', 'refresh', 'hide'
function ml_timesUpdateXmlF($login,$action='show'){
	global $_mldebug,$_players,$_ml_act,$_ml,$_ml_times_mods,$_ml_times_mods_list,$_ml_times_default_mod;;
  if(!is_string($login))
    $login = ''.$login;
	if(!isset($_players[$login]['Relayed']) || $_players[$login]['Relayed'])
		return;
	if(!isset($_players[$login]['ML']['ShowML']) || !$_players[$login]['ML']['ShowML'])
		return;
	$pml = &$_players[$login]['ML'];
	$state = $_players[$login]['Status2'];

	if($_mldebug>6) console("ml_timesUpdateXmlF({$login},{$action}):: state={$state}");

	// hide
	if($action=='hide' || $state<2){
		manialinksHide($login,'ml_times.F');
		manialinksHide($login,'ml_times.F2');
		return;
	}
	// refresh only if opened
	if($action=='refresh' && !manialinksIsOpened($login,'ml_times.F')){
		if($_mldebug>8) console("ml_timesUpdateXmlF({$login},{$action}):: ml_times.F is not opened !");
		return;
	}
	if(!$_players[$login]['ML']['Show.times']){
		if(manialinksIsOpened($login,'ml_times.1')){
			manialinksHide($login,'ml_times.F');
			manialinksHide($login,'ml_times.F2');
		}
		return;
	}

	if(!isset($pml['ml_times.mod']))
		return '';
	$mod = $pml['ml_times.mod'];
	if(!isset($_ml_times_mods[$mod]['Hook']))
		return '';

	// get up part values in $_players[$login]['ML']['ml_times.F'] array
	$pml['ml_times.F'] = call_user_func($_ml_times_mods[$mod]['Hook'],$login,$_ml_times_mods[$mod]['Data'],32,32);
	if($pml['ml_times.F']===false)
		return '';
	$timesf = &$pml['ml_times.F'];

	$xml = $_ml['times_final_header'];

	$tnames = array();
	$tshow = array();
	$n = 0;
	foreach($_ml_times_mods_list as $m => $modname){
		if($pml['ml_times.mod']!=$modname){
			$ttmp = call_user_func($_ml_times_mods[$modname]['Hook'],$login,$_ml_times_mods[$modname]['Data'],0,0);
			$tnames[$n] = '$884$o'.$ttmp['Name'];
			$tshow[$n] = '$884$s&gt; '.localeText($login,'ml_times.showrec');
		}else{
			$tnames[$n] = '$ff0$o'.$timesf['Name'];
			$tshow[$n] = '';
		}
		$n++;
	}
	while($n<8){
		$tnames[$n] = '';
		$tshow[$n] = '';
		$n++;
	}

	// column pad
	// build timesfinal cols
	$cell1 = '';
	$cell2 = '';
	$cell3 = '';
	for($i=0; $i<32 && isset($timesf[$i]['Time']); $i++){
		$cell1 .= sprintf($_ml['times_final_cell1_elt'],$timesf[$i]['Pos']);
		$cell2 .= sprintf($_ml['times_final_cell1_elt'],$timesf[$i]['Name']);
		$cell3 .= sprintf($_ml['times_final_cell1_elt'],$timesf[$i]['Time']);
	}
	$xml .= sprintf($_ml['times_final_cell1'],$cell1);
	$xml .= sprintf($_ml['times_final_cell2'],$cell2);
	$xml .= sprintf($_ml['times_final_cell3'],$cell3);

	$xml .= $_ml['times_final_end'];

	// build timesfinal titles
	$xml2 = $_ml['times_final_title_header'];

	$xml2 .= sprintf($_ml['times_final_title_cell2'],$tnames[3],$tnames[2],$tnames[1],$tnames[0]);
	$xml2 .= sprintf($_ml['times_final_title_cell3'],$tshow[3],$tshow[2],$tshow[1],$tshow[0]);
	$xml2 .= $_ml['times_final_end'];

	manialinksShow($login,'ml_times.F',$xml,1.0,0.45);
	manialinksShow($login,'ml_times.F2',$xml2,0.81,0.57);
}




?>
