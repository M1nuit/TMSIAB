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

namespace ManiaLib\Gui\Cards\Dialogs;

use ManiaLib\Gui\Elements\Label;

/**
 * Dialog box with 1 button
 */
class OneButton extends \ManiaLib\Gui\Cards\Panel
{
	/**
	 * @var \ManiaLib\Gui\Elements\Button
	 */
	public $button;
	/**
	 * @var \ManiaLib\Gui\Elements\Label
	 */
	public $text;
	
	function __construct($sizeX = 65, $sizeY = 25)
	{
		parent::__construct($sizeX, $sizeY);
		
		$this->setSubStyle(\ManiaLib\Gui\Elements\Bgs1::BgTitle2);
		$this->title->setStyle(\ManiaLib\Gui\Elements\Label::TextTitle2);
		$this->addCardElement($this->title);
		
		$this->button = new \ManiaLib\Gui\Elements\Button;
		$this->button->setAlign('center', 'bottom');
		$this->addCardElement($this->button);
		
		$this->text = new Label();
		$this->text->setAlign('center', 'center');
		$this->text->enableAutonewline();
		$this->text->setStyle(Label::TextInfoMedium);
		$this->addCardElement($this->text);
	}
	
	function preFilter()
	{
		parent::preFilter();
		$this->text->setSize($this->sizeX - 6, $this->sizeY - 11);
		$this->text->setPositionY(10 - ($this->sizeY/2));
		$this->button->setPositionY(10 - $this->sizeY);
	}
	
}

?>