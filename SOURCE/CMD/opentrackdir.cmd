@echo off

rem Scriptfile: 	opentrackdir.cmd
rem Destination: 	Open Dedicated Trackdir with Windowsexplorer


rem ### working directory current folder 
pushd %~dp0

explorer.exe ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks

rem ### restore original working directory
popd

EXIT

