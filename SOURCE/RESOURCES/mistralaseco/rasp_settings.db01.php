<?php
//##################################################################
//#------------------------- Features -----------------------------#
//#  Specify here which features you would like to be activated    #
//#  You must enter true or false in lowercase only!               #
//##################################################################

//Set to true if you want the rank system active.
$feature_ranks = true;

//Set to true if you want all time's recorded, and /pb command to be active
$feature_stats = true;

//Set to true ONLY if you use the karma feature.
//If you set this to true when you are not will produce errors
$feature_karma = false;

//Set to true if you want jukebox functionality to be extended to include the TMX add feature.
$feature_tmxadd = true;


//##################################################################
//#-------------------- Performance Variables ---------------------#
//#  These variables are used in the main plugin.                  #
//#  They specify how much data should be used for calculations    #
//#                                                                #
//#  If your server slows down considerably when calculating       #
//#  ranks it is recommended that you lower/increase these values  #
//##################################################################

//Sets the maximum number of records per map
// Lower = Faster
$maxrecs = 400;

//Sets the minimum ammount of records required for a player to be ranked
// Higher = Faster
$minrank = 10;

//Sets the number of time's used to calculate a players average
// Lower = Faster
$maxavg = 1;


//##################################################################
//#-------------------- Jukebox Variables -------------------------#
//#  These variables are used by the jukebox.                      #
//##################################################################

//Specifies how large the track buffer is.
//If a track that is in the buffer gets requested it won't be added.
$buffersize = 60;

//Specifies the required vote ratio for a TMX request to be successful.
$tmxvoteratio = 0.6;

//The location of the tracks folder for saving TMX tracks.
//There must be full write permissions on this folder.
//In linux the command will be..  chmod 777.
// regardless of OS, use the / character for pathing
$tmxdir = "Challenges/Online";


//##################################################################
//#------------------------ IRC Variables -------------------------#
//#  These variables are used by the IRC plugin.                   #
//##################################################################
$CONFIG = array();
$CONFIG['server'] = 'blueyonder.uk.quakenet.org'; // server (i.e. irc.gamesnet.net)
$CONFIG['nick'] = 'botname'; // nick (i.e. demonbot
$CONFIG['port'] = 6667; // port (standard: 6667)
$CONFIG['channel'] = '#channel'; // channel  (i.e. #php)
$CONFIG['name'] = 'botlogin'; // bot name (i.e. demonbot)
$show_connect = false; //If set to true, the IRC connection messages will be displayed in the console.


//-----------------------------------------
//Do not modify anything below this line...
//-----------------------------------------
$linesbuffer = array();
$ircmsgs = array();
$outbuffer = array();
$con = array();
$jukebox = array();
$jb_buffer = array();
$tmxadd = array();
$tmxplaying = false;
$tmxvotes = array();
$tmxplayed  = false;

//##################################################################
//#------------------------- Mistral ------------------------------#
//##################################################################
// Main
$servername = 'DDADICATEDNAME';
$asecoadmin = "DTMLOGIN1";	//login of admin who can exit aseco (/admin exit)
$settingsfile = "mistral";	//name of matchsettings file (no .txt! - automatically added)
$trigger = array();			// do not touch!
// Remove ONE comment if you run a single environment server (f.e. TMNF)
_STMNF$singleenv = "Stadium";
//$singleenv = "Island";
//$singleenv = "Bay";
//$singleenv = "Coast";
//$singleenv = "Speed";
//$singleenv = "Rally";
//$singleenv = "Alpine";

// Jukebox Settings
$jb_price = 10;				// normal price for using the jukebox - set to 0 to have a free jukebox
$jb_price_2nd = 50;			// price for 2nd, 3rd a.s.o. track added to jukebox
$jp_price_played = 200;		// price for adding a played track (still in the jb_buffer)
$jb_maxtracks = 3;			// maximum tracks in the jukebox allowed per player

// Trackadmins can /admin add and /undo ingame - comma seperated strings
$trackadmins = array("", "");

// Track Evaluation
$eval_threshold = 6; // Evaluate a track after a player finished it X times

