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

use ManiaLivePlugins\MLEPP\ForceMods\ForceMods;
use ManiaLivePlugins\MLEPP\ForceMods\Gui\Elements\Button;

use ManiaLib\Gui\Elements\Label;


class SettingsWindow extends \ManiaLive\Gui\Windowing\ManagedWindow {

	protected $callback = NULL;

	protected $disableButton;
	protected $incrementButton;
	protected $randomButton;
	protected $overrideOnButton;
	protected $overrideOffButton;


	/**
	 * @fn initializeComponents()
	 * @brief Function called on initialisation.
	 *
	 * @return void
	 */
	function initializeComponents() {
		$this->setTitle('ForceMods Settings');
		$this->setSize(72, 72);

		// mode label
		$label = new Label(40, 20);
		$label->setPosition(18, 20);
		$label->setAlign('center', 'center');
		$label->setText('$880$o Mode ');
		$this->addComponent($label);

		// mode 0 button
		$this->disableButton = new Button(10, 8, 'disable', array($this, 'onClick'), 'off', true);
		$this->disableButton->setPosition(7.5, 23);
		$this->addComponent($this->disableButton);

		// mode 1 button
		$this->incrementButton = new Button(10, 8, 'sequential', array($this, 'onClick'), 'inc', true);
		$this->incrementButton->setPosition(18.5, 23);
		$this->addComponent($this->incrementButton);

		// mode 2 button
		$this->randomButton = new Button(10, 8, 'random', array($this, 'onClick'), 'rand', true);
		$this->randomButton->setPosition(29.5, 23);
		$this->addComponent($this->randomButton);

		// override label
		$label = new Label(40, 20);
		$label->setPosition(18, 35);
		$label->setAlign('center', 'center');
		$label->setText('$880$o Override');
		$this->addComponent($label);

		// override on button
		$this->overrideOnButton = new Button(10, 8, 'enable', array($this, 'onClick'), 'overrOn', true);
		$this->overrideOnButton->setPosition(7.5, 38);
		$this->addComponent($this->overrideOnButton);

		// override off button
		$this->overrideOffButton = new Button(10, 8, 'disable', array($this, 'onClick'), 'overrOff', true);
		$this->overrideOffButton->setPosition(18.5, 38);
		$this->addComponent($this->overrideOffButton);

		// environments label
		$label = new Label(40, 20);
		$label->setPosition(18, 48);
		$label->setAlign('center', 'center');
		$label->setText('$880$o Environments');
		$this->addComponent($label);

		// adjust size
		$envis = ForceMods::getEnvironments();
		$this->setSizeY($this->getSizeY() + (ceil(count($envis) / 3) * 5));

		// environment buttons
		$i = 0;
		foreach($envis as &$name) {
			$button = new Button(10, 4, ' '.$name.' ', array($this, 'onClick'), $name, true);
			$button->setPosition(7.5, 53);
			$this->addComponent($button);
			$i++;
		}
	}


	/**
	 * @fn onDraw()
	 * @brief Function called on draw.
	 *
	 * @return void
	 */
	function onDraw() {
		$this->disableButton->setActive(ForceMods::$mode != 0);
		$this->incrementButton->setActive(ForceMods::$mode != 1);
		$this->randomButton->setActive(ForceMods::$mode != 2);

		$this->overrideOnButton->setActive(ForceMods::$override === false);
		$this->overrideOffButton->setActive(ForceMods::$override === true);
	}


	/**
	 * @fn setCallback()
	 * @brief Function to set callback handle.
	 *
	 * @param mixed $callback
	 * @return void
	 */
	function setCallback($callback) {
		$this->callback = $callback;
	}


	/**
	 * @fn onClick()
	 * @brief Function called on click.
	 *
	 * @param mixed $login
	 * @param mixed $action
	 * @return void
	 */
	function onClick($login, $action) {
		if(is_callable($this->callback)) {
			call_user_func($this->callback, $login, $action);
		}
	}
	
	function destroy()
	{
		parent::destroy();
	}

}

?>