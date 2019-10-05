<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 * 
 * -- MLEPP Plugin --
 * @name AutoTrackManager
 * @date 04-01-2011
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 * 
 * @author Petri "reaby" Järvisalo <petri.jarvisalo@mbnet.fi>
 * 
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
 * You are allowed to change things of use this in other projects, as
 * long as you leave the information at the top (name, date, version,
 * website, package, author, copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */

namespace ManiaLivePlugins\MLEPP\BingTranslate;
use ManiaLive\Utilities\Console;

class BingTranslate extends \ManiaLive\PluginHandler\Plugin {

	// Define global variables
	protected $branding = '$o$08fBi$08fng $n$4ceTranslate $z$s$fff|';
	private $players = array();
	private $config = array();
	private $cfg;
	private $mlepp;

	function onInit() {
		$this->setVersion(1050);
		Console::println('[' . date('H:i:s') . '] [Bing Translate] Enabling Bing Translate ' . $this->getVersion());
		$this->cfg = Config::getInstance();
	}

	function onLoad() {
		$this->enableDedicatedEvents();
		$this->mlepp = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance();

		if (empty($this->cfg->key)) {
			Console::println('[' . date('H:i:s') . '] [Bing Translate] Configuration missing, check config.ini');
			$this->sendChat("Error enabling bing translate: appId is not set.");
			$this->config['enabled'] = false;
			return;
		}

		Console::println('[' . date('H:i:s') . '] [Bing Translate] Plugin Bing Transtale Enabled.');
		

		if ($this->isPluginLoaded('MLEPP\Admin')) {
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'adm_translate'), array("set", "translate"), true, false, false, "Usage /admin set translate on|off");
		}
		$this->config['enabled'] = true;
	}

	function onUnload() {
		if ($this->isPluginLoaded('MLEPP\Admin')) {
			$this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', array("set", "translate"));
		}
		parent::onUnLoad();
	}
	function onReady() {
		$this->registerChatCommand("tr", "command_translate", -1, true);
		foreach ($this->storage->players as $login => $player) {
			$this->onPlayerConnect($login, false);
		}
		foreach ($this->storage->spectators as $login => $player) {
			$this->onPlayerConnect($login, true);
		}
	}

	function onPlayerConnect($login, $isSpectator) {
		$this->players[$login]['login'] = $login;
		if ($this->getClientDetails($login)) {
			$translate = $this->branding;
			$this->sendChat($translate . ' use $i/tr your text here | <lang>$i to translate', $login);
		}
	}

	function onPlayerDisconnect($login) {
		unset($this->players[$login]);
	}

	function adm_translate($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$translate = $this->branding;
		if ($param1 == "on") {
			$this->config['enabled'] = true;
			$message = "$translate Admin has enabled the translate service.";
			$this->sendChat($message);
			return;
		}

		if ($param1 == "off") {
			$this->config['enabled'] = false;
			$message = "$translate Admin has disabled the translate service.";
			$this->sendChat($message);
			return;
		}
	}

	function command_translate() {
		$args = func_get_args();
		$login = array_shift($args);
		$chat = implode(" ", $args);
		$tmp = explode("|", $chat);
		if (!isset($tmp[1])) {
			$target = "en";
		} else {
			$chat = $tmp[0];
			$target = trim($tmp[1]);
		}

		$translate = $this->branding;


		$ip = $this->players[$login]['ip'];
		if ($this->isPluginLoaded("MLEPP\CustomChat")) {
			$this->callPublicMethod("MLEPP\CustomChat", "sendChat", $login, $chat);
		} else {
			$message = '$ff0[' . $this->players[$login]['nickname'] . '$z$s$ff0] ' . $chat;
			$this->sendChat($message);
		}
		$this->translate($chat, $target, $ip, $login);
	}

	function translate($text, $lang, $ip, $login, $tologin = false) {
		$translate = $this->branding;
		if ($tologin != false) {
			if ($this->players[$tologin]['enabled'] == false) {
				$message = "$translate is disabled for you.";
				$this->sendChat($message, $tologin);
				return;
			}
		} else {
			if ($this->config['enabled'] == false) {
				$message = "$translate Translate service is turned off.";
				$this->sendChat($message, $login);
				return;
			}
			if ($this->players[$login]['enabled'] == false) {
				$message = "$translate Translate service is turned off for you.";
				$this->sendChat($message, $login);
				return;
			}
		}
		$translate = $this->branding;
		$extip = explode(":", $ip);
		$ip = $extip[0];
		if (empty($lang))
			$lang = "en";
		$query = rawurlencode($text);
		$url = "http://api.microsofttranslator.com/v2/Http.svc/Translate?appId=" . $this->cfg->key . "&text=" . $query . "&from=&to=" . $lang . "&contentType=text/plain&category=general";
		// sendRequest
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$body = curl_exec($ch);
		if ($body === false) {
			$message = 'Translate Curl error: ' . curl_error($ch);
			$this->sendChat($message);
		}
		curl_close($ch);

		$return = strip_tags($body);

		$message = "$translate $return";
		if ($tologin != false) {
			$this->sendChat($message, $tologin);
		} else {
			$this->sendChat($message);
		}
	}

	function getClientDetails($login) {
		$info = $this->connection->getDetailedPlayerInfo($login);
		$ip = $info->iPAddress;
		$nick = $info->nickName;
		if (empty($ip)) { // if info not found, exit
			$message = "$translate Error fetching player data from tm trying again...";
			$this->sendChat($message, $login);

			$info = $this->connection->getPlayerInfo($login);
			$nick = $info->nickName;
			if (empty($nick)) { // if info not found, exit
				$message = "$translate Another try, and no data, translating disabled!";
				$this->sendChat($message, $login);
				$this->players[$login]['enabled'] = false;
				return false;
			}
		} else {
			$this->players[$login]['ip'] = $ip;
			$this->players[$login]['nickname'] = $nick;
			$this->players[$login]['enabled'] = true;
		}
		return true;
	}

	function isAdmin($login) {
		return $this->mlepp->AdminGroup->hasPermission($login, "admin");
	}

	function checkLang($lang) {
		$support = array(
			'arabic' => 'ar',
			'chinese_simplified' => 'zh-CHS',
			'chinese_traditional' => 'zh-CHT',
			'czech' => 'cs',
			'danish' => 'da',
			'dutch' => 'nl',
			'english' => 'en',
			'estonian' => 'et',
			'finnish' => 'fi',
			'french' => 'fr',
			'german' => 'de',
			'greek' => 'el',
			'haitian_creole' => 'ht',
			'hebrew' => 'he',
			'hungarian' => 'hu',
			'italian' => 'it',
			'japanese' => 'ja',
			'korean' => 'ko',
			'latvian' => 'lv',
			'lithuanian' => 'lt',
			'norwegian' => 'no',
			'polish' => 'pl',
			'portuguese' => 'pt',
			'romanian' => 'ro',
			'russian' => 'ru',
			'slovak' => 'sk',
			'slovenian' => 'sl',
			'spanish' => 'es',
			'swedish' => 'sv',
			'thai' => 'th',
			'turkish' => 'tr',
			'ukrainian' => 'uk',
			'vietnamese' => 'vi',
		);

		foreach ($support as $long => $short) {
			if (strtolower($lang) == strtolower($long) || strtolower($lang) == strtolower($short))
				return $short;
		}

		return 'en';
	}

	function sendChat($text, $login = false) {
		if ($login) {
			$loginObj = $this->storage->getPlayerObject($login);
			$this->connection->chatSendServerMessage($text, $loginObj);
		} else {
			$this->connection->chatSendServerMessage($text);
		}
	}

}

?>