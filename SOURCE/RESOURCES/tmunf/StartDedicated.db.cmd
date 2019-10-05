@echo off

rem Scriptfile: 	StartDedicated01.cmd
rem Destination: 	Start the Dedicated Server


rem ### working directory current folder 
set prompt=$G
pushd %~dp0

start "//Trackmania Forever//DNEIGHBORHOOD//" .\TrackmaniaServer.exe /dedicated_cfg=DEDICATEDCFG /game_settings=MatchSettings/TRACKLISTTXT /noautoquit /nodaemon /INTERNETORLAN


rem ### restore original working directory
popd

rem EXIT