<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 * 
 * -- MLEPP Plugin --
 * @name ForceMods
 * @date $Date: 2011-06-22 21:20:36 +0200 (wo, 22 jun 2011) $
 * @version $Revision: 826 $
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


namespace ManiaLivePlugins\MLEPP\ForceMods\Gui\Elements;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\BgsPlayerCard;


class Button extends \ManiaLive\Gui\Windowing\Control
{

	protected $background;
	protected $label;
	protected $callback;
	protected $action;
	protected $active;

	
	/**
	 * @fn initializeComponents()
	 * @brief Function called on initialisation.
	 *
	 * @return void
	 */
	function initializeComponents() {
		$this->sizeX = $this->getParam(0);
		$this->sizeY = $this->getParam(1);
		
		// insert background ...
		$this->background = new Bgs1InRace ($this->getSizeX(), $this->getSizeY());
		$this->addComponent($this->background);
		
		// insert label ...
		$this->label = new Label($this->getSizeX() - 2, $this->getSizeY());
		$this->label->setAlign('center', 'center');
		$this->label->setPosition($this->getSizeX() / 2, $this->getSizeY() / 2);
		$this->addComponent($this->label);
		
		$this->label->setText($this->getParam(2));
		$this->callback = $this->getParam(3);
		$this->action = $this->getParam(4);
		$this->active = ($this->getParam(5) === true);
	}
	

	/**
	 * @fn onResize()
	 * @brief Function called on resize.
	 *
	 * @return void
	 */
	function onResize() {
		$this->background->setSize($this->getSizeX(), $this->getSizeY());
		$this->label->setSize($this->getSizeX() - 2, $this->getSizeY());
	}
	

	/**
	 * @fn beforeDraw()
	 * @brief Function called before Draw.
	 *
	 * @return void
	 */
	function beforeDraw() {
		if ($this->active) {
			$this->background->setSubStyle(Bgs1InRace::NavButton);
			$this->background->setAction($this->callback('onClicked'));
		}
		else {
			$this->background->setSubStyle(Bgs1InRace::NavButtonBlink);
			$this->background->setAction(NULL);
		}
	}
	
	
	/**
	 * @fn setActive()
	 * @brief Function to set button active.
	 *
	 * @param mixed $active
	 * @return void
	 */
	function setActive($active) {
		$this->active = $active;
	}
	
	
	/**
	 * @fn onClicked()
	 * @brief Function called on click.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function onClicked($login) {
		if(is_callable($this->callback)) {
			call_user_func($this->callback, $login, $this->action);
		}
	}
	
}