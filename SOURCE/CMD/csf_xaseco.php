<?php

/*
* TMSiaB csf_maseco.php  Create Server Files for XAseco 1.14
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
$dmlrpcport 		= $dedicated->dedicatedvalues[0]->dmlrpcport;
$tmspace			= '';


// XAseco: Load datafile jfreu.config.db.php
// Fill in data
// Save it in plugins dir
$config_file = file_get_contents('../RESOURCES/xaseco/jfreu.config.db.php');
$config_file = str_replace('DDADICATEDNAME', $ddadicatedname, $config_file);
$file = @fopen('../../SERVER/DedicatedServer/XAseco114/includes/jfreu.config.php', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;

// XAseco: Load config.db.xml
// Fill in data, depending on lan or internetgame
// Save it in plugins dir
$config_file = file_get_contents('../RESOURCES/xaseco/config.db.xml');
if( $dneighborhood == 'INTERNET' ) {
$config_file = str_replace('DTMLOGIN/DLOCALIP:UTMPORT', $dtmlogin, $config_file);
}
else {
$config_file = str_replace('DTMLOGIN', $dtmlogin, $config_file);
$config_file = str_replace('DLOCALIP', $dlocalip, $config_file);
$config_file = str_replace('UTMPORT', $utmport, $config_file);

}
$config_file = str_replace('SUPERADMIN_LOGIN', $superadmin_login, $config_file);
$config_file = str_replace('DSUPERDAMINPW', $dsuperdaminpw, $config_file);
$config_file = str_replace('DMLRPCPORT', $dmlrpcport, $config_file);
$config_file = str_replace('YOUR_MASTERADMIN_IP', '', $config_file);
$file = @fopen('../../SERVER/DedicatedServer/XAseco114/config.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;


// XAseco: Load localdatabase.xml.txt
// Fill in data, depending on Servertype
// Save it in plugins dir
$config_file = file_get_contents('../RESOURCES/xaseco/localdatabase.db.xml');
$config_file = str_replace('DDATABASE', 'xaseco', $config_file);
$file = @fopen('../../SERVER/DedicatedServer/XAseco114/localdatabase.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;


// XAseco: Load datafile adminops.db.xml
// Fill in data
// Save it in plugins dir
$config_file = file_get_contents('../RESOURCES/xaseco/adminops.db.xml');
$config_file = str_replace('DTMLOGIN', $dtmlogin, $config_file);
$file = @fopen('../../SERVER/DedicatedServer/XAseco114/adminops.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;

// XAseco: Load datafile dedimania.db.xml
// Fill in data
// Save it in plugins dir
$config_file = file_get_contents('../RESOURCES/xaseco/dedimania.db.xml');
$config_file = str_replace('DLOGIN', $dlogin, $config_file);
$config_file = str_replace('DPASSWORD', $dpassword, $config_file);
$config_file = str_replace('DNATIONALITY', $dnationality, $config_file);
$file = @fopen('../../SERVER/DedicatedServer/XAseco114/dedimania.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;

// XAseco: Load datafile _XAsecoIDStart.cmd
// Fill in data depending on TMSiaBID
// Save it _XAsecoIDStart.cmd
if( $ttmsiabid == '01' ) {
$config_file = file_get_contents('../RESOURCES/xaseco/_XAsecoIDStart.cmd');
$config_file = str_replace('ID', '01', $config_file);
$file = @fopen('../../SERVER/DedicatedServer/XAseco114/_XAseco01Start.cmd', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false; }
	
else {  if( $ttmsiabid == '02' ) {
		$config_file = file_get_contents('../RESOURCES/xaseco/_XAsecoIDStart.cmd');
		$config_file = str_replace('ID', '02', $config_file);
		$file = @fopen('../../SERVER/DedicatedServer/XAseco114/_XAseco02Start.cmd', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false; } 
	
		else 	{ $config_file = file_get_contents('../RESOURCES/xaseco/_XAsecoIDStart.cmd');
				$config_file = str_replace('ID', '03', $config_file);
				$file = @fopen('../../SERVER/DedicatedServer/XAseco114/_XAseco03Start.cmd', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false; } }
?>
