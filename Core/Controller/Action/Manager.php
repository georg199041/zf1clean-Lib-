<?php

/**
 * Advanced actions manager class
 * 
 * @author Pavlenko Evgeniy
 */
class Core_Controller_Action_Manager
{
	/**
	 * Action classes container
	 * 
	 * @var ArrayObject
	 */
	protected static $_actions = array();
	
	/**
	 * Controller instance container
	 * 
	 * @var Zend_Controller_Action
	 */
	protected $_controller;
	
	/**
	 * Constructor
	 * 
	 * @param  Zend_Controller_Action $controller Current controller instance
	 * @return void
	 */
	public function __construct(Zend_Controller_Action $controller)
	{
		$this->_controller = $controller;
	}
	
	/**
	 * Dispatch action
	 * 
	 * @param  string $actionName Name of action without namespace prefix
	 * @return void
	 * @throws Zend_Controller_Action_Exception
	 */
	public function dispatch($actionName)
	{
		if (!$this->checkAddActionTags($actionName)) {
			return false;
		}
		
		$action = $this->getAction($actionName);
		if (!$action instanceof Core_Controller_Action_Abstract) {
			return false;
		}
		
		if (!method_exists($action, $actionName)) {
			throw new Zend_Controller_Action_Exception("Action \"$actionName\" exist but hasn't of the same name method", 500);
		}
		
		$action->setController($this->_controller);
		$action->$actionName();
		return true;
	}
	
	public function checkAddActionTags($actionName)
	{
		if ('Action' == substr($actionName, -6)) {
			$action = substr($actionName, 0, strlen($actionName) - 6);
		}
		
		$reflect = new Zend_Reflection_Class($this->_controller);
		$actions = $reflect->getDocblock()->getTags('addAction');
		foreach ($reflect->getDocblock()->getTags('addAction') as $tag) {
			if (trim($action) == trim($tag->getDescription())) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get action instance from container
	 * 
	 * @param  string $actionName
	 * @return Core_Controller_Action_Abstract
	 */
    public function getAction($actionName)
	{
		if (!isset(self::$_actions[$actionName])) {
			$this->loadAction($actionName);
		}
		
		return self::$_actions[$actionName];
	}
	
	/**
	 * Load action via autoloader
	 * 
	 * @param  string $actionName
	 * @return void
	 * @throws Zend_Controller_Action_Exception
	 */
	public function loadAction($actionName)
	{
		if ('Action' == substr($actionName, -6)) {
			$action = substr($actionName, 0, strlen($actionName) - 6);
		}
		
		$autoloaders = Zend_Loader_Autoloader::getInstance()->getAutoloaders();
		foreach ($autoloaders as $autoloader) {
			if (!$autoloader instanceof Zend_Application_Module_Autoloader) {
				continue;
			}
			
			$types = $autoloader->getResourceTypes();
			if (!isset($types['controlleractions'])
				|| !isset($types['controlleractions']['namespace'])
				|| empty($types['controlleractions']['namespace'])) {
				continue;
			}
			
			$className = $types['controlleractions']['namespace'] . '_' . ucfirst($action);
			$fileName = $types['controlleractions']['path'] . DIRECTORY_SEPARATOR . ucfirst($action) . '.php';
			
			@include $fileName;
			if (!class_exists($className, false)) {
				continue;
			}
			
			// try to load action (name must be without namespace prefix)
			$class = new $className;
			if (!$class instanceof Core_Controller_Action_Abstract) {
				continue;
			}
			
			self::$_actions[$actionName] = $class; // may be clone needed for clean load
			return;
		}
		
		//throw new Zend_Controller_Action_Exception("Action \"$actionName\" does not exist", 404);
	}
}