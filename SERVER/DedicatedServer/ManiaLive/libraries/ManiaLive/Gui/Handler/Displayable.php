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

interface Displayable
{
	/**
	 * This function needs to be overwritten with code to
	 * actually draw the window and its contents.
	 */
	function display($login);
	
	/**
	 * Stuff to do when a window is being hidden.
	 */
	function hide($login);
	
	/**
	 * Position on the screen.
	 */
	function getPosX();
	function getPosY();
	function getPosZ();
	
	/**
	 * Returns the window's ID.
	 * this actually is not needed, but kept for compability
	 */
	function getId();
}
?>