<?php
/**
 * Represents a Mod for a TrackMania Dedicated Server
 * ManiaLive - TrackMania dedicated server manager in PHP
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 249 $:
 * @author      $Author: martin.gwendal@gmail.com $:
 * @date        $Date: 2011-08-12 13:41:42 +0200 (ven., 12 août 2011) $:
 */
namespace ManiaLive\DedicatedApi\Structures;

class Mod extends AbstractStructure
{
	public $env;
	public $url;
	
	function toArray()
	{
		return array('Env'=>$this->env,'Url'=>$this->url);
	}
}