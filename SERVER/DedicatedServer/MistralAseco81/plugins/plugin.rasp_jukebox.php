<?php
/*
 * Allow players to add tracks to the 'jukebox' so they can play favorites without waiting
 * Each player can only have one track in jukebox at a time
 */

include_once('includes/rasp.funcs.php');

//Register events and chat commands with aseco
Aseco::registerEvent('onEndRace', 'rasp_endrace');
Aseco::addChatCommand('jukebox', 'Sets a track to be played next.');
Aseco::addChatCommand('list', 'Lists tracks currently on the server');
Aseco::registerEvent('onNewChallenge', 'rasp_newtrack');
//Aseco::addChatCommand('add', 'Adds a track to the server, directly from TMX');
//Aseco::addChatCommand('y', 'Votes for a TMX map');

global $mistralJBfile;
$mistralJBfile = "mistralJB.xml";

function mistralLoadJukebox($aseco)
{
	global $mistralJBfile, $jukebox, $jb_buffer;

	if(!file_exists($mistralJBfile))
		return;

	$aseco->console("Reloading Jukebox...");

	$xml=$aseco->xml_parser->parseXml($mistralJBfile);
	$loadjukebox=$xml['JUKEBOX']['TRACKS'][0]['TRACK'];
	$loadjbbuffer=$xml['JUKEBOX']['JBBUFFER'][0]['UID'];

	if (is_array($loadjukebox))
	{
		foreach ($loadjukebox as $track)
		{
	 		$trackname = getTracknameFromUId($track['UID'][0]);
			$jukebox[$track['UID'][0]]['FileName'] = $track['FILENAME'][0];
			$jukebox[$track['UID'][0]]['Name'] = $trackname;
			$jukebox[$track['UID'][0]]['Environnement'] = $track['ENVIRONNEMENT'][0];
			$jukebox[$track['UID'][0]]['Nick'] = getNicknameFromLogin($track['LOGIN'][0]);
			$jukebox[$track['UID'][0]]['Login'] = $track['LOGIN'][0];
			$jukebox[$track['UID'][0]]['tmx'] = false;
			$aseco->console("Added ".stripColors($trackname)." to jukebox.");
		}
	}
	
	foreach ($loadjbbuffer as $buffer)
		$jb_buffer[] = $buffer;

}

function mistralSaveJukebox($aseco)
{
	global $mistralJBfile, $jukebox, $jb_buffer;
	
	$output = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
	$output .= "<jukebox>\n";

	$output .= "\t<tracks>\n";
	foreach ($jukebox as $uid => $track)
	{
	 	if ($uid=="")
	 		continue;
		$output .= "\t\t<track>\n";
		$output .= "\t\t\t<UId>$uid</UId>\n";
		$output .= "\t\t\t<FileName>".$track['FileName']."</FileName>\n";
		$output .= "\t\t\t<Environnement>".$track['Environnement']."</Environnement>\n";
		$output .= "\t\t\t<Login>".$track['Login']."</Login>\n";
		$output .= "\t\t</track>\n";
	}
	$output .= "\t</tracks>\n";
	
	$unique = array();
	$output .= "\t<jbbuffer>\n";
	foreach ($jb_buffer as $uid)
	{
	 	if ($uid=="")
	 		continue;
	 
	 	if ($unique[$uid])
		 	continue;
			 		
		$output .= "\t\t<UId>$uid</UId>\n";
		$unique[$uid] = true;
	}
	$output .= "\t</jbbuffer>\n";

	$output .= "</jukebox>\n";
	
	$file = fopen($mistralJBfile, 'w');
	if(!$file)
	{
		$aseco->console('[ERROR] can\'t write '.$mistralJBfile);
		return;
	}
	fputs($file,$output);
	fclose($file);
}

