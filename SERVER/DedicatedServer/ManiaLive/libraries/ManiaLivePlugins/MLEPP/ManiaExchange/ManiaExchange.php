<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name ManiaExchange
 * @date 09-06-2011
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

namespace ManiaLivePlugins\MLEPP\ManiaExchange;

use ManiaLive\Utilities\Console;
use ManiaLib\Utils\TMStrings as String;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLivePlugins\MLEPP\ManiaExchange\Gui\Windows\SimpleWindow;
use ManialivePlugins\MLEPP\Core\Core;

class ManiaExchange extends \ManiaLive\PluginHandler\Plugin {

    public static $mxLocation = 'tm.mania-exchange.com';

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */

	function onInit() {
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
		$this->enableDedicatedEvents();
		$this->enableStorageEvents();

		Console::println('['.date('H:i:s').'] [MLEPP] Plugin: ManiaExchange r'.$this->getVersion() );
        $cmd = $this->registerChatCommand("mxinfo", "mxinfo", 0, true);
	}


	 /**
	 * onUnload()
	 * Function called on Unload
	 *
	 * @return void
	 */

	function onUnload() {
		parent::onUnload();
	}

    function mxinfo($login, $uid = '') {
        if($uid == '') {
            $uid = Storage::getInstance()->currentChallenge->uId;
        }

		$mxData = $this->getData('http://'.self::$mxLocation.'/api/tracks/get_track_info/uid/'.$uid.'?format=json');
        $mxData = json_decode($mxData);
		if(is_null($mxData)) {
            $player = Storage::GetInstance()->getPlayerObject($login);
            $this->connection->chatSendServerMessage('$fff»» $i$f00The requested track was not found on $fffM$5dfX$f00!', $player);
			return;
        } else {
            $window = SimpleWindow::Create($login);
            $window->setSize(210, 100);
            $window->setData($mxData);
			$window->setTargetMx(self::$mxLocation);
            $window->centerOnScreen();
            $window->Show();
        }
    }

    function onMxClick($login) {
        $this->mxinfo($login);
    }

	function getData($url) {
		$options = array('http' => array(
				'user_agent' => 'manialive tmx-getter', // who am i
				'max_redirects' => 1, // stop after 10 redirects
				'timeout' => 1, // timeout on response
				));
		$context = stream_context_create($options);
		return @file_get_contents($url, false, $context);
	}
}
?>