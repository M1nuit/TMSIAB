<?php
/**
 * ManiaLive - TrackMania dedicated server manager in PHP
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 256 $:
 * @author      $Author: martin.gwendal $:
 * @date        $Date: 2011-08-23 12:16:09 +0200 (mar., 23 août 2011) $:
 */

namespace ManiaLive\Event;

use ManiaLive\Application\ErrorHandling;

abstract class Dispatcher
{
	static protected $listeners = array();
	
	public static function register($eventClass, Listener $listener)
	{
		self::$listeners[$eventClass][] = $listener;
	}
	
	public static function unregister($eventClass, Listener $listener)
	{
		if(array_key_exists($eventClass, self::$listeners) && is_array(self::$listeners[$eventClass]))
		{
			foreach(self::$listeners[$eventClass] as $key=>$value)
			{
				if($value === $listener)
				{
					unset(self::$listeners[$eventClass][$key]);
				}
			}			
		}
	}
	
	public static function dispatch(Event $event)
	{
		$class = get_class($event);
		if(array_key_exists($class, self::$listeners) && is_array(self::$listeners[$class]))
		{
			try
			{
				foreach(self::$listeners[$class] as $listener)
				{
					try
					{
						$event->fireDo($listener);
					}
					catch(\Exception $e)
					{
						ErrorHandling::processModuleException($e);
					}
				}
			}
			catch (\Exception $e)
			{
				ErrorHandling::processEventException($e);
			}
		}
	}
}

?>