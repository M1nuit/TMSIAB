@echo off

rem Scriptfile: 	_XAsecoIDStart.cmd
rem Destination: 	Starting remoteCE[LIVE]
rem ****** Tested with 2000, XP, Vista and Win 7

title Mistral Aseco

rem ****** Tested with NT, 2000, XP, Vista and Win 7

pushd %~dp0
set ASECODIR=%CD%
cd ..\..\USBWebserver_v8_en
set INSTPHP=%CD%\php
PATH=%PATH%;%INSTPHP%;%INSTPHP%\ext


cd %ASECODIR%
start "XAseco" "%INSTPHP%\php.exe" -c "%INSTPHP%\php.ini" aseco.php

rem ****************************************************