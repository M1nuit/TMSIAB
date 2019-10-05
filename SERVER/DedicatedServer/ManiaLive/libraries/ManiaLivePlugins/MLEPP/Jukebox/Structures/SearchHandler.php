<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Structures;

abstract class SearchHandler {

    protected $jbPlugin;

    protected $columns;


    abstract public function __construct($jbPlugin);

    abstract public function tracklist($login, $param1, $param2);

    public function setColumns($name, $columns){
        $this->columns[$name] = $columns;
    }

    public function getColumns($name){
        if(isset($this->columns[$name]))
            return $this->columns[$name];

        return false;
    }

    abstract function getChallangeFromNum($id, $login);

    function playerDisconnected($login){
		
	}


}
?>
