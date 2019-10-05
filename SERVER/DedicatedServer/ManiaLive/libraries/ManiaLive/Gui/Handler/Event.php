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

class Event extends \ManiaLive\Event\Event
{
	protected $action;
	protected $login;
	protected $answer;
	
	function __construct($source, $login, $action, array $answer)
	{
		parent::__construct($source);
		
		$this->action = $action;
		$this->login = $login;
		$this->answer = $answer;
	}
	
	function fireDo($listener)
	{
		$listener->onActionClick($this->login, $this->action, $this->answer);
	}
}

?>