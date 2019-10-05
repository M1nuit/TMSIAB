<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name ChatLog
 * @date 16-02-2011
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



namespace ManiaLivePlugins\MLEPP\ChatLog;

use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;
use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLivePlugins\MLEPP\ChatLog\Gui\Windows\ChatLogWindow;

class ChatLog extends \ManiaLive\PluginHandler\Plugin {
	private $mlepp;
	private $chatlog = array();
	private $descLog = "Usage: /chatlog";
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
		
		//Oliverde8 Menu
		if ($this->isPluginLoaded('oliverde8\HudMenu')) {
			Dispatcher::register(\ManiaLivePlugins\oliverde8\HudMenu\onOliverde8HudMenuReady::getClass(), $this);
		}
	}

    /**
     * onLoad()
     * Function called on loading of ManiaLive.
     *
     * @return void
     */

	function onLoad() {
        Console::println('['.date('H:i:s').'] [MLEPP] Plugin: ChatLog r'.$this->getVersion() );
		$this->enableDedicatedEvents();
		$command = $this->registerChatCommand("chatlog", "log", 0, true);
		$command->help = $this->descLog;
		
	}

		function onUnload() {
			parent::onUnload();
	}

	 /**
	 * onOliverde8HudMenuReady()
     * Function used for adding buttons to Olivers Hud Menu.
	 *
	 * @param mixed $menu
	 * @return void
	 */

	public function onOliverde8HudMenuReady($menu) {
		$parent = $menu->findButton(array("Menu", "Basic Commands"));

		if(!$parent){
			$button["style"] = "Icons64x64_1";
			$button["substyle"] = "GenericButton";

			$parent = $menu->addButton("Menu", "Basic Commands", $button);
		}
		$button["plugin"] = $this;

		$button["style"] = "BgRaceScore2";
		$button["substyle"] = "BgCardServer";
		$button["function"] = "log";
		$parent = $menu->addButton($parent, "Chat Log", $button);
	}

    /**
     * onPlayerChat()
     * Function called when someone is chatting.
     *
     * @param mixed $playerUid
     * @param mixed $login
     * @param mixed $chat
     * @param mixed $isRegistredCmd
     * @return
     */

    function onPlayerChat($playerUid, $login, $chat, $isRegistredCmd) {
		if($playerUid == 0) return;
		if(substr($chat,0,1) == "/") return;
		$source_player = $this->storage->getPlayerObject($login);
		$nick = $source_player->nickName;
		array_push($this->chatlog, array("stamp" => time(), "login" => $login, "nick" => $nick, "chat" => $chat));
		if(count($this->chatlog) > Config::getInstance()->history) {
			$this->chatlog = array_slice($this->chatlog, (count($this->chatlog) - Config::getInstance()->history), Config::getInstance()->history);
		}

	}

	 /**
	 * log()
     * Function providing the /chatlog command.
	 *
	 * @param mixed $login
	 * @return void
	 */

	function log($login) {
		$chatlog = array_reverse($this->chatlog);

		$window = ChatLogWindow::Create($login);
		$window->setSize(180, 80);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Time', 0.1);
		$window->addColumn('NickName', 0.2);
		$window->addColumn('Chat', 0.7);

		// refresh records for this window ...
		$window->clearItems();
		$id = 1;
		foreach ($chatlog as $data)
		{
			$entry = array
			(
				'Time' => date("H:i",$data['stamp']),
				'NickName' => $data['nick'],
				'Chat' => $data['chat']
			);
			$id++;
			$window->addItem($entry);
		}


		$window->centerOnScreen();
		$window->show();

	}
}
?>