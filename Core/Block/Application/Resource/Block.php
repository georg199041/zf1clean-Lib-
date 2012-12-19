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
 * @subpackage Core_Block_Application_Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Block.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * @see Core_Block_View
 */
require_once 'Core/Block/View.php';

/**
 * Block templating system instantiate resource
 * Can configure many block options
 * 
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Application_Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Application_Resource_Block
	extends Zend_Application_Resource_ResourceAbstract
{
	/**
	 * Main block class container
	 * 
	 * @var Core_Block_View
	 */
	protected $_block;
	
	/**
	 * Initialize main block instance
	 * Here were made modifies in:
	 * view renderer (change view template engine)
	 * layout (change view template engine)
	 * 
	 * @return Core_Block_View
	 */
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
	
	/**
	 * Instantiate and configure main block class
	 * 
	 * @return Core_Block_View
	 */
	public function getBlockContainer()
	{
		if (null === $this->_block) {
			$options = $this->getBootstrap()->getOption('resources');
			$options = (array) $options['view'];
			
			//$logger = new Zend_Log(new Zend_Log_Writer_Firebug());
			$this->_block = new Core_Block_View(array_merge_recursive(array('blocks' => $this->getOptions(), 'logger' => $logger), $options));
		}
		
		return $this->_block;
	}
}
