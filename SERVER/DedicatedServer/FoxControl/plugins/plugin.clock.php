<?php
//* plugin.clock.php - Just a simple clock ;)
//* Version:   0.9.0
//* Coded by:  libero6 && cyrilw
//* Copyright: FoxRace, http://www.fox-control.de

control::RegisterEvent('EverySecond', 'clock_everysecond');

function clock_everysecond($control){
	$control->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
	<manialink id="3">
	<timeout>0</timeout>
	<quad posn="50.3 32.75 0" sizen="26 4.3" style="Bgs1InRace" substyle="NavButtonBlink"/>
	<label text="$o'.date('H:i').'" posn="57.6 32.25 1" textsize="1" halign="center" scale="1.3" />
	<label posn="57.6 30.25 1" textsize="1" halign="center" text="'.date('d.m.Y').'" scale="0.9"/>
	</manialink>', 0, False);
}
?>