@echo off

rem Scriptfile: 	_FoxControlIDStart.cmd
rem Destination: 	Starting remoteCE[LIVE]
rem ****** Tested with 2000, XP, Vista and Win 7

title Fox Control

rem ****** Tested with NT, 2000, XP, Vista and Win 7

pushd %~dp0
set FOXDIR=%CD%
cd ..\..\USBWebserver_v8_en
set INSTPHP=%CD%\php
PATH=%PATH%;%INSTPHP%;%INSTPHP%\ext


cd %FOXDIR%
start "Fox Control" "%INSTPHP%\php.exe" -c "%INSTPHP%\php.ini" controlID.php

rem ****************************************************