<?php
/**
 * Database Updater for Mistral Mod (Aseco 0.x)
 * Tries to find the tmxID and kind of track (tmu, tmn, tmo, tms) from the TMX sites and inserts the data
 * If nothing is found - you get a conclusion at the end which tracks to look up manually
 *
 * You only have to run this ONCE! With "/admin add" the tmxid and tracktype is added automatically.
 * Is you add tracks with remoteCP etc. you have to rerun this script!
 *
 * Written by: ck|cyrus aka Martin Vierling
 * E-Mail: martin@die-webber.com
 * Homepage: www.die-webber.com
 *
 * Version 07.06.2007
 *
 * Added Sharemania support by Mistral
 * Added Forever support by Mistral
 */

///////////////////////////////////////
// INSERT YOUR DATABASE CONNECTION DATA

	$dbHost = 'DBSERVER';		//Database host
	$dbUser = 'DBUSER';						//Database username
	$dbName = 'DBSCHEMA';				//Database name
	$dbPass = 'DBPASS';					//Database password




///////////////////////////////////////////////////////////////////////////
// DON'T TOUCH THE CODE BELOW HERE (or you really know what you are doing!)

$tmxlinks['TMU']='http://united.tm-exchange.com/main.aspx?action=trackshow&uid=';
$tmxlinks['TMF']='http://tmnforever.tm-exchange.com/main.aspx?action=trackshow&uid=';
$tmxlinks['TMO']='http://original.tm-exchange.com/main.aspx?action=trackshow&uid=';
$tmxlinks['TMS']='http://sunrise.tm-exchange.com/main.aspx?action=trackshow&uid=';
$tmxlinks['TMN']='http://nations.tm-exchange.com/main.aspx?action=trackshow&uid=';

function GetSmIdfromUid($uid)
	{
	$url = "http://sharemania.eu/api.php?id=".urlencode($uid)."&i";
	$response = simplexml_load_file($url);
	if(!empty($response->error))
		return 0;
	else
		return $response->header->i;
	}
	
$sitedb = @mysql_connect($dbHost, $dbUser, $dbPass) or die("Couldn't connect to the MySQL server! Check your database settings."); 
$selectDB = @mysql_select_db($dbName, $sitedb) or die("Couldn't select MySQL database! Check your database settings.");

$db = new DBQuery(); //DB Object erstellen
$db2 = new DBQuery(); //DB Object erstellen
$db->query("SELECT * FROM challenges");

$notfound = array();

WHILE($track = $db->fetch()) {
	
	//	Skip tracks where tmxid and tmxtype exist
	if(!empty($track['TMXType']) && is_numeric($track['TMXId']) && $track['TMXId'] > 0)
		continue;
	
	printf("<b>Looking up track: <i>%s</i></b> [%s]<br>", stripFormatting($track['Name']), $track['Uid']); 

	// Search on Sharemania
	$tmxid = GetSmIdfromUid($track['Uid']);
	if ($tmxid != 0)
		{
		$type = "SM";
		}
	// Search on TMX
	else
		{
		if( stripos(($content = getSiteContent($tmxlinks['TMF'].$track['Uid'])), "track was not found") == false)
			$type = "TMF";
		if( !isset($type) && stripos(($content = getSiteContent($tmxlinks['TMU'].$track['Uid'])), "track was not found") == false)
			$type = "TMU";
		if( !isset($type) && stripos(($content = getSiteContent($tmxlinks['TMN'].$track['Uid'])), "track was not found") == false)
			$type = "TMN";
		if( !isset($type) && stripos(($content = getSiteContent($tmxlinks['TMO'].$track['Uid'])), "track was not found") == false)
			$type = "TMO";
		if( !isset($type) && stripos(($content = getSiteContent($tmxlinks['TMS'].$track['Uid'])), "track was not found") == false)
			$type = "TMS";
		$temp = explode("action=trackshow&id=", $content);
		$temp = explode("#auto", $temp[1]);
		$tmxid = $temp[0];
		
		if (!isset($type))
			{
			$type="NF";
			$tmxid="1";
			}
		}
		
	if(isset($type)) {

		printf("TMX ID: %s - TMX Type: %s<br><br>", $tmxid, $type);
		
		//	Add Data in Database
		$db2->query("UPDATE challenges SET TMXType = '".$type."', TMXId = '".$tmxid."' WHERE Id = '".$track['Id']."'");

	}else{
		//	Add to nothingfound array
		$notfound[] = array("Name" => $track['Name'], "Author" => $track['Author'], "Environment" => $track['Environment'], "Uid" => $track['Uid']);
		
		//	Output msg
		echo("<b><i><<< Nothing found - added to marker list >>></i></b><br><br>");
	}
	
	unset($type, $temp, $tmxid);
	flush();
	set_time_limit(60);
}
$db->free();
$db2->free();

//	Show notfound Data
if(count($notfound) > 0) {
	printf("<b><u>%i tracks not found! You have to look them up manually!</u></b><br><br>", count($notfound));
	foreach($notfound AS $nf) printf("Name: %s<br>Author: %s<br>Env: %s<br>Uid: %s<br><br><br>", stripFormatting($nf['Name']), $nf['Author'], $nf['Environment'], $nf['Uid']);
}

///////////////////////////////////////////////////
//	FUNCTIONS

function getSiteContent($link) {
	
	$handle = fopen($link, "rb");
	$contents = '';
	while (!feof($handle))
	{
		$contents .= fread($handle, 4096);
	}
	
	fclose($handle);
	
	return($contents);
}
// thanks to Bilge for coming up with something short, simple and fast
function stripFormatting($input)
	{
	// now at something like rev 12 of stripColors, now called stripFormatting
	return str_replace("\0", '$$', preg_replace('/\\$([0-9a-f]..|[hl]\\[.*?\\]|.|$)/i', '', str_replace('$$', "\0", $input)));
	}

class DBQuery
{		
  var $m_sql = "";
  var $m_result = 0;
  var $m_errno = 0;
  var $m_error = "";
  
  function query($sql)
  {
      // Query in der Klasse speichern
      global $sitedb;
      $this->m_sql = trim($sql);
      $this->m_result = mysql_query($this->m_sql, $sitedb);
      if(!$this->m_result)
      {
          $this->m_errno = mysql_errno();
          $this->m_error = mysql_error();
          return $this->m_result;
      }
      return $this->m_result;
  }

  function error()
  {
      // Result-ID in einer tmp-Variablen speichern
      $tmp = $this->m_result;

      // Variable in boolean umwandeln
      $tmp = (bool)$tmp;

      // Variable invertieren
      $tmp = !$tmp;

      // und zurckgeben
      return $tmp;
  }

  function getError()
  {
      if($this->error()) {
      $str  = "<pre>Anfrage:\n".$this->m_sql."\n";
      $str .= "Antwort:\n".$this->m_error."\n";
      $str .= "Fehlercode: ".$this->m_errno."<//pre>";
      } else {
      $str = "Kein Fehler aufgetreten.";
      }
      return $str;
  }

  function fetch()
  {
      if($this->error()) {
      echo "Es trat ein Fehler auf. Bitte berprfen sie ihr\n";
      echo "MySQL-Query.\n";
      $return = null;
      } else {
          $return = @mysql_fetch_assoc($this->m_result);
      }
      return $return;
  }

  function numRows()
  {
      if($this->error()) {
      $return = -1;
      } else {
       $return = mysql_num_rows($this->m_result);
      }
      return $return;
  }

  function free()
  {
      // Speicher freimachen
      @mysql_free_result($this->m_result);
  }
}
?>