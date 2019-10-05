<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 * 
 * -- MLEPP Plugin --
 * @name AdminGroup
 * @date $Date$
 * @version $Revision$
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
 
 
namespace ManiaLivePlugins\MLEPP\Core\Structures;


class AdminGroup extends \ManiaLive\DedicatedApi\Structures\AbstractStructure {
	
	protected $name = '';
	protected $isRoot = false;
	protected $permissions = array();
		
		
	/**
	 * @fn __construct()
	 * @brief Function to initialise object.
	 *
	 * @param mixed $name
	 * @param mixed $root
	 * @return void
	 */
	function __construct($name, $root = false) {
		$this->name = $name;
		$this->isRoot = ($root === true);
	}


	/**
	 * @fn getName()
	 * @brief Function returns name of group.
	 *
	 * @return 
	 */
	function getName() {
		return $this->name;
	}
	
	
	/**
	 * @fn addPermission()
	 * @brief Function to add permission to group.
	 *
	 * @param mixed $permissionId
	 * @param mixed $permit
	 * @return void
	 */
	function addPermission($permissionId, $permit = false) {
		if($this->isRoot) {
			return;
		}

		$this->permissions[$permissionId] = $permit;
	}
	
	
	/**
	 * @fn removePermission()
	 * @brief Function to remove permission from group.
	 *
	 * @param mixed $permissionId
	 * @return void
	 */
	function removePermission($permissionId) {
		unset($this->permissions[$permissionId]);
	}
	
	
	/**
	 * @fn getPermission()
	 * @brief Function to retrive permission
	 *
	 * @param mixed $permissionId
	 * @return 
	 */
	function getPermission($permissionId) {
		if($this->isRoot) {
			return 1;
		}
		
		// return: 0 = no Permission, 1 = Permission, 2 = unset
		if(isset($this->permissions[$permissionId])) {
			return $this->permissions[$permissionId] ? 1 : 0;
		}
		return 2;
	}
	
}

?>