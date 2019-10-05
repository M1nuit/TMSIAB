<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Structures;

class Columns implements \Iterator{

    private $position;

    private $columns;

    private $size;

    public function __construct() {
        $this->size = 0;
        $this->position = 0;
    }

    public function generateFromSetting($list){
        foreach($list as $name => $text){
            $var = explode(";", $text);
            $this->addColumn($name, (double)$var[0], $var[1]);
        }
    }

    public function addColumn($name, $width, $text, $type=null){
        $this->columns[$this->size] = new Column($name, $width, $text, $type);
        $this->size ++;

        $total = 0;
        foreach($this->columns as $column){
            $total += $column->eWidth;
        }

        $coef = (1 / $total);

        foreach($this->columns as $column){
            $column->width = $column->eWidth * $coef;
        }
    }

    function rewind() {
        $this->position = 0;
    }

    function current() {
        return $this->columns[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return isset($this->columns[$this->position]);
    }


}
?>
