<?php

namespace ManiaLivePlugins\MLEPP\Localrecords;

class Config extends \ManiaLib\Utils\Singleton
{
    public $numrec = 100;
	public $showChatMessages = true;
	public $showChatMessageOnBeginRace = true;
	public $lapsModeCount1lap = true;
	public $maxRecsDisplayed = 30;
	public $showChatRecords = true;
	public $newRecordChat = '%winnercolor%%nickname%$z$s%recordcolor% claimed the %variable%%newrank%. %recordcolor%Local Record with time: %variable%%score%%recordcolor%!';
	public $newRecordPrivate = '%recordcolor%You claimed the %variable%%newrank%. %recordcolor%Local Record with time: %variable%%score%%recordcolor%!';
	public $securedRecordChat = '%winnercolor%%nickname%$z$s%recordcolor% secured his %variable%%newrank%. %recordcolor%Local Record with time: %variable%%score%%recordcolor% (%variable%-%diff%%recordcolor%)!';
	public $securedRecordPrivate = '%recordcolor%You secured your %variable%%newrank%. %recordcolor%Local Record with time: %variable%%score%%recordcolor% (%variable%-%diff%%recordcolor%)!';
	public $gainedRecordChat = '%winnercolor%%nickname%$z$s%recordcolor% gained the %variable%%newrank%. %recordcolor%Local Record with time: %variable%%score%%recordcolor% (%variable%%oldrank%. -%diff%%recordcolor%)!';
	public $gainedRecordPrivate = '%recordcolor%You gained the %variable%%newrank%. %recordcolor%Local Record with time: %variable%%score%%recordcolor% (%variable%%oldrank%. -%diff%%recordcolor%)!';
	public $equalRecordChat = '%winnercolor%%nickname% $z$s%recordcolor% equaled his %variable%%newrank. %recordcolor%Local Record with time: %variable%%score%%recordcolor%!';
	public $equalRecordPrivate = '%recordcolor%You equaled your %variable%%newrank. %recordcolor%Local Record with time: %variable%%score%%recordcolor%!';
}

?>
