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

namespace ManiaLive\PluginHandler;

use ManiaLive\Cache\Entry;
use ManiaLive\Cache\Cache;
use ManiaLive\Utilities\Logger;
use ManiaLive\Application\FatalException;
use ManiaLive\Config\Loader;
use ManiaLive\Event\Dispatcher;
use ManiaLive\Data\Storage;
use ManiaLive\Features\ChatCommand\Interpreter;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Gui\Toolkit\Cards\Dialog;
use ManiaLive\Gui\Displayables\Blank;
use ManiaLive\Gui\Toolkit\Elements\Bgs1;
use ManiaLive\Gui\Displayables\Advanced;
use ManiaLive\Gui\Handler\GuiHandler;
use ManiaLive\Database\Connection as DbConnection;
use ManiaLive\Utilities\Console as Console;
use ManiaLive\GuiHandler\GuiToolkit;
use ManiaLive\DedicatedApi\Connection;

/**
 * Extend this class to create a Plugin that can be used with the
 * PluginHandler.
 * This will also provide function shortcuts for registering chat commands,
 * dependency handling and the possibility of Plugin communication.
 * To have a Plugin loaded, just attach it to the pluginhandler.xml which is
 * located in the config folder.
 * 
 * @author Florian Schnell
 */
abstract class Plugin extends \ManiaLive\DedicatedApi\Callback\Adapter implements \ManiaLive\Threading\Listener, \ManiaLive\Gui\Windowing\Listener, \ManiaLive\Features\Tick\Listener, \ManiaLive\Application\Listener, \ManiaLive\Data\Listener, \ManiaLive\PluginHandler\Listener, \ManiaLive\Cache\Listener
{

	private $uid;
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $author;
	/**
	 * @var integer
	 */
	private $version;
	/**
	 * @var array[Dependency]
	 */
	private $dependencies;
	/**
	 * Event subscriber swichtes
	 */
	private $eventsApplication;
	private $eventsThreading;
	private $eventsWindowing;
	private $eventsTick;
	private $eventsServer;
	private $eventsStorage;
	private $eventsPlugins;
	private $eventsCaching;
	/**
	 * @var ManiaLive\PluginHandler\PluginHandler
	 */
	private $pluginHandler;
	/**
	 * @var array[\ReflectionMethod]
	 */
	private $methods;
	/**
	 * @var array
	 */
	private $settings;
	/**
	 * @var \ManiaLive\Threading\ThreadPool
	 */
	private $threadPool;
	/**
	 * @var integer
	 */
	private $threadId;
	/**
	 * @var array[\ManiaLive\Features\ChatCommand\Command]
	 */
	private $chatCommands;
	/**
	 * @var ManiaLive\Data\Storage
	 */
	protected $storage;
	/**
	 * @var \ManiaLive\DedicatedApi\Connection
	 */
	protected $connection;
	/**
	 * @var \ManiaLive\Database\Connection
	 */
	protected $db;

	final function __construct($plugin_id)
	{
		$this->settings = array();
		$this->eventsApplication = false;
		$this->eventsThreading = false;
		$this->eventsTick = false;
		$this->eventsWindowing = false;
		$this->eventsPlugins = false;

		$this->dependencies = array();
		$this->methods = array();

		$classPath = get_class($this);
		$items = explode('\\', $classPath);

		$this->uid = uniqid();

		$this->id = $plugin_id;
		array_shift($items);
		array_pop($items);
		$this->name = array_pop($items);
		$this->author = array_shift($items);
		$this->setVersion(1);

		$this->connection = Connection::getInstance();



		$this->pluginHandler = PluginHandler::getInstance();
		$this->storage = Storage::getInstance();
		$this->threadPool = \ManiaLive\Threading\ThreadPool::getInstance();
		$this->threadId = false;
		$this->chatCommands = array();
	}

	final protected function getUid()
	{
		return $this->uid;
	}