function mistralJBadd($aseco, $player, $jid)
{
	global $jukebox, $rasp;

	$uid = $player->tracklist[$jid]['uid'];

	$jukebox[$uid]['FileName'] = $player->tracklist[$jid]['filename'];
	$jukebox[$uid]['Name'] = $player->tracklist[$jid]['name'];
	$jukebox[$uid]['Environnement'] = $player->tracklist[$jid]['environnement'];
	$jukebox[$uid]['Nick'] = $player->nickname;
	$jukebox[$uid]['Login'] = $player->login;
	$jukebox[$uid]['tmx'] = false;
			
	$player->mistal['lastjukebox'] = 2;
	$message = formatText($rasp->messages['JUKEBOX'][0], $player->tracklist[$jid]['name'], $player->tracklist[$jid]['environnement'], stripColors($player->nickname));
	$message = $aseco->formatColors($message);
	$aseco->addCall('ChatSendServerMessage', array($message));
	mistralSaveJukebox($aseco);
	// UPDATE PLAYERINFO MANIALINK
	displayAllPlayerInfo($aseco, 0, TRUE);
}

function rasp_newtrack(&$aseco, $data)
	{
	global $buffersize, $jb_buffer, $tmxplaying, $tmxvotes, $tmxplayed, $tmxadd;

	$tmxvotes = array();
	$tmxadd = array();
	if (sizeof($jb_buffer) > $buffersize)
		{
		$drop_value = array_shift($jb_buffer);
		}
	$jb_buffer[] = $data->uid;

	mistralSaveJukebox($aseco);

	if ($tmxplaying != false)
		{
		$tmxplayed = $tmxplaying;
		$tmxplaying = false;
		}
	else
		{
		if ($tmxplayed != false)
			{
			$set = $aseco->client->addCall('RemoveChallenge', array($tmxplayed));
			if (!$aseco->client->multiquery())
				{
				trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
				$response = array();
				}
			else
				{
				$tmxplayed = false;
				}
			}
		}
	}

function rasp_endrace(&$aseco, $challenge)
	{
	global $jukebox, $aseco, $tmxplaying;
	if (sizeof($jukebox) > 0)
		{
		$next = array_shift($jukebox);
		if ($next['tmx'])
			{
			if ( $aseco->debug )
				{
				$aseco->console_text('{RASP} TMX challenge filename is ' . $next['FileName']);
				}

			$aseco->client->addCall('AddChallenge', array($next['FileName']));
			if (!$aseco->client->multiquery())
				{
				trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
				return;
				}
			}
		$aseco->client->addCall('ChooseNextChallenge', array($next['FileName']));
		if (!$aseco->client->multiquery())
			{
			trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
			}
		else
			{
			$msg = '{RASP Jukebox} Setting Next Challenge to ' . $next['Name'];
			if ( $next['tmx'] )
				{
				$msg = '{RASP Jukebox} Setting Next Challenge to ' . $next['Name'] . ', file downloaded from TMX';
				}
			$aseco->console_text($msg);
			$response = $aseco->client->getResponse();
			$message = '{#server}>{#emotic} The next track will be {#highlite}' . $next['Name'] . '{#emotic} as requested by {#highlite}' . stripColors($next['Nick']);
			$message = $aseco->formatColors($message);
			$aseco->addCall('ChatSendServerMessage', array($message));
			if($next['tmx'])
				{
				$tmxplaying = $next['FileName'];
				}
			}
		}
	}


