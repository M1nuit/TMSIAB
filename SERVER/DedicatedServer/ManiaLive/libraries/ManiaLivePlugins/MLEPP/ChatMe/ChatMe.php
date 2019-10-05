<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Chat Me
 * @date 30-06-2011
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



namespace ManiaLivePlugins\MLEPP\ChatMe;

use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;

use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class ChatMe extends \ManiaLive\PluginHandler\Plugin {
	private $mlepp;
	private $desc = "Usage: /me text goes here [login]";
	private $help = "The chatme plugin helps to express yourself.
It can be used with or without a login.

\$wUsage\$z:
\$o/me text goes here\$z - Express your state, feeling, whereabouts etc.
\$o/me text goes here  \$ilogin\$i\$z - Express your state, feeling, whereabouts etc., followed by a nickname.";

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
        
        $this->mlepp = Mlepp::getInstance();
	}

    /**
     * onLoad()
     * Function called on loading of ManiaLive.
     *
     * @return void
     */

	function onLoad() {
        Console::println('['.date('H:i:s').'] [MLEPP] Plugin: ChatMe r'.$this->getVersion() );
		$this->enableDedicatedEvents();
		$command = $this->registerChatCommand("me", "me", -1, true);
		$command->help = $this->desc;

	}

	 /**
	 * onReady()
     * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */

	function onReady() {

	}

	function onUnload() {
			parent::onUnload();
	}

	 /**
	 * me()
     * Function providing the /me command.
	 *
	 * @param mixed $login
	 * @param mixed $message
	 * @param mixed $targetPlayer
	 * @return
	 */

	function me() {
		$args = func_get_args();
		$login = array_shift($args);
		$message = implode(" ",$args);

		$player = $this->storage->getPlayerObject($login);

		if($message == NULL || $message == "help") {
			$this->showHelp($login,$this->help);
			return;
		}

		if( $this->mlepp->isPlayerOnline($args[count($args)-1])  ) {
			$targetPlayer = $args[count($args)-1];
			unset($args[count($args)-1]);
			$message = implode(" ",$args);
			$targetPlayer = $this->storage->getPlayerObject($targetPlayer);
			$this->mlepp->sendChat($player->nickName . ' $z$s%emote%'. $message . '$z$s ' . $targetPlayer->nickName);
		}
		else {

			$this->mlepp->sendChat($player->nickName . ' $z$s%emote%'. $message );
		}
	}



	function showHelp($login,$text) {
		$this->callPublicMethod('MLEPP\Core', 'showHelp', $login, "help for /me", $text);
	}
}

?>