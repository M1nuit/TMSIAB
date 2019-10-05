<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows;

use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Gui\Windowing\Controls\ButtonResizeable;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Button;
use ManiaLive\Gui\Windowing\Windows\Info;
use ManiaLive\Gui\Windowing\Windows\Dialog;
use ManiaLive\Gui\Windowing\Controls\PageNavigator;
use ManiaLive\Gui\Windowing\Controls\Panel;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Tools;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Entry;
use ManiaLib\Gui\Layouts\Flow;
use ManiaLive\Gui\Windowing\WindowHandler;
use ManiaLive\Utilities\Time;
use ManiaLive\Utilities\Console;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Controls\Cell;

class trackList extends \ManiaLive\Gui\Windowing\ManagedWindow {

	private $sql;
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
	//Some settings
	private $clicks_disabled = false;
	//Other
	private $identifier;
	//Time when Draw has started.
	private $startTime;
	private $loaderOpen = false;
	private $loderScreen = null;
	private $mlepp;

	function initializeComponents() {
		$this->mlepp = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance();
		$this->page = 1;
		$this->page_last = 1;
		$this->item_height = 6;
		$this->table_height = 0;
		$this->columns = array();
		$this->highlight = false;

		// add background for navigation elements ...
		$this->navigator_back = new BgsPlayerCard();
		$this->navigator_back->setSubStyle(BgsPlayerCard::BgCardSystem);
		$this->addComponent($this->navigator_back);

		// create panel ...
		$this->panel = new Panel();
		$this->panel->setTitle('Tracks on server');
		$this->addComponent($this->panel);

		// build close button ...
		/* $this->btn_close = new Icons64x64_1(3);
		  $this->btn_close->setSubStyle(Icons64x64_1::Close);
		  $this->btn_close->setAction($this->callback('hide'));
		  $this->addComponent($this->btn_close); */

		// create records-table ...
		$this->table = new Frame($this->getSizeX() - 4, $this->getSizeY() - 21);
		$this->table->applyLayout(new Flow());
		$this->table->setPosition(2, 16);
		$this->addComponent($this->table);

		// create page navigator ...
		$this->navigator = new PageNavigator();
		$this->addComponent($this->navigator);

		$this->setMaximizable(true);

		$this->identifier = "challenge_id";
	}

	public function setTitle($title) {
		$this->panel->setTitle($title);
	}

	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	public function setSQL($sql, $nb = null) {
		$this->sql = $sql;


		if ($nb == null) {
			//Counting number of Challanges

			$this->sql->setSelect("Count(*) as Nb");
			$this->sql->setLimit(null);

			$nb = $this->mlepp->db->query($this->sql);
			$nb = $nb->fetchAssoc($nb);

			$nb = $nb["Nb"];
		}

		$this->calculatePages($nb);
	}

	function onResize() {
		$this->table->setSize($this->getSizeX() - 4, $this->getSizeY() - 21);

		if (empty($this->records) && !empty($this->sql))
			$this->setSQL($this->sql);
		else
			$this->calculatePages(count($this->records));
	}

	function onDraw() {
		// stretch panel onto full window size ...
		$this->panel->setSize($this->getSizeX(), $this->getSizeY());

		// position the exit button ...
		//$this->btn_close->setPosition($this->getSizeX() - 5, 1.6);
		// refresh table ...
		$this->table->clearComponents();

		foreach ($this->columns as $column) {

				$cell = new Cell($column->width * $this->table->getSizeX(), $this->item_height, "title\\" . $column->type);
				$cell->setText($column->text);

				$this->table->addComponent($cell);
		}

		if (empty($this->records) && !empty($this->sql))
			$this->createFromSql();
		else
			$this->createFromRecords();

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

		if ($this->page < $this->page_last && $this->info == null) {
			$this->navigator->arrowNext->setAction($this->callback('showNextPage'));
			$this->navigator->arrowLast->setAction($this->callback('showLastPage'));
		} else {
			$this->navigator->arrowNext->setAction(null);
			$this->navigator->arrowLast->setAction(null);
		}

		if ($this->page > 1 && $this->info == null) {
			$this->navigator->arrowPrev->setAction($this->callback('showPrevPage'));
			$this->navigator->arrowFirst->setAction($this->callback('showFirstPage'));
		} else {
			$this->navigator->arrowPrev->setAction(null);
			$this->navigator->arrowFirst->setAction(null);
		}
	}

