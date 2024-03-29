<?php
/**
 * ManiaLib - Lightweight PHP framework for Manialinks
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 257 $:
 * @author      $Author: melot.philippe $:
 * @date        $Date: 2011-08-23 17:30:21 +0200 (mar., 23 août 2011) $:
 */

namespace ManiaLib\Gui;

/**
 * Default element styles
 * @ignore
 */
abstract class DefaultStyles
{	
	/**#@+
	 * Default styles for the Panel card
	 */
	const Panel_Style = \ManiaLib\Gui\Elements\Quad::Bgs1;
	const Panel_Substyle = \ManiaLib\Gui\Elements\Bgs1::BgWindow2;
	const Panel_Title_Style = \ManiaLib\Gui\Elements\Label::TextTitle3;
	const Panel_TitleBg_Style = \ManiaLib\Gui\Elements\Quad::Bgs1;
	const Panel_TitleBg_Substyle = \ManiaLib\Gui\Elements\Bgs1::BgTitle3_1;
	/**#@-*/
	
	/**#@+
	 * Default styles for NavigationButton card
	 */
	const NavigationButton_Style = \ManiaLib\Gui\Elements\Quad::Bgs1;
	const NavigationButton_Substyle = \ManiaLib\Gui\Elements\Bgs1::BgEmpty;
	const NavigationButton_Text_Style = \ManiaLib\Gui\Elements\Label::TextButtonNav;
	const NavigationButton_Selected_Substyle = \ManiaLib\Gui\Elements\Bgs1::BgEmpty;
	/**#@-*/
	
	/**#@+
	 * Default styles for Navigation card
	 */
	const Navigation_Style = \ManiaLib\Gui\Elements\Quad::Bgs1;
	const Navigation_Substyle = \ManiaLib\Gui\Elements\Bgs1::BgWindow1;
	const Navigation_Title_Style = \ManiaLib\Gui\Elements\Label::TextTitle1;
	const Navigation_Subtitle_Style = \ManiaLib\Gui\Elements\Label::TextTips;
	const Navigation_TitleBg_Style = \ManiaLib\Gui\Elements\Quad::Bgs1;
	const Navigation_TitleBg_Substyle = \ManiaLib\Gui\Elements\Bgs1::BgTitle3_3;
	/**#@-*/
	
	/**#@+
	 * Default styles for the page navigator 
	 */
	const PageNavigator_ArrowNone_Substyle = \ManiaLib\Gui\Elements\Icons64x64_1::StarGold;
	const PageNavigator_ArrowNext_Substyle = \ManiaLib\Gui\Elements\Icons64x64_1::ArrowNext;
	const PageNavigator_ArrowPrev_Substyle = \ManiaLib\Gui\Elements\Icons64x64_1::ArrowPrev;
	const PageNavigator_ArrowLast_Substyle = \ManiaLib\Gui\Elements\Icons64x64_1::ArrowLast;
	const PageNavigator_ArrowFirst_Substyle = \ManiaLib\Gui\Elements\Icons64x64_1::ArrowFirst;
	const PageNavigator_ArrowFastNext_Substyle = \ManiaLib\Gui\Elements\Icons64x64_1::ArrowFastNext;
	const PageNavigator_ArrowFastPrev_Substyle = \ManiaLib\Gui\Elements\Icons64x64_1::ArrowFastPrev;
	/**#@-*/
}

?>