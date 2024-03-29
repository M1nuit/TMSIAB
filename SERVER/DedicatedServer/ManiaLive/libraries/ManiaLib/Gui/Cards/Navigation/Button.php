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

namespace ManiaLib\Gui\Cards\Navigation;

/**
 * Navigation button
 */ 
class Button extends \ManiaLib\Gui\Elements\Quad
{
	/**
	 * TrackMania formatting string appended to the text when a button
	 * is selected (default is just a light blue color)
	 */
	static public $unselectedTextStyle = '$fff';
	
	/**
	 * @var \ManiaLib\Gui\Elements\Label
	 */
	public $text;
	/**
	 * @var \ManiaLib\Gui\Elements\Icon
	 */
	public $icon;
	public $iconSizeMinimizer = 1.5;
	public $textSizeMinimizer = 3;
	public $textOffset = 8;
	/**
	 *
	 * @var \ManiaLib\Gui\Elements\Icons64x64_1
	 */
	protected $selectedIcon;
	/**
	 * @ignore
	 */
	protected $isSelected = false;
	protected $forceLinks = true;

	function __construct ($sx=29.5, $sy=8.5) 
	{
		$this->sizeX = $sx;
		$this->sizeY = $sy;	
		
		$this->setStyle(\ManiaLib\Gui\DefaultStyles::NavigationButton_Style);
		$this->setSubStyle(\ManiaLib\Gui\DefaultStyles::NavigationButton_Substyle);
		
		$this->text = new \ManiaLib\Gui\Elements\Label();
		$this->text->setValign("center");
		$this->text->setPosition($this->textOffset, 0.25, 1);
		$this->text->setStyle(\ManiaLib\Gui\DefaultStyles::NavigationButton_Text_Style);
		
		$this->icon = new \ManiaLib\Gui\Elements\Icons128x128_1($this->sizeY);
		$this->icon->setValign("center");
		$this->icon->setPosition(55, 0, 1);
		
	}
	
	/**
	 * Sets the button selected and change its styles accordingly
	 */
	function setSelected() 
	{
		$this->isSelected = true;	
		$this->selectedIcon = new \ManiaLib\Gui\Elements\Icons64x64_1(11);
		$this->selectedIcon->setSubStyle(\ManiaLib\Gui\Elements\Icons64x64_1::ShowRight);
		$this->selectedIcon->setValign('center');
		$this->selectedIcon->setPosX(71);
	}
	
	/**
	 * @ignore
	 */
	protected function postFilter ()
	{		
		if(!$this->isSelected)
		{
			if($this->text->getText())
			{
				$this->text->setText(self::$unselectedTextStyle.$this->text->getText());
			}
		}
		
		$this->text->setSizeX($this->sizeX - $this->text->getPosX() - $this->textSizeMinimizer);
		$this->text->setSizeY(0);
		
		if($this->forceLinks)
		{
			$this->text->addLink($this);
			$this->icon->addLink($this);
		}
		$newPos = \ManiaLib\Gui\Tools::getAlignedPos ($this, "left", "center");
		
		// Drawing
		\ManiaLib\Gui\Manialink::beginFrame($newPos["x"], $newPos["y"], $this->posZ+1);
			$this->text->save();
			$this->icon->save();
			if($this->isSelected)
			{
				$this->selectedIcon->save();
			}
		\ManiaLib\Gui\Manialink::endFrame();
	}
}

?>