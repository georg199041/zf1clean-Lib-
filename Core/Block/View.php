<?php

require_once "Zend/View/Interface.php";

require_once "Core/Attributes.php";

/**
 * Default view rendering engine
 * Proxied to Zend_View
 *
 * @author     Pavlenko Evgeniy
 * @category   Core
 * @package    Core_Block
 * @version    2.1
 * @subpackage View
 * @copyright  Copyright (c) 2012 SunNY Creative Technologies. (http://www.sunny.net)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_View extends Core_Attributes implements Zend_View_Interface
{
	const BLOCK_PLACEMENT_BEFORE = 'before';
	
	const BLOCK_PLACEMENT_AFTER = 'after';
	
	/**
	 * Global engine object
	 * 
	 * @var object
	 */
	protected static $_engine;
	
	/**
	 * Cache object
	 * 
	 * @var object
	 */
	protected static $_cache;
	
	/**
	 * Template script name
	 * 
	 * @var string
	 */
	protected $_scriptName;
	
	/**
	 * Template name highlighter enabled flag
	 * 
	 * @var boolean
	 */
	protected $_highligterEnabled = false;
	
	/**
	 * Flag of rendering completed
	 * 
	 * @var boolean
	 */
	protected $_rendered = false;
	
	/**
	 * Request object
	 * 
	 * @var Zend_Controller_Request_Abstract
	 */
	protected $_request;
	
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
	 * Default template highlighter css styles
	 * 
	 * @var array
	 */
	protected $_highliterCss = array(
		'background' => '#ccc',
		'padding'    => '1px',
		'border'     => '1px solid #999'
	);
	
	/**
	 * Throw custom view exception
	 * 
	 * @param  string $message
	 * @throws Zend_View_Exception
	 */
	protected function _throwViewException($message)
	{
		require_once 'Zend/View/Exception.php';
		$e = new Zend_View_Exception($message);
		$e->setView($this);
		throw $e;
	}
	
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
	 * Search script file
	 * 
	 * @throws Exception
	 * @return string
	 */
	protected function _getScriptFile()
	{
		$name = $this->getScriptName();
		if (null === $name) {
			throw new Exception("Script name not defined");
		}
		
		foreach ($this->getScriptPaths() as $path) {
			$file = $path . $name;
			if (file_exists($file)) {
				return $file;
			}
		}
		
		$paths = implode(', ', $this->getScriptPaths());
		throw new Exception("Script '$name' not found in paths: $paths");
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
			throw new Exception("Script name not defined");
		}
		
		require_once 'Zend/Controller/Action/HelperBroker.php';
		$viewSuffix = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix();
		$name = str_ireplace(".$viewSuffix", '', $name);
		
		require_once 'Zend/Controller/Front.php';
		$module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
		
		return array($module . '/' . $name, 'application/' . $name);;
	}
	
	/**
	 * Render highliter dom
	 * 
	 * @param  string $file
	 * @return string
	 */
	protected function _renderHighliter($file)
	{
		if ($this->isHighlighterEnabled()) {
			$css = array();
			foreach ($this->getHighlighterCss() as $key => $val) {
				$css[] = "$key:$val";
			}
			
			return '<div style="position:relative">'
			     . '<div style="position:absolute;' . implode(';', $css) . '">'
			     . $file
			     . '</div>'
			     . '</div>';
		}
		
		return '';
	}
	
	/**
	 * Render other blocks to specified placement
	 * 
	 * @param  string $placement response key
	 * @return string
	 */
	protected function _renderBlocks($placement = self::BLOCK_PLACEMENT_AFTER)
	{
		$response = '';
		foreach ($this->getBlockChilds($placement) as $child) {
			$response .= $child->render();
		}
		
		return $response;
	}
	
	/**
	 * Render blocks to layout keys
	 */
	protected function _renderBlocksToLayout()
	{
		$layout = Zend_Layout::startMvc();
		$placements = $this->getBlockChilds(null);
		foreach ($placements as $placement => $blocks) {
			if ($placement == self::BLOCK_PLACEMENT_AFTER || $placement == self::BLOCK_PLACEMENT_BEFORE) {
				continue;
			}
			
			foreach ($blocks as $child) {
				$layout->{$placement} .= $child->render();
			}
		}
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
	
	public function __($string)
	{
		return $string;// TODO
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
			$name = substr($name, strpos($name, '/') + 1);
			$this->_scriptName = $name . '.' . $viewSuffix;
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
		$this->_blockName = $name;
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
	 * Set parent block
	 * 
	 * @param  Core_Block_View $parent
	 * @return Core_Block_View
	 */
	public function setBlockParent(Core_Block_View $parent)
	{
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
	 * Get childs blocks
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
	 * @param  string|Core_View_Block $child
	 * @param  string                 $name
	 * @param  array                  $options [OPTIONAL]
	 * @throws Exception If not found
	 * @return Core_Block_View
	 */
	public function addBlockChild($child, $placement = self::BLOCK_PLACEMENT_AFTER)
	{
		if (null === $placement) {
			$placement = self::BLOCK_PLACEMENT_AFTER;
		}
		
		if ($child instanceof Core_Block_View) {
			$child->setBlockParent($this);
			$this->_blockChilds[$placement][$child->getBlockName()] = $child;
		} else if (is_array($child)) {
			if (!isset($child['type'])) {
				throw new Exception("Block options must have type");
			}
			
			$className = ucfirst(Zend_Filter::filterStatic($child['type'], 'Word_DashToCamelCase'));
			if (false === stripos($className, '_')) {
				$className = 'Core_Block_' . $className . '_Widget';
			}
			
			if (!@class_exists($className, true)) {
				throw new Exception("Class '$className' not found");
			}
			
			unset($child['type']);
			$class = new $className($child);
			$class->setBlockParent($this);
			$this->_blockChilds[$placement][$class->getBlockName()] = $class;
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
		if (null === $placement) {
			$placement = self::BLOCK_PLACEMENT_AFTER;
		}
		
		return $this->_blockChilds[$placement][$name];
	}
	
	public function getBlockChildPlacements()
	{
		return array_keys($this->_blockChilds);
	}
	
	/**
	 * Delete block from childs
	 * 
	 * @param  string $name
	 * @return Core_Block_View
	 */
	public function delBlockChild($name, $placement = null)
	{
		if (null === $placement) {
			foreach ($this->_blockChilds as $placement => $blocks) {
				$this->_blockChilds[$placement][$name] = null;
				unset($this->_blockChilds[$placement][$name]);
			}
		} else {
			$this->_blockChilds[$placement][$name] = null;
			unset($this->_blockChilds[$placement][$name]);
		}
		
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
		//$this->_rendered = (bool) $flag;
		return $this;
	}
	
	/**
	 * Check if rendered flag set
	 * 
	 * @return boolean
	 */
	public function isRendered()
	{
		return $this->_rendered;
	}
	
	public function setRequest(Zend_Controller_Request_Abstract $request)
	{
		$this->_request = $request;
		return $this;
	}
	
	public function getRequest()
	{
		if (null === $this->_request) {
			$this->setRequest(Zend_Controller_Front::getInstance()->getRequest());
		}
		
		return $this->_request;
	}
	
	/**
	 * Extensions initialize method
	 */
	public function init()
	{}
	
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
     * Set enabling highlighter flag
     * 
     * @param  boolean $flag
     * @return Core_Block_View
     */
    public function setHighlighterEnabled($flag = true)
    {
    	$this->_highligterEnabled = (bool) $flag;
    	return $this;
    }
    
    /**
     * Check if enabling highlighter flag set
     * 
     * @return boolean
     */
    public function isHighlighterEnabled()
    {
    	return $this->_highligterEnabled;
    }
	
    /**
     * Set highlighter css options
     * 
     * @param  array $css
     * @return Core_Block_View
     */
    public function setHighlighterCss(array $css)
    {
    	$this->_highliterCss = $css;
    	return $this;
    }

    /**
     * Get highlighter css options
     * 
     * @return array
     */
    public function getHighlighterCss()
    {
    	return $this->_highliterCss;
    }
    
    /**
     * Set script search path
     * Proxied to engine
     * 
     * @param unknown_type $path
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
    public static function setCache(Zend_Cache_Core $core)
    {
    	self::$_cache = $core;
    }
    
    /**
     * Get cache object
     * 
     * @return Zend_Cache_Core
     */
    public static function getCache()
    {
    	return self::$_cache;
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
    public function __get($key){}

    /**
     * Set new property
     * 
     * @param mixed $key
     * @param mixed $val
     */
    public function __set($key, $val)
    {
    	if ($this->_validateAssign($key)) {
    		$this->$key = $val;
    		return;
    	}
    	
    	$this->_throwViewException('Setting private or protected class members is not allowed');
    }

    /**
     * Check property exists
     * 
     * @param  mixed $key
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
     * Delete property
     * 
     * @param mixed $key
     */
    public function __unset($key)
    {
    	if ($this->_validateAssign($key) && isset($this->$key)) {
    		unset($this->$key);
    	}
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
    	if (is_string($spec)) {
    		if (!$this->_validateAssign($spec)) {
    			$this->_throwViewException('Setting private or protected class members is not allowed');
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
    			$this->_throwViewException('Setting private or protected class members is not allowed');
    		}
    	} else {
    		$this->_throwViewException('assign() expects a string or array, received ' . gettype($spec));
    	}
    	
    	return $this;
    }

    /**
     * Clear all properties
     * 
     * @return Core_Block_View
     */
    public function clearVars()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if ($this->_validateAssign($key)) {
                unset($this->$key);
            }
        }
    	
    	return $this;
    }

    /**
     * Render script, highlighter and blocks
     * 
     * @param  string $name overrided script name
     * @return string
     */
    public function render($name = null)
    {
    	if ($this->isRendered()) {
    		//return '';
    	}
    	
    	$exceptions = array();
    	if (null !== $name) {
    		$this->setScriptName($name);
    	}
    	
    	if (get_class($this) == 'Core_Block_View') {
    		$names = $this->_getBlockNames();
    		foreach ($names as $blockName) {
	    		try {
	    			$block = Core::getBlock($blockName);
	    			$block->setScriptName($this->getScriptName());
	    			return $block->render();
	    		} catch (Exception $e) {
	    			$exceptions[] = $e->getMessage();
	    		}
    		}
    	}
    	
    	$this->_renderBlocksToLayout();
   		
   		$response = '';
   		$response .= $this->_renderBlocks(self::BLOCK_PLACEMENT_BEFORE);
   		
   		try {
   			if (null !== self::getCache() && self::getCache()->test($this->getBlockName())) {
   				$script = self::getCache()->load($this->getBlockName());
   			} else {
	   			$file = $this->_getScriptFile();
	   			$this->preRender();
	    		
		    	ob_start();
	    		include $file;
	    		$script = ob_get_clean();
	    		
	    		$this->setRendered(true);
	    		if (null !== self::getCache()) {
	    			self::getCache()->save($script, $this->getBlockName());
	    		}
   			}
   			
    		$response .= $this->_renderHighliter($file) . $script;
    	} catch (Exception $e) {
    		$exceptions[] = $e->getMessage();
    		if (count($this->getBlockChilds()) == 0) {
    			$response = implode(" OR\n", $exceptions);
    		}
    	}
    	
    	$response .= $this->_renderBlocks(self::BLOCK_PLACEMENT_AFTER);
    	return $response;
    }
    
    /**
     * Rendering by echo call
     * 
     * @return string
     */
    public function __toString()
    {
    	return $this->render(); // REQUIRED by interface
    }
}
