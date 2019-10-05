<?php

namespace ManiaLivePlugins\MLEPP\LocalRecords\Events;

class onRecordUpdate extends \ManiaLive\Event\Event {

    const newRecord = 0;
    const firtRecord = 1;
    const bestScore = 2;

    protected $record;
    protected $oldRank;
    protected $event;

    function __construct($record, $oldRank, $event) {
            $this->record = $record;
            $this->oldRank = $oldRank;
            $this->event = $event;
    }

    function fireDo($listener){
        switch ($this->event) {
			case self::firtRecord:
                call_user_func_array(array($listener, 'onMLEPP_LocalRecords_firstRecord'), array($this->record->login, $this->record, $this->oldRank));
				
            case self::newRecord:
                call_user_func_array(array($listener, 'onMLEPP_LocalRecords_newRecord'), array($this->record->login, $this->record, $this->oldRank));
                call_user_func_array(array($listener, 'onMLEPP_LocalRecords_bestScore'), array($this->record->login, $this->record));
                break;

            case self::bestScore:
                call_user_func_array(array($listener, 'onMLEPP_LocalRecords_bestScore'), array($this->record->login, $this->record));
                break;                
        }
    }
}


?>