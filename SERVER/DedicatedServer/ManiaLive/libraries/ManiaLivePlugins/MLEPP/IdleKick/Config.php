<?php

namespace ManiaLivePlugins\MLEPP\IdleKick;

class Config extends \ManiaLib\Utils\Singleton
{
        public $specRounds = 2;
		public $kickRounds = 4;
		public $kickMessagePrivate =  '%idlekickcolor%You have been kicked because of being idle!$z';
		public $kickMessagePublic = '%server%IdleKick $fff»» %idlemsgcolor%Kicked player %variable%%nickname%$z$s%idlemsgcolor% after %variable%%idleRounds%%idlemsgcolor% rounds!';
		public $specMessagePublic = '%server%IdleKick $fff»» %idlemsgcolor%Forced player %variable%%nickname%$z$s%idlemsgcolor% into spectator; after %variable%%idleRounds%%idlemsgcolor% rounds!';
		public $kickAdmins = false;
}

?>
