<?php

// Original version by Sloth, via tm-forum.com
// Hack & Slash by AssemblerManiac

/* template file layout
   // header
   // {DATE} {TIME} {TRACK}
   // <!-- Player Data Begin ->  this tag not output to file
   // whatever is here gets duplicated for each person in the race, ranked 1-n, where n = <max_player_count> in the matchsave.xml file
   // {RANK} {NICK} {LOGIN} {TIME} {TEAM} {POINTS} are the reserved words
   // <!-- Player Data End ->    neither is this one
   // footer, tots for each team
   // <!-- Team Data Begin ->
   // {TEAM} {POINTS}
   // <!-- Team Data End ->
   // any remaining data will be written to the file from here to the end
 */

// if you want the teamname to show up properly when someone connects, make sure this plugin is AFTER the localdatabase plugin

require_once('includes/xmlparser.inc.php'); // provides a xml parser

global $MatchSettings;
$MatchSettings = array();

Aseco::registerEvent('onStartup', 'match_startup');		// checks for existence of 2 tables & creates if they don't exist
Aseco::registerEvent('onEndRace', 'match_endrace');
Aseco::registerEvent('onPlayerConnect', 'match_playerconnect');
Aseco::addChatCommand('teamname', 'Set your team name');

function checkTables() {
  	global $ldb_settings;
	$check[1] = false;
	$check[2] = false;

	$query = 'CREATE TABLE IF NOT EXISTS `match_main` (
							`ID` mediumint(9) NOT NULL auto_increment,
							`trackID` mediumint(9) NOT NULL default 0,
							`dttmrun` timestamp NOT NULL default Now(),
							PRIMARY KEY	(`ID`)
						) TYPE=MyISAM';
	$result2 = mysql_query($query);

	$query = 'CREATE TABLE IF NOT EXISTS `match_details` (
							`matchID` mediumint(9) not null,
							`playerID` mediumint(9) NOT NULL default 0,
							`teamname` varchar(40),
							`points` tinyint default 0,
							`score` mediumint(9),
							PRIMARY KEY (`matchID`,`playerID`)
						) TYPE=MyISAM';
	$result3 = mysql_query($query);

	$res = mysql_list_tables($ldb_settings['mysql']['database']);
	while ($row = mysql_fetch_row($res)){ $tables[] = $row[0]; }

	if (in_array('match_main', $tables)) { $check[1] = true; }
	if (in_array('match_details', $tables)) { $check[2] = true; }

	mysql_free_result($res);

	if ($check[1] && $check[2])
		{
		return true;
		}
	else
		{
		return false;
		}
	}

function match_playerconnect(&$aseco, &$player)
	{
	if ( $player->teamname != '' )
		{
		$aseco->addCall('ChatSendServerMessageToLogin', array($aseco->formatColors('{#highlite}> Your Team is currently ') . $player->teamname, $player->login));
		$aseco->client->multiquery();
		}
	}  //  match_playerconnect

function match_startup(&$aseco)
	{
	global $MatchSettings;
	$aseco->addCall('ChatSendServerMessage', array('Now Loading Match Plugin v1.0a'));
	$aseco->client->multiquery();
	match_loadsettings();
	if ( $MatchSettings['savedb'] )
		{
		checkTables();
		}
	}  //  match_startup

function chat_teamname(&$aseco, &$command)
	{
	$player = $command['author'];
	$player->teamname = $command['params'];
	$aseco->addCall('ChatSendServerMessageToLogin', array('You have joined team ' . $player->teamname, $player->login));
	$aseco->client->multiquery();
	$sql = 'update players set teamname=' . quotedString($command['params']) . ' where login=' . quotedString($player->login);
	mysql_query($sql);

	}  //  chat_teamname

