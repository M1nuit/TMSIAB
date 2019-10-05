<?php
/**
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @version     $Revision: 253 $:
 * @author      $Author: martin.gwendal $:
 * @date        $Date: 2011-08-16 19:39:16 +0200 (mar., 16 août 2011) $:
 */

namespace ManiaLive\Database;

class Config extends \ManiaLib\Utils\Singleton
{
	public $enable = true;
	public $host = '127.0.0.1';
	public $port = 3306;
	public $username = 'root';
	public $password = '';
	public $database = '';
	public $type = 'MySQL';
}

?>