function chat_jukebox(&$aseco, &$command) {
	global $player, $jukebox, $aseco, $rasp, $jb_buffer, $jb_price, $jb_price_2nd, $jp_price_played, $jb_maxtracks;
	$player = $command['author'];
	$login = $player->login;
	
	if (is_numeric($command['params']) && $command['params'] >= 0) {
		if(count($player->tracklist) == 0)
			{
			$message = $aseco->formatColors('{#server}>{#emotic} Use \'/list\' first');
			$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
			return;
			}
		$jid = ltrim($command['params'], '0');
		$jid--;

		$count=0;
		foreach ($jukebox as $key)
			{
			if ($player->login == $key['Login'])
				{
				$count++;
				}
			}

		if ($count >= $jb_maxtracks)
			{
			$message = $aseco->formatColors('{#server}>{#emotic} You have reached the maximum of '.$jb_maxtracks.' tracks in the jukebox.');
			$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
			return;
			}

		if (array_key_exists($jid, $player->tracklist)) 		// find track by given #
			{
			$uid = $player->tracklist[$jid]['uid'];
			if (array_key_exists($uid, $jukebox)) 				// find by uid in jukebox
				{
				$message = $aseco->formatColors('{#server}>{#emotic} This track has already been added to the jukebox, pick another one');
				$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
				return;
				}
			
			if (in_array($uid, $jb_buffer))
				{
				// free (Nations)
				if ($jb_price == 0)
					{
					$message = $aseco->formatColors('{#server}>{#emotic} This track has been played recently.');
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
					return;
					}
				// cost (United)
				else
					{
					$message = $aseco->formatColors('{#server}>{#emotic} This track has been played recently. ADDING IT AGAIN IS MOST EXPENSIVE!');
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
					mistralSendBill($aseco, $player, $jp_price_played, "JBadd", $jid);
					return;
					}
				}
			
			// cost (United)
			if (($player->mistal['lastjukebox']>0 || $count>0) && $jb_price > 0 )
				{
				$message = $aseco->formatColors('{#server}>{#emotic} You recently added a track to the jukebox. ADDING ANOTHER ONE IS MORE EXPENSIVE!');
				$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
				mistralSendBill($aseco, $player, $jb_price_2nd, "JBadd", $jid);
				return;
				}

			// free (Nations)			
			if ($jb_price == 0)
				{
				mistralJBadd($aseco, $player, $jid);
				}
			// cost (United)
			else
				{			
				mistralSendBill($aseco, $player, $jb_price, "JBadd", $jid);
				}

			return;
			} else {
				$message = $aseco->formatColors('{#server}>{#emotic} Track ID not found! Type {#highlite}/list {#emotic}to see all tracks.');
				$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
				return;
			}
	} elseif ($command['params'] == 'list') {
		if (sizeof($jukebox) > 0) {
			$message = '{#server}> {#emotic}Current tracks in the jukebox : ';
			$i = 1;
			foreach($jukebox as $item) {
				$message .= '{#highlite}' . $i . '{#emotic}. [{#highlite}' . $item['Name'] . '{#emotic} - '.$item['Environnement'].'], ';
				$i++;
			}
			$message = substr($message, 0, strlen($message)-2);
			$message = $aseco->formatColors($message);
			$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
			return;
		} else {
			$message = $aseco->formatColors('{#server}>{#emotic} No tracks in the jukebox, use {#highlite}/jukebox <Track_ID> {#emotic}to add one...');
			$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
			return;
		}
	} elseif ($command['params'] == '') {
		$message = $aseco->formatColors('{#server}>{#emotic} You have to include a Track ID, e.g {#highlite}/jukebox 12 - Type {#highlite}/list{#emotic} help for more info');
		$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
		return;
	}
}

