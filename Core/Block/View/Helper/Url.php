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
 * @package    Core_Block
 * @subpackage Core_Block_View_Helper
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Url.php 0.1 2012-12-12 pavlenko $
 */

/** Zend_View_Helper_Abstract.php */
require_once 'Zend/View/Helper/Abstract.php';

/**
 * Helper for making easy links and getting urls that depend on the routes and router
 * Added parse * like options as current associated in request
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_View_Helper
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_View_Helper_Url extends Zend_View_Helper_Abstract
{
    /**
     * Generates an url given the name of a route.
     *
     * @param  array $urlOptions Options passed to the assemble method of the Route object.
     * @param  mixed $name       The name of a Route to use. If null it will use the current Route
     * @param  bool  $reset      Whether or not to reset the route defaults with those provided
     * @param  bool  $encode     Whether or not to encode url parts with url_encode function
     * @return string Url for the link href attribute.
     */
    public function url(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        require_once 'Zend/Controller/Front.php';
    	$front = Zend_Controller_Front::getInstance();
    	$router = $front->getRouter();
    	
    	// EXTENDING
    	if (array_key_exists('module', $urlOptions) && $urlOptions['module'] == '*') {
    		$urlOptions['module'] = $front->getRequest()->getModuleName();
    	}
        
        if (array_key_exists('controller', $urlOptions) && $urlOptions['controller'] == '*') {
    		$urlOptions['controller'] = $front->getRequest()->getControllerName();
    	}
        
        if (array_key_exists('action', $urlOptions) && $urlOptions['action'] == '*') {
    		$urlOptions['action'] = $front->getRequest()->getActionName();
    	}
    	// /EXTENDING
    	
        return $router->assemble($urlOptions, $name, $reset, $encode);
    }
}
