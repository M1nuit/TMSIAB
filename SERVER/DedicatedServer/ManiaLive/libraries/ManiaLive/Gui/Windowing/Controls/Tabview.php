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

namespace ManiaLive\Gui\Windowing\Controls;

use ManiaLib\Gui\Layouts\Line;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1;

/**
 * Tabview component like it is known from any
 * windowing system.
 * 
 * @author Florian Schnell
 */
class Tabview extends \ManiaLive\Gui\Windowing\Control
{
	protected $tabs;
	protected $background;
	protected $tabFan;
	protected $content;
	protected $activeId;
	protected $activeIdPrev;
	
	function initializeComponents()
	{
		$this->tabs = array();
		$this->activeId = 0;
		$this->activeIdPrev = null;
		
		$this->background = new BgsPlayerCard();
		$this->background->setSubStyle(BgsPlayerCard::BgCardSystem);
		$this->addComponent($this->background);
		
		$this->tabFan = new Frame(0, 0, new Line());
		$this->addComponent($this->tabFan);
		
		$this->content = new Frame();
		$this->content->setPosition(0.5, 5);
		$this->addComponent($this->content);
	}
	
	function onResize()
	{
		$tab = $this->tabs[$this->activeId];
		$tab->setSize($this->sizeX - 1, $this->sizeY - 5.5);
	}
	
	function beforeDraw()
	{
		// draw content background
		$this->background->setPosition(0, 5);
		$this->background->setSize($this->getSizeX(), $this->getSizeY() - 5);
		
		$this->tabFan->clearComponents();
		
		// build tab fan on the top
		foreach ($this->tabs as $i => $tab)
		{
			// start building fan element for tab
			$frame = new Frame();
			$frame->setSize(25, 5);
			{
				$ui = new Bgs1(25, 5);
				if ($i == $this->activeId)
				{
					$ui->setSubStyle(Bgs1::NavButtonBlink);
				}
				else
				{
					$ui->setSubStyle(Bgs1::NavButton);
				}
				$ui->setAction($this->callback('clickOnTab', $i));
				$frame->addComponent($ui);
				
				$ui = new Label(23, 5);
				$ui->setPosition(1, 0.4);
				$ui->setTextSize(2);
				$ui->setTextColor('fff');
				$ui->setText($tab->getTitle());
				$frame->addComponent($ui);
			}
			$this->tabFan->addComponent($frame);
		}
		
		// change tab content if it has been switched
		if ($this->activeIdPrev !== $this->activeId)
		{
			// remove old tab
			if (isset($this->tabs[$this->activeIdPrev]))
			{
				$old_tab = $this->tabs[$this->activeIdPrev];
				$this->content->clearComponents();
				$old_tab->onDeactivate();
			}
			
			// add selected tab as content
			if (isset($this->tabs[$this->activeId]))
			{
				$new_tab = $this->tabs[$this->activeId];
				$new_tab->setSize($this->getSizeX()-1, $this->getSizeY() - 4);
				$this->content->addComponent($new_tab);
				$new_tab->onActivate();
			}
		}
		
		$this->activeIdPrev = $this->activeId;
	}
	
	/**
	 * Changes the active Tab on user's click.
	 * @param string $login
	 * @param integer $id
	 */
	function clickOnTab($login, $id)
	{
		$this->activeId = $id;
		$this->redraw();
	}
	
	/**
	 * Adds a Tab to the Tabview.
	 * @param $tab
	 */
	function addTab(Tab $tab)
	{
		$this->tabs[] = $tab;
	}
	
	/**
	 * Returns the Tab with the specified Id.
	 * Ids are assigned to Tabs in the order they are added to the Tabview.
	 * @param $id
	 */
	function getTab($id)
	{
		if (isset($this->tabs[$id]))
		{
			return $this->tabs[$id];
		}
	}
	
	/**
	 * Returns the Id of the Tab that is currently shown.
	 * @return integer Id o currently active Tab
	 */
	function getActiveTabId()
	{
		return $this->activeId;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see libraries/ManiaLive/Gui/Windowing/ManiaLive\Gui\Windowing.Control::destroy()
	 */
	function destroy()
	{
		if ($this->tabs != null)
		{
			foreach ($this->tabs as $tab)
			{
				$tab->destroy();
			}
		}
		$this->tabs = null;
		parent::destroy();
	}
}

?>