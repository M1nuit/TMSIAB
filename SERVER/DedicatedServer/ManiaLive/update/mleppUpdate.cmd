@echo off

rem Scriptfile: 	mlepp00Update.cmd
rem Destination: 	Updating mlepp
rem ****** Tested with 2000, XP, Vista and Win 7

title MLEPP Updater

rem ****** Tested with NT, 2000, XP, Vista and Win 7

pushd %~dp0
set MLIVEDIR=%CD%
cd ..\..\..\USBWebserver_v8_en
set INSTPHP=%CD%\php
PATH=%PATH%;%INSTPHP%;%INSTPHP%\ext


cd %MLIVEDIR%
start "MLEPP Update" "%INSTPHP%\php.exe" -c "%INSTPHP%\php.ini" mlepp.php

rem ****************************************************
