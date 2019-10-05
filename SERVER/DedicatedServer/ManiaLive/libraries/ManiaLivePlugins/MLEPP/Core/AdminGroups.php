<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name AdminGroups
 * @date 25-06-2011
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


namespace ManiaLivePlugins\MLEPP\Core;


use ManiaLive\Features\Admin\AdminGroup as MLAdminGroup;
use ManiaLive\Utilities\Console;
use ManiaLivePlugins\MLEPP\Core\Structures\AdminGroup;


class AdminGroups extends \ManiaLib\Utils\Singleton {

    protected $version = 1050;

	public static $file = 'config-mlepp-admins.ini';
	public $noPermissionMsg = 'You don\'t have the permission%s!';

	protected $admins = array();
	protected $groups = array();
	protected $permissions = array();
    protected $titles = array();
    protected $signs = array();


	 /**
	 * @fn load()
	 * @brief Function called on load.
	 *
	 * @return void
	 */
	function load() {
		Console::println('['.date('H:i:s').'] [MLEPP] Extension: AdminGroups r'.$this->version);
		$this->loadSettings(APP_ROOT.'config/'.self::$file);
	}


	 /**
	 * @fn addPermission()
	 * @brief Function to add permission
	 *
	 * @param mixed $name
	 * @return
	 */
	public function addPermission($name) {
		$name = trim($name);
		$id = array_search($name, $this->permissions);

		if ($id === false) {
			$this->permissions[] = $name;
			return array_search($name, $this->permissions);
		}

		return $id;
	}


	 /**
	 * @fn removePermission()
	 * @brief Function to remove permission
	 *
	 * @param mixed $name
	 * @return
	 */
	public function removePermission($name) {
		$name = trim($name);
		$id = array_search($name, $this->permissions);

		if ($id === false) {
			foreach($this->groups as &$group) {
				$group->removePermission($id);
			}
			unset($this->permissions[$id]);
			return true;
		}

		return false;
	}


	 /**
	 * @fn addGroup()
	 * @brief Function to add group
	 *
	 * @param mixed $name
	 * @return
	 */
	public function addGroup($name) {
		$name = trim($name);

		if($this->getGroupId($name) === false) {
			$this->groups[] = new AdminGroup($name, $name == 'root');
			return true;
		}

		return false;
	}


	 /**
	 * @fn removeGroup()
	 * @brief Function to add group
	 *
	 * @param mixed $name
	 * @return
	 */
	public function removeGroup($name) {
		$name = trim($name);

		// root and player not removeable
		if($name == 'root' || $name == 'player') {
			return false;
		}

		$gid = $this->getGroupId($name);
		if($gid === false) {
			unset($this->groups[$gid]);
			return true;
		}

		return false;
	}


	 /**
	 * @fn addAdmin()
	 * @brief Function to add admin (to groups if spezified).
	 *
	 * @param mixed $login
	 * @param mixed $groups
	 * @return
	 */
	public function addAdmin($login, $groups = '') {
		$login = trim($login);

		if(!isset($this->admins[$login])) {
			$this->admins[$login] = array();
			$groups = explode(',', $groups);
			if(is_array($groups)) {
				foreach($groups as &$group) {
					$this->addAdminToGroup($login, trim($group));
				}
			}
			return true;
		}

		return false;
	}


	 /**
	 * @fn removeAdmin()
	 * @brief Function to remove admin.
	 *
	 * @param mixed $login
	 * @return void
	 */
	public function removeAdmin($login) {
		unset($this->admins[trim($login)]);
	}


	 /**
	 * @fn addPermissionToGroup()
	 * @brief Function to add permission to group.
	 *
	 * @param mixed $group
	 * @param mixed $permission
	 * @param mixed $permit
	 * @return
	 */
	public function addPermissionToGroup($group, $permission, $permit = false) {
		$gid = $this->getGroupId(trim($group));
		$permission = trim($permission);

		if($gid !== false) {
			$grp = &$this->groups[$gid];
			$pid = $this->addPermission($permission);
			if($pid !== false) {
				$grp->addPermission($pid, $permit);
				return true;
			}
		}

		return false;
	}


	 /**
	 * @fn removePermissionFromGroup()
	 * @brief Function to remove permission from group.
	 *
	 * @param mixed $group
	 * @param mixed $permission
	 * @return
	 */
	public function removePermissionFromGroup($group, $permission) {
		$gid = $this->getGroupId(trim($group));
		$permission = trim($permission);

		if($gid !== false) {
			$grp = &$this->groups[$gid];
			$grp->removePermission($pid);
			return true;
		}

		return false;
	}


