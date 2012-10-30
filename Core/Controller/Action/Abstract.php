<?php

abstract class Core_Controller_Action_Abstract
{
	protected $_controller;
	
	public function __construct(Zend_Controller_Action $controller = null)
	{
		if (null !== $controller) {
			$this->setController($controller);
		}
	}
	
	public function setController(Zend_Controller_Action $controller)
	{
		$this->_controller = $controller;
		return $this;
	}
	
	public function getController()
	{
		return $this->_controller;
	}
	
	public function __call($methodName, $arguments)
	{
		if (!method_exists($this->getController(), $methodName)) {
			throw new Zend_Controller_Action_Exception("Method \"$methodName\" not found in controller \"" . get_class($this->getController()) . "\"", 500);
		}
		
		return call_user_func_array(array($this->getController(), $methodName), $arguments);
	}
}