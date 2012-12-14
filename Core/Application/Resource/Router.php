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
 * @version    $Id: Router.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Application_Resource_Router
 */
require_once 'Zend/Application/Resource/Router.php';

/**
 * Extending router resource
 * Here added parse of order attribute in route config before it can builds in router
 * 
 * @category   Core
 * @package    Core_Application
 * @subpackage Core_Application_Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Application_Resource_Router extends Zend_Application_Resource_Router
{
	/**
	 * Sortimng implementation method
	 * Calculates offset position difference between routes
	 * 
	 * @param  array $a Route A
	 * @param  array $b Route B
	 * @return int If positions are identical - returns 0, else if
	 * position of Route A above position of Route B returns -1, else returns 1
	 */
	public function sortRoutes($a, $b)
	{
		$a['order'] = (int) $a['order'];
		$b['order'] = (int) $b['order'];
		
		if ($a['order'] == $b['order']) {
			return 0;
		}
		
		return $a['order'] > $b['order'] ? 1 : -1;
	}
	
	/**
	 * Get application router
	 * If not exist try to instantiate it from FrontController and configure
	 * 
	 * @return Zend_Controller_Router_Rewrite
	 */
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
			
			// Here added implementation of order property
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