/*
function chat_y(&$aseco, &$command) {
	global $tmxadd, $tmxvotes, $jukebox;
	if (in_array($command['author']->login, $tmxvotes)) {
		$message = $aseco->formatColors('{#server}>{#emotic} You have already voted!');
		$aseco->addCall('ChatSendServerMessageToLogin', array($message, $command['author']->login));
		return;
	}
	if (sizeof($tmxadd) > 0 && $tmxadd['votes'] > 0) {
		$votereq = $tmxadd['votes'];
		$votereq--;
		if ($votereq > 0) {
			$tmxadd['votes'] = $votereq;
			$msg = '{#server}>{#highlite} ' . $votereq . '{#emotic} more votes needed. Type {#highlite}/y{#emotic} to vote.';
			$message = $aseco->formatColors($msg);
			$aseco->addCall('ChatSendServerMessage', array($message));
			$tmxvotes[] = $command['author']->login;
			return;
		} else {
			$uid = $tmxadd['uid'];
			$jukebox[$uid]['FileName'] = $tmxadd['filename'];
			$jukebox[$uid]['Name'] = stripColors($tmxadd['name']);
			$jukebox[$uid]['Nick'] = $tmxadd['nick'];
			$jukebox[$uid]['tmx'] = true;
			$msg = '{#server}>{#emotic} Vote Passed! {#highlite}' . stripColors($tmxadd['name']) . '{#emotic} has been added to the jukebox.';
			$message = $aseco->formatColors($msg);
			$aseco->addCall('ChatSendServerMessage', array($message));
			$tmxadd = array();
			return;
		}
	} else {
		$message = $aseco->formatColors('{#server}>{#emotic} There are no TMX votes right now. Use {#highlite}/add <tmx_id> {#emotic} to start one.');
		$aseco->addCall('ChatSendServerMessageToLogin', array($message, $command['author']->login));
	}
}

function chat_add(&$aseco, &$command) {
	global $tmxadd, $tmxvote, $tmxdir, $feature_tmxadd;
	$player = $command['author'];			// $aseco->server->players->getPlayer($login);
	$login = $player->login;
	$tracktypes = array();
	$tracktypes['TMO'] = array(0 => 7458, 'original');
	$tracktypes['TMS'] = array(0 => 7455, 'sunrise');
	$tracktypes['TMN'] = array(0 => 7456, 'nations');
	$tracktypes['TMU'] = array(0 => 7459, 'united');

	$params = explode(' ', trim($command['params']));
	$tmxid = $params[0];

	// if only one parm, tracktype = TMU, else validate tracktype to be TMO/TMS/TMN, error out if it's not
	// parm 1 should always be numeric (tested further down)

	if ($feature_tmxadd == true) {
		if (sizeof($tmxadd) > 0) {
			$message = $aseco->formatColors('{#server}>{#emotic} There is already a TMX vote in progress. Wait for this to complete.');
			$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
			return;
		}
		if (is_numeric($tmxid) && $tmxid >= 0) {
			$tmxid = ltrim($tmxid, '0');
			if ( count($params) > 1 )
				{
				$tracktype = strtoupper($params[1]);
				if ( !isset($tracktypes[$tracktype]) )
					{
					$message = $aseco->formatColors("{#server}>{#emotic} '" . $tracktype . "' is not valid, try again with TMO/TMS/TMN/TMU.");
					$aseco->addcall('ChatSendServerMessageToLogin', array($message, $login));
					return;
					}
				}
			else
				{
				$tracktype = 'TMU';
				}
			$remotefile = 'http://' . $tracktypes[$tracktype][1] . '.tm-exchange.com/get.aspx?action=trackgbx&id=' . $tmxid;
			if (!$stream = fopen($remotefile, 'r')) {
				$message = $aseco->formatColors('{#server}>{#emotic} Track not found, or error downloading. Please check the TrackID.');
				$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
				return;
			} else {
				if (!$file = stream_get_contents($stream)) {
					$message = $aseco->formatColors('{#server}>{#emotic} Error Saving file - unable to get data. Please contact admin.');
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
					return;
				}
				$sepchar = substr($aseco->server->trackdir, -1, 1);
				$partialdir = $tmxdir . $sepchar . $tmxid . '.challenge.gbx';
				$localfile = $aseco->server->trackdir . $partialdir;
				if ( $aseco->debug )
					{
					$aseco->console_text('/add - tmxdir=' . $tmxdir);
					$aseco->console_text('/add - path + filename=' . $partialdir);
					$aseco->console_text('/add - aseco->server->trackdir = ' . $aseco->server->trackdir);
					}
				if (file_exists($localfile)) {
					if (!unlink($localfile)) {
						$message = $aseco->formatColors('{#server}>{#emotic} Error erasing old file. Please contact admin');
						$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
						return;
					}
				}
				if (!$lfile = fopen($localfile, 'w+')) {
					$message = $aseco->formatColors('{#server}>{#emotic} Error creating file. Please contact admin');
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
					return;
				}
				if (!fwrite($lfile, $file)) {
					$message = $aseco->formatColors('{#server}>{#emotic} Error saving file - unable to write data. Please contact admin');
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
					return;
				}
				fclose($lfile);		// can't do filesize while it's open, might have also caused problems with tmserver giving back info
				if (filesize($localfile) == $tracktypes[$tracktype][0]) {
					$message = $aseco->formatColors('{#server}>{#emotic} No such track on TMX');
					$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
					unlink($localfile);
					return;
				} else
					{
					$newtrk = getChallengeData($localfile, True);		// 2nd parm is whether or not to get players & votes required
					if ( $newtrk['votes'] == 500 && $newtrk['name'] == 'Not a GBX file')
						{
						$message = $aseco->formatColors('{#server}>{#emotic} No such track on TMX');
						$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
						unlink($localfile);
//						return;
						}
					else
						{
						$ctr = 0;

						getAllChallenges($player, '*', '*');		// populate players tracklist with all current tracks
						foreach($player->tracklist as $key)
							{
							if ( $key['uid'] == $newtrk['uid'] )
								{
								$msg = '{#server}>{#highlite} File already in track list, added via jukebox';
								$message = $aseco->formatColors($msg);
								$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
								$command['params'] = $ctr;
								chat_jukebox($aseco, $command);
								return;
								}
							$ctr++;
							}
						$tmxadd['filename'] = $partialdir;
						$tmxadd['votes'] = $newtrk['votes'];
						$tmxadd['name'] = stripcolors($newtrk['name']);
						$tmxadd['nick'] = stripcolors($command['author']->nickname);
						$tmxadd['uid'] = $newtrk['uid'];
						$tmxvote = true;
						$msg = '{#server}>{#highlite} ' . $tmxadd['nick'] . '{#emotic} is requesting {#highlite}' . $tmxadd['name'] . '$z{#emotic} from TMX. This will require {#highlite}' . $tmxadd['votes'] . '{#emotic} votes to pass. Type {#highlite}/y {#emotic} to vote.';
						$message = $aseco->formatColors($msg);
						$aseco->addCall('ChatSendServerMessage', array($message));
						}
				}
			}
		} else {
			$message = $aseco->formatColors('{#emotic}You must include a TMX track ID');
			$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
		}
	build_tmx_trackfile($aseco);
	}
else
	{
	$message = $aseco->formatColors('{#emotic}/add is not currently enabled on this server');
	$aseco->addCall('ChatSendServerMessageToLogin', array($message, $login));
	}
}
*/

