@echo off
rem Scriptfile: 	getupdate.cmd
rem Destination: 	Download a file called "update.7z".
rem					TMSiaB will be updated automatically.
rem					After update is completed, TMSiaB will be closed.

rem ### working directory current folder 
pushd %~dp0

set logfile="..\..\update.log"

echo.TMSiaB UPDATE SCRIPT LOGFILE
echo.TMSiaB UPDATE SCRIPT LOGFILE >%logfile%
echo.%date%, %time% 
echo.%date%, %time% >>%logfile%
echo.
echo. >>%logfile%
echo.[%time%] Update in progress, please be patient ...
echo.[%time%] Update in progress, please be patient ...>>%logfile%

echo.[%time%] Trying to download updatefile ...
echo.[%time%] Trying to download updatefile ... >>%logfile%

..\WGET\wget.exe -t10 -O .\..\..\update.7z http://update.tmu-xrated.de/tmsiab4/update.7z
if not %errorlevel%==0 goto ERR

echo.[%time%] Download successfull!
echo.[%time%] Download successfull!>>%logfile%
cd ..\..
set logfile=".\update.log"

echo.[%time%] Updating files ...
echo.[%time%] Updating files ...>>%logfile%
.\SOURCE\7ZIP\7za.exe x update.7z -y>>%logfile%
if not %errorlevel%==0 goto ERR

del update.7z >>%logfile%
echo.[%time%] Update complete>>%logfile%
echo.[%time%] Update complete
echo.[%time%] Trying to shutdown TMSiaB, you have to restart it manually!
echo.[%time%] Trying to shutdown TMSiaB, you have to restart it manually! >>%logfile%
pause
.\SOURCE\PSKILL\pskill.exe mshta.exe
goto END


:END
rem ### restore original working directory
popd
EXIT

:ERR
echo.[%time%] Failure, please have a look at the file update.log>>%logfile%
echo.[%time%] Failure, please have a look at the file update.log
rem ### restore original working directory
popd
Pause
EXIT