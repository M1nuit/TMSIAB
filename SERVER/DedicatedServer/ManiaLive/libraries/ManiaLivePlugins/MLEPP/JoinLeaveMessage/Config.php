<?php
/**
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @version     $Revision$:
 * @author      $Author$:
 * @date        $Date$:
 */

namespace ManiaLivePlugins\MLEPP\JoinLeaveMessage;

class Config extends \ManiaLib\Utils\Singleton
{
        public $standardJoinMsg = '%server%%title% %variable%%nickname%$z$s%spec% %server%[%variable%%country%%server%] [Ladder: %variable%%ladderrank%%server%] joins the server.';
		public $rankedJoinMsg = '%server%%title% %variable%%nickname%$z$s%spec% %server%[%variable%%country%%server%] [Ladder: %variable%%ladderrank%%server%] [Server: %variable%%serverrank%%server%] joins the server.';
		public $adminJoinMsg = '%server%%title% %variable%%nickname%$z$s%spec% %server%[%variable%%country%%server%] [Ladder: %variable%%ladderrank%%server%] joins the server.';
		public $adminRankedJoinMsg = '%server%%title% %variable%%nickname%$z$s%spec% %server%[%variable%%country%%server%] [Ladder: %variable%%ladderrank%%server%] [Server: %variable%%serverrank%%server%] joins the server.';
		public $leaveMsg = '%server%%title% %variable%%nickname%$z$s %server% has left the server.';
}

?>
