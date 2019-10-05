<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Widgets
 * @date 02-07-2011
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

namespace ManiaLivePlugins\MLEPP\Widgets;

use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLivePlugins\MLEPP\Widgets\Structures\Settings;

use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;

class Widgets extends \ManiaLive\PluginHandler\Plugin {

    private $widgets = array('Defaults\LiveRankings');
	private $mlepp;

    private $widgets_open = array();
    private $widgets_settings = array();

    private $lastGameMode = -1;

    public function onInit() {
        $this->setVersion(1050);
        $this->setPublicMethod('getVersion');
        $this->setPublicMethod("getOpenWidgets");

		$this->mlepp = Mlepp::getInstance();
        \ManiaLivePlugins\MLEPP\Widgets\Structures\TypeView::$pluginWidgets = $this;
    }

    public function onLoad(){
        $storage = Storage::getInstance();
        $gameMode = $storage->gameInfos->gameMode;
        $this->lastGameMode = $gameMode;

        $this->loadSettings();

        if(\is_array($this->widgets)){
            foreach ($this->widgets as $widget) {
                $this->loadWidget($widget);
            }
        }
        $this->enableDedicatedEvents();
		$this->enablePluginEvents();
    }
	public function onUnload() {
		$storage = Storage::getInstance();
		   if(\is_array($this->widgets_open)){
                foreach ($this->widgets_open as $widgetName => $plugin) {
                    $this->unLoadWidget($widgetName, $storage->gameInfos->gameMode);
                }
            }
			parent::onUnload();
	}
    public function loadSettings(){
        $config = parse_ini_file(APP_ROOT.'config/config-mlepp-widgets.ini', true);

        //First : Getting the widget list
        $this->widgets = array();

        foreach($config['General']["widgets.load"] as $widget){
            $this->widgets[] = $widget;
        }

        if(isset($config['General']['autoHideActive']))
            \ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$autoHideActive = $this->stringToBool($config['General']['autoHideActive']);
        else
            \ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\AutoHideDuringRace::$autoHideActive = true;

        //Second : Applying the widget settings to Widgets
        foreach($config as $name => $setting){
            if($name != 'General'){
                $this->loadWidgetSettings($name, $setting);
            }
        }
    }

        /**
     * stringToBool()
     * Sets string into boolean.
     *
     * @param string $string
     * @return bool $bool
     */
    private function stringToBool($string) {
        if(strtoupper($string) == "FALSE" || $string == "0" || strtoupper($string) == "NO" || empty($string))
            return false;
        return true;
    }

    private function loadWidgetSettings($widget, $settings){
		$alreadyDOne = array();

        foreach($settings as $sname=>$setting){
            $var = \explode('.', $sname);

			$alreadyDone[$var[0]] = true;

            if($this->mlepp->getGameModeNumber($var[0]) || $var[0] == 'Standard' || $var[0] == 'Rounds'){
                $widgetClass = 'ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\\'.$widget.'\\'.$var[0];
		        //Need to check if this plugin exists for this game mode
                $file = "libraries/".str_replace("\\", "/", $widgetClass);

                if(isset($var[1]) && file_exists($file.".php")){
                    $widgetClass = "\\".$widgetClass;
                    if( $var[0] == 'Standard'){
                        $Gnames = array("Rounds", "TimeAttack", "Team", "Laps", "Stunt", "Cup");
                        foreach ($Gnames as $n){
							if(!isset($alreadyDone[$n]))
								$this->setSpecificWidgetSettings($widgetClass, $widget, $n, $var[1], $setting);

                        }
                    }else{
                        $this->setSpecificWidgetSettings($widgetClass, $widget, $var[0], $var[1], $setting);

                    }
                }
            }
        }
    }

    private function setSpecificWidgetSettings($widgetClass, $widget, $gameMode, $settingName, $value){

        if(!isset($this->widgets_settings[$widget][$gameMode])){
            $this->widgets_settings[$widget][$gameMode] = new Settings();
        }
        $this->widgets_settings[$widget][$gameMode]->setSetting($settingName, $value);

    }

    public function  onBeginChallenge($challenge, $warmUp, $matchContinuation) {
		$storage = Storage::getInstance();
        $gameMode = $storage->gameInfos->gameMode;

        if($gameMode != $this->lastGameMode){

            $this->console("GameMode changed : Reloading widgets started ...");

            //Unload all open widgets
            if(\is_array($this->widgets_open)){
                foreach ($this->widgets_open as $widgetName => $plugin) {
                    $this->unLoadWidget($widgetName, $this->lastGameMode);
                }
            }

            //ReLoad the widgets
            if(\is_array($this->widgets)){
                foreach ($this->widgets as $widget) {
                    $this->loadWidget($widget);
                }
            }
             $this->lastGameMode = $gameMode;
            $this->console("GameMode changed : Reloading widgets finished");

        }

    }

    private function loadWidget($widget){

        $storage = Storage::getInstance();
        $gameMode = $this->mlepp->getGameModeName($storage->gameInfos->gameMode);
        $pluginHandler = \ManiaLive\PluginHandler\PluginHandler::getInstance();

        $widgetClass = 'ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\\'.$widget.'\\'.$gameMode;

        //Need to check if this plugin exists for this game mode
        $file = "libraries/".str_replace("\\", "/", $widgetClass);
        $i = 0;

        while($this->isPluginLoaded($pluginHandler->getPluginIdFromClass($widgetClass)) && $i < 10){
            sleep(2);
            $i++;
        }

        if(file_exists($file.".php")){
            $this->widgets_open[$widget] = $this->loadPlugin('\\'.$widgetClass);

            if(isset ($this->widgets_settings[$widget][$gameMode])){
                $this->callPublicMethod('MLEPP\Widgets\Widgets\Controlers\\'.$widget, 'setSetings',$this->widgets_settings[$widget][$gameMode]);
            }
            return true;
        }else{
            $widgetClass = 'ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\\'.$widget.'\\Standard';
            $file = "libraries/".str_replace("\\", "/", $widgetClass);

            if(file_exists($file.".php")){
                $this->widgets_open[$widget] = $this->loadPlugin('\\'.$widgetClass);

                if(isset ($this->widgets_settings[$widget][$gameMode])){
                    $this->callPublicMethod('MLEPP\Widgets\Widgets\Controlers\\'.$widget, 'setSetings',$this->widgets_settings[$widget][$gameMode]);
                }
                return true;
            }
        }

        return false;
    }

    private function unLoadWidget($widget, $gameMode){
        unset($this->widgets_open[$widget]);
		$widgetClass = '\ManiaLivePlugins\MLEPP\Widgets\Widgets\Controlers\\'.$widget.'\\'.$gameMode;
        $this->unLoadPlugin($widgetClass);
		gc_collect_cycles();
    }

    private function loadPlugin($plugin){
        $pluginHandler = \ManiaLive\PluginHandler\PluginHandler::getInstance();
        return $pluginHandler->addPlugin($plugin);
    }

    private function unLoadPlugin($plugin){
        $pluginHandler = \ManiaLive\PluginHandler\PluginHandler::getInstance();
        //echo $plugin;
        $pluginHandler->deletePlugin($plugin);
		gc_collect_cycles();
    }

    public function getOpenWidgets(){
        return $this->widgets_open;
    }

    function console($text) {
        Console::println('['.date('H:i:s').'] [MLEPP] [Widgets] '.$text);
    }
}
?>