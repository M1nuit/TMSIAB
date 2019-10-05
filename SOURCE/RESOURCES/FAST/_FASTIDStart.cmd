@echo off

rem Scriptfile: 	_FASTIDStart.cmd
rem Destination: 	Starting FAST3
rem ****** Tested with 2000, XP, Vista and Win 7

title Mistral Aseco

rem ****** Tested with NT, 2000, XP, Vista and Win 7

pushd %~dp0
set FASTDIR=%CD%
cd ..\..\USBWebserver_v8_en
set INSTPHP=%CD%\php
PATH=%PATH%;%INSTPHP%;%INSTPHP%\ext


cd %FASTDIR%
start "FAST3" "%INSTPHP%\php.exe" -c "%INSTPHP%\php.ini" fast.php dedicated_cfgID.txt

rem ****************************************************