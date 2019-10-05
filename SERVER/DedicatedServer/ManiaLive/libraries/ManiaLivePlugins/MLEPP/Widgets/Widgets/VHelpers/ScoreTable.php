<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\VHelpers;

define("DEBUG_NEEDUPDATE", 0);

use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeView;

/**
 * Description of TimeAttack
 *
 * @author De Cramer Oliver
 */
class ScoreTable extends TypeView {

	protected $bg_title;
	protected $label_title;
	protected $bg_first;
	protected $bg_last;
	protected $endRank;
	protected $startRank;
	protected $amLast = false;
	public $lineHeight = 3;
	public $data;
	public $label = "Live Rankings";
	private $mplayer = null;

	public function initializeComponents() {
		
	}

	public function setSettings($set) {
		$this->settings = $set;

		//Title initialisation
		$this->bg_title = new \ManiaLib\Gui\Elements\Bgs1($this->settings->width, 3);
		$this->bg_title->setSubStyle("NavButton");
		$this->bg_title->setPosX(0);
		$this->bg_title->setPosY(0);

		$this->label_title = new \ManiaLib\Gui\Elements\Label($this->settings->width - 3, 4);
		$this->label_title->setPosX(($this->settings->width) / 2);
		$this->label_title->setPosY(0.2);
		$this->label_title->setHalign("center");
		$this->label_title->setText('$s' . $this->label);
		$this->label_title->setTextColor("fff");
		$this->label_title->setTextSize(1);

		//
		//Last BackGround initialisation
		$this->bg_last = new \ManiaLib\Gui\Elements\BgsPlayerCard($this->settings->width, 4);
		$this->bg_last->setSubStyle("BgPlayerCardBig");
		$this->bg_last->setPosX(0);
		$this->bg_last->setPosY($this->lineHeight);
		$this->bg_last->setSizeY($this->lineHeight * ($this->settings->nbFirst + $this->settings->nbLast + 1.5));
		$this->bg_last->setSizeX($this->settings->width);

		/*//First Background initialisation
		$this->bg_first = new \ManiaLib\Gui\Elements\BgsPlayerCard($this->settings->width - 0.5, 3);
		$this->bg_first->setSubStyle("BgPlayerCardBig");
		$this->bg_first->setPosX(0.2);
		$this->bg_first->setPosY($this->lineHeight);
		$this->bg_first->setSizeY(4 * $this->settings->nbFirst);
		$bg_highlite->setSizeX($this->settings->width - 0.4);
		 * */
	}

	public function resetData() {
		$this->data = array();
		$this->mplayer = null;
	}

	public function setData($d) {
		$this->data = array();
		$this->data = $d;
	}

	public function onDraw() {
		//echo "-----\n";
		$this->clearComponents();

		//$this->addComponent($this->bg_title);
		$this->addComponent($this->label_title);
		//$this->addComponent($bg_highlite);
		$this->addComponent($this->bg_last);

		$storage = Storage::getInstance();

		if ($this->mplayer == null) {
			$this->mplayer = $this->findPlayer($this->getRecipient());
			if ($this->mplayer == null) {
				$mplayer = new \ManiaLive\DedicatedApi\Structures\Player ();
				$mplayer->rank = -1;
			}else
				$mplayer = $this->mplayer;
		}else {
			$mplayer = $this->mplayer;
		}

		$nbRank = \sizeof($this->data);
		if ($mplayer->rank == -1) {
			//echo"1 \n";
			$this->startRank = $nbRank - $this->settings->nbLast + 1;
		} else if ($mplayer->rank > $this->settings->nbFirst) {
			//echo"2 \n";
			$this->startRank = (int) ($mplayer->rank - ($this->settings->nbLast / 2)) + 1;
		} else {
			//echo"3 \n";
			$this->startRank = $this->settings->nbFirst + 1;
		}

		if ($this->startRank <= $this->settings->nbFirst) {
			$this->startRank = $this->settings->nbFirst + 1;
		}

		$this->endRank = $this->startRank + $this->settings->nbLast - 1;

		//echo $nbRank."_".$mplayer->rank."_".$this->endRank."_".$this->startRank." \n";
		//Showing first ranks
		$i = 1;
		$nbShown = 0;
		while ($i <= $this->settings->nbFirst && $i <= $nbRank) {
			if (isset($this->data[$i]) && (!\is_int($this->data[$i]->score) || $this->data[$i]->score != 0)) {				
				$this->addPlayer($nbShown, $this->data[$i]);
			}
			$i++;
			$nbShown++;
		}

		//Show the Rest
		$i = $this->startRank;
		while ($i <= $this->endRank && $i <= $nbRank) {
			if (isset($this->data[$i]) && $this->data[$i]->score != 0) {
				$this->addPlayer($nbShown, $this->data[$i]);
			}
			$i++;
			$nbShown++;
		}
	}

