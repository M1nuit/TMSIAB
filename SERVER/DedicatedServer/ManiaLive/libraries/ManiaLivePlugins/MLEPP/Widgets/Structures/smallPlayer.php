<?php

namespace ManiaLivePlugins\MLEPP\Widgets\Structures;

/**
 * Description of smallPlayer
 *
 * @author De Cramer Oliver
 */
class smallPlayer {

    public $login;
    public $nickName;

    public $rank;
    public $bestTime;
    public $score;

    function __construct(\ManiaLive\DedicatedApi\Structures\Player $player = null) {

        if($player != null){
            $this->login    = $player->login;
            $this->nickName = $player->nickName;
            $this->rank     = $player->rank;
            $this->bestTime = $player->bestTime;
            $this->score    = $player->score;
        }
    }

	function __set($nomAttribut, $val){
		$this->$nomAttribut = $val;
	}

	function __get($nomAttribut){
		if(isset($this->$nomAttribut))
			return $this->$nomAttribut;
		else
			return null;
	}
    

}
?>
