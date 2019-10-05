<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 * 
 * -- MLEPP Plugin --
 * @name FModList
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


use ManiaLivePlugins\MLEPP\ForceMods\Structures\FMod;


class FModList extends \ManiaLive\DedicatedApi\Structures\AbstractStructure {
	
	protected $mods = array();
	protected $currentIndex = -1;
		

	/**
	 * @fn addMod()
	 * @brief Function adding Mod to ModList.
	 *
	 * @param mixed $mod
	 * @return
	 */
	function addMod($mod) {
		if(get_class($mod) == 'ManiaLivePlugins\MLEPP\ForceMods\Structures\FMod') {
			if($mod->getUrl()) {
				$this->mods[] = $mod;
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * @fn countMods()
	 * @brief Function counting Mods in ModList.
	 *
	 * @return
	 */
	function countMods() {
		return count($this->mods);
	}
	
	
	/**
	 * @fn nextMod()
	 * @brief Function calculates and returns index of next mod.
	 *
	 * @param mixed $random
	 * @return
	 */
	function nextMod($random = false) {
		$n = $this->countMods();
		if($n < 1) {
			$this->currentIndex = -1;
			return $this->currentIndex;
		}
		
		// skip the rest if only 1 mod
		if($n == 1) {
			$this->currentIndex = 0;
			return $this->currentIndex;
		}

		if($random) {
			$index = mt_rand() % $n;
			if($index == $this->currentIndex) {
				$index = ($index + 1) % $n;
			}
		}
		else {
			$index = ($this->currentIndex + 1) % $n;
		}	
			
		for($i = 0; $i < $n; $i++) {
			$mod = &$this->mods[$index];
			if($mod->isEnabled()) {
				$this->currentIndex = $index;
				return $index;
			}
			$index = ($index + 1) % $n;
		}
		
		$this->currentIndex = -1;
		return $this->currentIndex;
	}
	
	
	/**
	 * @fn getIndex()
	 * @brief Function returns index of current mod.
	 *
	 * @return
	 */
	function getIndex() {
		return $this->currentIndex;
	}
	

	/**
	 * @fn getName()
	 * @brief Function returns name of (current) mod.
	 *
	 * @param mixed $index
	 * @return
	 */
	function getName($index = -1) {
		if($index < 0) {
			$index = $this->currentIndex;
		}
		
		if(isset($this->mods[$index])) {
			return $this->mods[$index]->getName(); 
		}
		return '';
	}
	
	
	/**
	 * @fn getUrl()
	 * @brief Function returns url of (current) mod.
	 *
	 * @param mixed $index
	 * @return
	 */
	function getUrl($index = -1) {
		if($index < 0) {
			$index = $this->currentIndex;
		}
		
		if(isset($this->mods[$index])) {
			return $this->mods[$index]->getUrl(); 
		}
		return '';
	}
	
	
	/**
	 * @fn isEnabled()
	 * @brief Function returns status of (current) mod.
	 *
	 * @param mixed $index
	 * @return
	 */
	function isEnabled($index = -1) {
		if($index < 0) {
			$index = $this->currentIndex;
		}
		
		if(isset($this->mods[$index])) {
			return $this->mods[$index]->isEnabled(); 
		}
		return false;
	}
	
	
	/**
	 * @fn enable()
	 * @brief Function enables (current) mod.
	 *
	 * @param mixed $index
	 * @return
	 */
	function enable($index = -1) {
		if($index < 0) {
			$index = $this->currentIndex;
		}
		
		if(isset($this->mods[$index])) {
			$this->mods[$index]->enable(); 
		}
	}
	
	
	/**
	 * @fn disable()
	 * @brief Function disables (current) mod.
	 *
	 * @param mixed $index
	 * @return
	 */
	function disable($index = -1) {
		if($index < 0) {
			$index = $this->currentIndex;
		}
		
		if(isset($this->mods[$index])) {
			$this->mods[$index]->disable(); 
		}
		return '';
	}
	
	
	/**
	 * @fn __toString()
	 * @brief Function returns string-representation of FModList.
	 *
	 * @return
	 */
	function __toString() {
		$str = '';
		foreach($this->mods as $i => &$mod) {
			$str .= $mod . "; ";
		}
		return $str;
	}
	
}

?>