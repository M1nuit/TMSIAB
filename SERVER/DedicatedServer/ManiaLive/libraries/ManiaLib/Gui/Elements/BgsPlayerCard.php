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
 * BgsPlayerCard quad
 */	
class BgsPlayerCard extends \ManiaLib\Gui\Elements\Quad
{
	/**#@+
	 * @ignore
	 */
	protected $style = \ManiaLib\Gui\Elements\Quad::BgsPlayerCard;
	protected $subStyle = self::BgActivePlayerCard;
	/**#@-*/
	
	const BgActivePlayerCard    = 'BgActivePlayerCard';
	const BgActivePlayerName    = 'BgActivePlayerName';
	const BgActivePlayerScore   = 'BgActivePlayerScore';
	const BgCard                = 'BgCard';
	const BgCardSystem          = 'BgCardSystem';
	const BgMediaTracker        = 'BgMediaTracker';
	const BgPlayerCardBig       = 'BgPlayerCardBig';
	const BgPlayerCardSmall     = 'BgPlayerCardSmall';
	const BgPlayerCard          = 'BgPlayerCard';
	const BgPlayerName          = 'BgPlayerName';
	const BgPlayerScore         = 'BgPlayerScore';
	const BgRacePlayerLine      = 'BgRacePlayerLine';
	const BgRacePlayerName      = 'BgRacePlayerName';
	const ListFocus             = 'ListFocus';
	const ProgressBar           = 'ProgressBar';
}

?>