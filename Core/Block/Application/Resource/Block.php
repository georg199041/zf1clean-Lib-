<?php

require_once 'Zend/Application/Resource/ResourceAbstract.php';

require_once 'Core/Block/View.php';

class Core_Block_Application_Resource_Block
	extends Zend_Application_Resource_ResourceAbstract
{
	protected $_block;
	
	public function init()
	{
		// Bootstrapping view first
		$this->getBootstrap()->bootstrap('View');
		
		// Bootstrapping other resources
		$this->getBootstrap()->bootstrap('ViewRenderer');
		$viewRenderer = $this->getBootstrap()->getResource('ViewRenderer');
		
		$this->getBootstrap()->bootstrap('Layout');
		$layout = $this->getBootstrap()->getResource('Layout');
		
		// Instantiate manager
		$container = $this->getBlockContainer();

		// Replace view
		$layout->setView($container);
		$viewRenderer->setView($container);
		
		// Save in container
		return $container;
	}
	
	public function getBlockContainer()
	{
		if (null === $this->_block) {
			$options = $this->getBootstrap()->getOption('resources');
			$options = (array) $options['view'];
			//var_export($options);
			//var_export($this->getOptions());
			//var_export(array_merge_recursive($this->getOptions(), $options));
			
			$this->_block = new Core_Block_View(array_merge_recursive(array('blocks' => $this->getOptions()), $options));
			//var_export($this->_blockContainer);
		}
		
		return $this->_block;
	}
}
