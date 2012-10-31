<?php

require_once "Zend/Controller/Action.php";

require_once "Core/Controller/Action/Manager.php";

abstract class Core_Controller_Action extends Zend_Controller_Action
{
	/*protected $_actionsManager;
	
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
	{
		parent::__construct($request, $response, $invokeArgs);
	}
	
	public function getActionsManager()
	{
		if (null === $this->_actionsManager) {
			$this->setActionsManager(new Core_Controller_Action_Manager($this));
		}
		
		return $this->_actionsManager;
	}
	
	public function setActionsManager(Core_Controller_Action_Manager $manager)
	{
		$this->_actionsManager = $manager;
	}
	
	public function __call($methodName, $args)
	{
		if ($this->getActionsManager()->dispatch($methodName)) {
			return;
		}
		
		parent::__call($methodName, $args);
	}*/
	
	public function __($string)
	{
		// translate placeholder
		return $string;
	}
}