	private function findPlayer($login) {
		if (!\is_array($this->data))
			return null;

		foreach ($this->data as $key => $player) {
			if ($player->login == $login)
				return $player;
		}

		return null;
	}

	private function addPlayer($i, $player) {

		$posY = $this->lineHeight * ($i + 1.5) + 0.8;
		$marginX = 6;
		
		if ($player->login == $this->getRecipient()) {
			$this->playerSpecial($posY);
		}
		
		$label = new \ManiaLib\Gui\Elements\Label(5, $this->lineHeight);
		$label->setText('$s'.$player->rank . ".");
		$label->setPosX($marginX);
		$label->setPosY($posY);
		$label->setTextSize(1);
		$label->setHalign("right");
		$this->addComponent($label);

		$label = new \ManiaLib\Gui\Elements\Label(9, $this->lineHeight);

		$score = $this->formatScore($player->score);
		//if ($this->getRecipient() == $player->login) {
		// 	$score = '$000' . $score;
	//	} else {
			
		if ($i == 0)
			$score = '$ed0' . $score;
		elseif ($i == 1) {
		//elseif ($i < $this->settings->nbFirst)
			$score = '$ccc' . $score;
		} 
		elseif ($i == 2) 
			$score = '$ea0' . $score;
		else
			$score = '$222' . $score;
	//	}
		
		$label->setText($score);
		$label->setPosX($marginX);
		$label->setPosY($posY);
		$label->setTextSize(1);
		$this->addComponent($label);

		$label = new \ManiaLib\Gui\Elements\Label($this->settings->width - 12, $this->lineHeight);
		$label->setText($player->nickName);
		$label->setPosX($marginX + 10);
		$label->setPosY($posY);
		$label->setTextSize(1);

		$this->addComponent($label);

		
	}

	public function formatScore($score) {
		return \ManiaLive\Utilities\Time::fromTM($score);
	}

	public function playerSpecial($posY) {
		/*$arrow = new \ManiaLib\Gui\Elements\Quad(3, 3);
		$arrow->setStyle(\ManiaLib\Gui\Elements\Icons64x64_1::Icons64x64_1);
		$arrow->setSubStyle(\ManiaLib\Gui\Elements\Icons64x64_1::ArrowRed);
		$arrow->setPosX(-2);
		$arrow->setPosY($posY);
		$this->addComponent($arrow);

		$arrow = new \ManiaLib\Gui\Elements\Quad(3, 3);
		$arrow->setStyle(\ManiaLib\Gui\Elements\Icons64x64_1::Icons64x64_1);
		$arrow->setSubStyle(\ManiaLib\Gui\Elements\Icons64x64_1::LvlGreen);
		$arrow->setPosX($this->settings->width - 1);
		$arrow->setPosY($posY);
		$this->addComponent($arrow);
*/
		$bg_highlite = new \ManiaLib\Gui\Elements\BgsPlayerCard($this->settings->width, 4);
		$bg_highlite->setSubStyle("BgRacePlayerName");
		$bg_highlite->setPosX(0);
		$bg_highlite->setPosY($posY-0.3);
		$bg_highlite->setSizeY($this->lineHeight+0.5);
		$bg_highlite->setSizeX($this->settings->width);
		$this->addComponent($bg_highlite);
	}

	public function needUpdate($oldRank, $newRank, $player) {
		if (DEBUG_NEEDUPDATE > 0)
			echo "NeedUpdate : " . $oldRank . " -> " . $newRank . " = " . $player->login . "-" . $this->getRecipient() . "";

		if ($player->login == $this->getRecipient()) {
			if (DEBUG_NEEDUPDATE > 0)
				echo " :: true1\n";
			return true;
		}else if ($newRank <= $this->settings->nbFirst || ($newRank <= $this->endRank && $newRank >= $this->startRank)) {
			if (DEBUG_NEEDUPDATE > 0)
				echo " :: true2\n";
			return true;
		}else if (( $oldRank == -1 || $oldRank >= $this->endRank) && $newRank <= $this->endRank) {
			if (DEBUG_NEEDUPDATE > 0)
				echo " :: true3\n";
			return true;
		} elseif (( $oldRank == -1 || $oldRank <= $this->endRank) && $newRank >= $this->startRank) {
			if (DEBUG_NEEDUPDATE > 0)
				echo " :: true4\n";
			return true;
		}elseif ($newRank >= \sizeof($this->data)) {
			if (DEBUG_NEEDUPDATE > 0)
				echo " :: true5\n";
			return true;
		}
		if (DEBUG_NEEDUPDATE > 0)
			echo " :: false\n";
		return false;
	}

	public function getWidgetSizeX() {
		return $this->settings->width;
	}

	public function getWidgetSizeY() {
		return ($this->settings->nbFirst * $this->lineHeight) + ($this->settings->nbLast * $this->lineHeight) + $this->lineHeight + 0.3;
	}

	public function destroy() {
		$this->mplayer = null;
		unset($this->mplayer);
		parent::destroy();
	}

}

?>