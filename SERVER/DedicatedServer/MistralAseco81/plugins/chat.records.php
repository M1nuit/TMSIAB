<?php
/**
 * Chat plugin.
 * Displays TOP records of the currently played track.
 */

Aseco::addChatCommand("recs", "displays a list of the current top 5 records");

function chat_recs(&$aseco, &$command) {

	// added time driven, idea by Mistral, implemented slightly differently

	$records = "<?xml version='1.0' encoding='utf-8' ?>
<manialink posx='0.5' posy='0.35'>
<type>default</type>
<format textsize='2'/>
<background bgcolor='222C' bgborderx='0.03' bgbordery='0.03'/>
<line>
 <cell width='0.94'><text halign='center'>Top 5 Records</text></cell>
</line>
<line height='0.04'>
 <cell width='0.06' bgcolor='888E'><text></text></cell>
 <cell width='0.24' bgcolor='888E'><text halign='center'>  Driven</text></cell>
 <cell width='0.11' bgcolor='888E'><text halign='center'>Time</text></cell>
 <cell width='0.53' bgcolor='888E'><text>  Name</text></cell></line>";
	$detail = "<line>
 <cell width='0.06'><text halign='right'>{POS}</text></cell>
 <cell width='0.24'><text halign='right'>  {DATE}</text></cell>
 <cell width='0.11'><text halign='right'>{TIME}</text></cell>
 <cell width='0.53'><text>  {NAME}</text></cell>
</line>" . CRLF;

  $top = 5;

  // create the list of records,
  // both for all three versions ...
  if ($aseco->server->records->count() > 0) {
    for ($i = 0; $i < $top; $i++) {
      if($cur_record = $aseco->server->records->getRecord($i)) {
		$s = $detail;
		$s = str_replace('{POS}', ($i+1), $s);
		$s = str_replace('{TIME}', formatTime($cur_record->score), $s);
		$s = str_replace('{DATE}', date("Y-m-d H:i", strtotime($cur_record->date)), $s);
		$s = str_replace('{NAME}', sub_maniacodes($cur_record->player->nickname), $s);
		$records .= CRLF . $s;
      }
    }
  } else {
    $records .= "<line height='0.05'><cell></cell></line><line><cell width='0.94'><text halign='center'>No Records</text></cell></line>";
  }

	$records .= "
  <line height='.04'><cell><text></text></cell></line>
  <line>
    <cell width='.40'><text></text></cell>
    <cell width='.14'><text halign='center' action='12' textcolor='FFFF'>Close</text></cell>
	<cell width='.40'><text></text></cell>
	</line>
</manialink>";

	$aseco->addcall('SendDisplayManialinkPageToLogin', array($command['author']->login, $records, 0, TRUE));
}
?>
