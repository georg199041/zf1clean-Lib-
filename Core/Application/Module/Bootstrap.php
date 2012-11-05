<?php

require_once "Core/Application/Bootstrap/Abstract.php";

abstract class Core_Application_Module_Bootstrap extends Core_Application_Bootstrap_Abstract
{
	/**
	 * Set this explicitly to reduce impact of determining module name
	 * @var string
	 */
	protected $_moduleName;
	
	/**
	 * Constructor
	 *
	 * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
	 * @return void
	 */
	public function __construct($application)
	{
		$this->setApplication($application);
	
		// Use same plugin loader as parent bootstrap
		if ($application instanceof Zend_Application_Bootstrap_ResourceBootstrapper) {
			$this->setPluginLoader($application->getPluginLoader());
		}
	
		$key = strtolower($this->getModuleName());
		if ($application->hasOption($key)) {
			// Don't run via setOptions() to prevent duplicate initialization
			$this->setOptions($application->getOption($key));
		}
	
		if ($application->hasOption('resourceloader')) {
			$this->setOptions(array(
				'resourceloader' => $application->getOption('resourceloader')
			));
		}
		$this->initResourceLoader();
	
		// ZF-6545: ensure front controller resource is loaded
		if (!$this->hasPluginResource('FrontController')) {
			$this->registerPluginResource('FrontController');
		}
	
		// ZF-6545: prevent recursive registration of modules
		if ($this->hasPluginResource('modules')) {
			$this->unregisterPluginResource('modules');
		}
	}
	
	/**
	 * Ensure resource loader is loaded
	 *
	 * @return void
	 */
	public function initResourceLoader()
	{
		$this->getResourceLoader()->addResourceTypes(array(
			// Required for new model structure
			'controllerplugins' => array(
				'namespace' => 'Controller_Plugin',
				'path'      => 'Controller/Plugin'
			),
			'model'   => array(
                'namespace' => 'Model',
                'path'      => 'Model',
            ),
			'mappers' => array(
                'namespace' => 'Model_Mapper',
                'path'      => 'Model/Mapper',
            ),
			'entities' => array(
                'namespace' => 'Model_Entity',
                'path'      => 'Model/Entity',
            ),
			'collections' => array(
				'namespace' => 'Model_Collection',
				'path'      => 'Model/Collection',
			),
			'sources' => array(
				'namespace' => 'Model_Source',
				'path'      => 'Model/Source',
			),
			'blocks' => array(
				'namespace' => 'Block',
				'path'      => 'Block',
			),
		));
	}
	
	/**
	 * Get default application namespace
	 *
	 * Proxies to {@link getModuleName()}, and returns the current module
	 * name
	 *
	 * @return string
	 */
	public function getAppNamespace()
	{
		return $this->getModuleName();
	}
	
	/**
	 * Retrieve module name
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		if (empty($this->_moduleName)) {
			$class = get_class($this);
			if (preg_match('/^([a-z][a-z0-9]*)_/i', $class, $matches)) {
				$prefix = $matches[1];
			} else {
				$prefix = $class;
			}
			$this->_moduleName = $prefix;
		}
		return $this->_moduleName;
	}
}