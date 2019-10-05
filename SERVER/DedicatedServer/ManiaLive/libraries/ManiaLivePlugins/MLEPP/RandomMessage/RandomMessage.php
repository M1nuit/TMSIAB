<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Random Message
 * @date 04-01-2011
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP team
 * @copyright 2010 - 2011
 *
 * ---------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * You are allowed to change things or use this in other projects, as
 * long as you leave the information at the top (name, date, version,
 * website, package, author, copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */

namespace ManiaLivePlugins\MLEPP\RandomMessage;

use ManiaLive\Utilities\Console;
use ManiaLib\Utils\TMStrings as String;
use ManiaLive\DedicatedApi\Connection;
use \ManiaLive\PluginHandler\PluginHandler;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class RandomMessage extends \ManiaLive\PluginHandler\Plugin {

	private $tick;
	private $config;

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	function onInit() {
		$this->setVersion(1050);
		$this->setPublicMethod('getVersion');
		$this->tick = 0;
	}

	 /**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	function onLoad() {
		$this->enableDedicatedEvents();
		$this->enableTickerEvent();
		$this->config = Config::getInstance();
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: RandomMessage r' . $this->getVersion());
		if ($this->config->type == "delay" || $this->config->type == "endChallenge") {
			
		} else {
			Console::println('[' . date('H:i:s') . '] [Random Message] Warning: Check your config.ini, the type should be either "delay" or "endChallenge" !');
		}
	}

	 /**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */
	function onReady() {
		$pluginHandler = PluginHandler::getinstance();
		$plugins = $pluginHandler->getLoadedPluginsList();

		foreach ($plugins as $plugin) {
			list($author, $plugin) = explode('\\', $plugin);
			if ($author == "Standard") {
				switch ($plugin) {
					case 'Dedimania':
						$this->config->messages[] = 'Use $o$ea0$i/dedimania ' . $this->config->infocolor . ' to see the online world record for the current track.';
						break;
				}
			} if ($author == "oliverde8") {
				switch ($plugin) {
					case 'HudMenu':
						$this->config->messages[] = 'The $o$ea0$iMenu ' . $this->config->infocolor . ' button contains clickable versions of all commands that are available on this server.';
						break;
				}
			}
			if ($author == "MLEPP") {
				switch ($plugin) {
					case 'Admin':
						$this->config->messages[] = 'Use $o$ea0$i/players ' . $this->config->infocolor . ' to see the logins of all players on this server.';
						break;
					case 'ChatEmotes':
						$this->config->messages[] = 'This server has many commands to ease communication, like $o$ea0$i/hi ' . $this->config->infocolor . ', $o$ea0$i/lol ' . $this->config->infocolor . ' and $o$ea0$i/gg';
						$this->config->messages[] = 'You can use the chat emotes in combination with a login to show nicknames, for example $o$ea0$i/nl  $ilogin$i';
						$this->config->messages[] = 'You can use the chat emotes in combination with a text, for example $o$ea0$i/hi text goes here ' . $this->config->infocolor . ' and $o$ea0$i/hi  $ilogin$i  text goes here ' . $this->config->infocolor . '.';
						break;
					case 'ChatLog':
						$this->config->messages[] = 'Use $o$ea0$i/chatlog ' . $this->config->infocolor . ' to read back what has been written in the chat recently.';
						break;
					case 'ChatMe':
						$this->config->messages[] = 'Use $o$ea0$i/me text goes here ' . $this->config->infocolor . ' to make a chatmessage expressing your current state. (ie $o$ea0$i/me is winning ' . $this->config->infocolor . ' )';
						$this->config->messages[] = 'Use $o$ea0$i/me text goes here  $ilogin$i ' . $this->config->infocolor . ' to make a chatmessage using 2 nicknames. (ie $o$ea0$i/me is wishing you goodnight,  $ilogin$i  ' . $this->config->infocolor . ' )';
						break;
					case 'Communication':
						$this->config->messages[] = 'Use $o$ea0$i/players ' . $this->config->infocolor . ' to find the login of another player and $o$ea0$i/t invite  $ilogin$i ' . $this->config->infocolor . ' to invite him in a private chat channel.';
						$this->config->messages[] = 'Use $o$ea0$i/r ' . $this->config->infocolor . ' to send a new message to the person that you sent your last private message to.';
						$this->config->messages[] = 'Use $o$ea0$i/t invite  $ilogin$i  ' . $this->config->infocolor . ' to create a private chat channel and/or invite someone to join.';
						$this->config->messages[] = 'Use $o$ea0$i/t text goes here ' . $this->config->infocolor . ' to send a message in the private channel that you joined.';
						$this->config->messages[] = 'Use $o$ea0$i/t leave ' . $this->config->infocolor . ' to leave a private chat channel.';
						break;
					case 'Core':
						$this->config->messages[] = 'For more information about $fffMLEPP ' . $this->config->infocolor . 'please visit: $fff$lhttp://mlepp.trackmania.nl$l' . $this->config->infocolor . '!';
						$this->config->messages[] = 'For help with $fffMLEPP ' . $this->config->infocolor . 'commands, use $o$ea0$i/<commandname> help ' . $this->config->infocolor . '!';
						break;
					case 'CustomChat':
						$this->config->messages[] = 'The CustomChat plugin filters out ugly things from the chat and can indicate who are admins and operators.';
						break;
					case 'DonatePanel':
						$this->config->messages[] = 'Do you like our server? Please donate some planets!';
						break;
					case 'ForceMusics':
						$this->config->messages[] = 'Use $o$ea0$i/mlist ' . $this->config->infocolor . ' to see the songs on this server, and click on the names to add songs to the jukebox.';
						$this->config->messages[] = 'Use $o$ea0$i/musicbox  $inumber$i ' . $this->config->infocolor . ' to add the corresponding song in the mlist to the musicbox.';
						$this->config->messages[] = 'Click on the music widget to open the music list and add a song by clicking on it\'s title.';
						break;
					case 'IdleKick':
						$this->config->messages[] = 'This server forces inactive players into spectator, and eventually kicks them. Use spec when going afk please.';
						break;
					case 'Jukebox':
						$this->config->messages[] = '$o$ea0$i/list help ' . $this->config->infocolor . ' gives you an overview of the different ways to find specific tracks on this server.';
						$this->config->messages[] = 'Use $o$ea0$i/list ' . $this->config->infocolor . ' to see the tracks on this server, and click on the names to add tracks to the jukebox.';
						$this->config->messages[] = 'Use $o$ea0$i/jukebox list ' . $this->config->infocolor . ' to see the tracks in the jukebox.';
						$this->config->messages[] = 'Use $o$ea0$i/list rank  $inumber$i ' . $this->config->infocolor . ' to find the tracks where you have a certain rank.';
						$this->config->messages[] = '$o$ea0$i/list first  $inumber$i  ' . $this->config->infocolor . ' shows you all tracks where your record is within the first <number> of records. You can also use $o$ea0$i/list nofirst <number>' . $this->config->infocolor . ' to see all the other tracks.';
						$this->config->messages[] = 'Use $o$ea0$i/list shorter  $iseconds$i  ' . $this->config->infocolor . ' to find the tracks that are shorter than a certain number of seconds.';
						$this->config->messages[] = 'Use $o$ea0$i/list longer  $iseconds$i  ' . $this->config->infocolor . ' to find the tracks that are longer than a certain number of seconds.';
						$this->config->messages[] = 'With $o$ea0$i/list tmx_type  $itype$i  ' . $this->config->infocolor . ' you can find all tracks of a specific type. The types are Race, Puzzle, Platform, Stunts and Shortcut.';
						$this->config->messages[] = 'With $o$ea0$i/list tmx_dif  $idifficulty$i  ' . $this->config->infocolor . ' you can find all tracks of a specific difficulty. The difficulties are Beginner, Intermediate, Expert and Lunatic.';
						$this->config->messages[] = 'With $o$ea0$i/list tmx_style  $istyle$i  ' . $this->config->infocolor . ' you can find all tracks of a specific style. The styles are Normal, Stunt, Maze, Offroad, Laps, Fullspeed, Lol, Tech, Speedtech, RPG and Presforward.';
						$this->config->messages[] = 'Use $o$ea0$i/list nofinish ' . $this->config->infocolor . ' to see a list with the tracks on this server that you haven\'t finished yet.';
						$this->config->messages[] = 'You can search tracks with the $o$ea0$i/list  $itrackname$i  ' . $this->config->infocolor . ' command. You can also use a part of the name.';
						$this->config->messages[] = 'You can search tracks with the $o$ea0$i/list  $iauthorname$i  ' . $this->config->infocolor . ' command. You can also use a part of the name.';
						$this->config->messages[] = 'Find tracks where you set a good rec with the $o$ea0$i/list goldtime ' . $this->config->infocolor . ' command. You can also use $o$ea0$iauthortime' . $this->config->infocolor . ' , $o$ea0$isilvertime ' . $this->config->infocolor . ' and $o$ea0$ibronzetime ' . $this->config->infocolor . '.';
						$this->config->messages[] = 'Find tracks where you haven\'t set a good rec with the $o$ea0$i/list nogoldtime ' . $this->config->infocolor . ' command. You can also use $o$ea0$inoauthortime' . $this->config->infocolor . ' , $o$ea0$inosilvertime ' . $this->config->infocolor . ' and $o$ea0$inobronzetime ' . $this->config->infocolor . '.';
						break;
					case 'Karma':
						$this->config->messages[] = 'Use $o$ea0$i--' . $this->config->infocolor . ' , $o$ea0$i-' . $this->config->infocolor . ' , $o$ea0$i+' . $this->config->infocolor . ' , $o$ea0$i++ ' . $this->config->infocolor . ' and ' . $this->config->infocolor . '$o$ea0$i+++ ' . $this->config->infocolor . ' to give your opinion about the current track.';
						$this->config->messages[] = 'Please vote for this track to express your opinion.';
						break;
					case 'LocalRecords':
						$this->config->messages[] = 'Use $o$ea0$i/recs ' . $this->config->infocolor . ' to see the local records on the current track.';
						break;
					case 'Rankings':
						$this->config->messages[] = 'Use $o$ea0$i/top100 ' . $this->config->infocolor . ' to see the 100 best ranked players with their statistics.';
						$this->config->messages[] = 'Use $o$ea0$i/rank ' . $this->config->infocolor . ' to see your actual server rank and your average.';
						break;
					case 'ServerInfo':
						$this->config->messages[] = '$o$ea0$i/serverinfo ' . $this->config->infocolor . ' gives information about this server: Who is running it, links to other servers, the available commands and the installed plugins.';
						break;
					case 'ServerMail':
						$this->config->messages[] = 'Use $o$ea0$i/mail  $ilogin$i  text goes here  ' . $this->config->infocolor . ' to send a mail to someone, the mail will be stored on the server and can be read anytime.';
						$this->config->messages[] = 'Use $o$ea0$i/mail admin text goes here  ' . $this->config->infocolor . ', you will get a token, enter the token and your mail will be sent to all admins.';
						$this->config->messages[] = '$o$ea0$i/mail read ' . $this->config->infocolor . ' opens your mailbox, where you can read and manage your received mail.';
						$this->config->messages[] = 'Send yourself a mail if you want to make a memo, it will be safe in your mailbox.';
						break;
					case 'ManiaExchange':
						$this->config->messages[] = 'Click the MX logo to see more information about the current track.';
						$this->config->messages[] = 'If you like this track, click the MX logo to download or award it.';
						$this->config->messages[] = 'Press restart or respawn to load the track image in the MX-Info window.';
						break;
					case 'Votes':
						$this->config->messages[] = 'Use $o$ea0$i/skip ' . $this->config->infocolor . ' to start a vote to go directly to the next track.';
						$this->config->messages[] = 'Use $o$ea0$i/restart ' . $this->config->infocolor . ' to start a vote to replay a track.';
						$this->config->messages[] = 'You can see the statistics of a running vote in the vote widget in the top of the screen.';
						break;
					case 'Widgets':
						$this->config->messages[] = 'Some of the widgets automatically hide at first checkpoints.';
						$this->config->messages[] = 'Click the widget button to toggle the hide function. Yellow is Autohide, Green is Always visible and Red is Hidden.';
						break;
				}
			}
		}
	}

	 /**
	 * onTick()
	 * Function called every second, provides the delay messages.
	 *
	 * @return void
	 */
	function onTick() {
		if ($this->config->type == 'delay') {
			if ($this->tick != ($this->config->delay - 1)) {
				$this->tick++;
			} else {
				$this->tick = 0;
				$msgnum = rand(0, (count($this->config->messages) - 1));
				$message = $this->config->infoname . $this->config->infocolor . $this->config->messages[$msgnum];
				$this->connection->chatSendServerMessage($message);
			}
		}
	}

	 /**
	 * onEndChallenge()
	 * Function called on end of challenge, providing the end of challenge messages.
	 *
	 * @param mixed $rankings
	 * @param mixed $challenge
	 * @param mixed $wasWarmUp
	 * @param mixed $matchContinuesOnNextChallenge
	 * @param mixed $restartChallenge
	 * @return void
	 */
	function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge) {
		if ($this->config->type == 'endChallenge') {
			$msgnum = rand(0, (count($this->config->messages) - 1));
			$message = $this->config->infoname . $this->config->infocolor . $this->config->messages[$msgnum];
			$this->connection->chatSendServerMessage($message);
		}
	}

}

?>