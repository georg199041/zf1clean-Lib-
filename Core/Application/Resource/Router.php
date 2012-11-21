<?php

require_once 'Zend/Application/Resource/Router.php';

class Core_Application_Resource_Router extends Zend_Application_Resource_Router
{
	public function sortRoutes($a, $b)
	{
		$a['order'] = (int) $a['order'];
		$b['order'] = (int) $b['order'];
		
		if ($a['order'] == $b['order']) {
			return 0;
		}
		
		return $a['order'] > $b['order'] ? 1 : -1;
	}
	
	public function getRouter()
	{
		if (null === $this->_router) {
			$bootstrap = $this->getBootstrap();
			$bootstrap->bootstrap('FrontController');
			$this->_router = $bootstrap->getContainer()->frontcontroller->getRouter();
	
			$options = $this->getOptions();
			if (!isset($options['routes'])) {
				$options['routes'] = array();
			}
			
			uasort($options['routes'], array($this, 'sortRoutes'));
	
			if (isset($options['chainNameSeparator'])) {
				$this->_router->setChainNameSeparator($options['chainNameSeparator']);
			}
	
			if (isset($options['useRequestParametersAsGlobal'])) {
				$this->_router->useRequestParametersAsGlobal($options['useRequestParametersAsGlobal']);
			}
	
			$this->_router->addConfig(new Zend_Config($options['routes']));
		}
	
		return $this->_router;
	}
}