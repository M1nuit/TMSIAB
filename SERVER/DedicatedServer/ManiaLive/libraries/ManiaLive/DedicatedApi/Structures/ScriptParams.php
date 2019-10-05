<?php
/**
 * Represents a Dedicated TrackMania Server Player
 * ManiaLive - TrackMania dedicated server manager in PHP
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 249 $:
 * @author      $Author: martin.gwendal@gmail.com $:
 * @date        $Date: 2011-08-12 13:41:42 +0200 (ven., 12 août 2011) $:
 */

namespace ManiaLive\DedicatedApi\Structures;

final class ScriptParams extends AbstractStructure
{

    protected $params = array();

    public function __set($name, $value)
    {
	   $this->params[$name] = $value;
    }

    public function __get($name)
    {
	   return $this->params[$name];
    }

    public function __isset($name)
    {
	   return array_key_exists($name, $this->params);
    }

    public function __unset($name)
    {
	   if(array_key_exists($name, $this->params))
	   {
		  unset($this->params[$name]);
	   }
    }

}

?>
