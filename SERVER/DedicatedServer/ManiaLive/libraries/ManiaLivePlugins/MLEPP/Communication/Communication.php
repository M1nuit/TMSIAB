<?php
/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Communication
 * @date 04-01-2010
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author Petri "reaby" Järvisalo <petri.jarvisalo@mbnet.fi>
 *         Max "TheM" Klaversma <maxklaversma@gmail.com>
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

namespace ManiaLivePlugins\MLEPP\Communication;

use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\AdminPanel\Structures\AdminCommand;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class Communication extends \ManiaLive\PluginHandler\Plugin {
	private $pm;
	private $teams = array();
	private $helpAdmin = "The admin chat channel is a private channel for all admins.
Only admins can use and see this channel.

\$wUsage\$z:
\$o/a message goes here\$z - Write a message in the admin channel";
	private $helpTeam = "The Group Chat allows to make a private chat channel.
Only invited people in the same channel will be able to read and respond to the chat.

\$wUsage\$z:
\$o/g invite <login>\$z - Invite player to channel.
\$o/g message here \$z - Write a message in the channel you joined.
\$o/g leave\$z - Leave the chat channel you joined.";
	private $helpReply = "The respond function sends another private message to the person you sent your last message to.
Only you and the receipient can see the message.
You will need to send an initial pm to someone to be able to use this function.
(\$i/pm help\$z for more info)

\$wUsage\$z:
\$o/r message here \$z - Send another message to the same person that you sent the last pm.";
	private $helpPm = "The pm function allows you to send a private message.
Only you and the receipient can see the message.
You can use the login or ID of a player.
Find the login or ID of a player with \$i/players\$z.
Use the /r function to make a conversation easier (\$i/r help\$z for more info)

\$wUsage\$z:
\$o/pm login message goes here \$z - Send a private message to that login.
\$o/r message goes here\$z - Send another message to the same person that you sent the last pm.";

	private $descAdmin = "Usage: /a your message goes here";
	private $descTeam = "Usage: /g your message goes here";
	private $descReply = "Usage: /r your message goes here";
	private $descPm = "Usage: /pm login your message goes here";
	private $mlepp = null;
    /**
     * onInit()
     * Function called on initialisation of ManiaLive.
     *
     * @return void
     */

	function onInit() {
		// this needs to be set in the init section
		$this->setVersion(1050);
        $this->setPublicMethod('getVersion');
	}

    /**
     * onLoad()
     * Function called on loading of ManiaLive.
     *
     * @return void
     */

	function onLoad() {
        Console::println('['.date('H:i:s').'] [MLEPP] Plugin: Communication r'.$this->getVersion() );
		$this->enableDedicatedEvents();

		$command = $this->registerChatCommand('pm','sendPersonalMessage',-1,true);
		$command->help = $this->descPm;

		$command = $this->registerChatCommand('r','sendReply',-1,true);
		$command->help = $this->descReply;

		$command = $this->registerChatCommand('a','adminChat',-1,true);
		$command->help = $this->descAdmin;

		unset($command);
		$command = $this->registerChatCommand('g','teamChat',-1,true);
		$command->help = $this->descTeam;


		$this->mlepp = Mlepp::getInstance();
	}

	function onUnload() {
			parent::onUnload();
	}

	 /**
	 * onPlayerDisconnect()
     * Function called when a player disconnects.
	 *
	 * @param mixed $login
	 * @return void
	 */

	function onPlayerDisconnect($login) {
		if(isset($this->pm[$login])) {
			unset($this->pm[$login]);
		}
		$this->teamPart($login,true);
	}

	 /**
	 * Group Chat()
     * Function providing the Group Chat (/g).
	 *
	 * @return
	 */

	function teamChat() {
		$args = func_get_args();
		$login = array_shift($args);
		$param = implode(" ",$args);
		if (isset($args[1]))
		{
			$param2 = $args[1];
		} else {
			$param2 = NULL;
		}

		$player = $this->storage->getPlayerObject($login);

		if($param == NULL || $param == "help") {
			$this->showHelp($login,$this->helpTeam);
			return;
		}

		if(isset($args[0])) {
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Player used /g '.$param);
			switch($args[0]) {
				case "invite":
						$this->teamInvite($login,$param2);
						break;
				case "part":
						$this->teamPart($login);
						break;
				case "quit":
						$this->teamPart($login);
						break;
				case "leave":
						$this->teamPart($login);
						break;
				case "list":
						$this->teamList($login);
						break;
				default:
					if (isset($args[0])) {
					$this->doTeamChat($login,$param);
					} else {
						$this->showHelp($login,$this->helpTeam);
					}
					break;
			}
		}
	}

	 /**
	 * doGroup Chat()
     * Function used to send messages to the team.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @return void
	 */

	function doTeamChat($login, $param) {
        $player = $this->storage->getPlayerObject($login);
        $fromNick = $player->nickName;
        if(is_array($this->teams)) {
            foreach($this->teams as $team => $data) {
                if(in_array($login, $data)) {
                    foreach($data as $login) {
                        $player = $this->storage->getPlayerObject($login);
                        $this->mlepp->sendChat('$48c'.$fromNick.'$z$s$fff$w » $z$s$8cf'.$param, $player);
                    }
                    Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Sending '.$param.' to team.');
	           }
	       }
        }
	}


	 /**
	 * teamList()
     * Function providing the /g list command.
	 *
	 * @param mixed $login
	 * @return
	 */

	function teamList($login) {
		$player = $this->storage->getPlayerObject($login);
		$myChannel = NULL;
		foreach($this->teams as $team => $data) {
            if(in_array($login, $data)) {
                $myChannel = $team;
                break;
            }
		}

		if($myChannel == NULL) {
            $this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cfTrying to list players when not in channel!', $player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Trying to list players while not in channel.');
            return;
		}

		$nick = "";
		foreach ($this->teams[$myChannel] as $llogin) {
				$pplayer = $this->storage->getPlayerObject($llogin);
				$nick .= $pplayer->nickName.'$z$s$fff, ';
		}
		$nick =	substr($nick,0,-2);
		$this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cf At channel: '.$nick, $player);
        Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] At channel: '.$nick.'.');
	}

	 /**
	 * teamInvite()
     * Function providing the /g invite command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @return
	 */

	function teamInvite($login, $param) {
		$player = $this->storage->getPlayerObject($login);

		// check for same login
		if ($login == $param) {
			$this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cfTrying to invite yourself, eh?', $player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Tried to invite his-/herself.');
			return;
		}
		// check for valid playerlogin
		if (is_numeric($param)) $param = $this->mapPlayer($param);

		if (!$this->playerExists($param)) {
			$this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cf'.$param.' is an unknown player on the server!', $player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Tried to invite unknown player '.$param.'.');
			return;
		}


		// find my channel
		$myChannel = NULL;
		foreach($this->teams as $team => $data) {
			if(in_array($login, $data) ) {
				$myChannel = $team;
				break;
			}
		}

		// if no channel, make one and join to it.
		if($myChannel == NULL) {
			$unique = md5( uniqid() );
			$unique = substr($unique,0,6);
			$this->teams[$unique][] = $login;
			$this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cfnew group channel has been created', $player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Created Group Chat channel '.$unique);
			$myChannel = $unique;
		}

		// check for target player channel
		foreach($this->teams as $team => $data) {
            if(in_array($param, $data)) {
                $targetPlayer = $this->storage->getPlayerObject($param);
                $this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cf'.$param.' is already at a chat channel, tell him/her to leave the channel first!', $player);
                Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Tried to invite player who is already in a channel ('.$param.').');
                return;
            }
        }

		// invite
		$this->teams[$myChannel][] = $param;

        $allnicks = "";
        foreach($this->teams[$myChannel] as $llogin) {
            $pplayer = $this->storage->getPlayerObject($llogin);
            $allnicks .= $pplayer->nickName.'$z$s$fff, ';
        }
        $allnicks =	substr($allnicks,0,-2);

        //Broadcast Joined message to channel
        $jplayer = $this->storage->getPlayerObject($param);
        $Nick = $jplayer->nickName;

        foreach($this->teams[$myChannel] as $llogin) {
            $player = $this->storage->getPlayerObject($llogin);
            $this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cf'.$Nick.'$z$s$8cf has been invited to group channel', $player);
            $this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cf Players at channel: '.$allnicks, $player);
        }
        Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Succesfully invited '.$param.' into the channel.');
	}

	 /**
	 * teamJoin()
     * Function providing the /g join command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @return
	 */

	function teamJoin($login, $param) {
		foreach($this->teams as $team => $data) {
            if(in_array($login, $data)) {
                $player = $this->storage->getPlayerObject($login);
                $this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cf You are already in a chat channel, you can only be in one channel at a time!', $player);
                Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Tried to join channel, already in a channel.');
                return;
            }
        }
		if(isset($this->teams[$param])) {
			$this->teams[$param][] = $login;
			$jplayer = $this->storage->getPlayerObject($login);
			$Nick = $jplayer->nickName;
			foreach($this->teams[$param] as $llogin) {
				$player = $this->storage->getPlayerObject($llogin);
				$this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cf'.$Nick.'$z$s$8cf Joined channel '.$param, $player);
			}
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Succesfully joined channel.');
		} else {
			$this->teams[$param][] = $login;
			$player = $this->storage->getPlayerObject($login);
			$this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cfCreated Group Chat channel '.$param, $player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Succesfully created channel '.$param.'.');
		}
	}

	 /**
	 * teamPart()
     * Function providing the /g leave command.
	 *
	 * @param mixed $login
	 * @param bool $leaveServer
	 * @return
	 */

	function teamPart($login, $leaveServer = false) {
        if($this->playerExists($login)) {
            $jplayer = $this->storage->getPlayerObject($login);
			$nick = $jplayer->nickName;
        } else {
			$nick = "[nickname not set]";
        }

		$myChannel = NULL;
		// check for channel
		if(is_array($this->teams)) {
			foreach($this->teams as $team => $data) {
				if(in_array($login, $data) ) {
					$myChannel = $team;
					break;
				}
			}
		}

		// if no channel do nothing.
		if($myChannel == NULL) {
                  return;
		}
		// one player at channel
		if(count($this->teams[$myChannel]) == 1) {
			// destroy the channel completely
			unset($this->teams[$myChannel]);
			// if player doesn't leave server, show chat message
			if(!$leaveServer) {
                $targetPlayer = $this->storage->getPlayerObject($login);
                $this->mlepp->sendChat('$48cGroup Chat $fff$w »$z$s$8cfYou have left the Group Chat & channel removed.', $targetPlayer);
                Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Left the Group Chat, channel automaticly removed.');
			}
		} else {
		// multiple players at channel
			$new = array();
			// search channel for all players
			foreach($this->teams[$myChannel] as $llogin) {
                        //construct new channel, excluding the player who left.
                            if($llogin != $login) {
                                $new[] = $llogin;
                            }

                            //if player leaves the server
                            if($leaveServer) {
                                // don't show chat message for the one who leaves
                                if($llogin == $login) {
                                    continue;
                                }

                                // and show the message to others at chat
                                    $player = $this->storage->getPlayerObject($llogin);
                                    $this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cf'.$nick.'$z$s$8cf leaves channel.', $player);
                                }
                                else {
                                // if player doesn't leave server, show leave message to all
                                    $player = $this->storage->getPlayerObject($llogin);
                                    $this->mlepp->sendChat('$48cGroup Chat $fff$w»$z$s$8cf'.$nick.'$z$s$8cf leaves channel.', $player);
                                }
                            }
                                    Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Left the Group Chat channel.');
                                    // assign new constructed team as current channel
                                    $this->teams[$myChannel] = $new;
                            }

	}


	 /**
	 * adminChat()
     * Function providing the Admin Chat (/a).
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @return
	 */

	function adminChat() {
		// deny nonadmins
		$args = func_get_args();
		$login = array_shift($args);
		$param = implode(" ",$args);

		$player = $this->storage->getPlayerObject($login);
		$fromNick = $player->nickName;
		if(! $this->mlepp->AdminGroup->hasPermission($login,'admin')) {
			$this->mlepp->sendChat("You are not an admin of this server. So you can't use the Admin chat channel.",$player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Tried to use the Admin chat channel.');
			return;
		}

		if($param == NULL || $param == "help") {
			$this->showHelp($login,$this->helpAdmin);
			return;
		}

		$admins = array();
		// get admins to $admins array.
		foreach($this->storage->players as $player) {
			$login = $player->login;
			if($this->mlepp->AdminGroup->hasPermission($login,'admin')) $admins[] = $player;
		}

		foreach($this->storage->spectators as $player) {
			$login = $player->login;
			if($this->mlepp->AdminGroup->hasPermission($login,'admin')) $admins[] = $player;
		}

		//send chat to adminchannel.
		foreach($admins as $player) {
			$this->mlepp->sendChat('$d00'.$fromNick.'$z$s$fff$w » $z$s$f22'.$param, $player);
		}
        Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Sent message to the Admin Chat: '.$param.'.');
	}

/*
$d4fPersonal Messages $fff$w»$z$s$e9f testtest
$d00Admin Chat $fff$w»$z$s$f22 testtest
$48cGroup Chat $fff$w»$z$s$8cf testtest
*/

	 /**
	 * sendPersonalMessage()
     * Function used for sending personal messages.
	 *
	 * @param mixed $login
	 * @param mixed $targetLogin
	 * @param mixed $param
	 * @return
	 */

	function sendPersonalMessage() {
		$args = func_get_args();
		$login = array_shift($args);
		$targetLogin = array_shift($args);
		$param = implode(" ",$args);
		$player = $this->storage->getPlayerObject($login);

		if($targetLogin == NULL || $targetLogin == "help") {
			$this->showHelp($login,$this->helpPm);
			return;
		}

		if(is_numeric($targetLogin)) {
            $targetLogin = $this->mapPlayer($targetLogin);
        }

		if($login == $targetLogin) {
			$this->mlepp->sendChat('$d4fPersonal Messages $fff$w»$z$s$e9f Sorry, you can\'t send a message to yourself.',$player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Tried to send him-/herself a message.');
			return;
		}

		if($targetLogin == NULL) {
			$this->mlepp->sendChat('$d4fPersonal Messages $fff$w»$z$s$e9f Couldn\'t send message. The ID you have could not been mapped to any player on the server.', $player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Tried to send a message to unknown user.');
			return;
		}

		if(!$this->playerExists($targetLogin)) {
			$this->mlepp->sendChat('$d4fPersonal Messages $fff$w»$z$s$e9f Couldn\'t send message to login $fff'.$targetLogin.'$e9f. player not found on server.',$player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Tried to send a message to unknown user.');
			return;
		}

		$targetPlayer = $this->storage->getPlayerObject($targetLogin);

		//set reply address
		$this->pm[$login] = $targetLogin;
		$nick = $player->nickName;
		$targetNick = $targetPlayer->nickName;
		$this->mlepp->sendChat('$d4f Message to '.$targetNick.'$z$s $d4f($fff'.$targetLogin.'$d4f) $fff$w» $z$s$e9f'.$param,$player);
		$this->mlepp->sendChat('$d4f Message from '.$nick.'$z$s $d4f($fff'.$login.'$d4f) $fff$w» $z$s$e9f'.$param,$targetPlayer);
        Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Sent message to '.$targetLogin.': '.$param.'');
	}

	 /**
	 * sendReply()
     * Function providing the /r command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @return
	 */

	function sendReply($login, $param = NULL) {
		$args = func_get_args();
		$login = array_shift($args);
		$param = implode(" ",$args);
		$player = $this->storage->getPlayerObject($login);
		if($param == NULL || $param == "help") {
			$this->showHelp($login,$this->helpReply);
			return;
		}

		if(!isset($this->pm[$login]))  {
			$this->mlepp->sendChat('$d4fPersonal Messages $fff$w»$z$s$e9f Can\'t reply - no one to reply to. Usage /r text here',$player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Can\'t reply - no one to reply to.');
			return;
		}

		$targetLogin = $this->pm[$login];
		$targetPlayer = $this->storage->getPlayerObject($targetLogin);

		if(!$this->playerExists($targetLogin)) {
			$this->mlepp->sendChat('$d4fPersonal Messages $fff$w»$z$s$e9f Couldn\'t send message to login $fff'.$targetLogin.'$e9f, player not found on server.',$player);
            Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Couldn\'t send message to login '.$targetLogin.', player not found on server.');
			return;
		}

		$nick = $player->nickName;
		$targetNick = $targetPlayer->nickName;
		$this->mlepp->sendChat('$d4f Message to '.$targetNick.'$z$s $d4f($fff'.$targetLogin.'$d4f) $fff$w» $z$s$e9f'.$param, $player);
		$this->mlepp->sendChat('$d4f Message from '.$nick.'$z$s $d4f($fff'.$login.'$d4f) $fff$w» $z$s$e9f'.$param, $targetPlayer);
        Console::println('['.date('H:i:s').'] [MLEPP] [Communication] ['.$login.'] Sent message to '.$targetLogin.': '.$param.'');
	}

	 /**
	 * showHelp()
	 * Function used for showing the help window.
     *
	 * @param mixed $login
	 * @param mixed $text
	 * @return void
	 */

	function showHelp($login,$text) {
		$this->callPublicMethod('MLEPP\Core', 'showHelp', $login, "Help for plugin ".$this->getName(), $text);
	}

	 /**
	 * playerExists()
     * Function used for checking if the player exists.
	 *
	 * @param mixed $login
	 * @return
	 */

	function playerExists($login) {
		if(array_key_exists($login, $this->storage->players)) {
			return true;
		} else {
			if(array_key_exists($login, $this->storage->spectators)) {
			return true;
			}
		}
		return false;
	}

	 /**
	 * mapPlayer()
     * Function used for mapping a player id.
	 *
	 * @param mixed $id
	 * @return
	 */

	function mapPlayer($id) {
		$i = 0;
		foreach($this->storage->players as $player) {
			$i++;
			$players[$i] = $player->login;
		}
		foreach($this->storage->spectators as $player) {
			$i++;
			$players[$i] = $player->login;
		}

		if(!array_key_exists((int)$id,$players)) {
			return NULL;
		} else {
			return $players[$id];
		}
	}
}
?>