@echo off

rem Scriptfile: 	makeserver.cmd
rem Destination: 	Crunsh previously configured Server in one File.
rem					User is able to uncrunsh this file everywhere.
rem					The uncrushed files contains the whole Dedicated + Webserver.


rem ### working directory current folder 
pushd %~dp0

set logfile="..\..\makeserver.log"

cd ..\7ZIP\

echo.TMSiaB Makeserver SCRIPT LOGFILE >%logfile%
echo.%date%, %time% >>%logfile%
echo. >>%logfile%
echo.[%time%] Compressing in progress, please be patient ...

7za.exe a -t7z ../../Yourserver.exe ../../SERVER\ -sfx>>%logfile%

if not %errorlevel%==0 goto ERR
echo.[%time%] Serverfiles successfully compressed!
echo.[%time%] Serverfiles successfully compressed!>>%logfile%
echo.[%time%] Files where stored in a Selfextracting file named +Yourserver.exe+>>%logfile%
echo.[%time%] Files where stored in a Selfextracting file named +Yourserver.exe+
echo.[%time%] For more info, please have a look at the file makeserver.log
echo.[%time%] For more info, please have a look at the file makeserver.log>>%logfile%
Pause
goto END

:ERR
echo.[%time%] Failure, please have a look at the file makeserver.log
Pause

:END
echo.
explorer.exe ..\..\

rem ### restore original working directory
popd

EXIT

