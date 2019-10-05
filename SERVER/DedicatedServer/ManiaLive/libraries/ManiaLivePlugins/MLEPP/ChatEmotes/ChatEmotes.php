<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Chat Emotes
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

namespace ManiaLivePlugins\MLEPP\ChatEmotes;

use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;
use ManiaLive\Config\Loader;
use ManiaLive\Event\Dispatcher;
use ManiaLive\DedicatedApi\Xmlrpc\Exception;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class ChatEmotes extends \ManiaLive\PluginHandler\Plugin {

    private $mlepp;
    private $counters = array();
	private $tick = 0;
	private $bootTick = array();
    private $config;

    /**
     * onInit()
     * Function called on initialisation of ManiaLive.
     *
     * @return void
     */

	function onInit() {
		// this needs to be set in the init section
		$this->setPublicMethod('getVersion');
		$this->setVersion(1050);
		$this->config = Config::getInstance();
        //Oliverde8 Menu
		if($this->isPluginLoaded('oliverde8\HudMenu')) {
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
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: ChatEmotes r' . $this->getVersion() );
		//$this->enableDedicatedEvents();
		$commands = array("bb", "bye", "hi", "hello", "thx", "ty", "lol", "brb", "afk", "gg", "nl", "bgm", "sry", "sorry", "glhf", "wb", "omg", "buzz", "eat", "drink", "rant", "ragequit", "bootme");
		$help = "performs a chatemote.";
		foreach($commands as $command) {
			$cmd = $this->registerChatCommand($command, $command, -1, true);
			$cmd->help = $help;

		}
		$oneliners = array("joke", "fact", "proverb", "quote");

		foreach($oneliners as $command) {
			$cmd = $this->registerChatCommand($command, $command, 0, true);
			$cmd->help = $help;
		}
		$this->enableDedicatedEvents();
		$this->enableTickerEvent();
		$this->mlepp = Mlepp::getInstance();
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

		if(!$parent) {
			$button["style"] = "Icons64x64_1";
			$button["substyle"] = "GenericButton";
			$parent = $menu->addButton("Menu", "Basic Commands", $button);
		}
		$button["plugin"] = $this;

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "StateSuggested";
		$parent = $menu->addButton($parent, "Chat Emotes", $button);

		$commands = array("bb", "bye", "hi", "hello", "thx", "ty", "lol", "brb", "afk", "gg", "nl", "bgm", "sry", "sorry", "glhf", "wb", "omg", "buzz", "eat", "drink", "rant", "ragequit", "bootme");
		foreach($commands as $command) {
			$button["function"] = $command;
			$new['params'] = "null";
			$menu->addButton($parent, $command, $button);
		}
	}

	function onUnload() {
			parent::onUnload();
	}

	function onTick() {
		if ($this->tick >= 5)
		{
			$this->tick = 0;
			foreach($this->counters as $login => $data) {
			if ($data > 0) $this->counters[$login]--;
			}
		}
		$this->tick++;

		foreach ($this->bootTick as $login => $data) {
			if ($data > 0) $this->bootTick[$login]--;
			if ($data == 0) {
				unset($this->bootTick[$login]);
				$this->connection->kick($login);
				}
			}
	}

	function onPlayerDisconnect($login) {
		if (isset($this->counters[$login])) unset($this->counters[$login]);
	}

	 /**
	 * bootme()
     * Function providing the /bootme command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function bootme() {
		$args = func_get_args();
		$login = array_shift($args);
		$param = implode(" ",$args);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /bootme command.');
		$player = $this->storage->getPlayerObject($login);
		$nick = $player->nickName;
		$message = (string) $this->config->bootme[rand(0, count($this->config->bootme) - 1)];
		$this->mlepp->sendChat($nick . ' $z$s %emote%'. $message);
		try
		{
			$this->bootTick[$login] = 2;
		}
		catch (\Exception $e)
		{
		}
	}

	 /**
	 * ragequit()
     * Function providing the /ragequit command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function ragequit() {
		$args = func_get_args();
		$login = array_shift($args);
		$param = implode(" ",$args);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /ragequit command.');
		$player = $this->storage->getPlayerObject($login);
		$nick = $player->nickName;
		$message = (string) $this->config->ragequit[rand(0, count($this->config->ragequit) - 1)];
		$this->mlepp->sendChat($nick . ' $z$s %emote%'. $message);
		try
		{
			$this->bootTick[$login] = 2;
		}
		catch (\Exception $e)
		{
		}
	}

	 /**
	 * hi()
     * Function providing the /hi command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function hi() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->hi, $this->config->hi2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /hi command.');
	}

	 /**
	 * hello()
     * Function providing the /hello command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function hello() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->hi, $this->config->hi2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /hello command.');
	}

	 /**
	 * thx()
     * Function providing the /thx command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function thx() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->thx, $this->config->thx2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /thx command.');
	}

	 /**
	 * ty()
     * Function providing the /ty command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function ty() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args,$this->config->thx, $this->config->thx2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /ty command.');
	}

	 /**
	 * bb()
     * Function providing the /bb command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function bb() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->bb, $this->config->bb2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /bb command.');
	}

	 /**
	 * bye()
     * Function providing the /bye command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function bye() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->bb, $this->config->bb2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /bye command.');
	}

	 /**
	 * lol()
     * Function providing the /lol command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function lol() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->lol, $this->config->lol2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /lol command.');
	}

	 /**
	 * brb()
     * Function providing the /brb command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function brb() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args,$this->config->brb, $this->config->brb2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /brb command.');
	}

	 /**
	 * afk()
     * Function providing the /afk command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function afk() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->connection->forceSpectator($login, 1);
		$this->connection->forceSpectator($login, 0);
		$this->helper($login, $args, $this->config->afk, $this->config->afk2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /afk command.');
	}

	 /**
	 * gg()
     * Function providing the /gg command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function gg() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->gg, $this->config->gg2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /gg command.');
	}

	 /**
	 * nl()
     * Function providing the /nl command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function nl() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->nl, $this->config->nl2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /nl command.');
	}

	 /**
	 * bgm()
     * Function providing the /bgm command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function bgm() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->bgm, $this->config->bgm2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /bgm command.');
	}

	 /**
	 * sry()
     * Function providing the /sry command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function sry() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->sry, $this->config->sry2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /sry command.');
	}

	 /**
	 * sorry()
     * Function providing the /sorry command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function sorry() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->sry, $this->config->sry2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /sorry command.');
	}

	 /**
	 * glhf()
     * Function providing the /glhf command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function glhf() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->glhf, $this->config->glhf2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /glhf command.');
	}

	 /**
	 * wb()
     * Function providing the /wb command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function wb() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->wb, $this->config->wb2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /wb command.');
	}

	 /**
	 * omg()
     * Function providing the /omg command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function omg() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->omg, $this->config->omg2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /omg command.');
	}

	 /**
	 * buzz()
     * Function providing the /buzz command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function buzz() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->buzz, $this->config->buzz2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /buzz command.');
	}

	 /**
	 * eat()
     * Function providing the /eat command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function eat() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->eat, $this->config->eat2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /eat command.');
	}

	 /**
	 * drink()
     * Function providing the /drink command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function drink() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->drink, $this->config->drink2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /drink command.');
	}

	 /**
	 * rant()
     * Function providing the /rant command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @return void
	 */

	function rant() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->helper($login, $args, $this->config->rant, $this->config->rant2);
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /rant command.');
	}

	 /**
	 * joke()
     * Function providing the /joke command.
	 *
	 * @param mixed $login
	 * @return void
	 */

	function joke($login) {
		$this->oneLiner($login, "jokes");
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /joke command.');
	}

	 /**
	 * fact()
     * Function providing the /fact command.
	 *
	 * @param mixed $login
	 * @return void
	 */

	function fact($login) {
		$this->oneLiner($login, "facts");
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /fact command.');
	}

	 /**
	 * proverb()
     * Function providing the /proverb command.
	 *
	 * @param mixed $login
	 * @return void
	 */

	function proverb($login) {
		$this->oneLiner($login, "proverbs");
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /proverb command.');
	}

	 /**
	 * quote()
     * Function providing the /quote command.
	 *
	 * @param mixed $login
	 * @return void
	 */

	function quote($login) {
		$this->oneLiner($login, "quotes");
        Console::println('['.date('H:i:s').'] [MLEPP] [ChatEmotes] ['.$login.'] Player used /quote command.');
	}

	 /**
	 * helper()
     * Helper function, does the hard stuff for outputting text.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $text
	 * @param mixed $source1
	 * @param mixed $source2
	 * @return void
	 */

	function helper($login, $args, &$source1, &$source2) {

		if (!isset($this->counters[$login]) ) $this->counters[$login] = 1;
		if (isset($this->counters[$login]) && $this->counters[$login] >= 3) return;
		if (isset($this->counters[$login])) $this->counters[$login]++;
		$player = $this->storage->getPlayerObject($login);
		$message = (string) $source1[rand(0, count($source1) - 1)];
		$message2 = (string) $source2[rand(0, count($source2) - 1)];

		if(isset($args[0])) {

			if( ($nick = $this->getPlayerNick($args[0])) == "") {
			$text = implode(" ",$args);
				$this->mlepp->sendChat($player->nickName . '$z$s  %emote%'. $message2 . " %emote%". $text);
			} else {
				array_shift($args);
				$text = implode(" ",$args);
				$this->mlepp->sendChat($player->nickName . '$z$s %emote%'. $message2 . ", " . $nick . " %emote%". $text);
			}
		} else {
			$this->mlepp->sendChat($player->nickName . '$z$s  %emote%'. $message);
		}
	}

	 /**
	 * oneLiner()
     * Function used for outputting one-liners.
	 *
	 * @param mixed $login
	 * @param mixed $file
	 * @return void
	 */

	function oneLiner($login, $file) {
		$data = file_get_contents('libraries/ManiaLivePlugins/MLEPP/ChatEmotes/Texts/' . $file . '.txt');
		$lines = explode("\n", $data);
		$message = (string) $lines[rand(0, count($lines) - 1)];
		$player = $this->storage->getPlayerObject($login);
		$this->mlepp->sendChat($player->nickName . '$z$s  %emote%' . trim($message));
	}


	 /**
	 * getPlayerNick()
     * Function used for getting someones nickname.
	 *
	 * @param mixed $login
	 * @return
	 */

	function getPlayerNick($login) {
		if(isset($this->storage->players[$login])) {
			$player = $this->storage->getPlayerObject($login);
			return $player->nickName;
		} else {
			if(isset($this->storage->spectators[$login])) {
				$player = $this->storage->getPlayerObject($login);
				return $player->nickName;
			} else {
				return "";
            }
		}
	}
}
?>