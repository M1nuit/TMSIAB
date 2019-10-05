@echo off

rem Scriptfile: 	csf.cmd
rem Destination: 	Create Server Files


rem ### working directory current folder 
pushd %~dp0

..\..\SERVER\USBWebserver_v8_en\php\php.exe -n .\csf_rcp.php
..\..\SERVER\USBWebserver_v8_en\php\php.exe -n .\csf_tmunf.php
..\..\SERVER\USBWebserver_v8_en\php\php.exe -n .\csf_maseco.php
..\..\SERVER\USBWebserver_v8_en\php\php.exe -n .\csf_xaseco.php
..\..\SERVER\USBWebserver_v8_en\php\php.exe -n .\csf_FAST.php
..\..\SERVER\USBWebserver_v8_en\php\php.exe -n .\csf_manialive.php
..\..\SERVER\USBWebserver_v8_en\php\php.exe -n .\csf_foxcontrol.php
..\..\SERVER\USBWebserver_v8_en\php\php.exe -n .\csf_gen_sp.php

rem ### restore original working directory
popd

rem EXIT

