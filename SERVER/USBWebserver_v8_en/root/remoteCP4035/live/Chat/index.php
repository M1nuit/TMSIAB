<?php
/**
* remoteCP 4
* ütf-8 release
*
* @package remoteCPlive
* @author hal.sascha
* @copyright (c) 2006-2009
* @version 4.0.3.5
*/
class Chat extends rcp_liveplugin
{
	public  $title		= 'Chat';
	public  $author		= 'hal.ko.sascha';
	public  $version	= '4.0.3.5';
  
	public function onLoad()
	{
		Core::getObject('chat')->addCommand('gg'			, 'onChatgg'			, 'Displays a goodgame message'					, '/gg Playername');
		Core::getObject('chat')->addCommand('hi'			, 'onChathi'			, 'Displays a hello message'					, '/hi Playername');
		Core::getObject('chat')->addCommand('bye'			, 'onChatbye'			, 'Displays a bye message'						, '/bye everybody');
		Core::getObject('chat')->addCommand('gl'			, 'onChatgl'			, 'Displays a good luck message'				, '/gl Team');
		Core::getObject('chat')->addCommand('hf'			, 'onChathf'			, 'Displays a have fun message'					, '/hf Team');
		Core::getObject('chat')->addCommand('glhf'			, 'onChatglhf'			, 'Displays a good luck and have fun message'	, '/glhf Team');
		Core::getObject('chat')->addCommand('fuck'			, 'onChatfuck'			, 'Displays a fuck message...'					, '/fuck');
		Core::getObject('chat')->addCommand('serverlogin'	, 'onChatserverlogin'	, 'Displays the serverlogin...'					, '/serverlogin');
	}

	public function onChatgg($cmd)
	{
		Core::getObject('chat')->send('!cm!Good game !hl!'.$cmd[1], $cmd[0]);
	}

	public function onChathi($cmd)
	{
		Core::getObject('chat')->send('!cm!Hello !hl!'.$cmd[1], $cmd[0]);
	}

	public function onChatbye($cmd)
	{
		Core::getObject('chat')->send('!cm!Good bye !hl!'.$cmd[1], $cmd[0]);
	}

	public function onChatgl($cmd)
	{
		Core::getObject('chat')->send('!cm!Good luck !hl!'.$cmd[1], $cmd[0]);
	}

	public function onChathf($cmd)
	{
		Core::getObject('chat')->send('!cm!Have fun !hl!'.$cmd[1], $cmd[0]);
	}

	public function onChatglhf($cmd)
	{
		Core::getObject('chat')->send('!cm!Good luck and have fun !hl!'.$cmd[1], $cmd[0]);
	}

	public function onChatfuck($cmd)
	{
		Core::getObject('chat')->send('!cm!$w$b$iFUCK!!!!', $cmd[0]);
	}
}
?>