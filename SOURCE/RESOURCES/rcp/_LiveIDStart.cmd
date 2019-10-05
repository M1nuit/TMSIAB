@echo off

rem Scriptfile: 	_Live0IDStart.cmd
rem Destination: 	Starting remoteCE[LIVE]
rem ****** Tested with 2000, XP, Vista and Win 7

title remoteCP LIVE

rem ### working directory current folder 
pushd %~dp0
set RCPDIR=%CD%
cd ..\..
set INSTPHP=%CD%\php
PATH=%PATH%;%INSTPHP%;%INSTPHP%\ext
rem ****************************************************

cd %RCPDIR%
start "remoteCP LIVE" "%INSTPHP%\php.exe" -c "%INSTPHP%\php.ini" live.php -- ID http://localhost:8080/rcp4035/