<?php
/**
 * ManiaLive - TrackMania dedicated server manager in PHP
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 249 $:
 * @author      $Author: martin.gwendal@gmail.com $:
 * @date        $Date: 2011-08-12 13:41:42 +0200 (ven., 12 août 2011) $:
 */
namespace ManiaLive\Gui\Handler;

use ManiaLive\DedicatedApi\Connection;
use ManiaLive\DedicatedApi\Callback\Listener;
use ManiaLive\Event\Dispatcher;
use ManiaLive\Gui\Handler\DisplayableGroup;
use ManiaLive\DedicatedApi\Structures\Player;

class GuiHandler extends \ManiaLib\Utils\Singleton implements Listener
{
	protected $groups = array();
	protected static $nextNewManialinkId = 1;

	private static $playersDisplayables = array(null => array());
	
	// profiling
	private static $timesSendall;
	public static $avgSendall;
	
	/**
	 * @return \ManiaLive\Gui\Handler\GuiHandler
	 */
	static function getInstance()
	{
		return parent::getInstance();
	}
	
	function __construct()
	{
		Dispatcher::register(\ManiaLive\DedicatedApi\Callback\Event::getClass(), $this);
	}
	
	function onPlayerManialinkPageAnswer($playerUid, $login, $answer, array $entries)
	{		
		// fire new event ...
		Dispatcher::dispatch(new Event($this, $login, $answer, $entries));
	}
	
	/**
	 * This method will check whether a manialink with a given
	 * ID is currently viewed on a player's screen.
	 */
	static function isDisplayed($displayable_id, $login = null)
	{
		if (!key_exists($login, self::$playersDisplayables))
		return false;
		return key_exists($displayable_id, self::$playersDisplayables[$login]);
	}

	/**
	 * Add a Manialink to a group, this group is identified by its recipients
	 * @param Manialink $manialink
	 * @param mixed $recipients it can be null, an int or a string
	 * @return \ManiaLive\Gui\Handler\Group
	 */
	function getGroup()
	{
		if(func_num_args() == 1 && is_array(func_get_arg(0)))
			$recipients = func_get_arg(0);
		else
			$recipients = func_get_args();

		foreach ($recipients as $recipient)
		{
			if(!($recipient instanceof Player))
				throw new \InvalidArgumentException('Recipient must be of Player Type');
		}

		foreach ($this->groups as $group)
		{
			if($group->recipients == $recipients)
			{
				return $group;
			}
		}
		
		$group = new Group();
		$group->recipients = $recipients;
		$this->groups[] = $group;
		return $group;
	}

	/**
	 * Creates a new Id for a manialink
	 * @return int
	 */
	function createManialinkId()
	{
		return self::$nextNewManialinkId++;
	}

	/**
	 * Delivers the manialinks to the players
	 * and remembers which player's currently viewing which manialinks
	 * on their screens ...
	 */
	function sendAll()
	{
		if (empty($this->groups)) return;
		
		$start = microtime(true);
		foreach ($this->groups as $group)
		{
			$group->send();
				
			foreach ($group->displayableGroup->getDisplayables() as $displayable)
			{
				if(count($group->recipients))
				{
					$tmp = array();
					foreach ($group->recipients as $recipient)
					{
						$tmp[] = $recipient->login;
					}
					$login = implode(',', $tmp);
				}
				else
				$login = null;

				// if we're displaying a displayable of type blank
				if (get_class($displayable) == 'ManiaLive\Gui\Displayables\Blank')
				{
					// and there is a displayable already with the same id being displayed
					if (array_key_exists($login, self::$playersDisplayables)
						&& array_key_exists($displayable->getId(), self::$playersDisplayables[$login]))
					{
						// tell the displayable that has been viewed until now that
						// it is being hidden and remove it from the displayed list.
						self::$playersDisplayables[$login][$displayable->getId()]->hide($login);
						unset(self::$playersDisplayables[$login][$displayable->getId()]);
					}
				}
				else
				{
					// if this is anything other than a blank we're not going to hide-callback.
					self::$playersDisplayables[$login][$displayable->getId()] = $displayable;
				}
			}
		}
		self::$timesSendall[] = microtime(true) - $start;
		
		// calculate average for every 100th value
		if (count(self::$timesSendall) >= 10)
		{
			self::$avgSendall = array_sum(self::$timesSendall) / count(self::$timesSendall);
			self::$timesSendall = array();
		}
		
		$this->groups = array();
	}
	
	final static function hideAll()
	{
		Connection::getInstance()->sendDisplayManialinkPage(null, "", 0, false);
	}
	
	// free resources of player ...
	function onPlayerDisconnect($login)
	{
		unset(self::$playersDisplayables[$login]);
	}
	
	function onPlayerConnect($login, $isSpectator) {}
	function onPlayerChat($playerUid, $login, $text, $isRegistredCmd) {}
	function onEcho($internal, $public) {}
	function onServerStart() {}
	function onServerStop() {}
	function onBeginRace($challenge) {}
	function onEndRace($rankings, $challenge) {}
	function onBeginChallenge($challenge, $warmUp, $matchContinuation) {}
	function onEndChallenge($rankings, $challenge, $wasWarmUp, $matchContinuesOnNextChallenge, $restartChallenge) {}
	function onBeginRound() {}
	function onEndRound() {}
	function onStatusChanged($statusCode, $statusName) {}
	function onPlayerCheckpoint($playerUid, $login, $timeOrScore, $curLap, $checkpointIndex) {}
	function onPlayerFinish($playerUid, $login, $timeOrScore) {}
	function onPlayerIncoherence($playerUid, $login) {} 
	function onBillUpdated($billId, $state, $stateName, $transactionId) {}
	function onTunnelDataReceived($playerUid, $login, $data) {}
	function onChallengeListModified($curChallengeIndex, $nextChallengeIndex, $isListModified) {}
	function onPlayerInfoChanged($playerInfo) {}
	function onManualFlowControlTransition($transition) {}
	function onVoteUpdated($stateName, $login, $cmdName, $cmdParam) {}
	function onRulesScriptCallback($param1, $param2) {}
}