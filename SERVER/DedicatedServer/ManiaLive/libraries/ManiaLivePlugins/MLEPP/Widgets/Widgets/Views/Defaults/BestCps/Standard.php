<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Widgets\Views\Defaults\BestCps;

use ManiaLivePlugins\MLEPP\Widgets\Structures\TypeView;
use ManiaLive\Utilities\Time;

/**
 * Description of Standard
 *
 * @author Petri Järvisalo
 */
class Standard extends TypeView {

	protected $frame;
	protected $frame2;
	public $data;

	public function initializeComponents() {
		
	}

	public function setSettings($set) {
		$this->settings = $set;

		//Title initialisation
		$this->frame = new \ManiaLive\Gui\Windowing\Controls\Frame(280, 10);
		$this->frame->setPosX(0);
		$this->frame->setPosY(0);
		$this->frame2 = new \ManiaLive\Gui\Windowing\Controls\Frame(280, 10);
		$this->frame2->setPosX(0);
		$this->frame2->setPosY(4.5);
	}

	public function onDraw() {
		$this->clearComponents();
		$this->frame->clearComponents();
		$this->frame->applyLayout(new \ManiaLib\Gui\Layouts\Flow());
		$this->frame->setSizeX(280);

		$this->frame2->clearComponents();
		$this->frame2->applyLayout(new \ManiaLib\Gui\Layouts\Flow());
		$this->frame2->setSizeX(280);

		$this->generateFrame();

		$this->addComponent($this->frame);
		$this->addComponent($this->frame2);
	}

	public function generateFrame() {
		$login = $this->getRecipient();

		$displayCp = 6;
		if (isset($this->data->index[$login])) {
			$index = $this->data->index[$login];
		} else {
			$index = 0;
		}

		$pages = floor($this->data->totalCp / $displayCp);
		$multiplier = floor($index / $displayCp);
		$position = ($index % $displayCp);
		$curPage = ($multiplier * $displayCp);
		$storage = \ManiaLive\Data\Storage::getInstance();
		$player = $storage->getPlayerObject($login);
		$gamemode = $storage->gameInfos->gameMode;
		
		if (count($this->data->global) > 0) {
			for ($id = $curPage; $id < $curPage + $displayCp; $id++) {
				if (!isset($this->data->global[$id]))
					break;

				$data = $this->data->global[$id];
				if ($data['nickname'] == null)
					break;
				$frame = new \ManiaLive\Gui\Windowing\Controls\Frame(40, 4);
				$frame->setSizeX(41.5);
				$frame->setSizeY(4.5);
				// generate normal gamemode graphics
				$bg = new \ManiaLib\Gui\Elements\BgsPlayerCard(40, 4);
				if ( $id == $index) {
					$bg->setSubStyle("BgRacePlayerName");
				} else {
					$bg->setSubStyle("BgPlayerCardBig");
				}
				// additions to team mode
				if ($gamemode == 3) {
						$bg = new \ManiaLib\Gui\Elements\BgRaceScore2(40, 4);
						if ($player->teamId == 0) {
							$bg->setSubStyle("HandleBlue");
						} else {
							$bg->setSubStyle("HandleRed");
						}
					}
				$bg->setHalign('center');
				$bg->setValign('center');
				$title = new \ManiaLib\Gui\Elements\Label(40, 4);
				$time = new Time();
				$title->setText('$s$eee' . ($id + 1) . '. $ff5' . $time->FromTM($data['score']) . ' $z$s$eee' . $data['nickname']);
				$title->setTextSize(1);
				$title->setSizeX(39);
				$title->setSizeY(4);
				$title->setHalign('center');
				$title->setValign('center');
				$frame->addComponent($bg);
				$frame->addComponent($title);
				$this->frame->addComponent($frame);
			}

			for ($id = $curPage; $id < $curPage + $displayCp; $id++) {
				if (!isset($this->data->global[$id]))
					break;
				$globalTime = $this->data->global[$id]['score'];
				if (!isset($this->data->player[$login][$id]))
					break;

				$playerTime = $this->data->player[$login][$id];
				$timeDiff = abs($globalTime - $playerTime);
				if ( ($globalTime - $playerTime) < 0) {
					$ttime = '$e00+'. $time->FromTM($timeDiff);
				}
				else {
					$ttime = '$00e-'. $time->FromTM($timeDiff);
				}
				$frame = new \ManiaLive\Gui\Windowing\Controls\Frame(40, 4);
				$frame->setSizeX(41.5);
				$frame->setSizeY(4.5);
				$bg = new \ManiaLib\Gui\Elements\BgsPlayerCard(40, 4);
				if ( $id == $index) {
					$bg->setSubStyle("BgRacePlayerName");
				} else {
					$bg->setSubStyle("BgPlayerCardBig");
				}
				$bg->setHalign('center');
				$bg->setValign('center');
				$title = new \ManiaLib\Gui\Elements\Label(40, 4);
				$time = new Time();
				$title->setText($ttime);
				$title->setTextSize(1);
				$title->setSizeX(39);
				$title->setSizeY(4);
				$title->setHalign('center');
				$title->setValign('center');
				$frame->addComponent($bg);
				$frame->addComponent($title);
				$this->frame2->addComponent($frame);
			}
		}
	}

	public function setData($d) {
		$this->data = $d;
	}

	public function getWidgetSizeX() {
		return 240;
	}

	public function getWidgetSizeY() {
		return (5);
	}

	public function destroy() {
		parent::destroy();
	}

}

?>
