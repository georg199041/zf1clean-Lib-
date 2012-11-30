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
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Cachemanager.php 1.0 2012-11-30 13:20:00Z Pavlenko $
 */

/** @see Zend_Cache_Manager */
require_once 'Zend/Application/Resource/Cachemanager.php';

/**
 * Cache Manager resource overriding class
 * 
 * @category   Core
 * @package    Core_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Application_Resource_Cachemanager extends Zend_Application_Resource_Cachemanager
{
	/**
	 * Cache manager class name to use
	 * 
	 * @var string
	 */
	protected $_managerClass = 'Core_Cache_Manager';
	
    /**
     * Retrieve Zend_Cache_Manager instance
     *
     * @return Zend_Cache_Manager
     */
    public function getCacheManager()
    {
        if (null === $this->_manager) {
        	$options = $this->getOptions();
        	if (is_string($options['managerClass'])) {
        		$this->_managerClass = $options['managerClass'];
	        	unset($options['managerClass']);
        	}
        	
        	$this->_manager = new $this->_managerClass;

            $options = $this->getOptions();
            foreach ($options as $key => $value) {
                if ($this->_manager->hasCacheTemplate($key)) {
                    $this->_manager->setTemplateOptions($key, $value);
                } else {
                    $this->_manager->setCacheTemplate($key, $value);
                }
            }
            
            Zend_Registry::set('Zend_Cache_Manager', $this->_manager);
        }

        return $this->_manager;
    }
}