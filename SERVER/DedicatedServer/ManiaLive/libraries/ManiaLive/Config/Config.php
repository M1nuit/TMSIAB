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

namespace ManiaLive\Config;

class Config extends \ManiaLib\Utils\Singleton
{
	// depends on os
	public $phpPath;
	// base path for logging
	public $logsPath;
	public $logsPrefix = '';
	// enable runtime logging?
	public $runtimeLog = false;
	// log all errors from all instances?
	public $globalErrorLog = false;
	public $maxErrorCount = false;
	public $dedicatedPath = APP_ROOT;
	//Set to true to disable the updater
	public $lanMode = false;

	function __construct()
	{
		if(APP_OS == 'WIN')
			$this->phpPath = 'php.exe';
		else
			$this->phpPath = '`which php`';
		
		$this->logsPath = APP_ROOT.'logs';
	}
}

?>