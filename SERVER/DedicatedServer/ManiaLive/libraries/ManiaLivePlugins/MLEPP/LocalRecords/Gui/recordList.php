<?php

namespace ManiaLivePlugins\MLEPP\LocalRecords\Gui;

use ManiaLivePlugins\MLEPP\LocalRecords\Gui\Cell;
use ManiaLib\Gui\Layouts\Flow;
use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Gui\Windowing\Controls\ButtonResizeable;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Button;
use ManiaLive\Gui\Windowing\Windows\Info;
use ManiaLive\Gui\Windowing\Controls\PageNavigator;
use ManiaLive\Gui\Windowing\Controls\Panel;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Tools;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Entry;
use ManiaLive\Utilities\Time;
use ManiaLive\Utilities\Console;

class recordList extends \ManiaLive\Gui\Windowing\ManagedWindow
{
	//components ...
	private $panel;
	private $btn_close;
	private $navigator;
	private $table;
	private $btn_player;
	private $btn_website;
	private $navigator_back;
	
	private $page;
	private $records;
	private $page_last;
	private $page_items;
	private $item_height;
	private $table_height;
	private $columns;
	private $info;
	private $highlight;
	
	function initializeComponents()
	{
		$this->page = 1;
		$this->page_last = 1;
		$this->item_height = 4;
		$this->table_height = 0;
		$this->columns = array();
		$this->highlight = false;
		
		// add background for navigation elements ...
		$this->navigator_back = new BgsPlayerCard();
		$this->navigator_back->setSubStyle(BgsPlayerCard::BgCardSystem);
		$this->addComponent($this->navigator_back);
		
        $challengeinfo = Connection::getInstance()->getCurrentChallengeInfo();
        
		// create panel ...
		$this->panel = new Panel();
		$this->panel->setTitle('Records on '.$challengeinfo->name);
		$this->addComponent($this->panel);
		
		// build close button ...
		$this->btn_close = new Icons64x64_1(3);
		$this->btn_close->setSubStyle(Icons64x64_1::Close);
		$this->btn_close->setAction($this->callback('hide'));
		$this->addComponent($this->btn_close);
		
		// create records-table ...
		$this->table = new Frame($this->getSizeX() - 4, $this->getSizeY() - 18);
		$this->table->applyLayout(new Flow());
		$this->table->setPosition(2, 6);
		$this->addComponent($this->table);
		
		// create page navigator ...
		$this->navigator = new PageNavigator();
		$this->addComponent($this->navigator);
	}
	
	function onResize()
	{
		$this->table->setSize($this->getSizeX() - 4, $this->getSizeY() - 21);
		$this->calculatePages();
	}
	
	function onShow()
	{
		// stretch panel onto full window size ...
		$this->panel->setSize($this->getSizeX(), $this->getSizeY());
		
		// position the exit button ...
		$this->btn_close->setPosition($this->getSizeX() - 5, 1.6);
		
		// refresh table ...
		$this->table->clearComponents();
		
		foreach ($this->columns as $name => $percent)
		{
			$cell = new Cell($percent * $this->table->getSizeX(), $this->item_height + 1);
			$cell->setText($name);
			
			$this->table->addComponent($cell);
		}
		
		// create table body ...
		$count = count($this->records);
		$max = $this->page_items * $this->page;
		for ($i = $this->page_items * ($this->page - 1); $i < $count && $i < $max; $i++)
		{
			$record = $this->records[$i];
			
			foreach ($this->columns as $name => $percent)
			{
				$cell = new Cell($percent * $this->table->getSizeX(), $this->item_height);
				
				if (isset($record[$name]))
					$cell->setText($record[$name]);
				else
					$cell->setText('n/a');
				
				$this->table->addComponent($cell);
			}
		}
	
		// add page navigator to the bottom ...
		$this->navigator->setPositionX($this->getSizeX() / 2);
		$this->navigator->setPositionY($this->getSizeY() - 4);
		
		// place navigation background ...
		$this->navigator_back->setValign('bottom');
		$this->navigator_back->setSize($this->getSizeX() - 0.6, 8);
		$this->navigator_back->setPosition(0.3, $this->getSizeY() - 0.3);
		
		// configure ...
		$this->navigator->setCurrentPage($this->page);
		$this->navigator->setPageNumber($this->page_last);
		$this->navigator->showText(true);
		$this->navigator->showLast(true);
		
		if ($this->page < $this->page_last && $this->info == null)
		{
			$this->navigator->arrowNext->setAction($this->callback('showNextPage'));
			$this->navigator->arrowLast->setAction($this->callback('showLastPage'));
		}
		else
		{
			$this->navigator->arrowNext->setAction(null);
			$this->navigator->arrowLast->setAction(null);
		}
		
		if ($this->page > 1 && $this->info == null)
		{
			$this->navigator->arrowPrev->setAction($this->callback('showPrevPage'));
			$this->navigator->arrowFirst->setAction($this->callback('showFirstPage'));
		}
		else
		{
			$this->navigator->arrowPrev->setAction(null);
			$this->navigator->arrowFirst->setAction(null);
		}
	}
	
	function onHide()
	{
		$this->showFirstPage();
		$this->btn_close->setAction($this->callback('hide'));
		$this->highlight = false;
	}
	
	
	function calculatePages()
	{
		$this->page_items = floor($this->table->getSizeY() / $this->item_height);
		$this->page_last = ceil(count($this->records) * $this->item_height / $this->table->getSizeY());
	}
	
	function addColumn($name, $percent)
	{
		$this->columns[$name] = $percent;
	}
	
	function clearRecords()
	{
		$this->records = array();
	}
	
	function addRecord($record)
	{
		if (is_array($record))
		{
			$this->records[] = $record;
			$this->calculatePages();
		}
	}
	
	function showPrevPage($login = null)
	{
		$this->page--;
		if ($login) $this->show();
	}
	
	function showNextPage($login = null)
	{
		$this->page++;
		if ($login) $this->show();
	}
	
	function showLastPage($login = null)
	{
		$this->page = $this->page_last;
		if ($login) $this->show();
	}
	
	function showFirstPage($login = null)
	{
		$this->page = 1;
		if ($login) $this->show();
	}
}
