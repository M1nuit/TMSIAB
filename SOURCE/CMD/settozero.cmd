@echo off

rem Scriptfile: 	settozero.cmd
rem Destination: 	Delete ALL Files created since intallation of TMSiaB


rem ### working directory current folder 
pushd %~dp0

del ..\..\SERVER\USBWebserver_v8_en\root\remoteCP4035\cache\*.log /Q /F
del ..\..\SERVER\USBWebserver_v8_en\root\remoteCP4035\xml\servers.xml /Q /F
del ..\..\SERVER\USBWebserver_v8_en\root\remoteCP4035\xml\admins.xml /Q /F
del ..\..\SERVER\USBWebserver_v8_en\root\remoteCP4035\*.xml /Q /F
del ..\..\SERVER\_Start00Webserver.cmd /Q /F

del ..\RESOURCES\rcp\servers.db2.source.xml /Q /F
del ..\RESOURCES\rcp\servers.db3.source.xml /Q /F

del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Config\dedicated_cfg01.txt /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Config\dedicated_cfg02.txt /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Config\dedicated_cfg03.txt /Q /F

del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\_StartDedicated01.cmd /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\_StartDedicated02.cmd /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\_StartDedicated03.cmd /Q /F
del ..\..\SERVER\_StartDedicated01.cmd /Q /F
del ..\..\SERVER\_StartDedicated02.cmd /Q /F
del ..\..\SERVER\_StartDedicated03.cmd /Q /F

rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\Logs /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Cache\ManiaCode /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Manialinks /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Cache /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\ChallengeMusics /S /Q

del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Config\pkey.dat /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Config\Default.SystemConfig.Gbx /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Config\checksum.txt /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Config\tmublacklist.txt /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Config\tmuguestlist.txt /Q /F


rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\MediaTracker /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\MenuMusics /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Painter /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Profiles /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\QuestItems /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\RollingDemo /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Scores /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Skins /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\Challenges /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\Replays /S /Q
rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\Campaigns\Downloaded /S /Q
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\trackhist.txt /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\MatchSettings\mistral.txt /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\MatchSettings\last.*.*.txt /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Game.FidCache.Gbx /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\MatchSettings\tracklist01.txt /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\MatchSettings\tracklist02.txt /Q /F
del ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\MatchSettings\tracklist03.txt /Q /F


del ..\..\SERVER\USBWebserver_v8_en\root\remoteCP4035\_Live01Start.cmd /Q /F
del ..\..\SERVER\USBWebserver_v8_en\root\remoteCP4035\_Live02Start.cmd /Q /F
del ..\..\SERVER\USBWebserver_v8_en\root\remoteCP4035\_Live03Start.cmd /Q /F

rmdir ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\ChallengeMusics /S /Q

del ..\..\SERVER\DedicatedServer\MistralAseco81\config.xml /Q /F
del ..\..\SERVER\DedicatedServer\MistralAseco81\includes\rasp_settings.php /Q /F
del ..\..\SERVER\DedicatedServer\MistralAseco81\_MistralAseco01Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\MistralAseco81\_MistralAseco02Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\MistralAseco81\_MistralAseco03Start.cmd /Q /F

del ..\..\SERVER\DedicatedServer\XAseco114\_XAseco01Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\XAseco114\_XAseco02Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\XAseco114\_XAseco03Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\XAseco114\adminops.xml /Q /F
del ..\..\SERVER\DedicatedServer\XAseco114\config.xml /Q /F
del ..\..\SERVER\DedicatedServer\XAseco114\dedimania.xml /Q /F
del ..\..\SERVER\DedicatedServer\XAseco114\localdatabase.xml /Q /F
del ..\..\SERVER\DedicatedServer\XAseco114\includes\jfreu.config.php /Q /F
del ..\..\SERVER\DedicatedServer\XAseco114\includes\logfile.txt /Q /F

del ..\..\SERVER\DedicatedServer\FAST322\store.*.*.fast /Q /F
del ..\..\SERVER\DedicatedServer\FAST322\votes.xml.txt /Q /F
del ..\..\SERVER\DedicatedServer\FAST322\admin.*.*.*.txt /Q /F
del ..\..\SERVER\DedicatedServer\FAST322\dedicated_cfg01.txt /Q /F
del ..\..\SERVER\DedicatedServer\FAST322\dedicated_cfg02.txt /Q /F
del ..\..\SERVER\DedicatedServer\FAST322\dedicated_cfg03.txt /Q /F
del ..\..\SERVER\DedicatedServer\FAST322\_FAST01Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\FAST322\_FAST02Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\FAST322\_FAST03Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\FAST322\fastlog\*.txt /Q /F

del ..\..\SERVER\DedicatedServer\ManiaLive\config\config.ini /Q /F
del ..\..\SERVER\DedicatedServer\ManiaLive\logs\*.* /Q /F
del ..\..\SERVER\DedicatedServer\ManiaLive\_ManiaLive01Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\ManiaLive\_ManiaLive02Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\ManiaLive\_ManiaLive03Start.cmd /Q /F

del ..\..\SERVER\DedicatedServer\FoxControl\_FoxControl01Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\FoxControl\_FoxControl02Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\FoxControl\_FoxControl03Start.cmd /Q /F
del ..\..\SERVER\DedicatedServer\FoxControl\control01.php /Q /F
del ..\..\SERVER\DedicatedServer\FoxControl\control02.php /Q /F
del ..\..\SERVER\DedicatedServer\FoxControl\control03.php /Q /F
del ..\..\SERVER\DedicatedServer\FoxControl\config01.xml /Q /F
del ..\..\SERVER\DedicatedServer\FoxControl\config02.xml /Q /F
del ..\..\SERVER\DedicatedServer\FoxControl\config03.xml /Q /F
del ..\..\SERVER\DedicatedServer\FoxControl\logs\*.* /Q /F
del ..\..\SERVER\_Startplugin01.cmd /Q /F
del ..\..\SERVER\_Startplugin02.cmd /Q /F
del ..\..\SERVER\_Startplugin03.cmd /Q /F

xcopy ..\CONFIGURATION\config.old  ..\CONFIGURATION\config.xml /k /r /c /h /y
xcopy ..\RESOURCES\tmunf\tracklist01.txt ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\MatchSettings\ /k /r /e /i /s /c /h /y
xcopy ..\RESOURCES\tmunf\tracklist02.txt ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\MatchSettings\ /k /r /e /i /s /c /h /y
xcopy ..\RESOURCES\tmunf\tracklist03.txt ..\..\SERVER\DedicatedServer\TrackmaniaServer_2011-02-11\GameData\Tracks\MatchSettings\ /k /r /e /i /s /c /h /y
rem ### restore original working directory
popd

rem EXIT

