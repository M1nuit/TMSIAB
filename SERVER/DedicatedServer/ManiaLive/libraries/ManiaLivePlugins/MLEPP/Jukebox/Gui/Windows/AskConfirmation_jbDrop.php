<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows;

use ManiaLive\Event\Dispatcher;

use ManiaLive\Gui\Handler\IDGenerator;
use ManiaLive\Gui\Windowing\Windows\Dialog;
use ManiaLive\Gui\Windowing\WindowHandler;

/**
 * Description of jbList_remove
 *
 * @author De Cramer Oliver
 */
class AskConfirmation_jbDrop implements \ManiaLive\Event\Listener{

	private $plugin_jb;
	private $id;
	private $login;
	private $dialog;

	function __construct($jb, $login, $id) {

		if ($this->isPluginLoaded('oliverde8\HudMenu')) {
			Dispatcher::register(\ManiaLive\Gui\Windowing\Event::getClass(), $this);
		}

		$this->dialog = Dialog::Create($login);
		$this->dialog->setSize(80, 50);
		$this->dialog->setTitle('Attention');
		$this->dialog->setText("Are you sure you want to remove this track from the Jukebox?");
		$this->dialog->centerOnScreen();
		$this->dialog->setButtons(48);
		$this->dialog->setCloseCallback(call_user_func_array(array($this, 'OnClick'), array($login)));
		WindowHandler::showDialog($this->dialog);

		$this->plugin_jb = $jb;
		$this->login = $login;
		$this->id = $id;
	}

	public function OnClick($login){
		if($this->dialog->getAnswer() == Dialog::YES){
			$this->plugin_jb->dropFromJukebox($login, $this->id, true);
		}
	}

	public function onWindowClose($login, $win){

		if($login == $this->login && $win->getId() == $this->dialog->getId())
				$this->OnClick ($login);

		Dispatcher::unregister(\ManiaLive\Gui\Windowing\Event::getClass(), $this);

	}

	public function onWindowRecover($login, $win){

	}

	public function isPluginLoaded(){
		return true;
	}

	public function destroy() {
		unset($this->plugin_jb);
		parent::destroy();
		gc_collect_cycles();
	}

}
?>
