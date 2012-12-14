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
 * @package    Core_Application
 * @subpackage Core_Application_Bootstrap
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Application_Bootstrap_Bootstrap
 */
require_once "Zend/Application/Bootstrap/Bootstrap.php";

/**
 * Application bootstraping class
 * Here you can added some resource types for application loader
 * 
 * @category   Core
 * @package    Core_Application
 * @subpackage Core_Application_Bootstrap
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Application_Bootstrap_Abstract extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Constructor
     * 
     * @param Zend_Application $application
     */
	public function __construct($application)
    {
    	parent::__construct($application);
    	$this->initResourceLoader();
    }
	
    /**
     * Initialise resourse types for application
     * If you need to add some type for application you must do it here
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
}
