<?php
/**
 * ManiaLive - TrackMania dedicated server manager in PHP
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 253 $:
 * @author      $Author: martin.gwendal $:
 * @date        $Date: 2011-08-16 19:39:16 +0200 (mar., 16 août 2011) $:
 */

namespace ManiaLive\Features\Admin;

abstract class AdminGroup
{
	/**
	 * Check if the given login is an admin or not
	 * @param string $login
	 * @return bool
	 */
	public static function contains($login)
	{
		$login = explode('/', $login, 1);
		return (array_search($login[0], \ManiaLive\Application\Config::getInstance()->admins) !== false);
	}

	/**
	 * Return the list of Admins' login
	 * @return array
	 */
	public static function get()
	{
		return \ManiaLive\Application\Config::getInstance()->admins;
	}
}

?>