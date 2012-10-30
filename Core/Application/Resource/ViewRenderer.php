<?php

require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * View renderer instantiate resource
 *
 * @author     Pavlenko Evgeniy
 * @category   Core
 * @package    Core_Application
 * @version    2.3
 * @subpackage Resource
 * @copyright  Copyright (c) 2012 SunNY Creative Technologies. (http://www.sunny.net)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Application_Resource_ViewRenderer extends Zend_Application_Resource_ResourceAbstract
{
    protected $_helperFile = 'Zend/Controller/Action/Helper/ViewRenderer.php';
	
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
	 * (non-PHPdoc)
	 * @see Zend_Application_Resource_Resource::init()
	 */
    public function init()
    {
        $options = $this->getOptions();
		
		if (isset($options['heperFile']) && isset($options['helperClass'])) {
			$this->_helperFile  = $options['heperFile'];
			$this->_helperClass = $options['helperClass'];
		}
		
		
		// Instantiate view renderer before front controller dispatch started
		if (!Zend_Controller_Action_HelperBroker::hasHelper('viewRenderer')) {
			require_once $this->_helperFile;
			$helperClass = $this->_helperClass;
			//require_once 'Core/Controller/Action/Helper/ViewRenderer.php';
			Zend_Controller_Action_HelperBroker::getStack()->offsetSet(
				-80,
				new $helperClass()
			);
		}
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		
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
