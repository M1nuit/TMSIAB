@echo off

rem Scriptfile: 	_MistralAseco0IDStart.cmd
rem Destination: 	Starting remoteCE[LIVE]
rem ****** Tested with 2000, XP, Vista and Win 7

title Mistral Aseco

rem ****** Tested with NT, 2000, XP, Vista and Win 7

pushd %~dp0
set MISTRALDIR=%CD%
cd ..\..\USBWebserver_v8_en
set INSTPHP=%CD%\php
PATH=%PATH%;%INSTPHP%;%INSTPHP%\ext


cd %MISTRALDIR%
start "Mistral Aseco" "%INSTPHP%\php.exe" -c "%INSTPHP%\php.ini" aseco.php TMU

rem ****************************************************