<?php

/*
* TMSiaB csf_manialive.php  Create Server Files for ManiaLive
* v5.0
*/

//Load config.xml
$dedicated = simplexml_load_file('../CONFIGURATION/config.xml');
 
//All XML-Entrys will be variables

$ttmsiabid 			= $dedicated->dedicatedvalues[0]->ttmsiabid;
$dtype 				= $dedicated->dedicatedvalues[0]->dtype;
$dplugin 			= $dedicated->dedicatedvalues[0]->dplugin;
$dneighborhood 		= $dedicated->dedicatedvalues[0]->dneighborhood;
$dfreezone 			= $dedicated->dedicatedvalues[0]->dfreezone;
$dcopper 			= $dedicated->dedicatedvalues[0]->dcopper;
$dplayerkey 		= $dedicated->dedicatedvalues[0]->dplayerkey;
$dlocalip 			= $dedicated->dedicatedvalues[0]->dlocalip;
$utmport 			= $dedicated->dedicatedvalues[0]->utmport;
$dlogin				= $dedicated->dedicatedvalues[0]->dlogin;
$dpassword 			= $dedicated->dedicatedvalues[0]->dpassword;
$dtmlogin 			= $dedicated->dedicatedvalues[0]->dtmlogin;
$dcommunitycode 	= $dedicated->dedicatedvalues[0]->dcommunitycode;
$dnationality 		= $dedicated->dedicatedvalues[0]->dnationality;
$ddadicatedname 	= $dedicated->dedicatedvalues[0]->ddadicatedname;
$superadmin_login	= 'SuperAdmin';
$dsuperdaminpw 		= $dedicated->dedicatedvalues[0]->dsuperdaminpw;
$dserverport 		= $dedicated->dedicatedvalues[0]->dserverport;
$dptpport 			= $dedicated->dedicatedvalues[0]->dptpport;
$dmlrpcport 			= $dedicated->dedicatedvalues[0]->dmlrpcport;
$tmspace			= '';


// ManiaLive: Load datafile _ManiaLiveIDStart.cmd
// Fill in data depending on TMSiaBID
// Save it under__ManiaLiveIDStart.cmd
if( $ttmsiabid == '01' ) {
$config_file = file_get_contents('../RESOURCES/manialive/_ManiaLiveIDStart.cmd');
$config_file = str_replace('ID', '01', $config_file);
$file = @fopen('../../SERVER/DedicatedServer/ManiaLive/_ManiaLive01Start.cmd', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false; }
	
else {  if( $ttmsiabid == '02' ) {
		$config_file = file_get_contents('../RESOURCES/manialive/_ManiaLiveIDStart.cmd');
		$config_file = str_replace('ID', '02', $config_file);
		$file = @fopen('../../SERVER/DedicatedServer/ManiaLive/_ManiaLive02Start.cmd', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false; } 
	
		else 	{ $config_file = file_get_contents('../RESOURCES/manialive/_ManiaLiveIDStart.cmd');
				$config_file = str_replace('ID', '03', $config_file);
				$file = @fopen('../../SERVER/DedicatedServer/ManiaLive/_ManiaLive03Start.cmd', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false; } }

// ManiaLive: Load datafile config.db.ini
// Fill in data depending on TMSiaBID
// Save it under config.ini in config dir of ManiaLive				
				
$config_file = file_get_contents('../RESOURCES/manialive/config.db.ini');
$config_file = str_replace('DMLRPCPORT', $dmlrpcport, $config_file);
$config_file = str_replace('DSUPERDAMINPW1', $dsuperdaminpw, $config_file);
$config_file = str_replace('DTMLOGIN1', $dtmlogin, $config_file);
if( $ttmsiabid == '01' ) { $config_file = str_replace('MLEPPDB', 'mlepp1', $config_file); }
else {  if( $ttmsiabid == '02' ) {
		$config_file = str_replace('MLEPPDB', 'mlepp2', $config_file); } 
		else 	{ if( $ttmsiabid == '03' ) { $config_file = str_replace('MLEPPDB', 'mlepp3', $config_file); } } }
$file = @fopen('../../SERVER/DedicatedServer/ManiaLive/config/config.ini', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;
				
				
				
?>