<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 * 
 * -- MLEPP Plugin --
 * @name FMod
 * @date $Date: 2011-06-22 21:20:36 +0200 (wo, 22 jun 2011) $
 * @version $Revision: 934 $
 * @website mlepp.trackmania.nl
 * @package MLEPP
 * 
 * @author Klaus "schmidi" Schmidhuber <schmidi.tm@gmail.com>
 * @copyright 2011
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
 
 
namespace ManiaLivePlugins\MLEPP\ForceMods\Structures;


class FMod extends \ManiaLive\DedicatedApi\Structures\AbstractStructure {
	
	protected $name = '';
	protected $url = '';
	protected $enabled = false;
		
		
	/**
	 * @fn __construct()
	 * @brief Function initialize instance of FMod.
	 *
	 * @param mixed $name
	 * @param mixed $url
	 * @param mixed $enabled
	 * @return void
	 */
	function __construct($name, $url, $enabled = true) {
		$this->name = trim($name);
		$this->url = trim($url);
		if($enabled === true || strtolower(trim($enabled)) == 'true' || trim($enabled) == '') {
			$this->enabled = true;
		}
	}

	
	/**
	 * @fn getName()
	 * @brief Function returns the name.
	 *
	 * @return 
	 */
	function getName() {
		return $this->name;
	}
	
	
	/**
	 * @fn getUrl()
	 * @brief Function returns the url.
	 *
	 * @return 
	 */
	function getUrl() {
		return $this->url;
	}
	
	
	/**
	 * @fn isEnabled()
	 * @brief Function returns the status.
	 *
	 * @return 
	 */
	function isEnabled() {
		return $this->enabled;
	}
	
	
	/**
	 * @fn enable()
	 * @brief Function enables mod.
	 *
	 * @return void
	 */
	function enable() {
		$this->enabled = true;
	}
	
	
	/**
	 * @fn disable()
	 * @brief Function disables mod.
	 *
	 * @return void
	 */
	function disable() {
		$this->enabled = false;
	}
	
	
	/**
	 * @fn enable()
	 * @brief Function returns string-representation of FMod.
	 *
	 * @return 
	 */
	function __toString() {
		$str = "name={$this->name}, url={$this->url}, enabled=";
		$str .= $this->enabled ? 'true' : 'false';
		return $str;
	}
	
}

?>