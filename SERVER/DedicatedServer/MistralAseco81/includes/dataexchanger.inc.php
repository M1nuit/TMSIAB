<?php
/**
 * This class communicates with the aseco masterserver.
 * It realizes data exchanging between php and c#.
 * you can send requests formatted as arrays.
 * results will be outputted as arrays as well.
 * The data exchange is fully done by xml formatting.
 * 
 * @author Florian Schnell
 * @uses Examsly
 */
class DataExchanger {
  var $ip;
  var $port;
  var $stream;
  var $xmlparser;
  
  /**
   * Initializes the DataExchanger.
   *
   * @param string $ip
   * @param int $port
   * @return DataExchanger
   */
  
  // Official Master Server location is 193.47.83.151
  function DataExchanger ($ip = "127.0.0.1", $port = 10000) {
    $this->ip = $ip;
    $this->port = $port;
    $this->xmlparser = new Examsly();
  }
  
/**
 * Establishes the connection.
 * Sets up the network stream.
 *
 */
  function connect () {
    $this->stream = fsockopen($this->ip, $this->port);
  }
  
/**
 * Writes data into the network stream.
 *
 * @param string $data
 */
  function send ($data) {
    fwrite($this->stream, $data);
  }
  
  /**
   * Formats the request.
   * Sends it.
   * And receives the response.
   *
   * @param string $name
   * @param array $params
   * @return array containing the response.
   */
  function request ($name, $params = array()) {
    
    // format the request ...
    $request_r["Name"][] = $name;
    $request_r["Params"][] = $params;
    $request["Request"][] = $request_r;
    $xml = $this->xmlparser->parseArray($request);
    
    // attach the size of the request ...
    $request_size = strlen($xml);
    $xml = $request_size."#".$xml;
    
    // send the request ...
    $this->send($xml);
    
    // receive the response ...
    $response = $this->receive();
    
    // format the response ...
    $xml_response = $this->xmlparser->parseXml($response, false);
    $xml_response = $xml_response["RESPONSE"];
    
    // check for errors
    if ($xml_response["STATUS"][0] == "False") {
      
      // it's a dataserver error ...
      if ($xml_response["ERROR"][0]["DATASERVER"][0]) {
        $msg = $xml_response["ERROR"][0]["DATASERVER"][0]["MESSAGE"][0];
        $code = $xml_response["ERROR"][0]["DATASERVER"][0]["CODE"][0];
        trigger_error("[Masterserver Error $code] $msg", E_USER_WARNING);
      }
      
      // it's a mysql error ...
      if ($xml_response["ERROR"][0]["MYSQL"][0]) {
        $msg = $xml_response["ERROR"][0]["MYSQL"][0]["MESSAGE"][0];
        $code = $xml_response["ERROR"][0]["MYSQL"][0]["CODE"][0];
        trigger_error("[MySql Error $code] $msg", E_USER_WARNING);
      }
      
      return false;
    }
    
    return $xml_response;
  }
  
  /**
   * Reads packages of data out of the network stream.
   * Only packages marked with a \n\n at the end are read.
   *
   * @return string data
   */
  function receive () {
    $read = fgets($this->stream, 1024);
    while($read != "\n") {
      $data .= str_replace(chr(0), "", $read);
      $read = fgets($this->stream, 1024);
    }
    return $data;
  }
  
  /**
   * Ends the network connection.
   * Closes the network stream.
   *
   */
  function disconnect () {
    fclose($this->stream);
  }
}
?>