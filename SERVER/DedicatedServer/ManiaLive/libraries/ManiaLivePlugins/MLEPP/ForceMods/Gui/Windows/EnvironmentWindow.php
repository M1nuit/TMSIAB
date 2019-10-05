<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name ForceMods
 * @date $Date: 2011-07-09 19:18:31 +0200 (za, 09 jul 2011) $
 * @version $Revision: 858 $
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author Klaus "schmidi" Schmidhuber <schmidi.tm@gmail.com>
 * @copyright 2010 - 2011
 *
 * ---------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * You are allowed to change things of use this in other projects, as
 * long as you leave the information at the top (name, date, version,
 * website, package, author, copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */


namespace ManiaLivePlugins\MLEPP\ForceMods\Gui\Windows;

use ManiaLivePlugins\MLEPP\ForceMods\Gui\Elements\Button;
use ManiaLivePlugins\MLEPP\ForceMods\Structures\FModList;

use ManiaLib\Gui\Elements\Label;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLive\Gui\Windowing\Controls\PageNavigator;


class EnvironmentWindow extends \ManiaLive\Gui\Windowing\ManagedWindow {

	protected $currentPage = 1;
	protected $lastPage = 3;

	protected $name = '';
	protected $modList;

	protected $navigator;
	protected $frame;


	/**
	 * @fn initializeComponents()
	 * @brief Function called on initialisation.
	 *
	 * @return void
	 */
	function initializeComponents() {
		$this->setSize(72, 72);

		$this->modList = new FModList();

		// frame
		$this->frame = new Frame($this->getSizeX() - 4, $this->getSizeY() - 18);
		$this->frame->setPosition(2, 6);
		$this->addComponent($this->frame);

		// navigator
		$this->navigator = new PageNavigator();
		$this->addComponent($this->navigator);
	}


	/**
	 * @fn onDraw()
	 * @brief Function called on draw.
	 *
	 * @return void
	 */
	function onDraw() {
		$itemCount = 10;

		$count = $this->modList->countMods();

		$this->setTitle($this->name.' ('.$count.')');
		$this->frame->clearComponents();

		$count = $this->modList->countMods();
		$this->lastPage = ceil($count / $itemCount);

		$start = ($this->currentPage - 1) * $itemCount;
		$end = Min($start + $itemCount - 1, $count - 1);

		for($i = 0; $start <= $end; $start++, $i++) {
			// label name
			$label = new Label(40, 20);
			$label->setPosition(10, 23);
			$label->setHAlign('right');
			$label->setText($this->modList->getName($start));
			$this->frame->addComponent($label);

			// on button
			$button = new Button(5, 5, 'On', array($this, 'onClick'), $start, !$this->modList->isEnabled($start));
			$button->setPosition(20, 23);
			$this->frame->addComponent($button);

			// off button
			$button = new Button(5, 5, 'Off', array($this, 'onClick'), $start, $this->modList->isEnabled($start));
			$button->setPosition(30, 23);
			$this->frame->addComponent($button);
		}

		// navigator
		$this->navigator->setPositionX($this->getSizeX() / 2);
		$this->navigator->setPositionY($this->getSizeY() - 4);
		$this->navigator->setCurrentPage($this->currentPage);
		$this->navigator->setPageNumber($this->lastPage);
		$this->navigator->showText(true);
		$this->navigator->showLast(true);

		if($this->currentPage < $this->lastPage) {
			$this->navigator->arrowNext->setAction($this->callback('nextPage'));
			$this->navigator->arrowLast->setAction($this->callback('lastPage'));
		}
		else {
			$this->navigator->arrowNext->setAction(NULL);
			$this->navigator->arrowLast->setAction(NULL);
		}

		if($this->currentPage > 1) {
			$this->navigator->arrowPrev->setAction($this->callback('prevPage'));
			$this->navigator->arrowFirst->setAction($this->callback('firstPage'));
		}
		else {
			$this->navigator->arrowPrev->setAction(NULL);
			$this->navigator->arrowFirst->setAction(NULL);
		}
	}


	/**
	 * @fn setEnvironment()
	 * @brief Function to set current environment.
	 *
	 * @param mixed $name
	 * @param mixed $modList
	 * @return void
	 */
	function setEnvironment($name, &$modList) {
		if(get_class($modList) == 'ManiaLivePlugins\MLEPP\ForceMods\Structures\FModList') {
			$this->name = $name;
			$this->modList = $modList;
			$this->currentPage = 1;
		}
	}


	/**
	 * @fn nextPage()
	 * @brief Function called on nextPage click.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function nextPage($login = NULL) {
		$this->currentPage = Min(++$this->currentPage, $this->lastPage);
		if($login) {
			$this->show();
		}
	}


	/**
	 * @fn prevPage()
	 * @brief Function called on prevPage click.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function prevPage($login = NULL) {
		$this->currentPage = Max(--$this->currentPage, 0);
		if($login) {
			$this->show();
		}
	}


	/**
	 * @fn firstPage()
	 * @brief Function called on firstPage click.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function firstPage($login = NULL) {
		$this->currentPage = 0;
		if($login) {
			$this->show();
		}
	}


	/**
	 * @fn lastPage()
	 * @brief Function called on lastPage click.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function lastPage($login = NULL) {
		$this->currentPage = $this->lastPage;
		if($login) {
			$this->show();
		}
	}


	/**
	 * @fn onClick()
	 * @brief Function called on click.
	 *
	 * @param mixed $login
	 * @param mixed $index
	 * @return void
	 */
	function onClick($login, $index) {
		if($this->modList->isEnabled($index)) {
			$this->modList->disable($index);
		}
		else {
			$this->modList->enable($index);
		}
		$this->show();
	}

	function destroy()
	{
		parent::destroy();
	}
}

?>