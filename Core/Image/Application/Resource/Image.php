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
 * @package    Core_Image
 * @subpackage Core_Image_Application_Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Image.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * Image system connector resource
 *
 * @category   Core
 * @package    Core_Image
 * @subpackage Core_Image_Application_Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Image_Application_Resource_Image extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Initialize and configure image system
     * Add to view engine image helper
     * 
     * @return Core_Image_Application_Resource_Image
     */
	public function init()
    {
    	$options = $this->getOptions();
    	
    	require_once 'Zend/Controller/Action/HelperBroker.php';
    	$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		if (null === $viewRenderer->view) {
			$viewRenderer->initView();
		}
		
		$viewRenderer->view->addHelperPath('Core/Image/View/Helper', 'Core_Image_View_Helper');
		
		if (isset($options['noImagePath'])) {
			require_once "Core/Image/Factory.php";
			Core_Image_Factory::setNoImagePath($options['noImagePath']);
		}
		
        return $this;
    }
}
