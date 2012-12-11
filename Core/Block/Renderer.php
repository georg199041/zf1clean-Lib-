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
 * Basic core view renderer implementation
 *
 * @category   Core
 * @package    Core_Block
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Renderer implements Zend_View_Interface
{
	/**
	 * Placement constants
	 */
	const BLOCK_PLACEMENT_BEFORE = 'before';
	const BLOCK_PLACEMENT_AFTER  = 'after';
	
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
	
	protected static $_defaultCacheEnabling = array();
	
    /**
     * Strict variables flag; when on, undefined variables accessed in the view
     * scripts will trigger notices
     * 
     * @var boolean
     */
	protected $_strictVars = false;
	
	// TODO: ? cache to request method enabling map ?
	
	/**
	 * Cache object
	 * 
	 * @var Zend_Cache_Core
	 */
	protected $_cache;
	
	/**
	 * Cache enable flag
	 * 
	 * @var boolean
	 */
	protected $_cached = false;
	
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
	protected $_logging = false;
	
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
	 * @return Core_Block_Renderer
	 */
	protected function _log($message, $priority = 4)
	{
		if (!$this->isLogging()) {
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
	 * Set protected global block logger instance
	 * You can set instance as generic instance of Zend_Log
	 * or as config array
	 * 
	 * @param  array|Zend_Log $logger
	 * @return Core_Block_Renderer
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
			$this->setLogger(new Zend_Log(new Zend_Log_Writer_Stream('php://output')));
		}
		
		return $this->_logger;
	}
	
	/**
	 * Sel logging enabled or disabled
	 * 
	 * @param  boolean $flag
	 * @return Core_Block_Renderer
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
	 * @return Core_Block_Renderer
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
		return $this->_cache;
	}
	
	/**
	 * Set enable caching block
	 * 
	 * @param  boolean $flag
	 * @return Core_Block_Renderer
	 */
	public function setCached($flag = true)
	{
		$this->_cached = (bool) $flag;
		return $this;
	}
	
	/**
	 * Get cache enabled flag
	 * 
	 * @return boolean
	 */
	public function isCached()
	{
		return $this->_cached;
	}
	
	/**
	 * Setup default request to render type map definition
	 * 
	 * @param  array|string $spec   Array of key => value pairs defaults or sring key of specified request type
	 * @param  string       $value  Value for single type setting
	 * @throws Core_Block_Exception Thrown when definition is not an array or sring and $value pair
	 * @return Core_Block_Renderer
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
		} else {
			require_once 'Core/Block/Exception.php';
			$e = new Core_Block_Exception("Invalid default request render type(s) definition passed, must be an array or key => value pair");
			$e->setView($this);
			throw $e;
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
	 * @return Core_Block_Renderer
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
		} else {
			require_once 'Core/Block/Exception.php';
			$e = new Core_Block_Exception("Invalid request render type(s) definition passed, must be an array or key => value pair");
			$e->setView($this);
			throw $e;
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
	 * @return Core_Block_Renderer
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
	 * @return Core_Block_Renderer
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
	 * Processes a view script and returns the output.
	 *
	 * @param string $name The script name to process.
	 * @return string The script output.
	 */
	public function render($name)
	{
		$this->_log("Try to render script '{$name}'");
		
		try {
			if (!($this->getRequest() instanceof Zend_Controller_Request_Http)) {
				$this->_log("Selected 'toHtml' render method");
				return $this->toHtml();
			}
			
			foreach ($this->_renderMap as $requestMethod => $renderMethod) {
				if ($this->getRequest()->$requestMethod()) {
					$method = 'to' . ucfirst($renderMethod);
					return $this->$method();
				}
			}	
		} catch (Exception $e) {
			$this->_log("Render script '{$name}' failed");
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
		 
	}
	
	/**
	 * Render method
	 * Renders block contents to json string
	 *
	 * @return string
	 */
	public function toJson()
	{
		
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