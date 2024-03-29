<?php
/**
 * ManiaLive - TrackMania dedicated server manager in PHP
 * Based on
 * GbxRemote by Nadeo and
 * IXR - The Incutio XML-RPC Library - (c) Incutio Ltd 2002
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 253 $:
 * @author      $Author: martin.gwendal $:
 * @date        $Date: 2011-08-16 19:39:16 +0200 (mar., 16 août 2011) $:
 */

namespace ManiaLive\DedicatedApi\Xmlrpc;

use ManiaLive\Utilities\Console;

if (!defined('LF'))
{
	define('LF', "\n");
}

if (!defined('SIZE_MAX'))
{
	define('SIZE_MAX', 4096*1024);
}

class Client 
{
	public $socket;
	public $message = false;
	public $cb_message = array();
	public $reqhandle;
	public $protocol = 0;

	static $received;
	static $sent;
	
	function bigEndianTest() 
	{
		list($endiantest) = array_values(unpack('L1L', pack('V', 1)));
		if ($endiantest != 1) 
		{
			if(!function_exists(__NAMESPACE__.'\\unpack'))
			{
				/**
				 * The following code is a workaround for php's unpack function which
				 * does not have the capability of unpacking double precision floats
				 * that were packed in the opposite byte order of the current machine.
				 */
				function unpack($format, $data) 
				{
					$ar = unpack($format, $data);
					$vals = array_values($ar);
					$f = explode('/', $format);
					$i = 0;
					foreach ($f as $f_k => $f_v) 
					{
						$repeater = intval(substr($f_v, 1));
						if ($repeater == 0)
						{
							$repeater = 1;
						}
						if ($f_v{1} == '*') 
						{
							$repeater = count($ar) - $i;
						}
						if ($f_v{0} != 'd') 
						{
							$i += $repeater;
							continue;
						}
						$j = $i + $repeater;
						for ($a = $i; $a < $j; ++$a) 
						{
							$p = pack('d', $vals[$i]);
							$p = strrev($p);
							list($vals[$i]) = array_values(unpack('d1d', $p));
							++$i;
						}
					}
					$a = 0;
					foreach ($ar as $ar_k => $ar_v) 
					{
						$ar[$ar_k] = $vals[$a];
						++$a;
					}
					return $ar;
				}
			}
		}
	}

	function __construct($hostname = 'localhost', $port = 5000) 
	{
		$this->socket = false;
		$this->reqhandle = 0x80000000;
		$this->init($hostname, $port);
	}
	
	function __destruct()
	{
		$this->terminate();
	}

	protected function init($hostname, $port) 
	{

		$this->bigEndianTest();

		// open connection
		$this->socket = @fsockopen($hostname, $port, $errno, $errstr, \ManiaLive\DedicatedApi\Config::getInstance()->timeout);
		if (!$this->socket) 
		{
			throw new Exception("transport error - could not open socket (error: $errno, $errstr)", -32300);
		}
		// handshake
		$array_result = unpack('Vsize', fread($this->socket, 4));
		$size = $array_result['size'];
		if ($size > 64) 
		{
			throw new Exception('transport error - wrong lowlevel protocol header', -32300);
		}
		$handshake = fread($this->socket, $size);
		if ($handshake == 'GBXRemote 1') 
		{
			$this->protocol = 1;
		} 
		elseif ($handshake == 'GBXRemote 2') 
		{
			$this->protocol = 2;
		} 
		else 
		{
			throw new Exception('transport error - wrong lowlevel protocol version', -32300);
		}
	}
	
	function terminate() 
	{
		if ($this->socket) 
		{
			fclose($this->socket);
			$this->socket = false;
		}
	}

	protected function sendRequest(Request $request) 
	{
		$xml = $request->getXml();

		@stream_set_timeout($this->socket, 5);  // timeout 20 s (to write the request)
		// send request
		$this->reqhandle++;
		if ($this->protocol == 1) 
		{
			$bytes = pack('Va*', strlen($xml), $xml);
		} 
		else 
		{
			$bytes = pack('VVa*', strlen($xml), $this->reqhandle, $xml);
		}

		$bytes_to_write = strlen($bytes);
		
		// increase sent counter ...
		self::$sent += $bytes_to_write;
		
		while ($bytes_to_write > 0) 
		{
			$r = fwrite($this->socket, $bytes);
			if ($r === false || $r == 0) 
			{
				throw new Exception('Connection interupted');
			}

			$bytes_to_write -= $r;
			if ($bytes_to_write == 0)
			{
				break;
			}

			$bytes = substr($bytes, $r);
		}
	}

