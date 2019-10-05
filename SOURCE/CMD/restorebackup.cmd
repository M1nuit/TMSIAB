@echo off

rem Scriptfile: 	restorebackup.cmd
rem Destination: 	Restore whole TMSiaB from previously created Backup.


rem ### working directory current folder 
pushd %~dp0

set logfile=".\restore.log"

cd ..\..\

echo.WINDOWS RESTORE BACKUP SCRIPT LOGFILE >%logfile%
echo.%date%, %time% >>%logfile%
echo. >>%logfile%
echo.[%time%] Restore in progress, please be patient ...

TMSiaB_Backup.exe -y>>%logfile%

if not %errorlevel%==0 goto ERR
echo.[%time%] Restoring Backup successfully completed!
echo.[%time%] For more info, please have a look at the file restore.log
echo.
echo.[%time%] Trying to shutdown TMSiaB, you have to restart it manually!>>%logfile%
echo.[%time%] Trying to shutdown TMSiaB, you have to restart it manually!
pause
.\SOURCE\PSKILL\pskill.exe mshta.exe
goto END

:ERR
echo.[%time%] !!!!Restorefailure!!!! Please have a look at the file restore.log
Pause

:END
echo.
rem ### restore original working directory
popd

EXIT

