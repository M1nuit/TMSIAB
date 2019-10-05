<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\WhoIam;

use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeView;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLivePlugins\MLEPP\Core\Core;

/**
 * Description of TimeAttack
 *
 * @author Petri Järvisalo
 */
class Standard extends TypeView {

	protected $player_flag;
	protected $server_flag;
	protected $player_name;
	protected $server_name;
	public $data;

	public function initializeComponents() {

	}

	public function setSettings($set) {
		$this->settings = $set;

		$this->player_flag = new \ManiaLib\Gui\Elements\Quad(3, 3);
		$this->player_flag->setPosX(0);
		$this->player_flag->setPosY(0);
		$this->player_flag->SetValign("bottom");

		$this->server_flag = new \ManiaLib\Gui\Elements\Quad(3, 3);
		$this->server_flag->setPosX(0);
		$this->server_flag->setPosY(3);
		$this->server_flag->SetValign("bottom");

		//Title initialisation
		$this->player_name = new \ManiaLib\Gui\Elements\Label($this->settings->width, 2);
		$this->player_name->setPosX(4);
		$this->player_name->setPosY(0);
		$this->player_name->setHalign("left");
		$this->player_name->setValign("bottom");
		$this->player_name->setTextColor("fff");
		$this->player_name->setTextSize(2);

		$this->server_name = new \ManiaLib\Gui\Elements\Label($this->settings->width, 2);
		$this->server_name->setPosX(4);
		$this->server_name->setPosY(3);
		$this->server_name->setHalign("left");
		$this->server_name->setValign("bottom");
		$this->server_name->setTextColor("fff");
		$this->server_name->setTextSize(2);
	}

	public function onDraw() {
		$this->clearComponents();
		$mlepp = Mlepp::getInstance();
		$country = explode("|", $this->data['player']->path);
		if (isset($country[1])) {
			$playerCountry = $mlepp->mapCountry($country[1]);
			$this->player_flag->setImage('http://koti.mbnet.fi/reaby/manialive/images/Flags/' . $playerCountry . ".dds", true);
			$this->player_name->setText('$s' . $this->data['player']->nickName);
		}

		$country = explode("|", $this->data['server']->path);
		if (isset($country[1])) {
			$servercountry = $mlepp->mapCountry($country[1]);
			$this->server_flag->setImage('http://koti.mbnet.fi/reaby/manialive/images/Flags/' . $servercountry . ".dds", true);
		}

		$minRank = intval($this->data['serverinfo']->ladderServerLimitMin) / 1000;
		$maxRank = intval($this->data['serverinfo']->ladderServerLimitMax) / 1000;
		$ladderRank = $minRank . "-" . $maxRank . "k";
		$this->server_name->setText('$z$s' . $this->data['serverinfo']->name . ' $z$s(' . $ladderRank . ')');
		$this->addComponent($this->player_flag);
		$this->addComponent($this->player_name);
		$this->addComponent($this->server_flag);
		$this->addComponent($this->server_name);
	}

	public function setData($d) {
		$this->data = $d;
	}

	public function getWidgetSizeX() {
		return $this->settings->width;
	}

	public function getWidgetSizeY() {
		return (20);
	}

		public function destroy()
	{
		parent::destroy();
	}

}

?>
