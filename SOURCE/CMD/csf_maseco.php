<?php

/*
* TMSiaB csf_maseco.php  Create Server Files for Mistral Aseco 8.1
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

// MistralAseco: Load datafile config.db01.xml
// Fill in Data
// Save it under config.xml
$config_file = file_get_contents('../RESOURCES/mistralaseco/config.db01.xml');
$config_file = str_replace('DTMLOGIN1', $dtmlogin, $config_file);
$config_file = str_replace('SUPERADMIN_LOGIN', $superadmin_login, $config_file);
$config_file = str_replace('DSUPERDAMINPW1', $dsuperdaminpw, $config_file);
$config_file = str_replace('DMLRPCPORT', $dmlrpcport, $config_file);
$file = @fopen('../../SERVER/DedicatedServer/MistralAseco81/config.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;


// MistralAseco: Load datafile rasp_settings.db01.php
// Fill in data depending on Servertype
// Save it under rasp_settings.php
$config_file = file_get_contents('../RESOURCES/mistralaseco/rasp_settings.db01.php');
if( $dtype == 'TMNF' ) {
$config_file = str_replace('_STMNF', '', $config_file);
}
else {
$config_file = str_replace('_STMNF', '//', $config_file);
}
$config_file = str_replace('DLOGIN', $dlogin, $config_file);
$config_file = str_replace('DDADICATEDNAME', $ddadicatedname, $config_file);
$config_file = str_replace('DTMLOGIN1', $dtmlogin, $config_file);
$file = @fopen('../../SERVER/DedicatedServer/MistralAseco81/includes/rasp_settings.php', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;


// MAseco: Load datafile _MistralAsecoIDStart.cmd
// Fill in data depending on TMSiaBID
// Save it under_MistralAsecoIDStart.cmd
if( $ttmsiabid == '01' ) {
$config_file = file_get_contents('../RESOURCES/mistralaseco/_MistralAsecoIDStart.cmd');
$config_file = str_replace('ID', '01', $config_file);
$file = @fopen('../../SERVER/DedicatedServer/MistralAseco81/_MistralAseco01Start.cmd', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false; }
	
else {  if( $ttmsiabid == '02' ) {
		$config_file = file_get_contents('../RESOURCES/mistralaseco/_MistralAsecoIDStart.cmd');
		$config_file = str_replace('ID', '02', $config_file);
		$file = @fopen('../../SERVER/DedicatedServer/MistralAseco81/_MistralAseco02Start.cmd', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false; } 
	
		else 	{ $config_file = file_get_contents('../RESOURCES/mistralaseco/_MistralAsecoIDStart.cmd');
				$config_file = str_replace('ID', '03', $config_file);
				$file = @fopen('../../SERVER/DedicatedServer/MistralAseco81/_MistralAseco03Start.cmd', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false; } }
?>