<?php
/**
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 249 $:
 * @author      $Author: martin.gwendal@gmail.com $:
 * @date        $Date: 2011-08-12 13:41:42 +0200 (ven., 12 août 2011) $:
 */

namespace ManiaLib\Gui\Elements;

/**
 * ManiaPlanetLogos quad
 */
class ManiaPlanetLogos extends Quad
{
	/**#@+
	 * @ignore
	 */
	protected $style = \ManiaLib\Gui\Elements\Quad::ManiaPlanetLogos;
	protected $subStyle = self::IconPlanets;
	/**#@-*/
	
	const IconPlanets               = 'IconPlanets';
	const IconPlanetsPerspective    = 'IconPlanetsPerspective';
	const IconPlanetsSmall          = 'IconPlanetsSmall';
	const ManiaPlanetLogoBlack      = 'ManiaPlanetLogoBlack';
	const ManiaPlanetLogoBlackSmall = 'ManiaPlanetLogoBlackSmall';
	const ManiaPlanetLogoWhite      = 'ManiaPlanetLogoWhite';
	const ManiaPlanetLogoWhiteSmall = 'ManiaPlanetLogoWhiteSmall';
}

?>