<?php

namespace ManiaLivePlugins\MLEPP\LocalRecords\Events;

class onChallengeChange extends \ManiaLive\Event\Event {


    protected $records;
    protected $logedPlayers;

    function __construct($records, $logedPlayers) {
            $this->records = $records;
            $this->logedPlayers = $logedPlayers;
    }

    function fireDo($listener){
        call_user_func_array(array($listener, 'onMLEPP_LocalRecords_newChallange'), array($this->records, $this->logedPlayers));
    }
}


?>