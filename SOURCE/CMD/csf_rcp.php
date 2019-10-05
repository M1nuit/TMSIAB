<?php

/*
* TMSiaB csf_rcp.php  Create Server Files for RCP
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


//RCP: Load datafile servers.db1.source.xml
//Fill in Data
//Fill in Data depending on Pluginselection

if( $ttmsiabid == '01' ) {
$config_file = file_get_contents('../RESOURCES/rcp/servers.db1.source.xml');
$config_file = str_replace('TTMSIABID1', '1', $config_file);
$config_file = str_replace('DTMLOGIN1', $dtmlogin, $config_file);
$config_file = str_replace('DDADICATEDNAME1', $ddadicatedname, $config_file);
$config_file = str_replace('DMLRPCPORT1', $dmlrpcport, $config_file);
$config_file = str_replace('DSUPERDAMINPW1', $dsuperdaminpw, $config_file);
$config_file = str_replace('DCOMMUNITYCODE1', $dcommunitycode, $config_file);

if( $dplugin == 'LIVE' ) {
$config_file = str_replace('SETTINGSET1', '', $config_file);
}
else {
	if( $dplugin == 'MASECO' ) {
	$config_file = str_replace('SETTINGSET1', 'default_with_asecodb', $config_file);
	}
	else {
		if( $dplugin == 'XASECO' ) {
		$config_file = str_replace('SETTINGSET1', 'default_with_asecodb', $config_file);
		}
		else {
		$config_file = str_replace('SETTINGSET1', '', $config_file);
}
}
}
if( $dplugin == 'LIVE' ) {
$config_file = str_replace('DSN1', 'mysql:dbname=remotecp;host=localhost', $config_file);
}
else {
	if( $dplugin == 'MASECO' ) {
	$config_file = str_replace('DSN1', 'mysql:dbname=maseco;host=localhost', $config_file);
	}
	else {
		if( $dplugin == 'XASECO' ) {
		$config_file = str_replace('DSN1', 'mysql:dbname=xaseco;host=localhost', $config_file);
		}
		else {
		$config_file = str_replace('DSN1', 'mysql:dbname=remotecp;host=localhost', $config_file);
		$config_file = str_replace('true', 'false', $config_file);
}
}
}
$file = @fopen('../../SERVER/USBWebserver_v8_en/root/remoteCP4035/xml/servers.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$file = @fopen('../RESOURCES/rcp/servers.db2.source.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;
}

//RCP: Load datafile servers.db2.source.xml
//Fill in Data
//Fill in Data depending on Pluginselection

else { if( $ttmsiabid == '02' ) {
$config_file = file_get_contents('../RESOURCES/rcp/servers.db2.source.xml');
$config_file = str_replace('TTMSIABID2', '2', $config_file);
$config_file = str_replace('DTMLOGIN2', $dtmlogin, $config_file);
$config_file = str_replace('RESERVED2', $ddadicatedname, $config_file);
$config_file = str_replace('DMLRPCPORT2', $dmlrpcport, $config_file);
$config_file = str_replace('DSUPERDAMINPW2', $dsuperdaminpw, $config_file);
$config_file = str_replace('DCOMMUNITYCODE2', $dcommunitycode, $config_file);


if( $dplugin == 'LIVE' ) {
$config_file = str_replace('SETTINGSET2', '', $config_file);
}
else {
	if( $dplugin == 'MASECO' ) {
	$config_file = str_replace('SETTINGSET2', 'default_with_asecodb', $config_file);
	}
	else {
		if( $dplugin == 'XASECO' ) {
		$config_file = str_replace('SETTINGSET2', 'default_with_asecodb', $config_file);
		}
		else {
		$config_file = str_replace('SETTINGSET2', '', $config_file);
}
}
}
if( $dplugin == 'LIVE' ) {
$config_file = str_replace('DSN2', 'mysql:dbname=remotecp;host=localhost', $config_file);
}
else {
	if( $dplugin == 'MASECO' ) {
	$config_file = str_replace('DSN2', 'mysql:dbname=maseco;host=localhost', $config_file);
	}
	else {
		if( $dplugin == 'XASECO' ) {
		$config_file = str_replace('DSN2', 'mysql:dbname=xaseco;host=localhost', $config_file);
		}
		else {
		$config_file = str_replace('DSN2', 'mysql:dbname=remotecp;host=localhost', $config_file);
		$config_file = str_replace('true', 'false', $config_file);
}
}
}
$file = @fopen('../../SERVER/USBWebserver_v8_en/root/remoteCP4035/xml/servers.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$file = @fopen('../RESOURCES/rcp/servers.db3.source.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;
}

//RCP: Load datafile servers.db3.source.xml
//Fill in Data
//Fill in Data depending on Pluginselection

else {
$config_file = file_get_contents('../RESOURCES/rcp/servers.db3.source.xml');
$config_file = str_replace('TTMSIABID3', '3', $config_file);
$config_file = str_replace('DTMLOGIN3', $dtmlogin, $config_file);
$config_file = str_replace('RESERVED3', $ddadicatedname, $config_file);
$config_file = str_replace('DMLRPCPORT3', $dmlrpcport, $config_file);
$config_file = str_replace('DSUPERDAMINPW3', $dsuperdaminpw, $config_file);
$config_file = str_replace('DCOMMUNITYCODE3', $dcommunitycode, $config_file);

if( $dplugin == 'LIVE' ) {
$config_file = str_replace('SETTINGSET3', '', $config_file);
}
else {
	if( $dplugin == 'MASECO' ) {
	$config_file = str_replace('SETTINGSET3', 'default_with_asecodb', $config_file);
	}
	else {
		if( $dplugin == 'XASECO' ) {
		$config_file = str_replace('SETTINGSET3', 'default_with_asecodb', $config_file);
		}
		else {
		$config_file = str_replace('SETTINGSET3', '', $config_file);
}
}
}
if( $dplugin == 'LIVE' ) {
$config_file = str_replace('DSN3', 'mysql:dbname=remotecp;host=localhost', $config_file);
}
else {
	if( $dplugin == 'MASECO' ) {
	$config_file = str_replace('DSN3', 'mysql:dbname=maseco;host=localhost', $config_file);
	}
	else {
		if( $dplugin == 'XASECO' ) {
		$config_file = str_replace('DSN3', 'mysql:dbname=xaseco;host=localhost', $config_file);
		}
		else {
		$config_file = str_replace('DSN3', 'mysql:dbname=remotecp;host=localhost', $config_file);
		$config_file = str_replace('true', 'false', $config_file);
}
}
}
$file = @fopen('../../SERVER/USBWebserver_v8_en/root/remoteCP4035/xml/servers.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;
}
}

//RCP: Load datafile admins.db1.source.xml
//Fill in Data
//Write to xml dir


$config_file = file_get_contents('../RESOURCES/rcp/admins.db1.source.xml');
$config_file = str_replace('DTMLOGIN1', $dtmlogin, $config_file);
$file = @fopen('../../SERVER/USBWebserver_v8_en/root/remoteCP4035/xml/admins.xml', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;

//TMSiaB: Load datafile index.html
//Fill in Data
//Write to localhost\index.html


$config_file = file_get_contents('../RESOURCES/localhost/index.html');
$config_file = str_replace('DLOGIN1', $dlogin, $config_file);
$file = @fopen('../../SERVER/USBWebserver_v8_en/root/index.html', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;


//RCP: Load datafile _LiveIDStart.cmd
//Fill in Data
//Write to remoteCP-Web-Dir


if( $ttmsiabid == '01' ) {
$config_file = file_get_contents('../RESOURCES/rcp/_LiveIDStart.cmd');
$config_file = str_replace('ID', '1', $config_file);
$file = @fopen('../../SERVER/USBWebserver_v8_en/root/remoteCP4035/_Live01Start.cmd', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false; }
	
else {  if( $ttmsiabid == '02' ) {
		$config_file = file_get_contents('../RESOURCES/rcp/_LiveIDStart.cmd');
		$config_file = str_replace('ID', '2', $config_file);
		$file = @fopen('../../SERVER/USBWebserver_v8_en/root/remoteCP4035/_Live02Start.cmd', 'w+');
		if(@fwrite($file, $config_file) > 0)
		$config_file = false; } 
	
		else 	{ $config_file = file_get_contents('../RESOURCES/rcp/_LiveIDStart.cmd');
				$config_file = str_replace('ID', '3', $config_file);
				$file = @fopen('../../SERVER/USBWebserver_v8_en/root/remoteCP4035/_Live03Start.cmd', 'w+');
				if(@fwrite($file, $config_file) > 0)
				$config_file = false; } }
?>

