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
 * @subpackage Core_Application_Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Viewrenderer.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * View renderer instantiate resource
 * Can configure many options before displatching request
 *
 * @category   Core
 * @package    Core_Application
 * @subpackage Core_Application_Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Application_Resource_Viewrenderer extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Used view renderer action helper path
     * 
     * @var string
     */
	protected $_helperFile = 'Zend/Controller/Action/Helper/ViewRenderer.php';
	
	/**
	 * Used view renderer class name in helper
	 * 
	 * @var string
	 */
    protected $_helperClass = 'Zend_Controller_Action_Helper_ViewRenderer';
    
    /**
     * Methods names wich have boolean argument
     * 
     * @var array
     */
	protected $_booleanOptions = array(
		'neverRender',
		'neverController',
		'noController',
		'noRender',
	);
	
	/**
	 * Methods names wich have string argument
	 * 
	 * @var array
	 */
	protected $_stringOptions = array(
		'responseSegment',
		'scriptAction',
		'viewBasePathSpec',
		'viewScriptPathSpec',
		'viewScriptPathNoControllerSpec',
		'viewSuffix',
	);
	
	/**
	 * Methods names wich have object argument
	 * 
	 * @var array
	 */
	protected $_instanceOptions = array(
		'view'
	);
	
	/**
	 * Init resource method
	 * 
	 * @return Zend_Controller_Action_Helper_Abstract|Zend_Controller_Action_Helper_ViewRenderer
	 */
    public function init()
    {
        $options = $this->getOptions();
		
		if (isset($options['heperFile']) && isset($options['helperClass'])) {
			$this->_helperFile  = $options['heperFile'];
			$this->_helperClass = $options['helperClass'];
		}
		
		// Instantiate view renderer before front controller dispatch started
		require_once 'Zend/Controller/Action/HelperBroker.php';
		if (!Zend_Controller_Action_HelperBroker::hasHelper('viewRenderer')) {
			require_once $this->_helperFile;
			$helperClass = $this->_helperClass;
			Zend_Controller_Action_HelperBroker::getStack()->offsetSet(
				-80,
				new $helperClass()
			);
		}
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		// Configure helper
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			
			if (in_array($key, $this->_booleanOptions)) {
				$viewRenderer->$method((bool) $value);
			}
			
			if (in_array($key, $this->_stringOptions)) {
				$viewRenderer->$method((string) $value);
			}
			
			if (in_array($key, $this->_instanceOptions)) {
				if (is_string($value)) {
					$class = new $value();
					$viewRenderer->$method($class);
				} else if (is_array($value) && is_string($value['class'])) {
					$class = $value['class'];
					
					if (is_array($value['options'])) {
						$class = new $class($value['options']);
					} else {
						$class = new $class();
					}
					
					$viewRenderer->$method($class);
				}
			}
        }
		
        return $viewRenderer;
    }
}
