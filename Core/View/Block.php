<?php

require_once 'Core/View/Abstract.php';

/**
 * Abstract block templating engine
 *
 * @author     Pavlenko Evgeniy
 * @category   Core
 * @package    Core_View
 * @version    2.3
 * @subpackage Block
 * @copyright  Copyright (c) 2012 SunNY Creative Technologies. (http://www.sunny.net)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_View_Block extends Core_View_Abstract
{
	/**
	 * Used view files suffix
	 * 
	 * @var string
	 */
	protected $_viewSuffix = 'phtml';
	
	/**
	 * Requested module name
	 * 
	 * @var string
	 */
	protected $_moduleName; 
	
	/**
	 * Requested controller name
	 * 
	 * @var string
	 */
	protected $_controllerName;
	
	/**
	 * Requested action name
	 * 
	 * @var string
	 */
	protected $_actionName;
	
	/**
	 * Front controller container
	 * 
	 * @var Zend_Controller_Front
	 */
	protected $_frontController;
	
	/**
	 * Request object
	 * 
	 * @var Zend_Controller_Request_Abstract
	 */
	protected $_request;
	
	/**
	 * Script name
	 * 
	 * @var string
	 */
	protected $_scriptName;
	
	/**
	 * Parent block object
	 * 
	 * @var Core_View_Block
	 */
	protected $_parent;
	
	/**
	 * Child blocks collection
	 * 
	 * @var array
	 */
	protected $_childs = array();

    /**
     * Includes the view script in a scope
     *
     * @param string The view script to execute.
     */
	protected function _run()
	{
		include func_get_arg(0);
	}
	
	/**
	 * Parse script file name from block data
	 * 
	 * @return boolean|string Path string or false if file not exists or not readable
	 */
	protected function _parseScriptFilename()
	{
		$parts = explode('/', $this->getScriptName());
		
		if ($parts[0] != 'application') {
			$moduleDir = $this->getFrontController()->getModuleDirectory($parts[0]);
		} else {
			$moduleDir = APPLICATION_PATH;
		}
		
		if (empty($moduleDir)) {
			return false;
		}
		
		$moduleDir .= '/views/scripts';
		
		$controllerDir = 'index';
		if (!isset($parts[1])) {
			$controllerDir = $parts[1];
		}
		
		$action = 'index';
		if (isset($parts[2])) {
			$action = $parts[2];
		}
		
		$suffix = $this->getViewSuffix();
		
		$file = $moduleDir . '/' . $controllerDir . '/' . $action . '.' . $suffix;
		//echo $file;
		if (!is_readable($file)) {
			throw new Exception("Script");
			return false;
		}
		
		return $file;
	}
	
	public function __construct($config = array())
	{
		if (is_array($config)) {
			foreach ($config as $key => $value) {
				$method = 'set' . ucfirst($key);
				if (method_exists($this, $method)) {
					$this->$method($value);
				}
			}
		}
		
		parent::__construct($config);
	}
	
	/**
	 * Get view file suffix
	 * 
	 * @return string
	 */
	public function getViewSuffix()
	{
		return $this->_viewSuffix;
	}
	
	/**
	 * Set new view file suffix
	 * 
	 * @param string $suffix
	 */
	public function setViewSuffix($suffix)
	{
		$this->_viewSuffix = $suffix;
		return $this;
	}
	
	/**
	 * Get requested module name
	 * 
	 * @return string|boolean Return name string or false if can't get
	 */
	public function getModuleName()
	{
		if (null === $this->_moduleName) {
			$this->_moduleName = $this->getRequest()->getModuleName();
		}
		
		if (null === $this->_moduleName) {
			$this->_moduleName = false;
		}
		
		return $this->_moduleName;
	}
	
	/**
	 * Set requested module name
	 * 
	 * @param string $name
	 */
	public function setModuleName($name)
	{
		$this->_moduleName = $name;
		return $this;
	}
	
	/**
	 * Get requested controller name
	 * 
	 * @return string|boolean Return name string or false if can't get
	 */
	public function getControllerName()
	{
		if (null === $this->_controllerName) {
			$this->_controllerName = $this->getRequest()->getControllerName();
		}
		
		if (null === $this->_controllerName) {
			$this->_controllerName = false;
		}
		
		return $this->_controllerName;
	}
	
	/**
	 * Set requested controller name
	 * 
	 * @param string $name
	 */
	public function setControllerName($name)
	{
		$this->_controllerName = $name;
		return $this;
	}
	
	/**
	 * Get requested action name
	 * 
	 * @return string|boolean Return name string or false if can't get
	 */
	public function getActionName()
	{
		if (null === $this->_actionName) {
			$this->_actionName = $this->getRequest()->getActionName();
		}
		
		if (null === $this->_actionName) {
			$this->_actionName = false;
		}
		
		return $this->_actionName;
	}
	
	/**
	 * Set requested action name
	 * 
	 * @param string $name
	 */
	public function setActionName($name)
	{
		$this->_actionName = $name;
		return $this;
	}
	
	/**
	 * Get front controller
	 * Instantiate if not set
	 * 
	 * @return Zend_Controller_Front
	 */
	public function getFrontController()
	{
		if (null === $this->_frontController) {
			$this->setFrontController(Zend_Controller_Front::getInstance());
		}
		
		return $this->_frontController;
	}
	
	/**
	 * Set front controller instance to block
	 * 
	 * @param Zend_Controller_Front $front
	 */
	public function setFrontController(Zend_Controller_Front $front)
	{
		$this->_frontController = $front;
		return $this;
	}
	
	/**
	 * Get request object
	 * Try to instantiate from front controller
	 * Or create manually if all previous operations failure
	 * 
	 * @return Zend_Controller_Request_Abstract
	 */
	public function getRequest()
	{
		if (null === $this->_request) {
			$request = $this->getFrontController()->getRequest();
			
			if (null === $request) {
				$request = new Zend_Controller_Request_Http();
				$this->getFrontController()->setRequest($request);
			}
			
			$this->setRequest($request);
		}
		
		return $this->_request;
	}
	
	/**
	 * Set new request object
	 * 
	 * @param Zend_Controller_Request_Abstract $request
	 */
	public function setRequest(Zend_Controller_Request_Abstract $request)
	{
		$this->_request = $request;
		return $this;
	}
	
	/**
	 * Render script
	 * 
	 * @param  string $name Argument required by abstract but not used
	 * @return string       Result of rendering
	 */
	public function render($name = null)
	{
        echo get_called_class();die("Rewrite logic to implements Zend_Layout");
		unset($name); // remove $name from local scope NOT USED HERE
		
        // find the script file name using the parent private method
        $filename = $this->_parseScriptFilename();
        if (!$filename) {
        	return "";
        }

        ob_start();
        $this->_run($filename);

        return ob_get_clean();
	}
	
	/**
	 * Get script name rule
	 * Parse from class name if not set
	 * 
	 * @return string
	 */
	public function getScriptName()
	{
		if (null === $this->_scriptName) {
			$parts = explode('_', strtolower(Zend_Filter::filterStatic(get_class($this), 'Word_CamelCaseToDash')));
			
			if (isset($parts[3]) && $parts[3] == 'index') {
				unset($parts[3]); // cleanup action
			}
			
			if (isset($parts[2]) && $parts[3] == 'index' && !isset($parts[3])) {
				unset($parts[2]); // cleanup controller
			}
			
			unset($parts[1]); // cleanup block namespace
			$this->_scriptName = implode('/', $parts);
		}
		
		return $this->_scriptName;
	}
	
	/**
	 * Set script name
	 * 
	 * @param string $name
	 */
	public function setScriptName($name)
	{
		$this->_scriptName = $name;
		return $this;
	}
	
	public static function getBlockClassName($name)
	{
		$inflector = new Zend_Filter_Word_DashToCamelCase();
		list($module, $controller, $action) = explode('/', $name);
		
		$className = $inflector->filter($module) . '_Block';
		
		if (!empty($controller)) {
			$className .= '_' . $inflector->filter($controller);
		} else {
			$className .= '_Index';
		}
		
		if (!empty($action) && $action != 'index') {
			$className .= '_' . $inflector->filter($action);
		}
		
		return $className;
	}
	
	public static function loadBlockName($name = null)
	{
		if (!$name) {
			return;
		}
		
		return self::loadBlockClass(self::getBlockClassName($name));
	}
	
	public static function loadBlockClass($className = null)
	{
		if (!$className) {
			return;
		}
		
		if (@class_exists($className, true)) {
			$class = new $className();
			return $class;
		}
		
		return;
	}
	
	public function getBlock($name)
	{
		$block = self::loadBlockName($name);
		if (!$block instanceof Core_View_Block) {
			return;
		}
		
		$block->setViewSuffix($this->getViewSuffix());
		return $block;
	}
	
	/*public function getParent()
	{
		return $this->_parent;
	}
	
	public function setParent(Core_View_Block $block)
	{
		$this->_parent = $block;
		return $this;
	}
	
	public function getChilds()
	{
		return $this->_childs;
	}
	
	public function setChilds(array $childs)
	{
		$this->_childs = $childs;
		return $this;
	}*/
	
	public function getChild($name)
	{
		return isset($this->_childs[$name]) ? $this->_childs[$name] : null;
	}
	
	public function setChild(Core_View_Block $object, $name)
	{
		$this->_childs[$name] = $object;
		return $this;
	}
	
	public function __toString()
	{
		//TODO: render html request baset type output
		return $this->render();
	}
}