// definition of word triggers on chat and automatic responses from the server
$trigger[] = array("showforum", "http://www.tmsiab.tmu-xrated.de");
$trigger[] = array("showranking", "The server stores the Top$maxrecs records for every track. The overall average is the average rank on all tracks online and estimates your server rank. If you didn't drive a track, or didn't get to the top$maxrecs, the average will be $maxrecs.");
$trigger[] = array("showtmx", "Upload your track to \$l[http://united.tm-exchange.com]Trackmania Exchange\$l and give the 'id' of the 'external link' to any admin, to add the track to the server.");
$trigger[] = array("showdownload", "All tracks are available at \$l[http://www.tm-exchange.com]Trackmania Exchange\$l. \$F88(klick to browse)");
$trigger[] = array("showjukebox", "You can now add up to three tracks to the jukebox. 2nd and 3rd track will be more expensive (50 coppers). You can also add 'played' tracks without waiting, but the price is 200 coppers!");
$trigger[] = array("showpoints", "You only get points if you are \"official\". You become official automatically, if you are present at track start. You only get points for winning against drivers of same, or higher rank and only for drivers that finished the track.");
$trigger[] = array("showteam", "X-Rated: The Clan for the best people in the World: MEN!!!");
$trigger[] = array("showlottery", "When a track starts, a percentage of the server's coppers will be won by a player who finished the previous track. The more often you finish, the higher your chances to win. When the coppers get low, there won't be a lottery every round.");
$trigger[] = array("showdonation", "A donation goes directly to the server's account. The coppers are used for the lottery at the beginning of each track. This is an simple way to transfer coppers from 'wealthy' players to new players in a random manner.");
$trigger[] = array("showall", "show -forum, -download, -points, -ranking, -lottery, -donation, -team, -tmx, -jukebox");

// Localdatabase
$showtop=20;				// TopX records shown on the chat

// Billing+Lottery
$payee = "DLOGIN";	// server account
$partner = "heatseaker";	// partner account - gets loan if win
$winpercentage = 1;		// percentage of server coppers to win
$maxwin = 200;			// max amount to win
$minwin = 40;			// min amount to win
$finishminplayers = 1;	// min players finished for lottery
$onlineminplayers = 5;  // min players online for lottery

// Joinleave
$voteratio = 66;	// default vote ratio when last admin left the server

// Teamranking
$show_rank_auto=FALSE;		// Show team rank in chat after each round
$hide_rank_no_team=TRUE;	// Hide notification if not member of any team
$precision = 3;				// precision displayed of average xx.yyy (number of 'y')

// Playeradmin
$pa_perpage = 12;					// Number of players per page displayed in admin gui
$blacklist = "tmublacklist.txt";	// name of blacklist file
$guestlist = "tmuguestlist.txt";	// name of guestlist file

// Idlekick
$kickAfter = 3;				// Idle this number of challenges and you will be kicked
$resetOnChat = true;		// Reset idle counter on chat use
$resetOnCheckpoint = true;	// Reset idle counter when passing a checkpoint
$resetOnFinish = false;		// Reset idle counter when reaching the finish
							// don't use OnFinish in rounds or team mode, because every player will "finish"

// Autotime+Downloadlink
$tmxlinks = array();		// do not touch!
$tmxlinks['TMO']='http://original.tm-exchange.com/main.aspx?action=trackshow&id={ID}#auto';
$tmxlinks['TMS']='http://sunrise.tm-exchange.com/main.aspx?action=trackshow&id={ID}#auto';
$tmxlinks['TMN']='http://nations.tm-exchange.com/main.aspx?action=trackshow&id={ID}#auto';
$tmxlinks['TMU']='http://united.tm-exchange.com/main.aspx?action=trackshow&id={ID}#auto';
$tmxlinks['TMF']='http://tmnforever.tm-exchange.com/main.aspx?action=trackshow&id={ID}#auto';
		
$setmultiplicator = 3.6;	//Set the multiplicator for authortime (e.g. 7 x authorstime = new timelimit). Set to 0 to disable func
$trackbase = 60;			//This track's authotime will have ...
$timebase = 330;			//... this timelimit
$setmintime = 3;			//Set minimum timelimit in minutes
$setmaxtime = 7;			//Set maximum timelimit in minutes

// badwords
$mistral_badwordsallowed = 5;	
$bad_Words = array(	'putain','ptain','merde','fuck','cunt','fucker','fuckin','fucking',
						'wichs','fick','salop','siktirgit', 'shit','bordel','salope',
						'hitler', 'cock','faitchier','merda','scheis','arsch','scheise',
						'enculé','sucks','conerie','batard','bastard','enculer','connard',
						'baskasöle','baskasole','cocugu','kodugumun','cazo', 'fick',
						'penis', 'fotze', 'maul', 'pula', 'pizda','arschloch', 'tmdz',
						'sugi', 'cacat', 'pisat', 'labagiu', 'gaozar', 'scheisdreck',
						'orospu', 'bitch', 'pédé', 'gay', 'puta', 'schwul', 'kiri');

