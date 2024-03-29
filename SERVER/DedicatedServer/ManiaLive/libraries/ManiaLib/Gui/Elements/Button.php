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

namespace ManiaLib\Gui\Elements;

/**
 * Button
 */
class Button extends \ManiaLib\Gui\Elements\Label
{
	const CardButtonMedium       = 'CardButtonMedium';
	const CardButtonMediumWide   = 'CardButtonMediumWide';
	const CardButtonSmallWide     = 'CardButtonSmallWide';
	const CardButtonSmall         = 'CardButtonSmall';
	
	/**#@+
	 * @ignore 
	 */
	protected $subStyle = null;
	protected $style = self::CardButtonMedium;
	/**#@-*/
	
	function __construct($sizeX = 26, $sizeY = 4)
	{
		parent::__construct($sizeX, $sizeY);		
	}
}

?>