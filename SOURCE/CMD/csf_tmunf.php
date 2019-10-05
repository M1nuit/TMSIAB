<?php

/*
* TMSiaB csf_tmunf.php  Create Server Files for TMUF and TMNF Dedicated Server 
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


//TMUNF: Load datafile dedicated_cfg01.txt
//Fill in Data
//Fill in Data depending on Gametype
//Save it under ID-Related Name

$config_file = file_get_contents('../RESOURCES/tmunf/dedicated_cfg.db.txt');

$config_file = str_replace('DSUPERDAMINPW', $dsuperdaminpw, $config_file);
$config_file = str_replace('DLOGIN', $dlogin, $config_file);
$config_file = str_replace('DPASSWORD', $dpassword, $config_file);
$config_file = str_replace('DPLAYERKEY', $dplayerkey, $config_file);
$config_file = str_replace('DDADICATEDNAME', $ddadicatedname, $config_file);

if( $dtype == 'TMNF' ) {
	$config_file = str_replace('PACKSTAD', 'stadium', $config_file);
	if( $dfreezone == 'VISIBLE' ) {
	$config_file = str_replace('HIDEME', '0', $config_file);
	}
	else {
	$config_file = str_replace('HIDEME', '1', $config_file);
	}
}
else {
$config_file = str_replace('PACKSTAD', '', $config_file);
$config_file = str_replace('HIDEME', '0', $config_file);
}
$config_file = str_replace('DSERVERPORT', $dserverport, $config_file);
$config_file = str_replace('DPTPPORT', $dptpport, $config_file);
$config_file = str_replace('DMLRPCPORT', $dmlrpcport, $config_file);


if( $ttmsiabid == '01' ) {
	$file = @fopen('../../SERVER/DedicatedServer/TrackmaniaServer_2011-02-11/GameData/Config/dedicated_cfg01.txt', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false; }

else {  if( $ttmsiabid == '02' ) {
		$file = @fopen('../../SERVER/DedicatedServer/TrackmaniaServer_2011-02-11/GameData/Config/dedicated_cfg02.txt', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false; } 
	
		else {  if( $ttmsiabid == '03' ) {
				$file = @fopen('../../SERVER/DedicatedServer/TrackmaniaServer_2011-02-11/GameData/Config/dedicated_cfg03.txt', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false; }
			 }
	
	 }
 

//TMUNF: Load datafile StartDedicated.db.cmd
//Fill in Data
//Fill in Data
//Save it under ID-Related Name
 
$config_file = file_get_contents('../RESOURCES/tmunf/StartDedicated.db.cmd');

if( $dneighborhood == 'INTERNET' ) {
	$config_file = str_replace('DNEIGHBORHOOD', 'Internet', $config_file);
	$config_file = str_replace('INTERNETORLAN', 'internet', $config_file); }
else { $config_file = str_replace('DNEIGHBORHOOD', 'LAN-Party', $config_file);
	   $config_file = str_replace('INTERNETORLAN', 'lan', $config_file); }


if( $ttmsiabid == '01' ) {
	$config_file = str_replace('DEDICATEDCFG', 'dedicated_cfg01.txt', $config_file);
	$config_file = str_replace('TRACKLISTTXT', 'tracklist01.txt', $config_file); }
else {  if( $ttmsiabid == '02' ) {
		$config_file = str_replace('DEDICATEDCFG', 'dedicated_cfg02.txt', $config_file);
		$config_file = str_replace('TRACKLISTTXT', 'tracklist02.txt', $config_file); } 
		else {  if( $ttmsiabid == '03' ) {
				$config_file = str_replace('DEDICATEDCFG', 'dedicated_cfg03.txt', $config_file);
				$config_file = str_replace('TRACKLISTTXT', 'tracklist03.txt', $config_file); } } }
	 
	 
if( $ttmsiabid == '01' ) {
	$file = @fopen('../../SERVER/DedicatedServer/TrackmaniaServer_2011-02-11/_StartDedicated01.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false; }

else {  if( $ttmsiabid == '02' ) {
		$file = @fopen('../../SERVER/DedicatedServer/TrackmaniaServer_2011-02-11/_StartDedicated02.cmd', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false; } 
		else {  if( $ttmsiabid == '03' ) {
				$file = @fopen('../../SERVER/DedicatedServer/TrackmaniaServer_2011-02-11/_StartDedicated03.cmd', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false; } } } 
 
 
 


?>

