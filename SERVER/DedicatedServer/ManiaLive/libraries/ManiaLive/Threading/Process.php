<?php
/**
 * ManiaLive - TrackMania dedicated server manager in PHP
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 253 $:
 * @author      $Author: martin.gwendal $:
 * @date        $Date: 2011-08-16 19:39:16 +0200 (mar., 16 août 2011) $:
 */

namespace ManiaLive\Threading;

use ManiaLive\Config\Loader;
use ManiaLive\Utilities\Logger;
use ManiaLive\Database\SQLite\Connection;

/**
 * This class is running in it's own process and is
 * being instanciated by the thread_ignitor.php.
 * A Process is being represented by a Thread on the "server" side.
 * 
 * @author Florian Schnell
 */
class Process
{
	private $id;
	private $db;
	private $incomingJob;
	private $incomingJobCount;
	private $parent;
	
	/**
	 * Static function to set ready state.
	 * @param integer $pid
	 */
	function setReady()
	{
		$this->setBusy(0);
	}
	
	/**
	 * Static function to set busy state.
	 * @param integer $pid
	 */
	function setBusy($busy_flag = true)
	{
		$this->db->execute("UPDATE threads SET last_beat=" . (time()+60) . ", busy=" . intval($busy_flag) . " WHERE proc_id=" . $this->id);
	}
	
	/**
	 * Static function to set last thread activity.
	 * @param integer $pid
	 */
	function setLastBeat()
	{
		$this->db->execute("UPDATE threads SET last_beat=" . (time()+60) . " WHERE proc_id=" . $this->id);
	}
	
	/**
	 * Static function to set closed state.
	 * @param integer $pid
	 */
	function setClosed()
	{
		echo 'Closed Thread!' . APP_NL;
		$this->db->execute("UPDATE threads SET state=3 WHERE proc_id=" . $this->id);
	}
	
	function __construct($pid, $parent)
	{
		$this->id = $pid;
		$this->parent = $parent;
		
		// print first message from thread ...
		echo "Thread started successfully!" . APP_NL;
		
		// when script terminates call this function to
		// update the thread's status ...
		register_shutdown_function(array($this, 'setClosed'));
		
		// connect to database ...
		$this->db = Tools::getDb($this->parent);
		if ($this->db->isConnected())
		{
			echo "DB is connected, waiting for jobs ..." . APP_NL;
		}
		
		$this->incomingJob = null;
		
		// get configuration ...
		\ManiaLive\Config\Config::forceInstance(Tools::getData($this->db, 'config'));
		\ManiaLive\Database\Config::forceInstance(Tools::getData($this->db, 'database'));
		\ManiaHome\Config::forceInstance(Tools::getData($this->db, 'maniahome'));
		\ManiaLive\Application\Config::forceInstance(Tools::getData($this->db, 'manialive'));
		\ManiaLive\DedicatedApi\Config::forceInstance(Tools::getData($this->db, 'server'));
		\ManiaLive\Threading\Config::forceInstance(Tools::getData($this->db, 'threading'));
		
		// thread state is ready ...
		$this->setReady();
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $cmd
	 * @param unknown_type $return_value
	 */
	function returnResult($cmd_id, $return_value)
	{
		//$return_value = array($return_value, ($this->incoming_job_count == 0));
		$this->db->execute("UPDATE cmd SET done=1, result='".base64_encode(serialize($return_value))."' WHERE cmd_id=".$cmd_id);
		echo "Result saved rows:" . $this->db->affectedRows() . APP_NL;
	}
	
	/**
	 * Checks the threading database Connection for new
	 * commands addressed to itself.
	 * If so, get them, process them and return the result.
	 */
	function getWork()
	{
		// query db for jobs ...
		$result = $this->db->query("SELECT cmd_id, cmd, param FROM cmd WHERE done=0 AND proc_id=" . $this->id . " ORDER BY datestamp DESC");
		$this->incomingJobCount = $result->recordCount();
		
		if ($this->incomingJobCount > 0)
		{
			echo 'Incoming Jobs: ' . $this->incomingJobCount . APP_NL;
		}
		else
		{
			return false;
		}
		
		// process incoming jobs ...
		while ($this->incomingJob = $result->fetchArray())
		{
			$this->incomingJobCount--;
			
			// this will save some writing ...
			$cmd = $this->incomingJob['cmd'];
			$cmd_id = $this->incomingJob['cmd_id'];
			$cmd_param = $this->incomingJob['param'];
			
			echo 'Got Command: ' . $cmd . APP_NL;
			
			switch ($cmd)
			{
				case 'ping':
					
					// ping returns always true ...
					$this->returnResult($cmd_id, true);
					
					break;
				
				case 'run':
					
					// update thread status ...
					$this->setBusy();
					
					// start job processing ...
					echo "Processing Command ID: " . $cmd_id . APP_NL;
					
					// process incoming job ...
					// __autoload is automatically triggered on unserialize!
					$job = unserialize(base64_decode($cmd_param));
					$return_value = $job->run();
					
					//store results
					$this->returnResult($cmd_id, $return_value);		
					
					// update thread status ...
					$this->setReady();
					
					break;
					
				case 'exit':
					
					exit();
	
					break;
			}
		}

		// reset.
		$this->incomingJob = null;
		return true;
	}
}