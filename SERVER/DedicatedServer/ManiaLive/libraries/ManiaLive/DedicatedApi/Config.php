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

namespace ManiaLive\DedicatedApi;

class Config extends \ManiaLib\Utils\Singleton
{
	public $host = 'localhost';
	public $port = 5000;
	public $user = 'SuperAdmin';
	public $password = 'SuperAdmin';
	public $timeout = 1;
}

?>