	// TODO maybe tell the plugin handler here that the plugin did successfully unload?
	function __destruct()
	{
//		echo "plugn " . get_called_class() . " successfully unloaded!\n";
	}

	/**
	 * This will unregister all chat commands that have been
	 * created using the plugins method registerChatCommand.
	 * @see \ManiaLive\PluginHandler\Plugin::registerChatCommand
	 */
	final public function unregisterAllChatCommands()
	{
		while($command = array_pop($this->chatCommands))
		{
			Interpreter::getInstance()->unregister($command);
		}
	}

	/**
	 * Sets the current version number for this Plugin.
	 * Can only be used during initialization!
	 * @param integer $version
	 * @throws \InvalidArgumentException
	 */
	final protected function setVersion($version)
	{
		$this->version = $version;
	}

	/**
	 * Returns the version number of the Plugin.
	 * @return integer
	 */
	final public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Returns the name of the Plugin.
	 * @return string
	 */
	final public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns author\name combination for identification.
	 * @return string
	 */
	final public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns the author of the Plugin.
	 * @return string
	 */
	final public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * Adds a Dependency to the Plugin.
	 * Can only be used during initialisation!
	 * @param ManiaLive\PluginHandler\Dependency $dependency
	 */
	final public function addDependency(Dependency $dependency)
	{
		$this->dependencies[] = $dependency;
	}

	/**
	 * Returns an array of all known dependencies of this Plugin.
	 * @return array[ManiaLive\PluginHandler\Dependency]
	 */
	final public function getDependencies()
	{
		return $this->dependencies;
	}

	/**
	 * Declare this method as public.
	 * It then can be called by other Plugins.
	 * @param string $name The name of the method you want to expose.
	 * @throws Exception
	 */
	final protected function setPublicMethod($name)
	{
		try
		{
			$method = new \ReflectionMethod($this, $name);

			if(!$method->isPublic())
			{
				throw new Exception('The method "'.$name.'" must be declared as public!');
			}

			$this->methods[$name] = $method;
		}
		catch(\ReflectionException $ex)
		{
			throw new Exception('The method "'.$name.'" does not exist and therefor can not be exposed!');
		}
	}

	/**
	 * Calls a public method of the specified plugin.
	 * The method has been marked as public by the owner.
	 * The plugin has to be registered at the plugin handler.
	 * @param string $plugin_name
	 * @param string $method_name
	 */
	final protected function callPublicMethod($plugin_id, $method_name)
	{
		$this->restrictIfUnloaded();
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		return $this->pluginHandler->callPublicMethod($this, $plugin_id,
			$method_name, $args);
	}

	/**
	 * Gets a method, that has been marked as public, from this Plugin.
	 * This method will be invoked by the Plugin Handler.
	 * If you want to call a method from another Plugin, then use the internal callPublicMethod function.
	 * @param \ReflectionMethod $method_name
	 * @throws Exception
	 */
	final public function getPublicMethod($method_name)
	{
		if(isset($this->methods[$method_name]))
		{
			return $this->methods[$method_name];
		}
		else
		{
			throw new Exception("The method '$method_name' does not exist or has not been set public for plugin '{$this->name}'!");
		}
	}

	/**
	 * Returns a list of the commands that are exposed by this plugin.
	 * @return array An array with the keys: name, parameter_count, parameters
	 */
	final public function getPublicMethods()
	{
		$methods = array();
		foreach($this->methods as $name => $method)
		{
			$info = array
				(
				'name' => $name,
				'parameter_count' => $method->getNumberOfParameters(),
				'parameters' => array()
			);

			$parameters = $method->getParameters();
			foreach($parameters as $parameter)
			{
				if($parameter->allowsNull())
				{
					$info['parameters'][] = '['.$parameter->name.']';
				}
				else
				{
					$info['parameters'][] = $parameter->name;
				}
			}

			$methods[] = $info;
		}
		return $methods;
	}