// infomessages
$mi_messages = array();	// do not touch!
$mi_messages[]='Server rank, top players and top teams can be seen environment specific.';
$mi_messages[]='Type "/help" to get the list of available commands.';
$mi_messages[]='If you closed the player information (HUD), type "/pi" to redisplay.';
$mi_messages[]='Click "Topteams" to see the Top20 registered teams of '.$servername.'$F88.';
$mi_messages[]='In the teamlist, click on a team to see the members.';
$mi_messages[]='Type "/team" to see how you can register your own team (READ THE EXAMPLE).';
$mi_messages[]='Type "/list <filter>" to filter Track- or Authornames.';
$mi_messages[]='Click "No Record" to see the tracks you don\'t have a record.';
$mi_messages[]='Click "Tracklist" to see all available tracks.';
$mi_messages[]='Your rank is shown in the tracklist (green<'.((int)($maxrecs/3)).', yellow<'.((int)(2*$maxrecs/3)).", red<$maxrecs)";
$mi_messages[]='The time and date of your last record is shown in the tracklist.';
$mi_messages[]='Click on any rank in the tracklist to change the sort order.';
$mi_messages[]='Click on any date in the tracklist to change the sort order.';
$mi_messages[]='Click on the trackname of the "Tracklist" to add the track to the jukebox';
$mi_messages[]='Click on an environment of the "Tracklist" to filter the list.';
$mi_messages[]='Click on "Statistic" to see your time played on this server and number of you Top5 records.';
$mi_messages[]='Click on "Records" to see the Top20 records of the current track.';
$mi_messages[]='Click on "TopPlayers" to see the Top20 ranked players of '.$servername.'$F88.';
$mi_messages[]='Click on "JB: <nr.>" in the HUD to see the content of the jukebox.';
$mi_messages[]='Click on the server rank (to the left) to see your environment specfic ranking.';
$mi_messages[]='The timelimit is dynamically calculated for every track based on the authortime.';
$mi_messages[]='When an administrator is online, votes become automatically disabled.';
$mi_messages[]="The serverranking shows your average rank on all tracks (worst=$maxrecs)";
$mi_messages[]='You can now add a played track to the jukebox again, but beware: this costs 200 coppers!';
$mi_messages[]='You can now add up to three tracks to the jukebox, but beware: 2nd and 3rd track cost 50 coppers!';
$mi_messages[]='Your ladderrank shown in the playerlist (TAB) is enviroment specific.';
$mi_messages[]='If a badword isn\'t recognize by the bot, that doesn\'t mean you won\'t get kicked/banned by an admin with a single mouseclick.';
$mi_messages[]='The rank shown in the chat, when a player joins, is environment specific.';
$mi_messages[]='If you like our tracks you should add this server to your favorites (ESC->Advanced Options).';
$mi_messages[]='If you want us to test one of you tracks, you have to upload it to $l[http://www.tm-exchange.com]http://www.tm-exchange.com$l and tell the ID to an admin.';
$mi_messages[]='Reality is an illusion, caused by lack of alcohol.';
$mi_messages[]='Speed is the key to spectacular accidents.';
$mi_messages[]='Fascinating: If you change only four letters in the word "Milk", you will get "Beer". Coincidence? Nah!';
$mi_messages[]='This race was proudly presented by: CocaCola, MC Donald\'s, Burger King, Pizza Hut and Weight Watchers.';
$mi_messages[]='Only two things are infinite, the universe and human stupidity. I\'m not sure about the first one. (A. Einstein)';
$mi_messages[]='This information is absolutely uninformative.';
$mi_messages[]='There will be more information after the next track.';
$mi_messages[]="If coppers available, there might be a lottery at the beginning of a track. All players who finished the previous track might win. If you finish more often, you have higher chances.";
$mi_messages[]='If you had fun, consider to make a donation for the lottery (Use button on top).';
$mi_messages[]='There\'s a manialink hidden on your screen. Find it, click it and you will get more server informations.';
$mi_messages[]="After you finished a track $eval_threshold times, you will be asked to evaluate it. Please be fair.";
$mi_messages[]="If you evaluated a track (after $eval_threshold and more finishs), just klick on the display (top right) of your evaluation, to reevaluate.";


?>