	protected function getResult() 
	{
		$contents = '';
		$contents_length = 0;
		do 
		{
			$size = 0;
			$recvhandle = 0;
			@stream_set_timeout($this->socket, 5);  // timeout 20 s (to read the reply header)
			// Get result
			if ($this->protocol == 1) 
			{
				$contents = fread($this->socket, 4);
				if (strlen($contents) == 0) 
				{
					throw new Exception('transport error - connection interrupted!', -32700);
				}
				$array_result = unpack('Vsize', $contents);
				$size = $array_result['size'];
				$recvhandle = $this->reqhandle;
			} 
			else 
			{
				$contents = fread($this->socket, 8);
				if (strlen($contents) == 0) 
				{
					throw new Exception('transport error - connection interrupted!', -32700);
				}
				$array_result = unpack('Vsize/Vhandle', $contents);
				$size = $array_result['size'];
				$recvhandle = $array_result['handle'];
				// -- amd64 support --
				$bits = sprintf('%b', $recvhandle);
				if (strlen($bits) == 64) 
				{
					$recvhandle = bindec(substr($bits, 32));
				}
			}

			if ($recvhandle == 0 || $size == 0) 
			{
				throw new Exception('transport error - connection interrupted!', -32700);
			}
			
			if ($size > SIZE_MAX) 
			{
				throw new Exception("transport error - answer too big ($size)", -32700);
			}

			self::$received += $size;
			
			$contents = '';
			$contents_length = 0;
			@stream_set_timeout($this->socket, 0, 10000);  // timeout 10 ms (for successive reads until end)
			while ($contents_length < $size) 
			{
				$contents .= fread($this->socket, $size-$contents_length);
				$contents_length = strlen($contents);
			}

			if (($recvhandle & 0x80000000) == 0) 
			{
				// this is a callback, not our answer! handle= $recvhandle, xml-rpc= $contents
				// just add it to the message list for the user to read
				$new_cb_message = new Message($contents);
				if ($new_cb_message->parse() && $new_cb_message->messageType != 'fault') 
				{
					array_push($this->cb_message, array($new_cb_message->methodName, $new_cb_message->params));
				}
			}
		} 
		while ((int)$recvhandle != (int)$this->reqhandle);

		$this->message = new Message($contents);
		if (!$this->message->parse()) 
		{
			// XML error
			throw new Exception('parse error. not well formed', -32700);
		}
		// Is the message a fault?
		if ($this->message->messageType == 'fault') 
		{
			throw new Exception($this->message->faultString, $this->message->faultCode);
		}
		
		return $this->message;
	}


	function query() 
	{
		$args = func_get_args();
		$method = array_shift($args);

		if (!$this->socket || $this->protocol == 0) 
		{
			throw new Exception('transport error - Client not initialized', -32300);
		}

		$request = new Request($method, $args);

		// Check if request is larger than 512 Kbytes
		if ($request->getLength() > 512*1024-8) 
		{
			throw new Exception('transport error - request too large!', -32700);
		}

		$this->sendRequest($request);
		return $this->getResult();
	}

	// Non-blocking query method: doesn't read the response
	function queryIgnoreResult() 
	{
		$args = func_get_args();
		$method = array_shift($args);

		if (!$this->socket || $this->protocol == 0) 
		{
			throw new Exception('transport error - Client not initialized', -32300);
		}

		$request = new Request($method, $args);

		// Check if the request is greater than 512 Kbytes to avoid errors
		// If the method is system.multicall, make two calls (possibly recursively)
		if ($request->getLength() > 512*1024-8) 
		{
			if ($method = 'system.multicall' && isset($args[0])) 
			{
				$count = count($args[0]);
				// If count is 1, query cannot be reduced
				if ($count < 2) 
				{
					throw new Exception('transport error - request too large!', -32700);
				}
				$length = floor($count/2);

				$args1 = array_slice($args[0], 0, $length);
				$args2 = array_slice($args[0], $length, ($count-$length));

				$res1 = $this->queryIgnoreResult('system.multicall', $args1);
				$res2 = $this->queryIgnoreResult('system.multicall', $args2);
				return ($res1 && $res2);
			}
			// If the method is not a multicall, just stop
			else 
			{
				throw new Exception('transport error - request too large!', -32700);
			}
		}

		$this->sendRequest($request);
	}
	
