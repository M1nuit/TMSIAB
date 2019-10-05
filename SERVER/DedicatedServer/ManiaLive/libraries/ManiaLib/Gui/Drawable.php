<?php
/**
 * ManiaLib - Lightweight PHP framework for Manialinks
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 249 $:
 * @author      $Author: martin.gwendal@gmail.com $:
 * @date        $Date: 2011-08-12 13:41:42 +0200 (ven., 12 août 2011) $:
 */

namespace ManiaLib\Gui;

/**
 * Can be drawn onto the Screen.
 * This is mainly used by ManiaLive.
 */
interface Drawable
{
	/**
	 * This draws the object onto the screen.
	 * Contains the drawing process.
	 */
	function save();
}

?>