<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8">
		<title>Mistral World Selection Track Evaluation</title>
		<link rel="stylesheet" type="text/css" href="mychat.css">
	</head>

	<body background="Background.jpg">
<?php
require_once('class.parse.php');

//------------------------------- EDIT ---------------------------------
$DBSERVER =	"localhost";
$DBLOGIN =	"root";
$DBPASS =	"usbw";
$ASECODB =	"maseco";
$LIMIT = 10;	// minimum votes to evaluate track
//------------------------------- EDIT ---------------------------------

function getEvalCount($id, $val)
	{
	$ret = 0;
	
	$query = "SELECT COUNT(Eval) FROM mistral_trackeval WHERE Eval=$val AND ChallengeId=$id;";
	$result = mysql_query($query);
	if ($result)
		{
		$row = mysql_fetch_row($result);
		$ret = $row[0];
		mysql_free_result($result);
		}
	
	return $ret;
	}

function getTracknameFromId($id) {
  	$query = "SELECT name FROM challenges WHERE id=$id";
  	$result = mysql_query($query);
  	if (!$result)
  		return "unknown";
  	if (mysql_num_rows($result) > 0) {
	    	$row = mysql_fetch_row($result);
			$name = $row[0];
			mysql_free_result($result);
    		return $name;
  	} else {
    		return "unknown";
  	}
}

function getEnvironmentFromId($id) {
  	$query = "SELECT environment FROM challenges WHERE id=$id";
  	$result = mysql_query($query);
  	if (!$result)
  		return "unknown";
  	if (mysql_num_rows($result) > 0) {
	    	$row = mysql_fetch_row($result);
			$name = $row[0];
			mysql_free_result($result);
    		return $name;
  	} else {
    		return "unknown";
  	}
}

function getAuthorFromId($id) {
  	$query = "SELECT author FROM challenges WHERE id=$id";
  	$result = mysql_query($query);
  	if (!$result)
  		return "unknown";
  	if (mysql_num_rows($result) > 0) {
	    	$row = mysql_fetch_row($result);
			$name = $row[0];
			mysql_free_result($result);
    		return $name;
  	} else {
    		return "unknown";
  	}
}

$parse = new parse();

$connection = mysql_connect($DBSERVER, $DBLOGIN, $DBPASS) or die("Cannot connect to database");
mysql_select_db($ASECODB) or die("Cannot select Schema");

$tracks = array();
$eval = array();

$query = "select challengeid, count(challengeid) as votes from mistral_trackeval where eval<0 and challengeid in (select id from challenges) group by challengeid having votes>=$LIMIT";
$result = mysql_query($query);
while ($row = mysql_fetch_row($result))
	{
	$id = $row[0];
	$track->all = $row[1];
	$name = utf8_decode(getTracknameFromId($id));
	$env = getEnvironmentFromId($id);
	$author = getAuthorFromId($id);
	$keep = getEvalCount($id, -1);
	$dontcare = getEvalCount($id, -2);
	$delete = getEvalCount($id, -3);
	$notmyenv = getEvalCount($id, -4);
	$evaluation = 2*$delete*100/(2*$keep+2*$delete+$dontcare+$notmyenv);
	$track->id = $id;
	$track->name = $name;
	$track->env = $env;
	$track->author = $author;
	$track->keep = $keep;
	$track->dontcare = $dontcare;
	$track->delete = $delete;
	$track->notmyenv = $notmyenv;
	$track->evaluation = $evaluation;
	$tracks[] = $track;
	$eval[] = $evaluation;

	unset($track);
	}

array_multisort($eval, SORT_DESC, $tracks);

echo '<table BORDER="1" RULES="groups">';
echo '<thead><tr><th>Name</th><th>- keep -</th><th>- don\'t care -</th><th>- delete -</th><th>- not my env. -</th><th>- all votes -</th><th>- Result -</th></thead><tbody><p>';

$i = 0;
foreach ($tracks as $track)
	{
	$i++;
	if ($i%2)
		$id = "bg2";
	else
		$id = "bg1";
	echo "<tr id='$id'><td>$i. ".$parse->tmtohtml($track->name)." ($track->env by $track->author)</td><td align='center'>$track->keep</td><td align='center'>$track->dontcare</td><td align='center'>$track->delete</td><td align='center'>$track->notmyenv</td><td align='center'>$track->all</td><td align='center'>".round($track->evaluation,2)."</td></tr>";
	}

echo "</tbody></table>";
mysql_free_result($result);

mysql_close($connection);
?>
	</body>
</html>
