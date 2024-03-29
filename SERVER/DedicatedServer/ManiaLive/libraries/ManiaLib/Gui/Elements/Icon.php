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
 * Icon
 * Should be abstract some day, use classes like "\ManiaLib\Gui\Elements\Icons128x128_1" instead
 */
abstract class Icon extends \ManiaLib\Gui\Elements\Quad
{
	/**#@+
	 * @ignore
	 */
	protected $style = \ManiaLib\Gui\Elements\Quad::Icons128x128_1;
	protected $subStyle = \ManiaLib\Gui\Elements\Icons128x128_1::United;
	/**#@-*/

	function __construct($size = 7)
	{
		$this->sizeX = $size;
		$this->sizeY = $size;
	}
}

?>