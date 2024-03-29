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

namespace ManiaLive\Utilities;

use ManiaLive\DedicatedApi\Structures\Player;

use ManiaLive\Config\Loader;

abstract class Console
{
	public static function println($string)
	{
		Logger::getLog('Runtime')->write($string);
		echo $string.APP_NL;
	}
	
	public static function print_rln($string)
	{
		$line = print_r($string, true);
		Logger::getLog('Runtime')->write($line);
		echo $line.APP_NL;
	}
	
	public static function getDatestamp()
	{
		return date("H:i:s");
	}
	
	public static function printlnFormatted($string)
	{
		$line = '[' . self::getDatestamp() . '] ' . $string;
		self::println($line);
	}
	
	public static function printDebug($string)
	{
		if (APP_DEBUG)
		{
			$line = '[' . self::getDatestamp() . '|Debug] ' . $string;
			self::println($line);
		}
	}
	
	public static function printPlayerBest(Player $player)
	{
		$str = array();
		$str[] = '[Time by ' . $player->login . ' : ' . $player->bestTime . ']';
		foreach ($player->bestCheckpoints as $i => $time)
		{
			$str[] = '  [Checkpoint #' . $i . ': ' . $time . ']';
		}
		Console::println(implode(APP_NL, $str));
	}
	
	public static function printPlayerScore(Player $player)
	{
		$str = array();
		$str[] = '[Score by ' . $player->login . ' : ' . $player->score . ']';
		foreach ($player->bestCheckpoints as $i => $score)
		{
			$str[] = '  [Checkpoint #' . $i . ': ' . $score . ']';
		}
		Console::println(implode(APP_NL, $str));
	}
}

?>