function match_endrace(&$aseco, &$info)
	{
	global $MatchSettings, $rasp;
	if ( !$MatchSettings['enable'] )
		{
		return;
		}

	$ranking = $info[0];
	if($ranking[0]['Login'] == '')
		{
		return;
		}

	$TeamPoints = array();
	$challenge = $info[1];

	$db_challenge_id = $rasp->getChallengeId($challenge['UId']);

	$sql = 'insert into match_main (trackID) values (' . $db_challenge_id . ')';
	mysql_query($sql);
	$newID = mysql_insert_id();

	$template = $MatchSettings['template'];
	$stgout = $template['header'];

	$ctr = 0;
	for($i = 0;$i < $MatchSettings['pointcount']; $i++)
		{
		if ( $ranking[$i]['Login'] > '' )
			{
			if ( ($i > 0 && $ranking[$i]['BestTime'] != $ranking[$i - 1]['BestTime']))		// if two people have the same time, they both get the same points
				{
				$ctr++;
				}
			$player = $aseco->server->players->getPlayer($ranking[$i]['Login']);

			$rank = $ranking[$i]['Rank'];
			$bt = $ranking[$i]['BestTime'];
			if ( $bt != -1 )
				{
				$bt = formattime($bt);
				$pts = $MatchSettings['points'][$ctr];
				}
			else
				{
				$bt = 'DNF';
				$pts = 0;
				}

			$TeamPoints[$player->teamname] += $pts;

			if ( $MatchSettings['savefile'] )
				{
				$nickname = stripcolors($ranking[$i]['NickName']);
				// RANK, NICK, TIME, TEAM, POINTS are the substituted words for output
				$s = $template['detail'];
				$s = str_replace('{RANK}', $rank, $s);
				$s = str_replace('{NICK}', $nickname, $s);
				$s = str_replace('{TIME}', $bt, $s);
				$s = str_replace('{TEAM}', $player->teamname, $s);
				$s = str_replace('{POINTS}', $pts, $s);

				$stgout .= $s;
				}

			if ( $MatchSettings['savedb'] )
				{
				$sql = 'select Id from players where Login=' . quotedString($player->login) . ' AND Game=' . quotedString($aseco->server->getGame());
				$result = mysql_query($sql);
				$db_player = mysql_fetch_array($result);
				$db_player_id = $db_player["Id"];
				mysql_free_result($result);
				$sql = "insert into match_details (matchID, playerID, teamname, points, score) values ($newID, $db_player_id, '{$player->teamname}', $pts, " . $ranking[$i]["BestTime"] . ")";
				mysql_query($sql);
				}

			}
		}

	$stgout .= $template['middle'];

	$tots = '';
	$msg = '';
	foreach($TeamPoints as $key => $value)
		{
		$s = $template['teamdetail'];
		if ( $key == '' )
			{
			$key = 'OTHERS';
			}
		$s = str_replace('{TEAM}', $key, $s);
		$s = str_replace('{POINTS}', $value, $s);
		$stgout .= $s;
		$msg = $msg . '$F00' . $key . '$z {#highlite}' . $value . '  ';
		}

	if ( $MatchSettings['savefile'] )
		{
		$stgout .= $template['footer'];
		$stgout = str_replace('{TRACK}', stripcolors($challenge['Name']), $stgout);
		$stgout = str_replace('{DATE}', date($MatchSettings['format_date'], time()), $stgout);
		$stgout = str_replace('{TIME}', date($MatchSettings['format_time'], time()), $stgout);

		$fp = fopen($MatchSettings['outfile'], 'a');
		fwrite($fp, $stgout);
		fclose($fp);

		$fp = fopen($MatchSettings['outfilelast'], 'w');
		fwrite($fp, $stgout);
		fclose($fp);
		}

	$msg = $aseco->formatColors('$i$F00Points This Round$z  ' . $msg);
	$aseco->addCall('ChatSendServerMessage', array($msg));
	$aseco->client->multiquery();
	}

function match_loadsettings()
	{
	global $MatchSettings;
	$xml_parser = new Examsly();
	$settings = $xml_parser->parseXML('matchsave.xml');
	$settings = $settings['MATCHSAVE_SETTINGS'];
	$MatchSettings['savedb'] = $settings['SAVE_TO_DB'][0] == 'True';
	$MatchSettings['savefile'] = $settings['SAVE_TO_FILE'][0] == 'True';
	$MatchSettings['template'] = $settings['TEMPLATE_NAME'][0];
	$MatchSettings['outfile'] = $settings['OUTPUT_NAME'][0];
	$MatchSettings['outfilelast'] = $settings['OUTPUT_NAME_LAST'][0];
	$MatchSettings['pointcount'] = $settings['MAX_PLAYER_COUNT'][0];
	$MatchSettings['format_date'] = $settings['FORMAT_DATE'][0];
	$MatchSettings['format_time'] = $settings['FORMAT_TIME'][0];
	$MatchSettings['enable'] = $settings['ENABLED'][0] == 'True';

//print_r($settings);

	$s = $settings['POINTS'][0];
	$s = str_replace(' ', '', $s);
	$MatchPoints = explode(',', $s);

	foreach($MatchPoints as $key => $value)
		{
		$MatchPoints[$key] = intval($value);
		}

	$MatchSettings['points'] = $MatchPoints;
	$MatchOutput = array();
	$fp = fopen($MatchSettings['template'], 'r');
	$data = fread($fp, 32767);		// get whole template
	fclose($fp);
	$i = strpos($data, '<!-- Player Data Begin ->');		// strlen = 25
	$j = strpos($data, '<!-- Player Data End ->');			// strlen = 23
	$k = strpos($data, '<!-- Team Data Begin ->');			// strlen = 23
	$l = strpos($data, '<!-- Team Data End ->');			// strlen = 21
	$header = substr($data, 0, $i);		// strpos returns 0 based, no adj to $i necessary
	$detail = substr($data, $i + 25, $j - ($i + 26) + 1);

	$middle = substr($data, $j + 23, $k - ($j + 24) + 1);

	$teamdetail = substr($data, $k + 23, $l - ($k + 24) + 1);
	$footer = substr($data, $l + 21);

	$MatchOutput['header'] = $header;
	$MatchOutput['detail'] = $detail;
	$MatchOutput['middle'] = $middle;
	$MatchOutput['teamdetail'] = $teamdetail;
	$MatchOutput['footer'] = $footer;

	$MatchSettings['template'] = $MatchOutput;

// features to add
// /admin match begin # - run the match for # tracks
// /admin match scores - popup window with team scores for
// /admin team login teamname - move a player to a particular team

	// /tc message - plugin to send messages to your team only, via chat console - already written by someone else (el fuego?)

	}  //  match_loadsettings
?>
