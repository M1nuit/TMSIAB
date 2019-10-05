<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8">
		<meta http-equiv="refresh" content="30">
		<title>Mistral Live Chat</title>
		<link rel="stylesheet" type="text/css" href="mychat.css">
	</head>

	<body background="Background.jpg">
<?php
require_once('class.parse.php');
//------------------------------- EDIT ---------------------------------
$DBSERVER =	"127.0.0.1";
$DBLOGIN =	"root";
$DBPASS =	"usbw";
$ASECODB =	"maseco";
//------------------------------- EDIT ---------------------------------

$parse = new parse();

$count=50;
$output=array();

$connection = mysql_connect($DBSERVER, $DBLOGIN, $DBPASS) or die("Cannot connect to database");
mysql_select_db($ASECODB) or die("Cannot select Schema");

$query = "select message from mistral_chat order by mid desc limit ".$count;
$result = mysql_query($query);

$i=$count-1;
while ($row = mysql_fetch_object($result)) {
	$output[$i]=utf8_decode($row->message);
	$i--;
	}
mysql_free_result($result);
mysql_close($connection);

for ($i=0; $i<$count; $i++) {
	$line = $output[$i];
	if (strstr($line, "****") & strstr($line, "PLAYING."))
		{
		$line = str_replace("****", "", $line);
		$css = "track";
		}
	elseif (strstr($line, "++++") & strstr($line, "JOINED."))
		{
		$line = str_replace("++++", "", $line);
		$css = "join";
		}
	elseif (strstr($line, "----") & strstr($line, "LEFT."))
		{
		$line = str_replace("----", "", $line);
		$css = "leave";
		}
	elseif (strstr($line, "[Admin -"))
		$css = "console";
	else
		$css = "chat";
	if ($i%2)
		$id = "bg2";
	else
		$id = "bg1";
	echo "<div id=\"" . $id . "\" class=\"" . $css . "\">" . $parse->tmtohtml($line) . "</div>\n";
	}
?>
	</body>
</html>
