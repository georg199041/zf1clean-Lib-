<?php

require_once "Zend/Application/Bootstrap/Bootstrap.php";

class Core_Application_Bootstrap_Abstract extends Zend_Application_Bootstrap_Bootstrap
{
    public function __construct($application)
    {
    	parent::__construct($application);
    	$this->initResourceLoader();
    }
	
	public function initResourceLoader()
	{
		$this->getResourceLoader()->addResourceTypes(array(
			'controllerhelpers' => array(
				'namespace' => 'Controller_Helper',
				'path'      => 'Controller/Helpers'
			),
			'controlleractions' => array(
				'namespace' => 'Controller_Action',
				'path'      => 'Controller/Actions'
			),
			// Required for new model structure
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
}
