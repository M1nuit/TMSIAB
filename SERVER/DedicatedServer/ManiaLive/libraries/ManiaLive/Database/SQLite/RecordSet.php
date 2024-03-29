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

namespace ManiaLive\Database\SQLite;

class RecordSet implements \ManiaLive\Database\RecordSet
{
	const FETCH_ASSOC = SQLITE_ASSOC;
	const FETCH_NUM = SQLITE_NUM;
	const FETCH_BOTH = SQLITE_BOTH;
	
	protected $result;
	
	function __construct($result)
	{
		$this->result = $result;
	}
	
	function fetchArray()
	{
		return sqlite_fetch_array($this->result);
	}
	
	function fetchAssoc()
	{
		return sqlite_fetch_array($this->result, self::FETCH_ASSOC);
	}
	
	function fetchObject($className, array $params=array() )
	{
		if (count($params) > 0)
		{
			return sqlite_fetch_object($this->result, $className, $params);
		}
		else
		{
			return sqlite_fetch_object($this->result, $className);
		}
	}
	
	function fetchScalar()
	{
		$row = $this->fetchRow();
		return $row[0];
	}
	
	function fetchRow()
	{
		return sqlite_fetch_array($this->result, self::FETCH_NUM);
	}
	
	function fetchStdObject()
	{
		return sqlite_fetch_object($this->result);
	}
	
	function recordCount()
	{
		return sqlite_num_rows($this->result);
	}
	
	function recordAvailable()
	{
		return $this->recordCount() > 0;
	}
}
?>