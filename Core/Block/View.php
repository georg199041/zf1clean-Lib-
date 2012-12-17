<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Core
 * @package    Core_Block
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: View.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Zend_View_Interface
 */
require_once "Zend/View/Interface.php";

/**
 * @see Core_Attributes
 */
require_once "Core/Attributes.php";

/**
 * Block templating strategy view engine
 * 
 * @category   Core
 * @package    Core_Block
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_View extends Core_Attributes implements Zend_View_Interface
{
	/**
	 * Dummy string, uses fron no script file required renreds
	 */
	const BLOCK_DUMMY = 'DUMMY';
	
	const BLOCK_PLACEMENT_BEFORE = 'BEFORE';	
	const BLOCK_PLACEMENT_AFTER  = 'AFTER';
	
	/**
	 * Cache manager place key
	 */
	const BLOCK_CACHEMANAGER_KEY = 'Core_Block_View';
	
	/**
	 * Global engine object
	 * 
	 * @var object
	 */
	protected static $_engine;
	
	/**
	 * Global cache object
	 * 
	 * @var object
	 */
	protected static $_cache;
	
	/**
	 * Cache local use flag
	 * 
	 * @var boolean
	 */
	protected $_cacheable = false;
	
	/**
	 * Template script name
	 * 
	 * @var string
	 */
	protected $_scriptName;
	
	/**
	 * Flag of rendering completed
	 * 
	 * @var boolean
	 */
	protected $_rendered = false;
	
	/**
	 * Internal block name
	 * 
	 * @var string
	 */
	protected $_blockName;
	
	/**
	 * Parent block object
	 * 
	 * @var null|Core_Block_View
	 */
	protected $_blockParent;
	
	/**
	 * Array of childs blocks
	 * 
	 * @var array
	 */
	protected $_blockChilds = array();
	
	/**
	 * Plock placement relative to parent
	 * 
	 * @var string
	 */
	protected $_blockPlacment = self::BLOCK_PLACEMENT_AFTER;
	
	/**
	 * All available placements for comarison functions
	 * 
	 * @var array
	 */
	protected $_blockAvailablePlacements = array(
		self::BLOCK_PLACEMENT_BEFORE,
		self::BLOCK_PLACEMENT_AFTER,
	);
	
	/**
	 * Logger instance
	 * 
	 * @var Zend_Log
	 */
	protected static $_logger;
	
	/**
	 * Add log message
	 * 
	 * @param  string $message  Log message
	 * @param  string $priority Log priority (message status)
	 * @return Core_Block_View
	 */
	protected function _log($message, $priority = Zend_Log::INFO)
	{
		if (self::$_logger instanceof Zend_Log) {
			self::$_logger->log(get_class($this) . '::' . $message, $priority);
		}
		
		return $this;
	}
	
	/**
	 * Search script file
	 * 
	 * @throws Exception
	 * @return string
	 */
	protected function _getScriptFile()
	{
		$this->_log(__FUNCTION__ . '()');
		$name = $this->getScriptName();
		if (null === $name) {
			require_once 'Core/Block/Exception.php';
			$e = new Core_Block_Exception("Script name not defined");
			$e->setView($this);
			throw $e;
		}
		
		foreach ($this->getScriptPaths() as $path) {
			$file = $path . $name;
			if (file_exists($file)) {
				$this->_log(__FUNCTION__ . '() founded file: ' . $file);
				return $file;
			}
		}
		
		require_once 'Core/Block/Exception.php';
		$e = new Core_Block_Exception("Script '$name' not found in paths: " . implode(', ', $this->getScriptPaths()));
		$e->setView($this);
		throw $e;
	}
	
	/**
	 * Get block name variants for loader
	 * 
	 * @throws Exception
	 * @return array
	 */
	protected function _getBlockNames()
	{
		$this->_log(__FUNCTION__ . '()');
		$name = $this->getScriptName();
		if (null === $name) {
			require_once 'Core/Block/Exception.php';
			$e = new Core_Block_Exception("Script name not defined");
			$e->setView($this);
			throw $e;
		}
		
		require_once 'Zend/Controller/Action/HelperBroker.php';
		$viewSuffix = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix();
		$name = str_ireplace(".$viewSuffix", '', $name);
		
		require_once 'Zend/Controller/Front.php';
		$module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
		
		$names = array($module . '/' . $name, 'application/' . $name);
		$this->_log(__FUNCTION__ . "() founded names: array(" . implode(", ", $names) . ")");
		return $names;
	}
	
	/**
	 * Generate cache id
	 * 
	 * @return string
	 */
	protected function _cacheId()
	{
		$id = get_class($this) . '_' . md5(serialize($this));
		$this->_log(__FUNCTION__ . '() = ' . $id);
		return $id;
	}
	
	/**
	 * Helper for test cache record exists
	 * 
	 * @param  string $id
	 * @return boolean
	 */
	protected function _cacheTest($id)
	{
		if ($this->getCache() && $this->isCacheable()) {
			return $this->getCache()->test($id);
		}
		
		$this->_log(__FUNCTION__ . '() simulated');
		return false;
	}
	
	/**
	 * Helper for load cache record data
	 * 
	 * @param  string $id
	 * @return boolean
	 */
	protected function _cacheLoad($id)
	{
		if ($this->getCache() && $this->isCacheable()) {
			return $this->getCache()->load($id);
		}
		
		$this->_log(__FUNCTION__ . '() simulated');
		return true;
	}
	
	/**
	 * Helper for save cache record data
	 * 
	 * @param  mixed $data
	 * @param  string $id
	 * @return boolean
	 */
	protected function _cacheSave($data, $id = null)
	{
		if ($this->getCache() && $this->isCacheable()) {
			return $this->getCache()->save($data, $id);
		}
		
		$this->_log(__FUNCTION__ . '() simulated');
		return true;
	}
	
	/**
	 * Try to render 'BLOCK' strategy
	 * 
	 * @return boolean|string
	 */
	protected function _findAndRenderBlock()
	{
		$this->_log(__FUNCTION__ . '()');
		if (get_class($this) != 'Core_Block_View') {
			$this->_log(__FUNCTION__ . '() we are inside custom block class -> no need to use');
			return false;
		}
		
		$names = $this->_getBlockNames();
		foreach ($names as $blockName) {
			try {
				$block = Core::getBlock($blockName);
				$block->setScriptName($this->getScriptName());
				//$block->setLogger($this->getLogger());
				$this->_log(__FUNCTION__ . '() block ' . get_class($block) . ' founded, try to render it');
				return $block->render(self::BLOCK_DUMMY);
			} catch (Exception $e) {}
		}
		
		$this->_log(__FUNCTION__ . '() block(s) not found', Zend_Log::WARN);
		return false;
	}
	
	/**
	 * Constructor
	 * 
	 * @param array|Zend_Config $options
	 */
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		} else if ($options instanceof Zend_Config) {
			$this->setOptions($options->toArray());
		}
		
		$this->_log(__FUNCTION__ . '(' . gettype($options) . ')');
		
		$this->getEngine()->addHelperPath('Core/Block/View/Helper', 'Core_Block_View_Helper');
		
		$this->_log('init()');
		$this->init();
	}
	
	/**
	 * Set all options at once
	 * 
	 * @param  array $options
	 * @return Core_Block_View
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
		
		return $this;
	}
	
	/**
	 * Helper function to translate some strings
	 * 
	 * Internaly use Zend_Translate
	 * 
	 * Usage anywhere in block class or in template:
	 * <php>
	 *     $this->__('Untranslated string');
	 * </php>
	 * 
	 * @see Zend_Translate
	 * @param  string $string Input string in default language
	 * @return string Translated into selected language string
	 */
	public function __($string)
	{
		return $string;
	}
	
	/**
	 * Set new script name
	 * 
	 * @param  string $name
	 * @return Core_Block_View
	 */
	public function setScriptName($name)
	{
		$this->_log(__FUNCTION__ . '(' . $name . ') string ' . self::BLOCK_DUMMY . ' will be ignored');
		if ($name != self::BLOCK_DUMMY) {
			$this->_scriptName = (string) $name;
		}
		
		return $this;
	}
	
	/**
	 * Get script name
	 * 
	 * @return null|string
	 */
	public function getScriptName()
	{
		$this->_log(__FUNCTION__ . '()');
		if (null === $this->_scriptName) {
			require_once 'Zend/Controller/Action/HelperBroker.php';
			$viewSuffix = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix();
			$name = $this->getBlockName();
			$namespace = substr($name, 0, strpos($name, '/'));
			$name = substr($name, strpos($name, '/') + 1);
			$this->_scriptName = $name . '.' . $viewSuffix;
			
			try {
				$this->addScriptPath(
					Zend_Controller_Front::getInstance()->getModuleDirectory($namespace) . '/views/scripts/'
				);
			} catch (Exception $e) {}
		}
		
		return $this->_scriptName;
	}
	
	/**
	 * Set block name
	 * 
	 * @param  string $name
	 * @return Core_Block_View
	 */
	public function setBlockName($name)
	{
		$this->_log(__FUNCTION__ . '(' . $name . ')');
		$this->_blockName = $name;
		return $this;
	}
	
	/**
	 * Get block name
	 * Create from class name if not defined
	 * 
	 * @return string
	 */
	public function getBlockName()
	{
		$this->_log(__FUNCTION__ . '()');
		if (null === $this->_blockName) {
			$parts = explode('_', get_class($this));
			unset($parts[1]);
			foreach ($parts as &$p) {
				$p = Core::useFilter($p, 'Zend_Filter_Word_CamelCaseToDash');
				$p = strtolower($p);
			}
			
			$this->setBlockName(implode('/', $parts));
			$this->_log(__FUNCTION__ . '() creating -> ' . $this->_blockName);
		}
		
		return $this->_blockName;
	}
	
	/**
	 * Set block placement relative to arent
	 * 
	 * @param  string $placement Blocks pacement name
	 * @return Core_Block_View
	 */
	public function setBlockPlacement($placement)
	{
		$this->_log(__FUNCTION__ . '(' . $placement . ')');
		if (in_array($placement, $this->_blockAvailablePlacements)) {
			$this->_blockPlacment = $placement;
		}
		
		return $this;
	}
	
	/**
	 * Get block placement
	 * 
	 * @return string
	 */
	public function getBlockPlacement()
	{
		$this->_log(__FUNCTION__ . '(' . $this->_blockPlacment . ')');
		return $this->_blockPlacment;
	}
	
	/**
	 * Set parent block
	 * 
	 * @param  Core_Block_View $parent
	 * @return Core_Block_View
	 */
	public function setBlockParent(Core_Block_View $parent)
	{
		$this->_log(__FUNCTION__ . '(' . get_class($parent) . ')');
		$this->_blockParent = $parent;
		return $this;
	}
	
	/**
	 * Get parent block
	 * 
	 * @return Core_Block_View
	 */
	public function getBlockParent()
	{
		$this->_log(__FUNCTION__ . '()');
		return $this->_blockParent;
	}
	
	/**
	 * Set childs blocks
	 * 
	 * @param  array  $childs    Blocks list to set
	 * @param  string $placement Blocks pacement name
	 * @return Core_Block_View
	 */
	public function setBlockChilds(array $childs, $placement = self::BLOCK_PLACEMENT_AFTER)
	{
		$this->_log(__FUNCTION__ . '(' . gettype($childs) . ', ' . $placement . ')');
		$this->_blockChilds = array();
		$this->addBlockChilds($childs, $placement);
		return $this;
	}
	
	/**
	 * Get childs blocks
	 * 
	 * @param  string $placement Blocks pacement name
	 * @return array
	 */
	public function getBlockChilds($placement = null)
	{
		$this->_log(__FUNCTION__ . '(' . $placement . ')');
		if (null !== $placement) {
			if (!in_array($placement, $this->_blockAvailablePlacements)) {
				$this->_log(__FUNCTION__ . '(' . $placement . ') invalid placement -> return empty list');
				return array();
			}
			
			$blocks = array();
			foreach ($this->_blockChilds as $name => $block) {
				if ($block->getBlockPlacement() == $placement) {
					$blocks[$name] = $block;
				}
			}
			
			return $blocks;
		}
		
		return $this->_blockChilds;
	}
	
	/**
	 * Add childs blocks
	 * 
	 * @param  array  $childs    Block list to add
	 * @param  string $placement Blocks pacement name
	 * @return Core_Block_View
	 */
	public function addBlockChilds(array $childs, $placement = self::BLOCK_PLACEMENT_AFTER)
	{
		$this->_log(__FUNCTION__ . '(' . gettype($childs) . ', ' . $placement . ')');
		foreach ($childs as $name => $child) {
			if ($child instanceof Core_Block_View) {
				$this->addBlockChild($child, $placement);
			} else if (is_array($child)) {
				if (!is_numeric($name) && false !== stripos($name, '/') && !isset($child['name'])) {
					$child['blockName'] = $name;
				}
				
				$this->addBlockChild($child, $placement);
			}
		}
		
		return $this;
	}
	
	/**
	 * Add single child block
	 * 
	 * @param  string|Core_View_Block $child     Block to add
	 * @param  string                 $placement Block placement
	 * @throws Exception If not found
	 * @return Core_Block_View
	 */
	public function addBlockChild($child, $placement = self::BLOCK_PLACEMENT_AFTER)
	{
		$this->_log(__FUNCTION__ . '(' . gettype($child) . ', ' . $placement . ')');
		if (null === $placement || !in_array($placement, $this->_blockAvailablePlacements)) {
			$placement = self::BLOCK_PLACEMENT_AFTER;
		}
		
		if ($child instanceof Core_Block_View) {
			$child->setBlockParent($this);
			$child->setBlockPlacement($placement);
			$this->_blockChilds[$child->getBlockName()] = $child;
		} else if (is_array($child)) {
			if (!isset($child['type'])) {
				require_once 'Core/Block/Exception.php';
				$e = new Core_Block_Exception("Block options must have type");
				$e->setView($this);
				throw $e;
			}
			
			$className = ucfirst(Zend_Filter::filterStatic($child['type'], 'Word_DashToCamelCase'));
			if (false === stripos($className, '_')) {
				$className = 'Core_Block_' . $className . '_Widget';
			}
			
			if (!@class_exists($className, true)) {
				require_once 'Core/Block/Exception.php';
				$e = Core_Block_Exception("Class '$className' not found");
				$e->setView($this);
				throw $e;
			}
			
			unset($child['type']);
			$class = new $className($child);
			$class->setBlockParent($this);
			$child->setBlockPlacement($placement);
			$this->_blockChilds[$class->getBlockName()] = $class;
		}
		
		return $this;
	}
	
	/**
	 * Get block by name
	 * 
	 * @param  string $name
	 * @return null|Core_Block_View
	 */
	public function getBlockChild($name)
	{
		$this->_log(__FUNCTION__ . '(' . $name . ')');
		return $this->_blockChilds[$name];
	}
	
	/**
	 * Delete block from childs
	 * 
	 * @param  string $name
	 * @return Core_Block_View
	 */
	public function delBlockChild($name)
	{
		$this->_log(__FUNCTION__ . '(' . $name . ')');
		$this->_blockChilds[$name] = null;
		unset($this->_blockChilds[$name]);
		return $this;
	}
	
	/**
	 * Set rendered flag
	 * 
	 * @param  boolean $flag
	 * @return Core_Block_View
	 */
	public function setRendered($flag = false)
	{
		$this->_log(__FUNCTION__ . '(' . var_export((bool) $flag, true) . ')');
		$this->_rendered = (bool) $flag;
		return $this;
	}
	
	/**
	 * Check if rendered flag set
	 * 
	 * @return boolean
	 */
	public function isRendered()
	{
		$this->_log(__FUNCTION__ . '() = ' . var_export($this->_rendered, true));
		return $this->_rendered;
	}
	
	/**
	 * Get front controller request object
	 * 
	 * @return Zend_Controller_Request_Http
	 */
	public function getRequest()
	{
		return Zend_Controller_Front::getInstance()->getRequest();
	}
	
	/**
	 * Get front controller response
	 * 
	 * @return Zend_Controller_Response_Abstract
	 */
	public function getResponse()
	{
		return Zend_Controller_Front::getInstance()->getResponse();
	}
	
	/**
	 * Extensions initialize method
	 */
	public function init()
	{}
	
	/**
	 * Direct before render initializations
	 */
	public function preRender()
	{}
	
	/**
	 * Get rendering core engine
	 * 
	 * @return object
	 */
	public function getEngine()
    {
    	if (null === self::$_engine) {
    		self::$_engine = new Zend_View();
    	}
    	
    	return self::$_engine;
    }
    
    /**
     * Set new logger instance
     * 
     * @param  array|Zend_Log $logger
     * @return Core_Block_View
     */
    public function setLogger($logger)
    {
    	if (is_array($logger)) {
    		$logger = Zend_Log::factory($logger);
    	}
    	
    	if ($logger instanceof Zend_Log) {
    		self::$_logger = $logger;
    	}
    	
    	return $this;
    }
    
    /**
     * Gets logger instance
     * 
     * @return Zend_Log
     */
    public function getLogger()
    {
    	if (null === self::$_logger) {
    		self::$_logger = false;
    	}
    	
    	return self::$_logger;
    }
    
    /**
     * Set script search path
     * Proxied to engine
     * 
     * @param  string $path
     * @return Core_Block_View
     */
    public function setScriptPath($path)
    {
    	$this->getEngine()->setScriptPath($path);
    	return $this;
    }

    /**
     * Get search script paths
     * 
     * @return array
     */
    public function getScriptPaths()
    {
    	return $this->getEngine()->getScriptPaths();
    }

    /**
     * Set base path
     * Proxied to engine
     * 
     * @param  string $path
     * @param  string $classPrefix
     * @return Core_Block_View
     */
    public function setBasePath($path, $classPrefix = 'Zend_View')
    {
    	$this->getEngine()->setBasePath($path, $classPrefix);
    	return $this;
    }

    /**
     * Add base path
     * Proxied to engine
     * 
     * @param  string $path
     * @param  string $classPrefix
     * @return Core_Block_View
     */
    public function addBasePath($path, $classPrefix = 'Zend_View')
    {
    	$this->getEngine()->addBasePath($path, $classPrefix);
    	return $this;
    }
    
    /**
     * Set new cache object
     * 
     * @param Zend_Cache_Core $core
     */
    public function setCache(Zend_Cache_Core $core)
    {
    	$this->_log(__FUNCTION__ . '(' . get_class($core) . ')');
    	self::$_cache = $core;
    }
    
    /**
     * Get cache object
     * 
     * @return Zend_Cache_Core
     */
    public function getCache()
    {
    	$this->_log(__FUNCTION__ . '() = ' . (gettype(self::$_cache) == "object" ? get_class(self::$_cache) : var_export(self::$_cache, true)));
    	if (null === self::$_cache) {
    		$this->_log(__FUNCTION__ . '() try to instantiate from Zend_Registry');
    		if (Zend_Registry::isRegistered('Zend_Cache_Manager')) {
    			self::$_cache = Zend_Registry::get('Zend_Cache_Manager')->getCache(self::BLOCK_CACHEMANAGER_KEY);
    		}
	    	
	    	if (!(self::$_cache instanceof Zend_Cache_Core)) {
	    		$this->_log(__FUNCTION__ . '() instantiate from Zend_Registry failed, set to FALSE for prevent second iteration');
	    		self::$_cache = false;
	    	}
    	}
    	
    	
    	return self::$_cache;
    }
    
    /**
     * Set local use cache
     * 
     * @param  string $flag
     * @return Core_Block_View
     */
    public function setCacheable($flag = true)
    {
    	$this->_log(__FUNCTION__ . '(' . var_export((bool) $flag, true) . ')');
    	$this->_cacheable = (bool) $flag;
    	return $this;
    }
    
    /**
     * Check if localy cache s enabled
     * 
     * @return boolean
     */
    public function isCacheable()
    {
    	$this->_log(__FUNCTION__ . '() = ' . var_export($this->_cacheable, true));
    	return $this->_cacheable;
    }
    
    /**
     * Generate cache id
     * 
     * @return string
     */
    public function getCacheId()
    {
    	$id = get_class($this) . '_' . md5(serialize($this));
    	$this->_log(__FUNCTION__ . '() = ' . $id);
    	return $id;
    }
    
    /**
     * Proxy undefined methods to engine
     * 
     * @param  string $method
     * @param  array $args
     * @return mixed|Core_Block_View
     */
    public function __call($method, $args)
    {
    	$this->_log('getEngine()->' . $method . '()');
    	
    	$result = call_user_func_array(array($this->getEngine(), $method), $args);
   		if ($result instanceof Zend_View_Abstract) {
   			return $this;
   		}
   		
   		return $result;
    }
    
    /**
     * Get property
     * Currently not used
     * 
     * @param mixed $key
     */
    public function __get($key)
    {
    	return $this->getEngine()->__get($key);
    }

    /**
     * Set new property
     * 
     * @param mixed $key
     * @param mixed $val
     */
    public function __set($key, $val)
    {
    	return $this->getEngine()->__set($key, $val);
    }

    /**
     * Check property exists
     * 
     * @param  mixed $key
     * @return boolean
     */
    public function __isset($key)
    {
    	return $this->getEngine()->__isset($key);
    }

    /**
     * Delete property
     * 
     * @param mixed $key
     */
    public function __unset($key)
    {
    	$this->getEngine()->__unset($key);
    }
    
    /**
     * Assign propert(y|ies)
     * 
     * @param  mixed $spec
     * @param  mixed $value
     * @return Core_Block_View
     */
    public function assign($spec, $value = null)
    {
    	$this->getEngine()->assign($spec, $value);
    	return $this;
    }

    /**
     * Clear all properties
     * 
     * @return Core_Block_View
     */
    public function clearVars()
    {
        $this->getEngine()->clearVars();
    	return $this;
    }
    
    /**
     * Render only blocks of specified placement
     * 
     * @param  string $placement
     * @return string
     */
    public function renderBlockChilds($placement = null)
    {
    	$this->_log(__FUNCTION__ . '(' . $placement . ')');
    	
    	$response = '';
    	if (!in_array($placement, $this->_blockAvailablePlacements)) {
    		$this->_log(__FUNCTION__ . '(' . $placement . ') invalid placement', Zend_Log::WARN);
    		return $response;
    	}
    	
    	foreach ($this->getBlockChilds($placement) as $block) {
    		if (!$block->isRendered()) {
    			$response .= $block->render(self::BLOCK_DUMMY);
    		}
    	}
    	
    	return $response;
    }

    /**
     * Render script, highlighter and blocks
     * 
     * @param  string $name overrided script name
     * @return string
     */
    public function render($name)
    {
    	$this->_log(__FUNCTION__ . '(' . $name . ')');
    	if ($this->isRendered()) {
    		//return '';
    	}
    	
    	$exceptions = array();
    	$this->setScriptName($name);
    	
    	$this->_log(__FUNCTION__ . "(" . $name . ") try to use 'BLOCK' rendering strategy");
    	if (($html = $this->_findAndRenderBlock())) {
    		return $html;
    	}
    	/*if (get_class($this) == 'Core_Block_View') {
    		$names = $this->_getBlockNames();
    		foreach ($names as $blockName) {
	    		try {
	    			$block = Core::getBlock($blockName);
	    			$block->setScriptName($this->getScriptName());
	    			return $block->render('DUMMY');
	    		} catch (Exception $e) {
	    			$exceptions[] = $e->getMessage();
	    		}
    		}
    	}*/
    	
   		$this->_log(__FUNCTION__ . "(" . $name . ") 'BLOCK' strategy failed, try to use 'TEMPLATE' rendering strategy", Zend_Log::WARN);
   		try {
   			$cacheId = $this->_cacheId();
   			if (!$this->_cacheTest($cacheId)) {
   				$file = $this->_getScriptFile();
   				$this->preRender();
   				
   				$this->_log(__FUNCTION__ . "(" . $name . ") !!! OB_START !!!");
   				ob_start();
   				include $file;
   				$script = ob_get_clean();
   				$this->_log(__FUNCTION__ . "(" . $name . ") !!! OB_GET_CLEAN !!!");
   				 
   				$this->_cacheSave($script, $cacheId);
   			} else {
   				$script = $this->_cacheLoad($cacheId);
   			}
   			
    		//$response .= $script;
    	} catch (Exception $ex) {
    		$this->_log(__FUNCTION__ . "(" . $name . ") render failed throw exception if block hasn't childs", Zend_Log::WARN);
    		if (count($this->getBlockChilds()) == 0) {
    			require_once 'Core/Block/Exception.php';
    			$e = new Core_Block_Exception($ex->getMessage());
    			$e->setView($this);
    			throw $e;
    		}
    	}
    	
    	$this->_log(__FUNCTION__ . "(" . $name . ") 'TEMPLATE' rendering strategy complete");
    	$this->setRendered(true);
    	
    	return $this->renderBlockChilds(self::BLOCK_PLACEMENT_BEFORE)
    	     . $script
    	     . $this->renderBlockChilds(self::BLOCK_PLACEMENT_AFTER);
    }
    
    /**
     * Rendering by echo call
     * 
     * @return string
     */
    public function __toString()
    {
    	return $this->render(self::BLOCK_DUMMY);
    }
    
    /**
     * Converts all variables to array representation
     * 
     * @return array();
     */
    public function toArray()
    {}
}
