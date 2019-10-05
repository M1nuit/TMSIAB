<?php

namespace ManiaLivePlugins\MLEPP\ProfanityFilter;

class Config extends \ManiaLib\Utils\Singleton
{
	public $action = 'mute';
	public $maxAttempts = 3;
	public $wordlist = 'vittu,prkl,perkele,shit,fuck,mother fucker,mtfk,fuk,fuc,sh1t,cunt,asshole,arsehole,ass hole,arse hole,putin';
}
?>
