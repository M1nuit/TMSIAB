<?php

namespace ManiaLivePlugins\MLEPP\Votes;

class Config extends \ManiaLib\Utils\Singleton
{
        public $payToLogin = '';
		public $skipAmount = 50;
		public $restartAmount = 100;
		public $disableVotingOnAdminPresent = false;
		public $disablePayingOnAdminPresent = true;
		public $useQueueRestart = true;
		public $timeout = 60;
        public $chatmessages = true;
        public $maxRestarts = 3;
}
?>
