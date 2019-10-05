<?php

/*
* TMSiaB csf_gen_sp.php  Create Startlink for TMSiaB Webinterface
* v5.0
*/

$dedicated = simplexml_load_file('../CONFIGURATION/config.xml');
$ttmsiabid 			= $dedicated->dedicatedvalues[0]->ttmsiabid;
$dplugin 			= $dedicated->dedicatedvalues[0]->dplugin;

if( $ttmsiabid == '01' and $dplugin == "MASECO")
{
	$config_file = file_get_contents('../RESOURCES/MCP/Mistral.cmd');
	$config_file = str_replace('ID', '01', $config_file);
	$file = @fopen('../../SERVER/_Startplugin01.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '01' and $dplugin == "MANIALIVE")
{
	$config_file = file_get_contents('../RESOURCES/MCP/ManiaLive.cmd');
	$config_file = str_replace('ID', '01', $config_file);
	$file = @fopen('../../SERVER/_Startplugin01.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '01' and $dplugin == "MANIALIVE")
{
	$config_file = file_get_contents('../RESOURCES/MCP/ManiaLive.cmd');
	$config_file = str_replace('ID', '01', $config_file);
	$file = @fopen('../../SERVER/_Startplugin01.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '01' and $dplugin == "FOXCONTROL")
{
	$config_file = file_get_contents('../RESOURCES/MCP/FoxControl.cmd');
	$config_file = str_replace('ID', '01', $config_file);
	$file = @fopen('../../SERVER/_Startplugin01.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '01' and $dplugin == "FAST3")
{
	$config_file = file_get_contents('../RESOURCES/MCP/FAST3.cmd');
	$config_file = str_replace('ID', '01', $config_file);
	$file = @fopen('../../SERVER/_Startplugin01.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '01' and $dplugin == "XASECO")
{
	$config_file = file_get_contents('../RESOURCES/MCP/XAseco.cmd');
	$config_file = str_replace('ID', '01', $config_file);
	$file = @fopen('../../SERVER/_Startplugin01.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '01' and $dplugin == "LIVE")
{
	$config_file = file_get_contents('../RESOURCES/MCP/Live.cmd');
	$config_file = str_replace('ID', '01', $config_file);
	$file = @fopen('../../SERVER/_Startplugin01.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '01' )
{
	$config_file = file_get_contents('../RESOURCES/MCP/Dedicated.cmd');
	$config_file = str_replace('ID', '01', $config_file);
	$file = @fopen('../../SERVER/_StartDedicated01.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '02' and $dplugin == "MASECO")
{
	$config_file = file_get_contents('../RESOURCES/MCP/Mistral.cmd');
	$config_file = str_replace('ID', '02', $config_file);
	$file = @fopen('../../SERVER/_Startplugin02.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '02' and $dplugin == "MANIALIVE")
{
	$config_file = file_get_contents('../RESOURCES/MCP/ManiaLive.cmd');
	$config_file = str_replace('ID', '02', $config_file);
	$file = @fopen('../../SERVER/_Startplugin02.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '02' and $dplugin == "MANIALIVE")
{
	$config_file = file_get_contents('../RESOURCES/MCP/ManiaLive.cmd');
	$config_file = str_replace('ID', '02', $config_file);
	$file = @fopen('../../SERVER/_Startplugin02.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '02' and $dplugin == "FOXCONTROL")
{
	$config_file = file_get_contents('../RESOURCES/MCP/FoxControl.cmd');
	$config_file = str_replace('ID', '02', $config_file);
	$file = @fopen('../../SERVER/_Startplugin02.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '02' and $dplugin == "FAST3")
{
	$config_file = file_get_contents('../RESOURCES/MCP/FAST3.cmd');
	$config_file = str_replace('ID', '02', $config_file);
	$file = @fopen('../../SERVER/_Startplugin02.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '02' and $dplugin == "XASECO")
{
	$config_file = file_get_contents('../RESOURCES/MCP/XAseco.cmd');
	$config_file = str_replace('ID', '02', $config_file);
	$file = @fopen('../../SERVER/_Startplugin02.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '02' and $dplugin == "LIVE")
{
	$config_file = file_get_contents('../RESOURCES/MCP/Live.cmd');
	$config_file = str_replace('ID', '02', $config_file);
	$file = @fopen('../../SERVER/_Startplugin02.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '02' )
{
	$config_file = file_get_contents('../RESOURCES/MCP/Dedicated.cmd');
	$config_file = str_replace('ID', '02', $config_file);
	$file = @fopen('../../SERVER/_StartDedicated02.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '03' and $dplugin == "MASECO")
{
	$config_file = file_get_contents('../RESOURCES/MCP/Mistral.cmd');
	$config_file = str_replace('ID', '03', $config_file);
	$file = @fopen('../../SERVER/_Startplugin03.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '03' and $dplugin == "MANIALIVE")
{
	$config_file = file_get_contents('../RESOURCES/MCP/ManiaLive.cmd');
	$config_file = str_replace('ID', '03', $config_file);
	$file = @fopen('../../SERVER/_Startplugin03.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '03' and $dplugin == "MANIALIVE")
{
	$config_file = file_get_contents('../RESOURCES/MCP/ManiaLive.cmd');
	$config_file = str_replace('ID', '03', $config_file);
	$file = @fopen('../../SERVER/_Startplugin03.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '03' and $dplugin == "FOXCONTROL")
{
	$config_file = file_get_contents('../RESOURCES/MCP/FoxControl.cmd');
	$config_file = str_replace('ID', '03', $config_file);
	$file = @fopen('../../SERVER/_Startplugin03.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '03' and $dplugin == "FAST3")
{
	$config_file = file_get_contents('../RESOURCES/MCP/FAST3.cmd');
	$config_file = str_replace('ID', '03', $config_file);
	$file = @fopen('../../SERVER/_Startplugin03.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '03' and $dplugin == "XASECO")
{
	$config_file = file_get_contents('../RESOURCES/MCP/XAseco.cmd');
	$config_file = str_replace('ID', '03', $config_file);
	$file = @fopen('../../SERVER/_Startplugin03.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '03' and $dplugin == "LIVE")
{
	$config_file = file_get_contents('../RESOURCES/MCP/Live.cmd');
	$config_file = str_replace('ID', '03', $config_file);
	$file = @fopen('../../SERVER/_Startplugin03.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

if( $ttmsiabid == '03' )
{
	$config_file = file_get_contents('../RESOURCES/MCP/Dedicated.cmd');
	$config_file = str_replace('ID', '01', $config_file);
	$file = @fopen('../../SERVER/_StartDedicated03.cmd', 'w+');
	if(@fwrite($file, $config_file) > 0)
	$config_file = false;
}

$config_file = file_get_contents('../RESOURCES/MCP/Webserver.cmd');
$file = @fopen('../../SERVER/_Start00Webserver.cmd', 'w+');
if(@fwrite($file, $config_file) > 0)
$config_file = false;

?>