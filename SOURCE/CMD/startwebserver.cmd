@echo off

rem Scriptfile: 	startwebserver.cmd
rem Destination: 	Start the webserver ^^

rem ### working directory current folder 
pushd %~dp0

cd ..\..\SERVER\USBWebserver_v8_en

start .\usbwebserver.exe

popd
EXIT