<?php

// Read file webserverpath.txt to variable $webserverpath
$webserverpath = file_get_contents("./webserverpath.txt");

// Replace character \ in variable $webserverpath with / 
$webserverpath = str_replace('\\', '/', $webserverpath);

// Read file config-example.ini to variable $config_file
$config_file = file_get_contents('./config/config.ini');

// Replace word WEBSERVERPATH in file config.ini with content of variable $webserverpath
$config_file = str_replace('WEBSERVERPATH', $webserverpath, $config_file);

// Write new file config.ini
$file = @fopen('./config/config.ini', 'w+');
if(@fwrite($file, $config_file) > 0)

// Destroy variables
$config_file = false;
$webserverpath = false;

// End of file
?>