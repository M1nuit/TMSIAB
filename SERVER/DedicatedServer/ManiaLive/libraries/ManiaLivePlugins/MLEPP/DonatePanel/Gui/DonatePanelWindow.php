<?php
// this line defines the namespace, this needs to match the relative path to
// where your file is located, in general you only need to change the part
// marked in yellow.
namespace ManiaLivePlugins\MLEPP\DonatePanel\Gui;

// you need to rename every component, that you are using in the code.
// for more elements to use, you can check the
// “Important Namespaces and its Classes”-section.

use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\BgRaceScore2;
use ManiaLib\Gui\Elements\Label;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLib\Gui\Layouts\Line;
use ManiaLib\Gui\Elements\Bgs1;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLib\Gui\Elements\Format;
use ManiaLivePlugins\MLEPP\DonatePanel\DonatePanel;

class DonatePanelWindow extends \ManiaLive\Gui\Windowing\Window
{
	protected $panel;
	protected $restart;
	private $container;

	protected function initializeComponents()
	{
		// set a default size for the window.
		$this->setSize(100, 4);

		// creating the panel which serves, as described,
		// as the window’s background

		$this->container = new Frame();
		$layout = new Line();

		//$layout->setDirection(Column::DIRECTION_LEFT);
		$this->container->clearComponents();
		$this->container->setPosition(30,86);
		$this->container->applyLayout($layout);

		$this->addComponent($this->container);

		/*$this->background = new Bgs1();
		$this->background->setSize($this->getSizeX(), $this->getSizeY());
		$this->background->setSubStyle("ProgressBar");
		$this->container->addComponent($this->background);*/


		$ui = new Label(12, 3);
		$ui->setHalign('right');
		$ui->setValign('top');
		//$ui->setScale();
		$ui->setText('$fff$sDonate:');
		$this->container->addComponent($ui);

		$donations = array("100","200","500","1000","2000");
		foreach ($donations as $text) {
			$ui = new Label(16, 3);
			$ui->setScale(0.7, 0.7);
			$ui->setHalign('right');
			$ui->setValign('top');
			$ui->setText('$fff$s'.$text);

			$ui->setStyle(Format::TextCardScores2);
			$ui->setAction($this->callback('donate'.$text));
			$this->container->addComponent($ui);
			}
	}

	function donate100($login) {
		$this->Donate($login,100);
	}
	function donate200($login) {
		$this->Donate($login,200);
	}
	function donate500($login) {
		$this->Donate($login,500);
	}
	function donate1000($login) {
		$this->Donate($login,1000);
	}
	function donate2000($login) {
		$this->Donate($login,2000);
	}

	function Donate($login, $amount) {

		$storage = Storage::getInstance();
		$connection = Connection::getInstance();
		$toPlayer = new \ManiaLive\DedicatedApi\Structures\Player();
		$config = \ManiaLivePlugins\MLEPP\DonatePanel\Config::getInstance();
		if (empty($config->toLogin)) {
        $toPlayer->login = $storage->serverLogin;
		}
		else {
        $toPlayer->login = $config->toLogin;
		}
		$fromPlayer = $storage->getPlayerObject($login);
		$billId = $connection->sendBill($fromPlayer, $amount, 'Planets Donation', $toPlayer);
		DonatePanel::$billId[$billId] = array($billId,$login,$amount);
	}

	protected function onHide() {}

	// this is executed every time the window is drawn onto the screen.
	// we will position and draw all elements here.
	protected function onShow()
	{
		// stretch panel background to fill window size ...
		$posx = 30;
		$posy = 50;
		$this->container->setSize($this->getSizeX(), $this->getSizeX());
		//$this->background->setPosition($posx,$posy);

		// position and resize text ...
	}

	function destroy()
	{
		parent::destroy();
	}
}
?>