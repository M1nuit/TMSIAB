<?php
/**
 * ManiaLive - TrackMania dedicated server manager in PHP
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 249 $:
 * @author      $Author: martin.gwendal@gmail.com $:
 * @date        $Date: 2011-08-12 13:41:42 +0200 (ven., 12 août 2011) $:
 */

namespace ManiaLive\Gui\Handler;

use ManiaLive\Utilities\Console;

define('ID_MAX', pow(2,32) - 1);

/**
 * Generates IDs for Windows and Actions.
 */
abstract class IDGenerator
{
	static private $manialinkCounter = 0;
	static private $actionCounter = array();
	static private $actionMax = 0;
	static private $actionToManialink = array();
	
	/**
	 * Generates unique IDs for Manialinks.
	 * @throws \OverflowException
	 */
	static function generateManialinkID()
	{
		self::$manialinkCounter++;
		
		// check whether any more windows can be created ...
		if (self::checkForOverflow())
		{
			throw new \OverflowException('There are too many Manialinks already!');
		}
		
		// pretty dirty, but because stringfunctions are php inbuilt they are very fast!
		// generate binary string of upcounted value, then reverse and shift zeros from the right.
		// after that process the action-counter can be AND-linked to receive a windowspecific actionid.
		$id = bindec(str_pad(strrev(decbin(self::$manialinkCounter)), 31, '0'));
		self::$actionCounter[$id] = 0;
		
		// Console::printDebug('Manialink ID #' . $id . ' assigned.');
		
		// return handle as decimal number ...
		return $id;
	}
	
	static function getManialinkIDByActionID($actionId)
	{
		return self::$actionToManialink[$actionId];
	}
	
	/**
	 * Free the memory allocated by a certain Manialink.
	 * @param integer $manialink_id
	 */
	static function freeManialinkIDs($manialink_id)
	{
		unset(self::$actionCounter[$manialink_id]);
		
		foreach (self::$actionToManialink as $actionId => $manialinkId)
		{
			if ($manialinkId == $manialink_id)
			{
				unset(self::$actionToManialink[$manialinkId]);
			}
		}
	}
	
	/**
	 * Generate a new unique action ID for a certain Manialink.
	 * @param integer $manialink_id
	 * @throws \Exception
	 * @throws \OverflowException
	 */
	static function generateActionID($manialink_id)
	{
		// check for the windows ...
		if (!key_exists($manialink_id, self::$actionCounter))
		{
			throw new \Exception('There is no Manialink with this ID, or it has not been created using ManialinkHandle!');
		}
		
		// check for an overflow in case of too many actions or windows ...
		if (self::$actionMax < ++self::$actionCounter[$manialink_id])
		{
			// set new action max ...
			self::$actionMax = self::$actionCounter[$manialink_id];
			
			// only check overflow for a new action max
			// this will enable action creation for windows that haven't reached
			// the bounds yet ...
			if (self::checkForOverflow())
			{
				throw new \OverflowException('This window has too many actions yet!');
			}
		}
		
		// Console::printDebug('Action #' . self::$action_counter[$manialink_id] . ' assigned.');
		
		$actionId = ($manialink_id | self::$actionCounter[$manialink_id]);
		
		self::$actionToManialink[$actionId] = $manialink_id;
		
		return $actionId;
	}
	
	/**
	 * Checks if there's a collision of Manialink IDs.
	 */
	static function checkForOverflow()
	{
		return (self::$manialinkCounter + self::$actionMax > ID_MAX);
	}
}
?>