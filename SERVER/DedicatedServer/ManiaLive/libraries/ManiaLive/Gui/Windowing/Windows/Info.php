<?php
/**
 * ManiaLive - TrackMania dedicated server manager in PHP
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 259 $:
 * @author      $Author: melot.philippe $:
 * @date        $Date: 2011-08-24 17:03:19 +0200 (mer., 24 août 2011) $:
 */

namespace ManiaLive\Gui\Windowing\Windows;

use ManiaLib\Gui\Elements\Bgs1;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Label;
use ManiaLive\Gui\Windowing\Controls\Panel;

/**
 * @author Florian Schnell
 */
class Info extends \ManiaLive\Gui\Windowing\Window
{
	protected $title;
	protected $text;
	protected $panel;
	protected $button;
	
	protected function initializeComponents()
	{	
		$this->panel = new Panel();
		$this->panel->setBackgroundStyle(Bgs1::BgWindow2);
		$this->addComponent($this->panel);

		$this->text = new Label();
		$this->text->enableAutonewline();
		$this->addComponent($this->text);
	}
	
	protected function onHide() {}
	
	protected function onShow()
	{
		// stretch panel to fill window size ...
		$this->panel->setSize($this->sizeX, $this->sizeY);
		
		// position and resize text ...
		$this->text->setPosition(2, 17);
		$this->text->setSize($this->sizeX - 4, $this->sizeY - 6);
	}
	
	function setText($text)
	{		
		$this->text->setText($text);
	}
	
	function makeTransparent()
	{
		$this->panel->setBackgroundStyle(Bgs1::BgWindow1);
	}
	
	function makeOpaque()
	{
		$this->panel->setBackgroundStyle(Bgs1::BgTitle2);
	}
	
	function setTitle($title)
	{
		$this->panel->setTitle($title);
	}
}

?>