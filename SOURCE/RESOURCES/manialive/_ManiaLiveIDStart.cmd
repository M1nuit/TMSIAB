@echo off

rem Scriptfile: 	_ManiaLiveIDStart.cmd
rem Destination: 	Starting remoteCE[LIVE]
rem ****** Tested with 2000, XP, Vista and Win 7

rem ****** Tested with NT, 2000, XP, Vista and Win 7

pushd %~dp0
set MLIVEDIR=%CD%
cd ..\..\USBWebserver_v8_en
set INSTPHP=%CD%\php
PATH=%PATH%;%INSTPHP%;%INSTPHP%\ext
cd %MLIVEDIR%
del .\webserverpath.txt /Q /F
echo %INSTPHP%>.\webserverpath.txt
..\..\USBWebserver_v8_en\php\php.exe -n .\csf_pathformanialive.php

rem ****************************************************

start "ManiaLive" "%INSTPHP%\php.exe" -c "%INSTPHP%\php.ini" bootstrapper.php

rem ****************************************************
popd