function build_tmx_trackfile(&$aseco)
	{
	global $tmxdir;
	$td = $aseco->server->trackdir . $tmxdir;

	if ( is_dir($td) )
		{
		$dir = opendir($td);
		$fp = fopen($td . '/trackref.txt', 'w');
		while ( ($file = readdir($dir)) !== false )
			{
			if ( substr($file, -4) == '.gbx' )
				{
				$ci = getChallengeData($td . '/' . $file, false);
				$file = str_replace('.challenge.gbx', '', $file);
				fwrite($fp, $file . "\t" . $ci['author'] . "\t" . stripColors($ci['name']) . "\t" . $ci['coppers'] . CRLF);
				}
			}
		fclose($fp);
		closedir($dir);
		}

	}  //  build_tmx_trackfile

function getChallengeInfo($filename) {
	global $aseco, $tmxvoteratio;
	$resp = $aseco->client->addCall('AddChallenge', array($filename));
	if (!$aseco->client->multiquery()) {
		trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
		$response = array();
		$ret['name'] = 'failed';
		$ret['votes'] = 500;
		return $ret;
	} else {
		$track = $aseco->client->addCall('GetChallengeInfo', array($filename));
		if (!$aseco->client->multiquery()) {
			trigger_error('[' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage());
			$response = array();
			$ret['name'] = 'failed';
			$ret['votes'] = 500;
			return $ret;
		} else {
			$response = $aseco->client->getResponse();
			$ret['name'] = $response[$track][0]['Name'];
			$nbplrs = sizeof($aseco->server->players->player_list);
			$ret['votes'] = floor($nbplrs * $tmxvoteratio);
			if ($ret['votes'] < 1) {
				$ret['votes']++;
			}
		}
	}
}

