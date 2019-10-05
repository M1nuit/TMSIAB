@echo off

rem ****** Set here your php path *******
set INSTPHP=C:\xampp\php
rem *************************************

PATH=%PATH%;%INSTPHP%;%INSTPHP%\extensions
"%INSTPHP%\php.exe" live.php -- 1 http://domain.com/

pause