	/**
	 * This method can be used to restrict a call to a specific method
	 * until the plugin has been loaded successfully!
	 * @throws \Exception
	 */
	final private function restrictIfUnloaded()
	{
		if(!$this->isLoaded())
		{
			$trace = debug_backtrace();
			throw new \Exception("The method '{$trace[1]['function']}' can not be called before the Plugin '".$this->getId()."' has been loaded!");
		}
	}

	/**
	 * Checks whether the current plugin has been loaded.
	 * @return bool
	 */
	final public function isLoaded()
	{
		return $this->isPluginLoaded($this->getId());
	}

	/**
	 * Is the plugin currently loaded or not?
	 * @param string $name
	 * @return bool
	 */
	final public function isPluginLoaded($plugin_id, $min = Dependency::NO_LIMIT,
		$max = Dependency::NO_LIMIT)
	{
		return $this->pluginHandler->isPluginLoaded($plugin_id, $min, $max);
	}

	/**
	 * Retrieve an array of all the public methods
	 * of a specific plugin.
	 * @param string $plugin_id
	 */
	final public function getPluginPublicMethods($plugin_id)
	{
		return $this->pluginHandler->getPublicMethods($plugin_id);
	}

	// Helpers
	final function enableDatabase()
	{
		$config = \ManiaLive\Database\Config::getInstance();
		$this->db = \ManiaLive\Database\Connection::getConnection(
				$config->host,
				$config->username,
				$config->password,
				$config->database,
				$config->type,
				$config->port
		);
	}
	
	final function disableDatabase()
	{
		$this->db = null;
	}

	/**
	 * Start invoking methods for application intern events which are
	 * onInit, onRun, onPreLoop, onPostLoop, onTerminate
	 */
	final function enableApplicationEvents()
	{
		$this->restrictIfUnloaded();
		if(!$this->eventsApplication)
		{
			Dispatcher::register(\ManiaLive\Application\Event::getClass(), $this);
		}
		$this->eventsApplication = true;
	}

	/**
	 * Stop listening for application events.
	 */
	final function disableApplicationEvents()
	{
		$this->restrictIfUnloaded();
		Dispatcher::unregister(\ManiaLive\Application\Event::getClass(), $this);
		$this->eventsApplication = false;
	}

	/**
	 * Start invoking the ticker method (onTick) every second.
	 */
	final function enableTickerEvent()
	{
		$this->restrictIfUnloaded();
		if(!$this->eventsTick)
		{
			Dispatcher::register(\ManiaLive\Features\Tick\Event::getClass(), $this);
		}
		$this->eventsTick = true;
	}

	/**
	 * Stop listening for the ticker event.
	 */
	final function disableTickerEvent()
	{
		$this->restrictIfUnloaded();
		Dispatcher::unregister(\ManiaLive\Features\Tick\Event::getClass(), $this);
		$this->eventsTick = false;
	}

	/**
	 * Start invoking methods for extern dedicated server events which are
	 * the callbacks described in the ListCallbacks.html which you have retrieved with your
	 * dedicated server.
	 * Otherwise you can find an online copy here http://server.xaseco.org/callbacks.php
	 */
	final function enableDedicatedEvents()
	{
		$this->restrictIfUnloaded();
		if(!$this->eventsServer)
		{
			Dispatcher::register(\ManiaLive\DedicatedApi\Callback\Event::getClass(),
				$this);
		}
		$this->eventsServer = true;
	}

	/**
	 * Stop listening for dedicated server events.
	 */
	final function disableDedicatedEvents()
	{
		$this->restrictIfUnloaded();
		Dispatcher::unregister(\ManiaLive\DedicatedApi\Callback\Event::getClass(),
			$this);
		$this->eventsServer = false;
	}