	 /**
	 * @fn addAdminToGroup()
	 * @brief Function to add admin to group
	 *
	 * @param mixed $login
	 * @param mixed $group
	 * @return
	 */
	public function addAdminToGroup($login, $group) {
		$login = trim($login);
		$group = trim($group);

		if($group == 'player') {
			return false;
		}

		$gid = $this->getGroupId($group);
		if(isset($this->admins[$login]) && $gid !== false) {
			$admin = &$this->admins[$login];
			if(array_search($gid, $admin) === false) {
				$admin[] = $gid;
				return true;
			}
		}

		return false;
	}


	 /**
	 * @fn removeAdminFromGroup()
	 * @brief Function to remove admin from group
	 *
	 * @param mixed $login
	 * @param mixed $group
	 * @return
	 */
	public function removeAdminFromGroup($login, $group) {
		$login = trim($login);
		$gid = $this->getGroupId(trim($group));

		if(isset($this->admins[$login]) && $gid !== false) {
			$admin = &$this->admins[$login];
			$id = array_search($gid, $admin);
			if($id !== false) {
				unset($admin[$id]);
				return true;
			}
		}

		return false;
	}


	 /**
	 * @fn hasPermission()
	 * @brief Function to determine if admin has permission.
	 *
	 * @param mixed $login
	 * @param mixed $permission
	 * @return
	 */
	public function hasPermission($login, $permission) {
		// ManiaLive admins will have permission to everything
		if(MLAdminGroup::contains($login)) {
			return true;
		}

		$login = trim($login);
		$pid = $this->getPermissionId(trim($permission));
		$ret = false;

		if($pid !== false) {
			$gid = $this->getGroupId('player');
			if($gid !== false) {
				$ret = ($this->groups[$gid]->getPermission($pid) == 1);
			}

			if(isset($this->admins[$login])) {
				$admin = &$this->admins[$login];

				$gid = end($admin);
				while($gid !== false) {
					$p = $this->groups[$gid]->getPermission($pid);
					if($p == 0) {
						$ret = false;
					}
					elseif($p == 1) {
						$ret = true;
					}

					$gid = prev($admin);
				}
			}
		}

		return $ret;
	}


	 /**
	 * @fn getPermissions()
	 * @brief Function to retrieve permissions of an admin.
	 *
	 * @param mixed $login
	 * @param mixed $addFalse
	 * @return
	 */
	public function getPermissions($login, $addFalse = false) {
		$login = trim($login);


		$permissions = array();

		foreach($this->permissions as $pid => &$perm) {
			$tmp = $this->hasPermission($login, $perm);
			if($tmp || $addFalse) {
				$permissions[$perm] = $tmp;
			}
		}

		return $permissions;
	}


	 /**
	 * @fn getAdminGroups()
	 * @brief Function to retrieve groups of admin.
	 *
	 * @param mixed $login
	 * @return
	 */
	public function getAdminGroups($login) {
		$login = trim($login);
		$groups = array();
		foreach (MLAdminGroup::get() as $MLadmin) {
					if ($MLadmin == $login) {
					$groups[] = 'root';
					break;
					}
		}

		if(isset($this->admins[$login])) {
			foreach($this->admins[$login] as $id) {
				$groups[] = $this->groups[$id]->getName();
			}
		}

		return $groups;
	}


	 /**
	 * @fn getAdmins()
	 * @brief Function to retrieve all admins.
	 *
	 * @return
	 */
	public function getAdmins() {
		return array_keys($this->admins);
	}


	 /**
	 * @fn getAdminsByPermission()
	 * @brief Function to retrieve all admins filtered by permission.
	 *
	 * @param mixed $permission
	 * @return
	 */
	public function getAdminsByPermission($permission) {
		$permission = trim($permission);
		$admins = array();
		foreach (MLAdminGroup::get() as $login) {
					$admins[] = $login;
		}
		if($this->getPermissionId($permission) !== false) {
			foreach($this->admins as $login => &$gids) {
				if($this->hasPermission($login, $permission)) {
					$admins[] = $login;
				}
			}
		}

		return $admins;
	}


	 /**
	 * @fn getAdminsByGroup()
	 * @brief Function to retrieve all admins filtered by group.
	 *
	 * @param mixed $group
	 * @return
	 */
	public function getAdminsByGroup($group) {
		$gid = $this->getGroupId(trim($group));
		$admins = array();
		foreach (MLAdminGroup::get() as $login) {
					$admins[] = $login;
		}
		if($gid !== false) {
			foreach($this->admins as $login => &$gids) {
				if(in_array($gid, $gids)) {
					$admins[] = $login;
				}


			}
		}

		return $admins;
	}


