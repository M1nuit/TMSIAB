<?php
/**
 * @author      Florian Schnell
 * 
 * Builds an easy structured array out of
 * a xml file.
 * Element names will be the keys and the data the values.
 */

class Examsly {
  var $data;
  var $struct;
  var $parser;
  var $stack;
  
  /**
   * Parses a XML structure into an array.
   */
  
  function parseXml($source, $isfile = true) {
    
    // clear last results ...
    $this->stack = array();
    $this->struct = array();
    
    // create the parser ...
    $this->parser = xml_parser_create();
    xml_set_object($this->parser, $this);
    xml_set_element_handler($this->parser, "openTag", "closeTag");
    xml_set_character_data_handler($this->parser, "tagData");
    
    // load the xml file ...
    if ($isfile) {
      $this->data = file_get_contents($source);
    } else {
      $this->data = $source;
    }
    
    // parse xml file ...
    $parsed = xml_parse($this->parser, $this->data);
    
    // display errors ...
    if (!$parsed) {
      $code = xml_get_error_code($this->parser);
      $err = xml_error_string($code);
      $line = xml_get_current_line_number($this->parser);
      trigger_error("[XML Error $code] $err on line $line", E_USER_WARNING);
      return false;
    }
    return $this->struct;
  }
  
  function openTag($parser, $name, $attributes) {
    $this->stack[] = $name;
    $this->struct[$name] = "";
  }
  
  function tagData($parser, $data) {
    if (trim($data)) {
      $index = $this->stack[count($this->stack)-1];
      $this->struct[$index] .= utf8_decode(urldecode($data));
    }
  }
  
  function closeTag($parser, $name) {
    if (count($this->stack) > 1) {
      $from = array_pop($this->stack);
      $to = $this->stack[count($this->stack)-1];
      $this->struct[$to][$from][] = $this->struct[$from];
      unset($this->struct[$from]);
    }
  }
  
  /**
   * Parses an array into an XML structure.
   */
  
  function parseArray($array) {
    $xmlstring = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $xmlstring .= $this->parseArrayElements($array);
    return $xmlstring;
  }
  
  function parseArrayElements($array, $opt_tag = ""){
    
    // read each element of the array ...
    for($i=0; $i<sizeof($array); $i++){
      
      // check if array is associative ...
      if(is_numeric(key($array))){
        $xml .= '<'.$opt_tag.'>';
        if(is_array(current($array))){
          $xml .= $this->parseArrayElements(current($array), key($array));
        }else{
          $xml .= urlencode(utf8_encode(current($array)));
        }
        $xml .= '</'.$opt_tag.'>';
      }else{
        if(is_array(current($array))){
          $xml .= $this->parseArrayElements(current($array), key($array));
        }else{
          $xml .= '<'.key($array).'>';
          $xml .= urlencode(utf8_encode(current($array)));
          $xml .= '</'.key($array).'>';
        }
      }
      next($array);
    }
    return $xml;
  }
}
?>