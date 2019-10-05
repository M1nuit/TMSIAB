<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Structures;

class Column {

    public $width;
    public $eWidth;
    public $text;
    public $name;
    public $type;
    
    public function __construct($name, $width, $text, $type=null){
        $this->width = $width;
        $this->eWidth = $width;
        $this->text = $text;
        $this->name = $name;

        if($type==null)
            $type=$name;

        $this->type = $type;
    }
}
?>