	/**
	 * Start listening for Storage events:
	 * onPlayerNewBestTime, onPlayerNewRank, onPlayerNewBestScore.
	 */
	final function enableStorageEvents()
	{
		$this->restrictIfUnloaded();
		if(!$this->eventsStorage)
		{
			Dispatcher::register(\ManiaLive\Data\Event::getClass(), $this);
		}
		$this->eventsStorage = true;
	}

	/**
	 * Stop listening for Storage Events.
	 */
	final function disableStorageEvents()
	{
		$this->restrictIfUnloaded();
		Dispatcher::unregister(\ManiaLive\Data\Event::getClass(), $this);
		$this->eventsStorage = false;
	}

	/**
	 * Starts to listen for Window events like:
	 * onWindowClose
	 */
	final function enableWindowingEvents()
	{
		$this->restrictIfUnloaded();
		if(!$this->eventsWindowing)
		{
			Dispatcher::register(\ManiaLive\Gui\Windowing\Event::getClass(), $this);
		}
		$this->eventsWindowing = true;
	}

	/**
	 * Stop listening for Window events.
	 */
	final function disableWindowingEvents()
	{
		$this->restrictIfUnloaded();
		Dispatcher::unregister(\ManiaLive\Gui\Windowing\Event::getClass(), $this);
		$this->eventsWindowing = false;
	}

	/**
	 * Starts listening for threading events like:
	 * onThreadStart, onThreadRestart, onThreadDies, onThreadTimeOut
	 */
	final function enableThreadingEvents()
	{
		$this->restrictIfUnloaded();
		if(!$this->eventsThreading)
		{
			Dispatcher::register(\ManiaLive\Threading\Event::getClass(), $this);
		}
		$this->eventsThreading = true;
	}

	/**
	 * Stop listening for threading events.
	 */
	final function disableThreadingEvents()
	{
		$this->restrictIfUnloaded();
		Dispatcher::unregister(\ManiaLive\Threading\Event::getClass(), $this);
		$this->eventsThreading = false;
	}

	/**
	 * Start listen for plugin events like
	 * onPluginLoaded and onPluginUnloaded
	 */
	final function enablePluginEvents()
	{
		$this->restrictIfUnloaded();
		if(!$this->eventsPlugins)
		{
			Dispatcher::register(\ManiaLive\PluginHandler\Event::getClass(), $this);
		}
		$this->eventsPlugins = true;
	}

	/**
	 * stop to listen for plugin events.
	 */
	final function disablePluginEvents()
	{
		$this->restrictIfUnloaded();
		Dispatcher::unregister(\ManiaLive\PluginHandler\Event::getClass(), $this);
		$this->eventsPlugins = false;
	}

	/**
	 * Start listen for cache events like
	 * onStore, onModify and onDestroy
	 */
	final function enableCachingEvents()
	{
		$this->restrictIfUnloaded();
		if(!$this->eventsCaching)
		{
			Dispatcher::register(\ManiaLive\Cache\Event::getClass(), $this);
		}
		$this->eventsCaching = true;
	}

