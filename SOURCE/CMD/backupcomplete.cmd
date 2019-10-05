@echo off

rem Scriptfile: 	backupcomplete.cmd
rem Destination: 	Crunsh whole TMSiaB in one File.
rem					User is able to uncrunsh this file everywhere.
rem					The uncrushed files contains the whole TMSiaB.

rem ### working directory current folder 
pushd %~dp0

set logfile="..\..\backup.log"

cd ..\7ZIP\

echo.WINDOWS BACKUP SCRIPT LOGFILE >%logfile%
echo.%date%, %time% >>%logfile%
echo. >>%logfile%
echo.[%time%] Backup in progress, please be patient ...

7za.exe a -t7z ../../TMSiaB_Backup.exe ../../SERVER\ -sfx>>%logfile%
7za.exe a -t7z ../../TMSiaB_Backup.exe ../../SOURCE\ -sfx>>%logfile%
7za.exe a -t7z ../../TMSiaB_Backup.exe ../../TmSiaB4.hta -sfx>>%logfile%
if not %errorlevel%==0 goto ERR
echo.[%time%] Backupfile +TMSiaB_Backup.exe+ successfully completed!
echo.[%time%] For more info, please have a look at the file backup.log
Pause
goto END

:ERR
echo.[%time%] !!!!Backfailure!!!! Please have a look at the file backup.log
Pause

:END
echo.
explorer.exe ..\..\

rem ### restore original working directory
popd

EXIT