	function getResponse() 
	{
		// methodResponses can only have one param - return that
		return $this->message->params[0];
	}
	
	function readCallbacks($timeout = 2000) 
	{
		if (!$this->socket || $this->protocol == 0) 
			throw new Exception('transport error - Client not initialized', -32300);
		if ($this->protocol == 1)
			return false;

		// flo: moved to end
		//$something_received = count($this->cb_message)>0;
		$contents = '';
		$contents_length = 0;

		@stream_set_timeout($this->socket, 0, 10000);  // timeout 10 ms (to read available data)
		// (assignment in arguments is forbidden since php 5.1.1)
		$read = array($this->socket);
		$write = NULL;
		$except = NULL;
		$nb = false;
		
		try
		{
			$nb = @stream_select($read, $write, $except, 0, $timeout);
		}
		catch (\Exception $e)
		{
			if (strpos($e->getMessage(), 'Invalid CRT') !== false)
			{
				$nb = true;
			}
			elseif (strpos($e->getMessage(), 'Interrupted system call') !== false)
			{
				return;
			}
			else
			{
				throw $e;
			}
		}
		
		// workaround for stream_select bug with amd64
		if ($nb !== false)
		{
			$nb = count($read);
		}

		while ($nb !== false && $nb > 0) 
		{
			$timeout = 0;  // we don't want to wait for the full time again, just flush the available data

			$size = 0;
			$recvhandle = 0;
			// Get result
			$contents = fread($this->socket, 8);
			if (strlen($contents) == 0) 
			{
				throw new Exception('transport error - connection interrupted!', -32700);
			}
			$array_result = unpack('Vsize/Vhandle', $contents);
			$size = $array_result['size'];
			$recvhandle = $array_result['handle'];

			if ($recvhandle == 0 || $size == 0) 
			{
				throw new Exception('transport error - connection interrupted!', -32700);
			}
			if ($size > SIZE_MAX) 
			{
				throw new Exception("transport error - answer too big ($size)", -32700);
			}
			
			self::$received += $size;

			$contents = '';
			$contents_length = 0;
			while ($contents_length < $size) 
			{
				$contents .= fread($this->socket, $size-$contents_length);
				$contents_length = strlen($contents);
			}

			if (($recvhandle & 0x80000000) == 0) 
			{
				// this is a callback. handle= $recvhandle, xml-rpc= $contents
				//echo 'CALLBACK('.$contents_length.')[ '.$contents.' ]' . LF;
				$new_cb_message = new Message($contents);
				if ($new_cb_message->parse() && $new_cb_message->messageType != 'fault') 
				{
					array_push($this->cb_message, array($new_cb_message->methodName, $new_cb_message->params));
				}
				// flo: moved to end ...
				// $something_received = true;
			}

			// (assignment in arguments is forbidden since php 5.1.1)
			$read = array($this->socket);
			$write = NULL;
			$except = NULL;
			
			try
			{
				$nb = @stream_select($read, $write, $except, 0, $timeout);
			}
			catch (\Exception $e)
			{
				if (strpos($e->getMessage(), 'Invalid CRT') !== false)
				{
					$nb = true;
				}
				else
				{
					throw $e;
				}
			}
			
			// workaround for stream_select bug with amd64
			if ($nb !== false)
			{
				$nb = count($read);
			}
		}
		return !empty($this->cb_message);
	}

	function getCallbackResponses() 
	{
		// (look at the end of basic.php for an example)
		$messages = $this->cb_message;
		$this->cb_message = array();
		return $messages;
	}
}

?>