	/**
	 * Stop listen for cache events.
	 */
	final function disableCachingEvents()
	{
		$this->restrictIfUnloaded();
		Dispatcher::unregister(\ManiaLive\Cache\Event::getClass(), $this);
		$this->eventsCaching = false;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param integer $timeToLive
	 */
	final function store($key, $value, $timeToLive = null)
	{
		return Cache::storeInModuleCache($this, $key, $value, $timeToLive);
	}

	/**
	 * Fetches data from the cache.
	 * @param string $key
	 */
	final function fetch($pluginId, $key)
	{
		return $this->pluginHandler->fetchPluginCacheEntry($pluginId, $key);
	}

	/**
	 * Checks whether there is a cache entry with
	 * the given key.
	 * @param string $pluginId
	 * @param string $key
	 * @return bool If the plugin is not found it will return NULL
	 */
	final function exists($pluginId, $key)
	{
		return $this->pluginHandler->existsPluginCacheEntry($pluginId, $key);
	}

	/**
	 * Fetch value from own cache.
	 * @param string $key
	 */
	final function fetchOwn($key)
	{
		return Cache::fetchFromModuleCache($this, $key);
	}

	/**
	 * Creates a new Thread.
	 * @return integer
	 */
	protected function createThread()
	{
		if($this->threadId === false)
		{
			$this->threadId = $this->threadPool->createThread();
		}
		else
		{
			return false;
		}
		return $this->threadId;
	}

	/**
	 * Gets the thread that belongs to this
	 * plugin and returns its id.
	 * @return integer
	 */
	function getThreadId()
	{
		return $this->threadId;
	}

	/**
	 * Kills the plugin's thread.
	 * @return bool
	 */
	function killThread()
	{
		if($this->threadId !== false)
		{
			return $this->threadPool->removeThread($this->threadId);
		}
		return false;
	}

	/**
	 * Assigns work only to the thread that has been created by this plugin.
	 * @param \ManiaLive\Threading\Runnable $work
	 */
	protected function sendWorkToOwnThread(\ManiaLive\Threading\Runnable $work,
		$callback = null)
	{
		if($callback != null)
		{
			$callback = array($this, $callback);
		}
		if($this->threadId !== false)
		{
			$this->threadPool->addCommand(new \ManiaLive\Threading\Commands\RunCommand($work, $callback),
				$this->threadId);
		}
	}

	/**
	 * Assign work to a thread.
	 * @param \ManiaLive\Threading\Runnable $work
	 */
	protected function sendWorkToThread(\ManiaLive\Threading\Runnable $work,
		$callback = null)
	{
		$command = null;
		if($callback != null)
		{
			$callback = array($this, $callback);
		}
		if($this->threadPool->getThreadCount() > 0)
		{
			$command = new \ManiaLive\Threading\Commands\RunCommand($work, $callback);
			$this->threadPool->addCommand($command);
		}
		return $command;
	}

	/**
	 * Registers a chatcommand at the Interpreter.
	 * @param string $command_name
	 * @param integer $parameter_count
	 * @param string $callback_method
	 * @param bool $add_login
	 * @param array[string] $authorizedLogin
	 * @return \ManiaLive\Features\ChatCommand\Command
	 */
	final function registerChatCommand($command_name, $callback_method,
		$parameter_count = 0, $add_login = false, $authorizedLogin = array())
	{
		$this->restrictIfUnloaded();
		$cmd = new Command($command_name, $parameter_count, $authorizedLogin);
		$cmd->callback = array($this, $callback_method);
		$cmd->addLoginAsFirstParameter = $add_login;
		$cmd->isPublic = true;
		Interpreter::getInstance()->register($cmd);
		$this->chatCommands[] = $cmd;

		// this method will be accessible by other plugins
		$this->setPublicMethod($callback_method);

		return $cmd;
	}

	/**
	 * Write message into the plugin's logfile.
	 * Prefix with Plugin's name.
	 * @param string $text
	 */
	final protected function writeLog($text)
	{
		Logger::getLog($this->author.''.$this->name)->write($text);
	}

	/**
	 * Write message onto the commandline.
	 * Prefix with Plugin's name and 
	 * @param string $text
	 */
	final protected function writeConsole($text)
	{
		Console::println('['.Console::getDatestamp().'|'.$this->name.'] '.$text);
	}

	// LISTENERS
	// plugin events ...

	function onInit()
	{
		
	}

	function onLoad()
	{
		
	}

	function onReady()
	{
		
	}

	/**
	 * If you override this method you might want to
	 * call the parent's onUnload as well, as it does some
	 * useful stuff!
	 * Use this method to remove any windows that are
	 * currently displayed by the plugin, you might also need to
	 * destroy some objects that have been created without using the
	 * plugin intern methods.
	 */
	function onUnload()
	{
		// disable all events
		$this->disableApplicationEvents();
		$this->disableDedicatedEvents();
		$this->disableStorageEvents();
		$this->disableThreadingEvents();
		$this->disableTickerEvent();
		$this->disableWindowingEvents();
		$this->disablePluginEvents();

		// unregister chat commands
		$this->unregisterAllChatCommands();

		// kill the plugin's thread!
		$this->killThread();

		$this->threadpool = null;
		$this->storage = null;
		$this->pluginHandler = null;
		$this->connection = null;
		$this->dependencies = null;
		$this->settings = null;
		$this->methods = null;
		unset($this->chatCommands);
	}

	// application events ...

	function onRun()
	{
		
	}

	function onPreLoop()
	{
		
	}

	function onPostLoop()
	{
		
	}

	function onTerminate()
	{
		
	}

	// dedicated callbacks

	function onPlayerConnect($login, $isSpectator)
	{
		
	}

	function onPlayerDisconnect($login)
	{
		
	}

	function onPlayerChat($playerUid, $login, $text, $isRegistredCmd)
	{
		
	}

	function onPlayerManialinkPageAnswer($playerUid, $login, $answer,array $entries)
	{
		
	}

	function onEcho($internal, $public)
	{
		
	}

	function onServerStart()
	{
		
	}

	function onServerStop()
	{
		
	}

	function onBeginRace($challenge)
	{
		
	}

	function onEndRace($rankings, $challenge)
	{
		
	}

	function onBeginChallenge($challenge, $warmUp, $matchContinuation)
	{
		
	}

	function onEndChallenge($rankings, $challenge, $wasWarmUp,
		$matchContinuesOnNextChallenge, $restartChallenge)
	{
		
	}

	function onBeginRound()
	{
		
	}

	function onEndRound()
	{
		
	}

	function onStatusChanged($statusCode, $statusName)
	{
		
	}

	function onPlayerCheckpoint($playerUid, $login, $timeOrScore, $curLap,
		$checkpointIndex)
	{
		
	}

	function onPlayerFinish($playerUid, $login, $timeOrScore)
	{
		
	}

	function onPlayerIncoherence($playerUid, $login)
	{
		
	}

	function onBillUpdated($billId, $state, $stateName, $transactionId)
	{
		
	}

	function onTunnelDataReceived($playerUid, $login, $data)
	{
		
	}

	function onChallengeListModified($curChallengeIndex, $nextChallengeIndex,
		$isListModified)
	{
		
	}

	function onPlayerInfoChanged($playerInfo)
	{
		
	}

	function onManualFlowControlTransition($transition)
	{
		
	}

	function onVoteUpdated($stateName, $login, $cmdName, $cmdParam)
	{
		
	}
	
	function onRulesScriptCallback($param1, $param2)
	{
		
	}

	// windowing events

	function onWindowClose($login, $window)
	{
		
	}

	function onWindowRecover($login, $window)
	{
		
	}

	// threading events

	function onThreadDies($thread)
	{
		
	}

	function onThreadRestart($thread)
	{
		
	}

	function onThreadStart($thread)
	{
		
	}

	function onThreadTimesOut($thread)
	{
		
	}

	// ticker event

	function onTick()
	{
		
	}

	// storage events

	function onPlayerNewBestScore($player, $score_old, $score_new)
	{
		
	}

	function onPlayerNewBestTime($player, $best_old, $best_new)
	{
		
	}

	function onPlayerNewRank($player, $rank_old, $rank_new)
	{
		
	}

	function onPlayerChangeSide($player, $oldSide)
	{
		
	}

	function onPlayerFinishLap($player, $time, $checkpoints, $nbLap)
	{
		
	}

	// plugin events

	function onPluginLoaded($pluginId)
	{
		
	}

	function onPluginUnloaded($pluginId)
	{
		
	}

	// caching events

	function onStore(Entry $entry)
	{
		
	}

	function onEvict(Entry $entry)
	{
		
	}

}

?>