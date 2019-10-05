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

namespace ManiaLive\Gui\Windowing\Controls;

/**
 * Frame element will move all its content
 * when position changes.
 * You can also apply a layout that is applied
 * to all its subcomponents.
 * 
 * @author Florian Schnell
 */
class Frame extends \ManiaLive\Gui\Windowing\Control
{
	function initializeComponents()
	{
		$this->posX = $this->getParam(0);
		$this->posY = $this->getParam(1);
		$this->layout = $this->getParam(2);
	}
	
	function beforeDraw() {}
	
	function afterDraw() {}
}

?>