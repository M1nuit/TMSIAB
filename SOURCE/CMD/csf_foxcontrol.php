<?php

/*
* TMSiaB csf_foxcontrol.php  Create Server Files for FoxControl
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


// FoxControl: Load datafile _FoxControlIDStart.cmd, config.xml and control.php
// Fill in data depending on TMSiaBID
// Write Files to Fox Control dir

if( $ttmsiabid == '01' ) {
$config_file = file_get_contents('../RESOURCES/foxcontrol/_FoxControlIDStart.cmd');
$config_file = str_replace('ID', '01', $config_file);
$file = @fopen('../../SERVER/DedicatedServer/FoxControl/_FoxControl01Start.cmd', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;

$config_file = file_get_contents('../RESOURCES/foxcontrol/config.db.xml');
$config_file = str_replace('DTMLOGIN1', $dtmlogin, $config_file);
$config_file = str_replace('DLOGIN', $dlogin, $config_file);
$config_file = str_replace('DPASSWORD', $dpassword, $config_file);
$config_file = str_replace('DCOMMUNITYCODE', $dcommunitycode, $config_file);
$config_file = str_replace('DMLRPCPORT', $dmlrpcport, $config_file);
$config_file = str_replace('DSUPERDAMINPW1', $dsuperdaminpw, $config_file);
$config_file = str_replace('DNATIONALITY', $dnationality, $config_file);
$file = @fopen('../../SERVER/DedicatedServer/FoxControl/config01.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;
$config_file = file_get_contents('../RESOURCES/foxcontrol/control.db.php');
$config_file = str_replace('config.xml', 'config01.xml', $config_file);
$file = @fopen('../../SERVER/DedicatedServer/FoxControl/control01.php', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false; }
	
else {  if( $ttmsiabid == '02' ) {
		$config_file = file_get_contents('../RESOURCES/foxcontrol/_FoxControlIDStart.cmd');
		$config_file = str_replace('ID', '02', $config_file);
		$file = @fopen('../../SERVER/DedicatedServer/FoxControl/_FoxControl02Start.cmd', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false;
		$config_file = file_get_contents('../RESOURCES/foxcontrol/config.db.xml');
		$config_file = str_replace('DTMLOGIN1', $dtmlogin, $config_file);
		$config_file = str_replace('DLOGIN', $dlogin, $config_file);
		$config_file = str_replace('DPASSWORD', $dpassword, $config_file);
		$config_file = str_replace('DCOMMUNITYCODE', $dcommunitycode, $config_file);
		$config_file = str_replace('DMLRPCPORT', $dmlrpcport, $config_file);
		$config_file = str_replace('DSUPERDAMINPW1', $dsuperdaminpw, $config_file);
		$config_file = str_replace('DNATIONALITY', $dnationality, $config_file);
		$file = @fopen('../../SERVER/DedicatedServer/FoxControl/config02.xml', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false;
		$config_file = file_get_contents('../RESOURCES/foxcontrol/control.db.php');
		$config_file = str_replace('config.xml', 'config02.xml', $config_file);
		$file = @fopen('../../SERVER/DedicatedServer/FoxControl/control02.php', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false; } 
	
		else 	{ $config_file = file_get_contents('../RESOURCES/foxcontrol/_FoxControlIDStart.cmd');
				$config_file = str_replace('ID', '03', $config_file);
				$file = @fopen('../../SERVER/DedicatedServer/FoxControl/_FoxControl03Start.cmd', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false;
				$config_file = file_get_contents('../RESOURCES/foxcontrol/config.db.xml');
				$config_file = str_replace('DTMLOGIN1', $dtmlogin, $config_file);
				$config_file = str_replace('DLOGIN', $dlogin, $config_file);
				$config_file = str_replace('DPASSWORD', $dpassword, $config_file);
				$config_file = str_replace('DCOMMUNITYCODE', $dcommunitycode, $config_file);
				$config_file = str_replace('DMLRPCPORT', $dmlrpcport, $config_file);
				$config_file = str_replace('DSUPERDAMINPW1', $dsuperdaminpw, $config_file);
				$config_file = str_replace('DNATIONALITY', $dnationality, $config_file);
				$file = @fopen('../../SERVER/DedicatedServer/FoxControl/config03.xml', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false;
				$config_file = file_get_contents('../RESOURCES/foxcontrol/control.db.php');
				$config_file = str_replace('config.xml', 'config03.xml', $config_file);
				$file = @fopen('../../SERVER/DedicatedServer/FoxControl/control03.php', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false; } }
?>