	private function createFromSql() {

		$this->startTime = microtime(true);

		// create table body ...
		//echo $this->page_items."-".$this->page;
		$start = $this->page_items * ($this->page - 1);
		if ($this->page < 1) {
			$start = 0;
			$this->page = 1;
		}
		//Create SQL to make the search
		$this->sql->setSelect("*");

		$this->sql->setLimit(array($start, $this->page_items));

		$items = $this->mlepp->db->query($this->sql);

		$i = 1;
		while ($record = $items->fetchAssoc($items)) {

			/*if($this->loaderOpen == true){
			  $this->loaderScreen->setText("The Track list is opening be patient plz. Progress : ".($i/$this->page_items)*100);
			  }
			  else if( ($i/$this->page_items)<0.75 && ( (microtime(true) - $this->startTime)/$i ) > 10 ){
			  $this->loaderScreen = Info::Create($this->login);
			  $this->loaderScreen->setSize(50, 20);
			  $this->loaderScreen->setTitle('Plw Wait. Track List is opening');
			  $this->loaderScreen->setText("The Track list is opening be patient plz. Progress : ".($i/$this->page_items)*100);
			  $this->loaderScreen->centerOnScreen();
			  WindowHandler::showDialog($this->loaderScreen);

			  echo "gee0";
			  $this->loaderOpen = true;
			  } */
			$this->createline($record, '');
		}

		/* if($this->loaderOpen == true){
		  WindowHandler::closeWindowThumbs($this->loaderOpen);
		  $this->loaderScreen->destroy();
		  $this->loaderOpen = false;
		  } */
	}

	private function createFromRecords() {
			$count = count($this->records);
			$this->calculatePages($count);
			$max = $this->page_items * $this->page;
			for ($i = $this->page_items * ($this->page - 1); $i < $count && $i < $max; $i++) {
				$this->createline($this->records[$i]);
			}
		}

		private function createline($record, $prefix="") {

			foreach ($this->columns as $column) {

				if (isset($record[$prefix . "challenge_uid"]))
					$uid = $record[$prefix . "challenge_uid"];
				else
					$uid = -1;

				$cell = new Cell($column->width * $this->table->getSizeX(), $this->item_height, "content\\" . $column->type, $record[$prefix . $this->identifier], $this->login, $this, $uid);

				if (isset($record[$prefix . $column->name]))
					$cell->setText($record[$prefix . $column->name]);
				else
					$cell->setText('n/a');

				$this->table->addComponent($cell);
			}
		}


	function onHide() {
		$this->showFirstPage();
		$this->highlight = false;
	}

	function calculatePages($nb=1) {
		if ($nb == 0)
			return;
		
		$this->page_items = floor( ($this->table->getSizeY()-12) / $this->item_height);
		$this->page_last = ceil($nb * $this->item_height / max(1, $this->table->getSizeY()-12));
		
		if ($this->page > $this->page_last) {
			$this->page = $this->page_last;
		}
	}

	function setColumns($columns) {
		$this->columns = $columns;
	}

	function clearRecords() {
		$this->records = array();
		$this->identifier = "challenge_id";
	}

	function addRecord($record) {
		if (is_array($record)) {
			$this->sql = "";
			$this->records[] = $record;
		}
	}

	function showPrevPage($login = null) {
		$this->page--;
		if ($login)
			$this->show();
	}

	function showNextPage($login = null) {
		$this->page++;
		if ($login)
			$this->show();
	}

	function showLastPage($login = null) {
		$this->page = $this->page_last;
		if ($login)
			$this->show();
	}

	function showFirstPage($login = null) {
		$this->page = 1;
		if ($login)
			$this->show();
	}

	public function getClicks_disabled() {
		return $this->clicks_disabled;
	}

	public function setClicks_disabled($clicks_disabled) {
		$this->clicks_disabled = $clicks_disabled;
	}

	public function destroy() {
		//self::$plugin_jb = null;
		//$this->mlepp->db = null;
		parent::destroy();
	}

}