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
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Renderer.php 0.2 2012-12-10 pavlenko $
 */

/**
 * @see Zend_View_Interface
 */
require_once 'Zend/View/Interface.php';

/**
 * @see Zend_Log
 */
require_once 'Zend/Log.php';

/**
 * @see Core
 */
require_once 'Core.php';

/**
 * Basic core view renderer implementation
 *
 * @category   Core
 * @package    Core_Block
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_View implements Zend_View_Interface
{
	/**
	 * Placement constants
	 */
	const BLOCK_PLACEMENT_BEFORE = 'before';
	const BLOCK_PLACEMENT_AFTER  = 'after';
	
	/**
	 * Rendered stauses
	 */
	const BLOCK_RENDERED_SUCCESS = 'SUCCESS';
	const BLOCK_RENDERED_ERROR   = 'ERROR';
	
	/**
	 * Render type constants
	 */
	const BLOCK_RENDER_TYPE_HTML = 'html';
	const BLOCK_RENDER_TYPE_JSON = 'json';
	const BLOCK_RENDER_TYPE_XML  = 'xml';
	
	/**
	 * Request types constants
	 */
	const BLOCK_REQUEST_TYPE_XHR    = 'XmlHttpRequest';
	const BLOCK_REQUEST_TYPE_FLASH  = 'FlashRequest';
	const BLOCK_REQUEST_TYPE_POST   = 'POST';
	const BLOCK_REQUEST_TYPE_GET    = 'GET';
	const BLOCK_REQUEST_TYPE_PUT    = 'PUT';
	const BLOCK_REQUEST_TYPE_DELETE = 'DELETE';
	
	/**
	 * Global protected render engine container
	 * 
	 * @var object
	 */
	protected static $_engine;
	
	/**
	 * Flag is block rendered (success or error)
	 * 
	 * @var string
	 */
	protected $_rendered;
	
	/**
	 * Script name
	 * 
	 * @var string
	 */
	protected $_scriptName;
	
	/**
	 * Available placements
	 * 
	 * @var array
	 */
	protected $_placements = array(
		self::BLOCK_PLACEMENT_AFTER,
		self::BLOCK_PLACEMENT_BEFORE,
	);
	
	/**
	 * Render types array for comparsions
	 * 
	 * @var array
	 */
	protected $_renderTypes = array(
		self::BLOCK_RENDER_TYPE_HTML,
		self::BLOCK_RENDER_TYPE_JSON,
		self::BLOCK_RENDER_TYPE_XML
	);
	
	/**
	 * Request types array for compasions
	 * 
	 * @var array
	 */
	protected $_requestTypes = array(
		self::BLOCK_REQUEST_TYPE_XHR,
		self::BLOCK_REQUEST_TYPE_FLASH,
		self::BLOCK_REQUEST_TYPE_POST,
		self::BLOCK_REQUEST_TYPE_GET,
		self::BLOCK_REQUEST_TYPE_PUT,
		self::BLOCK_REQUEST_TYPE_DELETE,
	);
	
	/**
	 * Global block default request to render type map
	 * 
	 * @var array
	 */
	protected static $_defaultRequestRenderTypes = array(
		self::BLOCK_REQUEST_TYPE_XHR    => self::BLOCK_RENDER_TYPE_JSON,
		self::BLOCK_REQUEST_TYPE_FLASH  => self::BLOCK_RENDER_TYPE_JSON,
		self::BLOCK_REQUEST_TYPE_POST   => self::BLOCK_RENDER_TYPE_HTML,
		self::BLOCK_REQUEST_TYPE_GET    => self::BLOCK_RENDER_TYPE_HTML,
		self::BLOCK_REQUEST_TYPE_PUT    => self::BLOCK_RENDER_TYPE_HTML,
		self::BLOCK_REQUEST_TYPE_DELETE => self::BLOCK_RENDER_TYPE_HTML,
	);
	
	/**
	 * Current block request to render type map
	 * 
	 * @var array
	 */
	protected $_requestRenderTypes = array();
	
	/**
	 * Cache defaults enable by request type map 
	 * 
	 * @var array
	 */
	protected static $_defaultCacheEnable = array(
		self::BLOCK_REQUEST_TYPE_XHR => array(
			self::BLOCK_RENDER_TYPE_HTML => false,
			self::BLOCK_RENDER_TYPE_JSON => false,
			self::BLOCK_RENDER_TYPE_XML  => false,
		),
		self::BLOCK_REQUEST_TYPE_FLASH => array(
			self::BLOCK_RENDER_TYPE_HTML => false,
			self::BLOCK_RENDER_TYPE_JSON => false,
			self::BLOCK_RENDER_TYPE_XML  => false,
		),
		self::BLOCK_REQUEST_TYPE_POST => array(
			self::BLOCK_RENDER_TYPE_HTML => false,
			self::BLOCK_RENDER_TYPE_JSON => false,
			self::BLOCK_RENDER_TYPE_XML  => false,
		),
		self::BLOCK_REQUEST_TYPE_GET => array(
			self::BLOCK_RENDER_TYPE_HTML => false,
			self::BLOCK_RENDER_TYPE_JSON => false,
			self::BLOCK_RENDER_TYPE_XML  => false,
		),
		self::BLOCK_REQUEST_TYPE_PUT => array(
			self::BLOCK_RENDER_TYPE_HTML => false,
			self::BLOCK_RENDER_TYPE_JSON => false,
			self::BLOCK_RENDER_TYPE_XML  => false,
		),
		self::BLOCK_REQUEST_TYPE_DELETE => array(
			self::BLOCK_RENDER_TYPE_HTML => false,
			self::BLOCK_RENDER_TYPE_JSON => false,
			self::BLOCK_RENDER_TYPE_XML  => false,
		),
	);
	
	/**
	 * Current block cache enable map
	 * 
	 * @var array
	 */
	protected $_cacheEnable = array();
	
    /**
     * Strict variables flag; when on, undefined variables accessed in the view
     * scripts will trigger notices
     * 
     * @var boolean
     */
	protected $_strictVars = false;
	
	/**
	 * Cache object
	 * 
	 * @var Zend_Cache_Core
	 */
	protected $_cache;
	
	/**
	 * Logger object
	 * 
	 * @var Zend_Log
	 */
	protected $_logger;
	
	/**
	 * Logger enable flag
	 * 
	 * @var boolean
	 */
	protected $_logging = true;
	
	/**
	 * Block name
	 * 
	 * @var string
	 */
	protected $_blockName;
	
	/**
	 * Block parent name or instance
	 * 
	 * @var string|Core_Block_View
	 */
	protected $_blockParent;
	
	/**
	 * Childs array by placement
	 * 
	 * @var array
	 */
	protected $_blockChilds = array();
	
	/**
	 * Validate assign key for protection of
	 * private and protected properties
	 *
	 * @param  string $key
	 * @return boolean
	 */
	protected function _validateAssign($key)
	{
		return ('_' == substr($key, 0, 1)) ? false : true;
	}
	
	/**
	 * Logger helper method
	 * Add log message to logger if enabled and instantiate
	 * 
	 * @param  string Message
	 * @param  string Log message level (warning, error, etc...)
	 * @return Core_Block_View
	 */
	protected function _log($message, $priority = Zend_Log::DEBUG)
	{
		if (!$this->isLogging()) {echo 1;
			return $this;
		}
		
		if ($this->getLogger() instanceof Zend_Log) {
			$this->getLogger()->log($message, $priority);
			return $this;
		}
		
		require_once 'Core/Block/Exception.php';
		$e = new Core_Block_Exception('Logging is enabled but logger is not set');
		$e->setView($this);
		throw $e;
	}
	
	/**
	 * Generate cache id string
	 *
	 * @return string
	 */
	protected function _getCacheId(Core_Block_View $block)
	{
		$parts = explode('/', $block->getBlockName());
		$namespace = Core::useFilter($parts[0], 'Zend_Filter_Word_DashToCamelCase');
		$parts[0] = 'Block';
		foreach ($parts as &$part) {
			$part = Core::useFilter($part, 'Zend_Filter_Word_DashToCamelCase');
		}
		 
		return $namespace . '_' . implode('_', $parts);
	}	

	/**
	 * Search script file
	 *
	 * @throws Exception
	 * @return string
	 */
	protected function _getScriptFile()
	{
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
				return $file;
			}
		}
	
		$paths = implode(', ', $this->getScriptPaths());
		
		require_once 'Core/Block/Exception.php';
		$e = new Core_Block_Exception("Script '$name' not found in paths: $paths");
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
	
		return array($module . '/' . $name, 'application/' . $name);;
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
	
		$this->getEngine()->addHelperPath('Core/Block/View/Helper', 'Core_Block_View_Helper');
		$this->_log("Block '{$this->getBlockName()}' constructor passed", Zend_Log::DEBUG);
		$this->init();
		$this->_log("Block '{$this->getBlockName()}' user init() passed", Zend_Log::DEBUG);
	}
	
	/**
	 * User initialization method
	 */
	public function init()
	{
		
	}
	
	/**
	 * User before render method
	 */
	public function preRender()
	{
		
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
   		$result = call_user_func_array(array($this->getEngine(), $method), $args);
   		if ($result == $this->getEngine()) {
   			return $this;
   		}
   		
   		return $result;
    }
	
	/**
	 * Set configuration options
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
	 * Set protected global block logger instance
	 * You can set instance as generic instance of Zend_Log
	 * or as config array
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
			$this->_logger = $logger;
		}
		
		return $this;
	}
	
	/**
	 * Get protected global block logger instance
	 * 
	 * @return Zend_Log
	 */
	public function getLogger()
	{
		if (null === $this->_logger) {
			$this->setLogger(new Zend_Log(new Zend_Log_Writer_Firebug()));
		}
		
		return $this->_logger;
	}
	
	/**
	 * Sel logging enabled or disabled
	 * 
	 * @param  boolean $flag
	 * @return Core_Block_View
	 */
	public function setLogging($flag = true)
	{
		$this->_logging = (bool) $flag;
		return $this;
	}
	
	/**
	 * Get logging enabled state
	 * 
	 * @return boolean
	 */
	public function isLogging()
	{
		return $this->_logging;
	}
	
	/**
	 * Set new cache adapter
	 * 
	 * @param  Zend_Cache_Core $cache
	 * @return Core_Block_View
	 */
	public function setCache(Zend_Cache_Core $cache)
	{
		$this->_cache = $cache;
		return $this;
	}
	
	/**
	 * Get cache adapter
	 * 
	 * @return Zend_Cache_Core
	 */
	public function getCache()
	{
		if (null === $this->_cache) {
			if (Zend_Registry::isRegistered('Zend_Cache_Manager')
				&& Zend_Registry::get('Zend_Cache_Manager')->hasCache('Core_Block_View')) {
				$this->_cache = Zend_Registry::get('Zend_Cache_Manager')->getCache('Core_Block_View');
			}
		}
		
		return $this->_cache;
	}
	
	/**
	 * Generate cache id string
	 * 
	 * @return string
	 */
	public function getCacheId(Core_Block_View $block = null)
	{
		if (null === $block) {
			return get_class($this);
		}
		
		return $this->_getCacheId($block);
	}
	
	/**
	 * Set default flags map or if specified keys sets specified flag
	 * 
	 * @param  array|string $spec       All map array or request type key string
	 * @param  array|string $renderType All request specified map or render type key string
	 * @param  boolean      $value      Only if request and render types passed used this flag value
	 * @return Core_Block_View
	 */
	public function setDefaultCacheEnable($spec, $renderType = null, $value = null)
	{
		if (is_array($spec)) {
			foreach ($spec as $rqType => $rdTypes) {
				if (in_array($rqType, $this->_requestTypes) && is_array($rdTypes)) {
					$rdTypes = array_intersect_key($rdTypes, array_flip($this->_renderTypes));
					$rdTypes = array_merge($rdTypes, array_fill_keys(array_flip($this->_renderTypes), false));
					self::$_defaultCacheEnable[$rqType] = $rdTypes;
				}
			}
		} else if (in_array($spec, $this->_requestTypes)) {
			if (is_array($renderType)) {
				$renderType = array_intersect_key($renderType, array_flip($this->_renderTypes));
				$renderType = array_merge($renderType, array_fill_keys(array_flip($this->_renderTypes), false));
				self::$_defaultCacheEnable[$spec] = $renderType;
			} else if (in_array($renderType, $this->_renderTypes)) {
				self::$_defaultCacheEnable[$spec][$renderType] = (bool) $value;
			}
		}
		
		return $this;
	}
	
	/**
	 * Get cache enabled all map or by request type map or by request and render types value
	 * 
	 * @param  string $requestType [OPTIONAL] Request type, one of BLOCK_REQUEST_TYPE_* constants
	 * @param  string $renderType  [OPTIONAL] Render type, one of BLOCK_RENDER_TYPE_* constants
	 * @return array|boolean
	 */
	public function getDefaultCacheEnable($requestType = null, $renderType = null)
	{
		if (null !== $requestType) {
			if (null !== $renderType) {
				if (in_array($renderType, $this->_renderTypes)) {
					return self::$_defaultCacheEnable[$requestType][$renderType];
				}
				
				return false; // By default, request or render type independed state
			}
			
			if (in_array($requestType, $this->_requestTypes)) {
				return self::$_defaultCacheEnable[$requestType];
			}
			
			return array( // By default, request or render type independed states array
				self::BLOCK_RENDER_TYPE_HTML => false,
				self::BLOCK_RENDER_TYPE_JSON => false,
				self::BLOCK_RENDER_TYPE_XML  => false
			);
		}
		
		return self::$_defaultCacheEnable;
	}
	
	/**
	 * Set flags map or if specified keys sets specified flag
	 *
	 * @param  array|string $spec       All map array or request type key string
	 * @param  array|string $renderType All request specified map or render type key string
	 * @param  boolean      $value      Only if request and render types passed used this flag value
	 * @return Core_Block_View
	 */
	public function setCacheEnable($spec, $renderType = null, $value = null)
	{
		if (is_array($spec)) {
			foreach ($spec as $rqType => $rdTypes) {
				if (in_array($rqType, $this->_requestTypes) && is_array($rdTypes)) {
					$rdTypes = array_intersect_key($rdTypes, array_flip($this->_renderTypes));
					$rdTypes = array_merge($rdTypes, array_fill_keys(array_flip($this->_renderTypes), false));
					$this->_cacheEnable[$rqType] = $rdTypes;
				}
			}
		} else if (in_array($spec, $this->_requestTypes)) {
			if (is_array($renderType)) {
				$renderType = array_intersect_key($renderType, array_flip($this->_renderTypes));
				$renderType = array_merge($renderType, array_fill_keys(array_flip($this->_renderTypes), false));
				$this->_cacheEnable[$spec] = $renderType;
			} else if (in_array($renderType, $this->_renderTypes)) {
				$this->_cacheEnable[$spec][$renderType] = (bool) $value;
			}
		}
	
		return $this;
	}
	
	/**
	 * Get cache enabled all map or by request type map or by request and render types value
	 *
	 * @param  string $requestType [OPTIONAL] Request type, one of BLOCK_REQUEST_TYPE_* constants
	 * @param  string $renderType  [OPTIONAL] Render type, one of BLOCK_RENDER_TYPE_* constants
	 * @return array|boolean
	 */
	public function getCacheEnable($requestType = null, $renderType = null)
	{
		if (null !== $requestType) {				
			if (in_array($requestType, $this->_requestTypes)) {
				if (!array_key_exists($requestType, $this->_cacheEnable)) {
					$this->_cacheEnable[$requestType] = $this->getDefaultCacheEnable($requestType);
				}
				
				if (null !== $renderType) {
					if (in_array($renderType, $this->_renderTypes)) {
						if (!array_key_exists($renderType, $this->_cacheEnable[$requestType])) {
							$this->_cacheEnable[$requestType][$renderType] = $this->getDefaultCacheEnable($requestType, $renderType);
						}
						
						return $this->_cacheEnable[$requestType][$renderType];
					}
		
					return false; // By default, request or render type independed state
				}
				
				return $this->_cacheEnable[$requestType];
			}
				
			return array( // By default, request or render type independed states array
					self::BLOCK_RENDER_TYPE_HTML => false,
					self::BLOCK_RENDER_TYPE_JSON => false,
					self::BLOCK_RENDER_TYPE_XML  => false
			);
		}
	
		return $this->_cacheEnable;
	}
	
	/**
	 * Check if cache enabled (flase by default)
	 * 
	 * @param  string $renderType Render type selected
	 * @return boolean
	 */
	public function isCacheEnabled($renderType = self::BLOCK_RENDER_TYPE_HTML)
	{
		if ($this->getRequest() instanceof Zend_Controller_Request_Http) {
			if ($this->getRequest()->isXmlHttpRequest())
			{
				$enabled = $this->getCacheEnable(self::BLOCK_REQUEST_TYPE_XHR, $renderType);
			}
			else if ($this->getRequest()->isFlashRequest())
			{
				$enabled = $this->getCacheEnable(self::BLOCK_REQUEST_TYPE_FLASH, $renderType);
			}
			else if ($this->getRequest()->isPost())
			{
				$enabled = $this->getCacheEnable(self::BLOCK_REQUEST_TYPE_POST, $renderType);
			}
			else if ($this->getRequest()->isGet())
			{
				$enabled = $this->getCacheEnable(self::BLOCK_REQUEST_TYPE_GET, $renderType);
			}
			else if ($this->getRequest()->isPut())
			{
				$enabled = $this->getCacheEnable(self::BLOCK_REQUEST_TYPE_PUT, $renderType);
			}
			else if ($this->getRequest()->isDelete())
			{
				$enabled = $this->getCacheEnable(self::BLOCK_REQUEST_TYPE_DELETE, $renderType);
			}
		}

		return false;
	}
	
	/**
	 * Gets request based render type
	 * 
	 * @return string
	 */
	public function getRenderType()
	{
		if (!($this->getRequest() instanceof Zend_Controller_Request_Http)) {
			return self::BLOCK_RENDER_TYPE_HTML;
		}
			
		if ($this->getRequest()->isXmlHttpRequest())
		{
			return  $this->getRequestRenderType(self::BLOCK_REQUEST_TYPE_XHR);
		}
		else if ($this->getRequest()->isFlashRequest())
		{
			return  $this->getRequestRenderType(self::BLOCK_REQUEST_TYPE_FLASH);
		}
		else if ($this->getRequest()->isPost())
		{
			return  $this->getRequestRenderType(self::BLOCK_REQUEST_TYPE_POST);
		}
		else if ($this->getRequest()->isGet())
		{
			return  $this->getRequestRenderType(self::BLOCK_REQUEST_TYPE_GET);
		}
		else if ($this->getRequest()->isPut())
		{
			return  $this->getRequestRenderType(self::BLOCK_REQUEST_TYPE_PUT);
		}
		else if ($this->getRequest()->isDelete())
		{
			return  $this->getRequestRenderType(self::BLOCK_REQUEST_TYPE_DELETE);
		}
		
		return  $this->getRequestRenderType(false);		
	}
	
	/**
	 * Setup default request to render type map definition
	 * 
	 * @param  array|string $spec   Array of key => value pairs defaults or sring key of specified request type
	 * @param  string       $value  Value for single type setting
	 * @throws Core_Block_Exception Thrown when definition is not an array or sring and $value pair
	 * @return Core_Block_View
	 */
	public function setDefaultRequestRenderType($spec, $value = null)
	{
		if (is_array($spec)) {
			foreach ($spec as $key => $val) {
				if (in_array($key, $this->_requestTypes) && in_array($val, $this->_renderTypes)) {
					self::$_defaultRequestRenderTypes[$key] = $val;
				}
			}
		} else if (in_array($spec, $this->_requestTypes) && in_array($value, $this->_renderTypes)) {
			self::$_defaultRequestRenderTypes[$spec] = $value;
		}
		
		return $this;
	}
	
	/**
	 * If $type is passed try to get render type for specified request type
	 * Else if request type not found return default render type
	 * Else return all map
	 * 
	 * @param  string $type Request type
	 * @return array|string Default render type for request type or all map
	 */
	public function getDefaultRequestRenderType($type = null)
	{
		if (null !== $type) {
			if (array_key_exists($type, self::$_defaultRequestRenderTypes)) {
				return self::$_defaultRequestRenderTypes[$type];
			}
			
			return self::BLOCK_RENDER_TYPE_HTML;
		}
		
		return self::$_defaultRequestRenderTypes;
	}
	
	/**
	 * Setup request to render type map definition
	 * 
	 * @param  array|string $spec   Array of key => value pairs defaults or sring key of specified request type
	 * @param  string       $value  Value for single type setting
	 * @throws Core_Block_Exception Thrown when definition is not an array or sring and $value pair
	 * @return Core_Block_View
	 */
	public function setRequestRenderType($spec, $value = null)
	{
		if (is_array($spec)) {
			foreach ($spec as $key => $val) {
				if (in_array($key, $this->_requestTypes) && in_array($val, $this->_renderTypes)) {
					$this->_requestRenderTypes[$key] = $val;
				}
			}
		} else if (in_array($spec, $this->_requestTypes) && in_array($value, $this->_renderTypes)) {
			$this->_requestRenderTypes[$spec] = $value;
		}
		
		return $this;		
	}
	
	/**
	 * If $type is passed try to get render type for specified request type
	 * Else if request type not found return default render type
	 * Else return all map
	 * 
	 * @param  string $type Request type
	 * @return array|string Default render type for request type or all map
	 */
	public function getRequestRenderType($type = null)
	{
		if (null !== $type) {
			if (array_key_exists($type, $this->_requestTypes)) {
				if (!array_key_exists($type, $this->_requestRenderTypes)) {
					$this->setRequestRenderType($type, $this->getDefaultRequestRenderType($type));
				}
				
				return $this->_requestRenderTypes[$type];
			}
				
			return self::BLOCK_RENDER_TYPE_HTML;
		}
		
		return $this->_requestRenderTypes;
	}
	
	/**
	 * Set block name
	 * 
	 * @param  string $name
	 * @return Core_Block_View
	 */
	public function setBlockName($name)
	{
		$this->_blockName = (string) $name;
		return $this;
	}
	
	/**
	 * Get block name
	 * 
	 * @return string
	 */
	public function getBlockName()
	{
		if (null === $this->_blockName) {
			$parts = explode('_', get_class($this));
			unset($parts[1]);
			foreach ($parts as &$p) {
				$p = Core::useFilter($p, 'Zend_Filter_Word_CamelCaseToDash');
				$p = strtolower($p);
			}
			
			$this->setBlockName(implode('/', $parts));
		}
		
		return $this->_blockName;
	}
	
	/**
	 * Set parent block name or instance
	 * 
	 * @param  string|Core_Block_View $block
	 * @return Core_Block_View
	 */
	public function setBlockParent($block)
	{
		$this->_blockParent = $block;
		return $this;
	}
	
	/**
	 * Get block parent
	 * If needed try to find and instantiate it
	 * If not fount set parent to false for prevent cyclic checking
	 * 
	 * @return Core_Block_View
	 */
	public function getBlockParent()
	{
		if (is_string($this->_blockParent)) {
			$block = Core::getBlock($this->_blockParent);
			if ($block instanceof Core_Block_View) {
				$this->_blockParent = $block;
			}
		}
		
		if ($this->_blockParent instanceof Core_Block_View) {
			$this->_blockParent = false;
		}
		
		return $this->_blockParent;
	}
	
	/**
	 * Set childs blocks
	 *
	 * @param  array $childs
	 * @return Core_Block_View
	 */
	public function setBlockChilds(array $childs, $placement = self::BLOCK_PLACEMENT_AFTER)
	{
		$this->_blockChilds = array();
		$this->addBlockChilds($childs, $placement);
		return $this;
	}

	/**
	 * Get child blocks by placenet or all possible
	 *
	 * @return array
	 */
	public function getBlockChilds($placement = null)
	{
		if (null !== $placement) {
			return (array) $this->_blockChilds[$placement];
		}
	
		return $this->_blockChilds;
	}
	
	/**
	 * Add childs blocks
	 *
	 * @param  array $childs
	 * @return Core_Block_View
	 */
	public function addBlockChilds(array $childs, $placement = self::BLOCK_PLACEMENT_AFTER)
	{
		foreach ($childs as $child) {
			if ($child instanceof Core_Block_View || is_array($child)) {
				$this->addBlockChild($child, $placement);
			}// TODO: ??? need invalid definition exception ???
		}
	
		return $this;
	}
	
	/**
	 * Add single child block
	 *
	 * @param  string|Core_View_Block $child
	 * @param  string                 $name
	 * @throws Exception If not found
	 * @return Core_Block_View
	 */
	public function addBlockChild($child, $placement = self::BLOCK_PLACEMENT_AFTER)
	{
		if (null === $placement || !in_array($placement, $this->getBlockChildPlacements())) {
			$placement = self::BLOCK_PLACEMENT_AFTER;
		}
	
		if ($child instanceof Core_Block_View) {
			$child->setBlockParent($this);
			$this->_blockChilds[$placement][$child->getBlockName()] = $child;
		} else if (is_array($child)) {
			$type = $child['type'];
			unset($child['type']);
			
			if (false === stripos($type, '_')) {
				$type = "Core_Block_{$type}_Widget";
			}
			
			try {				
				$class = Core::getClass($type, false, $child);
				$class->setBlockParent($this);
				
				$this->_blockChilds[$placement][$class->getBlockName()] = $class;
			} catch (Exception $ex) {
				require_once 'Core/Block/Exception.php';
				$e = new Core_Block_Exception("Class '$type' not found");
				$e->setView($this);
				throw $e;
			}
		}
	
		return $this;
	}
	
	/**
	 * Get block by name
	 *
	 * @param  string $name
	 * @return null|Core_Block_View
	 */
	public function getBlockChild($name, $placement = null)
	{
		foreach ($this->_blockChilds as $childsPlacement => $blocks) {
			if (!isset($placement) || (isset($placement)
				&& in_array($placement, $this->getBlockChildPlacements())
				&& $placement == $childsPlacement)) {
				foreach ($blocks as $childName => $block) {
					if ($childName == $name) {
						return $block;
					}
				}
			}
		} 
	
		return null;
	}
	
	/**
	 * Get available placenets
	 * 
	 * @return array
	 */
	public function getBlockChildPlacements()
	{
		return $this->_placements;
	}
	
	/**
	 * Set rendered status flag
	 * 
	 * @param  string $status
	 * @return Core_Block_View
	 */
	public function setRendered($status)
	{
		$this->_rendered = $status;
		return $this;
	}
	
	/**
	 * Check if block rendered
	 * 
	 * @return boolean
	 */
	public function isRendered()
	{
		return (null !== $this->_rendered
				&& $this->_rendered == self::BLOCK_RENDERED_SUCCESS
				&& $this->_rendered == self::BLOCK_RENDERED_ERROR);
	}
	
	/**
	 * Return the template engine object, if any
	 *
	 * If using a third-party template engine, such as Smarty, patTemplate,
	 * phplib, etc, return the template engine object. Useful for calling
	 * methods on these objects, such as for setting filters, modifiers, etc.
	 * 
	 * @return mixed
	 */
	public function getEngine()
	{
		if (null === self::$_engine) {
			require_once 'Zend/View.php';
			$this->setEngine(new Zend_View());
		}
		 
		return self::$_engine;
	}
	
	/**
	 * Sets the template engine object
	 * 
	 * @param  mixed Rendering engine object
	 * @return Core_Block_View
	 */
	public function setEngine($engine)
	{
		self::$_engine = $engine;
		return $this;
	}
	
	/**
	 * Set the path to find the view script used by render()
	 * Proxied to self::$_engine
	 *
	 * @param string|array The directory (-ies) to set as the path. Note that
	 * the concrete view implentation may not necessarily support multiple
	 * directories.
	 * @return Core_Block_View
	 */
	public function setScriptPath($path)
	{
		$this->getEngine()->setScriptPath($path);
		return $this;
	}
	
	/**
	 * Retrieve all view script paths
	 * Proxied to self::$_engine
	 *
	 * @return array
	 */
	public function getScriptPaths()
	{
		return $this->getEngine()->getScriptPaths();
	}
	
	/**
	 * Set a base path to all view resources
	 * Proxied to self::$_engine
	 *
	 * @param  string $path
	 * @param  string $classPrefix
	 * @return void
	 */
	public function setBasePath($path, $classPrefix = 'Zend_View')
	{
		$this->getEngine()->setBasePath($path, $classPrefix);
		return $this;
	}
	
	/**
	 * Add an additional path to view resources
	 * Proxied to self::$_engine
	 *
	 * @param  string $path
	 * @param  string $classPrefix
	 * @return void
	 */
	public function addBasePath($path, $classPrefix = 'Zend_View')
	{
		$this->getEngine()->addBasePath($path, $classPrefix);
		return $this;
	}
	
	/**
	 * Assign a variable to the view
	 *
	 * @param string $key The variable name.
	 * @param mixed $val The variable value.
	 * @return void
	 */
	public function __set($key, $val)
	{
		if ($this->_validateAssign($key)) {
			$this->$key = $val;
			return;
		}
		 
		require_once 'Core/Block/Exception.php';
		$e = new Core_Block_Exception('Setting private or protected class members is not allowed');
		$e->setView($this);
		throw $e;
	}
	
	/**
	 * Prevent E_NOTICE for nonexistent values
	 *
	 * If {@link strictVars()} is on, raises a notice.
	 *
	 * @param  string $key
	 * @return null
	 */
	public function __get($key)
	{
		if ($this->_strictVars) {
			trigger_error('Key "' . $key . '" does not exist', E_USER_NOTICE);
		}
	
		return null;
	}
	
	/**
	 * Allows testing with empty() and isset() to work
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		if ($this->_validateAssign($key)) {
			return isset($this->$key);
		}
		
		return false;
	}
	
	/**
	 * Allows unset() on object properties to work
	 *
	 * @param string $key
	 * @return void
	 */
	public function __unset($key)
	{
		if ($this->_validateAssign($key) && isset($this->$key)) {
			unset($this->$key);
		}
	}
	
	/**
	 * Assign variables to the view script via differing strategies.
	 *
	 * Suggested implementation is to allow setting a specific key to the
	 * specified value, OR passing an array of key => value pairs to set en
	 * masse.
	 *
	 * @see __set()
	 * @param string|array $spec The assignment strategy to use (key or array of key
	 * => value pairs)
	 * @param mixed $value (Optional) If assigning a named variable, use this
	 * as the value.
	 * @return void
	 */
	public function assign($spec, $value = null)
	{
		if (is_string($spec)) {
			if (!$this->_validateAssign($spec)) {
				require_once 'Core/Block/Exception.php';
				$e = new Core_Block_Exception('Setting private or protected class members is not allowed');
				$e->setView($this);
				throw $e;
			}
		
			$this->$spec = $value;
		} elseif (is_array($spec)) {
			$error = false;
			foreach ($spec as $key => $val) {
				if (!$this->_validateAssign($key)) {
					$error = true;
					break;
				}
				$this->$key = $val;
			}
		
			if ($error) {
				require_once 'Core/Block/Exception.php';
				$e = new Core_Block_Exception('Setting private or protected class members is not allowed');
				$e->setView($this);
				throw $e;
			}
		} else {
			require_once 'Core/Block/Exception.php';
			$e = new Core_Block_Exception('assign() expects a string or array, ' . gettype($spec) . ' given');
			$e->setView($this);
			throw $e;
		}
		 
		return $this;
	}
	
	/**
	 * Clear all assigned variables
	 *
	 * Clears all variables assigned to Zend_View either via {@link assign()} or
	 * property overloading ({@link __get()}/{@link __set()}).
	 *
	 * @return void
	 */
	public function clearVars()
	{
		foreach (get_object_vars($this) as $key => $value) {
			if ($this->_validateAssign($key)) {
				unset($this->$key);
			}
		}
		 
		return $this;
	}
	
	/**
	 * Get all public object vars
	 * 
	 * return array
	 */
	public function getVars()
	{
		$vars = array();
		
		foreach (get_object_vars($this) as $key => $value) {
			if ($this->_validateAssign($key)) {
				$vars[$key] = $this->$key;
			}
		}
			
		return $vars;
	}
	
	/**
	 * Set new script name
	 * 
	 * @param  string $name
	 * @return Core_Block_View
	 */
	public function setScriptName($name)
	{
		$this->_scriptName = (string) $name;
		return $this;
	}
	
	/**
	 * Get script name
	 * 
	 * @return null|string
	 */
	public function getScriptName()
	{
		if (null === $this->_scriptName) {
			require_once 'Zend/Controller/Action/HelperBroker.php';
			$viewSuffix = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix();
			$name = $this->getBlockName();
			$namespace = substr($name, 0, strpos($name, '/'));
			$name = substr($name, strpos($name, '/') + 1);
			$this->_scriptName = $name . '.' . $viewSuffix;
			
			try {
				$this->addScriptPath(Zend_Controller_Front::getInstance()->getModuleDirectory($namespace) . '/views/scripts/');
			} catch (Exception $e) {}
		}
		
		return $this->_scriptName;
	}
		
	/**
	 * Processes a view script and returns the output.
	 *
	 * @param  string $name The script name to process.
	 * @return string The script output.
	 */
	public function render($name)
	{
		if (null !== $name && 'DUMMY' !== $name) {
			$this->setScriptName($name);
		}
		
		if (get_class($this) == 'Core_Block_View') {
			// Templating engine -> block engine
			$names = $this->_getBlockNames();
			foreach ($names as $blockName) {
				try {
					$block = Core::getBlock($blockName);
					$block->setScriptName($this->getScriptName());
					return $block->render('DUMMY'); // If block founded returns this response
				} catch (Exception $e) {
					// If blocks not found or has errors show exception
					$this->_log("Blocks with names '" . implode("','", $names) . "' not found", Zend_Log::WARN);
				}
			}
		}
		
		$this->_log("Try to render script '{$name}'", Zend_Log::DEBUG);
		if ($this->isRendered()) {
			$this->_log("Block already rendered with status '{$this->_rendered}'", Zend_Log::DEBUG);
			return '';
		}		
		
		try {
			$renderType   = $this->getRenderType();
			$cacheEnabled = $this->isCacheEnabled($renderType);
			
			$html = '';
			$json = array();
			$xml  = array();
			
			// Processing prepended blocks
			foreach ($this->getBlockChilds(self::BLOCK_PLACEMENT_BEFORE) as $name => $child) {
				if ($renderType == self::BLOCK_RENDER_TYPE_HTML) {
					$html .= $child->$renderType();
				} else if ($renderType == self::BLOCK_RENDER_TYPE_JSON) {
					$json[self::BLOCK_PLACEMENT_BEFORE][$name] = $child->$responseType();
				} else if ($renderType == self::BLOCK_RENDER_TYPE_XML) {
					$xml[self::BLOCK_PLACEMENT_BEFORE][$name] = $child->$responseType();
				}
			}
			
			// Processing current block
			if (null !== $this->getCache() && $cacheEnabled && $this->getCache()->test($this->getCacheId())) {
				// Load cached data if exists
				$response = $this->getCache()->load($this->getCacheId());
			} else {
				$renderType = 'to' . ucfirst($renderType);
				$this->_log("Selected '{$renderType}' render method", Zend_Log::DEBUG);
				$response = $this->$renderType();
				
				if (null !== $this->getCache() && $cacheEnabled) {
					// save cache if needed
					$this->getCache()->save($response, $this->getCacheId());
				}
			}
			
			// Add to response current block result
			if ($renderType == self::BLOCK_RENDER_TYPE_HTML) {
				$html .= $response;
			} else if ($renderType == self::BLOCK_RENDER_TYPE_JSON) {
				$json['body'] = $response;
			} else if ($renderType == self::BLOCK_RENDER_TYPE_XML) {
				$xml['body'] = $response;
			}
			
			// Processing appended blocks
			foreach ($this->getBlockChilds(self::BLOCK_PLACEMENT_AFTER) as $name => $child) {
				if ($renderType == self::BLOCK_RENDER_TYPE_HTML) {
					$html .= $child->$renderType();
				} else if ($renderType == self::BLOCK_RENDER_TYPE_JSON) {
					$json[self::BLOCK_PLACEMENT_AFTER][$name] = $child->$responseType();
				} else if ($renderType == self::BLOCK_RENDER_TYPE_XML) {
					$xml[self::BLOCK_PLACEMENT_AFTER][$name] = $child->$responseType();
				}
			}
			
			$this->setRendered(self::BLOCK_RENDERED_SUCCESS);
			
			// return response
			if ($renderType == self::BLOCK_RENDER_TYPE_HTML) {
				return $html;
			} else if ($renderType == self::BLOCK_RENDER_TYPE_JSON) {
				$this->getResponse()->setHeader('Content-type', 'application/json', true);
				return json_encode($json);
			} else if ($renderType == self::BLOCK_RENDER_TYPE_XML) {
				//$this->getResponse()->setHeader('Content-type', 'text/xml', true);
				//return $this->_createXml($xml);//TODO: xml creation
			}	
		} catch (Exception $e) {
			$this->setRendered(self::BLOCK_RENDERED_ERROR);
			$this->_log("Render script '{$name}' failed", Zend_Log::ERR);
			require_once 'Core/Block/Exception.php';
			$e = new Core_Block_Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
			$e->setView($this);
			throw $e;
		}
	}
	
	/**
	 * Render method
	 * Renders block contents to html string
	 *
	 * @return string
	 */
	public function toHtml()
	{
		$file = $this->_getScriptFile();
		$this->preRender();
		 
		ob_start();
		include $file;
		return ob_get_clean();
	}
	
	/**
	 * Render method
	 * Renders block contents to json string
	 *
	 * @return string
	 */
	public function toJson()
	{
		$vars = $this->getVars();
		foreach ($vars as $key => &$val) {
			if (is_object($val) && method_exists($val, 'toArray')) {
				$val = $val->toArray();
			}
		}
		
		return $vars;
	}
	
	/**
	 * Render method
	 * Renders block contents to xml string
	 *
	 * @return string
	 */
	public function toXml()
	{
		// TODO: xml as variant for realization in future
		return array();
	}
	
	/**
	 * Helper method
	 * Gets front controller instance
	 * 
	 * @return Zend_Controller_Front
	 */
	public function getFrontController()
	{
		return Zend_Controller_Front::getInstance();
	}
	
	/**
	 * Helper method
	 * Get request object from front controller
	 * 
	 * @return Zend_Controller_Request_Http|Zend_Controller_Request_Abstract
	 */
	public function getRequest()
	{
		return $this->getFrontController()->getRequest();
	}
	
	/**
	 * Helper method
	 * Get response object from front controller
	 * 
	 * @return Zend_Controller_Response_Abstract
	 */
	public function getResponse()
	{
		return $this->getFrontController()->getResponse();
	}
	
	/**
	 * Helper method
	 * Get router object from front controller
	 * 
	 * @return Zend_Controller_Router_Rewrite
	 */
	public function getRouter()
	{
		return $this->getFrontController()->getRouter();
	}
}