function chat_list(&$aseco, &$command)
	{
	global $manialinkstack;
	
	$login = $command['author']->login;
	$player = $aseco->server->players->getPlayer($login);

	// split params into array
	if ( strlen(trim($command['params'])) > 0 )
		{
		$params = explode(' ', trim($command['params']));
		}
	else
		{
		$params = array();
		}

	$cmdcount = count($params);
	$env = "*";

	if ( $cmdcount > 0 )
		{
		foreach($params as $parm)
			{
			if ( substr($parm, 0, 4) == 'env:' )
				{
				$command['params'] = trim(str_replace($parm, '', $command['params']));	// if env: was here, remove it so you can search for stuff later
				$env = substr($parm, 4);			// get the text of the environment
				$cmdcount--;
				}
			}
		}

	if ( $cmdcount == 1 && $params[0] == 'help' )
		{
		$lf = "\n";
		$help = '/list will show tracks in rotation on the server' . $lf;
		$help .= '  - nofinish, tracks you haven\'t completed' . $lf;
		$help .= '  - xxx, where xxx is part of a track or author name' . $lf;
		$help .= '  - env:bay/coast/desert/island/rally/snow/stadium to list all tracks of that type' . $lf;
		$help .= '  - karma +/-#, shows all with karma <= or >= given value' . $lf;
		$help .= '    (example: /list karma -3 will show all tracks with karma <= -3)';

		popup_msg($login, $help);
		return;
		}
	elseif ( $cmdcount == 1 && ($params[0] == "nofinish") )
		{
		getChallengesNoFinish($player);
		}
	elseif ( $cmdcount == 2 && ($params[0] == "karma" ) )
		{
		$karmaval = intval($params[1]);
		getChallengesByKarma($player, $karmaval);
		}
	elseif ( $cmdcount == 0 )
		{
		$wildcard = "*";
		getAllChallenges($player, $wildcard, $env);
		}
	elseif ( $cmdcount >=1 && strlen($params[0]) > 0 )
		{
		$wildcard = $command['params'];
		getAllChallenges($player, $wildcard, $env);
		}

	if ( sizeof($player->tracklist) == 0 )
		{
		$width = 40;
		$height = 15;
		$hw = $width/2;
		$hh = $height/2;

		$manialink = "<?xml version='1.0' encoding='utf-8' ?><manialink id='90'><frame posn='-$hw $hh $manialinkstack'>";
		$manialink .= "<quad posn='0 0 -1' sizen='$width $height' style='Bgs1InRace' substyle='BgWindow1'/>";
		
		$manialink .= "<quad posn='0 0 0' sizen='$width 4' style='Bgs1InRace' substyle='BgTitle3'/>";
		$manialink .= '<label posn="'.$hw.' -0.5 1" halign="center" textsize="3" text="Tracklist"/>';
		$manialink .= "<quad posn='0 -4 0' sizen='$width 3' style='Bgs1InRace' substyle='BgTitle3_2'/>";
		$manialink .= "<label posn='$hw -4.5 1' halign='center' textsize='2' text='Empty result'/>";	
		$manialink .= "<label posn='$hw -8 1' halign='center' autonewline='1' sizen='".($width-2)." $height' textsize='2' text='Sorry, no tracks found with the selected filter.'/>";

		$manialink .= 	"<label posn='$hw -11 1' halign='center' style='CardButtonSmall' action='12' text='Close'/>";

		$manialink .= "</frame></manialink>";
		
		$aseco->addcall('SendDisplayManialinkPageToLogin', array($login, $manialink, 0, TRUE));
		return;
		}

	show_multi_msg($player);
	}

?>
