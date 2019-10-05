<?php

/*
* TMSiaB csf_FAST.php  Create Server Files for FAST 3.2.2y
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


// FAST: Load datafile _FASTIDStart.cmd
// Fill in data depending on TMSiaBID
// Save it under_FASTIDStart.cmd
if( $ttmsiabid == '01' ) {
$config_file = file_get_contents('../RESOURCES/FAST/_FASTIDStart.cmd');
$config_file = str_replace('ID', '01', $config_file);
$file = @fopen('../../SERVER/DedicatedServer/FAST322/_FAST01Start.cmd', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;
$config_file = file_get_contents('../../SERVER/DedicatedServer/TrackmaniaServer_2011-02-11/GameData/Config/dedicated_cfg01.txt');
$file = @fopen('../../SERVER/DedicatedServer/FAST322/dedicated_cfg01.txt', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false; }
	
else {  if( $ttmsiabid == '02' ) {
		$config_file = file_get_contents('../RESOURCES/FAST/_FASTIDStart.cmd');
		$config_file = str_replace('ID', '02', $config_file);
		$file = @fopen('../../SERVER/DedicatedServer/FAST322/_FAST02Start.cmd', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false;
		$config_file = file_get_contents('../../SERVER/DedicatedServer/TrackmaniaServer_2011-02-11/GameData/Config/dedicated_cfg02.txt');
		$file = @fopen('../../SERVER/DedicatedServer/FAST322/dedicated_cfg02.txt', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false; } 
	
		else 	{ $config_file = file_get_contents('../RESOURCES/FAST/_FASTIDStart.cmd');
				$config_file = str_replace('ID', '03', $config_file);
				$file = @fopen('../../SERVER/DedicatedServer/FAST322/_FAST03Start.cmd', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false;
				$config_file = file_get_contents('../../SERVER/DedicatedServer/TrackmaniaServer_2011-02-11/GameData/Config/dedicated_cfg03.txt');
				$file = @fopen('../../SERVER/DedicatedServer/FAST322/dedicated_cfg03.txt', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false; } }
?>