	 /**
	 * @fn getNoPermissionMsg()
	 * @brief Function to retrieve standardised message.
	 *
	 * @param mixed $cmd
	 * @return
	 */
	public function getNoPermissionMsg($cmd = NULL) {
		if(!empty($cmd)) {
			$cmd = ' to '.$cmd;
		}

		return sprintf(self::$noPermissionMsg, $cmd);
	}

    public function getGroups() {
        $groups = array();
        for($i = 0; $i < sizeof($this->groups); ++$i) {
            $groups[] = $this->groups[$i]->getName();
        }
        return $groups;
    }

	 /**
	 * @fn reloadSettings()
	 * @brief Function to reload settings from file
	 *
	 * @param mixed $file
	 * @return void
	 */
	public function reloadSettings($file = NULL) {
		if (empty($file)) {
			$file = APP_ROOT.'config/'.self::$file;
		}

		$this->admins = array();
		$this->groups = array();
		$this->permissions = array();

		$this->loadSettings($file);
	}


	 /**
	 * @fn saveSettings()
	 * @brief Function to save settings to file
	 *
	 * @param mixed $file
	 * @param mixed $addFalse
	 * @return
	 */
	public function saveSettings($file = NULL, $addFalse = false) {
		if (empty($file)) {
			$file = APP_ROOT.'config/'.self::$file;
		}
		if(is_writable($file)) {
		@file_put_contents($file, $this->getSaveString($addFalse === true));
		$this->reloadSettings();
		} else {
			Console::println('['.date('H:i:s').'] [MLEPP] [AdminGroup] Failed to save the settings file, please check file permissions.');
		}
	}


	 /**
	 * @fn getGroupId()
	 * @brief Function to retrieve group-id.
	 *
	 * @param mixed $group
	 * @return
	 */
	private function getGroupId($group) {
		foreach($this->groups as $id => $grp) {
			if ($group == $grp->getName()) {
				return $id;
			}
		}
		return false;
	}


	 /**
	 * @fn getPermissionId()
	 * @brief Function to retrieve permission-id.
	 *
	 * @param mixed $permission
	 * @return
	 */
	private function getPermissionId($permission) {
		return array_search($permission, $this->permissions);
	}


	 /**
	 * @fn loadSettings()
	 * @brief Function to load settings from file.
	 *
	 * @param mixed $filename
	 * @return
	 */
	private function loadSettings($filename) {
		if(!is_readable($filename)) {
			return false;
		}

		$values = parse_ini_file($filename, true);
		$admins = array();

		// add groups and permissions
		foreach($values as $key => $value) {
			if (is_array($value)) {
				$this->addGroup($key);
				foreach($value as $key1 => $value1) {
				    if($key1 == 'title') {
				        $this->titles[$key] = $value1;
				    } elseif($key1 == 'sign') {
				        $this->signs[$key] = $value1;
                    } else {
					   $this->addPermissionToGroup($key, $key1, $this->str2bool($value1));
                    }
				}
			}
			else {
				$admins[$key] = $value;
			}
		}

		//add default groups
		$this->addGroup('root');
		$this->addGroup('player');

        $this->titles['root'] = 'MasterAdmin';
        $this->signs['root'] = '@';
        $this->titles['player'] = 'Player';
        $this->signs['player'] = '';

		//add admins to groups
		foreach($admins as $login => &$groups) {
			$this->addAdmin($login, $groups);
		}

		return true;
	}

    public function getTitle($group) {
        return $this->titles[$group];
    }

    public function getSign($group) {
        return $this->signs[$group];
    }


	 /**
	 * @fn getSaveString()
	 * @brief Function to convert settings into saveable string.
	 *
	 * @param mixed $addFalse
	 * @return
	 */
	public function getSaveString($addFalse = true) {
		$str = "; machine generated\n\n";

		foreach($this->admins as $login => &$groups) {
			$str .= $login." = '".implode(',', $this->getAdminGroups($login))."'\n";
		}

		foreach($this->groups as $gid => &$group) {
			$name = $group->getName();
			if ($name != 'root') {
				$str .= "\n[{$name}]\n";
				$str .= 'title = "'.$this->titles[$name].'"'."\n";
				$str .= 'sign = "'.$this->signs[$name].'"'."\n";
				foreach($this->permissions as $pid => &$permission) {
					$perm = $group->getPermission($pid);
					if($perm == 1) {
						$str .= $permission." = true\n";
					}
					elseif($addFalse) {
						$str .= $permission." = false\n";
					}
				}
			}
		}

		return $str;
	}


	 /**
	 * @fn str2bool()
	 * @brief Function to convert str to bool.
	 *
	 * @param mixed $str
	 * @return
	 */
	private function str2bool($str) {
		$str = trim($str);
		return $str == 'true' || $str == 1 || $str === true;
	}

}
?>