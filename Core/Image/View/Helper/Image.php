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
 * @subpackage Core_Image_View_Helper
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Image.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Zend_Exception
 */
require_once 'Core/Image/Exception.php';

/**
 * @see Zend_View_Helper_Abstract
 */
require_once "Zend/View/Helper/Abstract.php";

/**
 * @see Core_Image_Factory
 */
require_once "Core/Image/Factory.php";

/**
 * @see Core_Image_Adapter_Abstract
 */
require_once "Core/Image/Adapter/Abstract.php";

/**
 * Image view helper class
 *
 * @category   Core
 * @package    Core_Image
 * @subpackage Core_Image_View_Helper
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Image_View_Helper_Image extends Zend_View_Helper_Abstract
{
	/**
	 * Image adaper object
	 * 
	 * @var Core_Image_Adapter_Abstract
	 */
	protected $_image;
	
	/**
	 * Image tag attributes
	 * 
	 * @var array
	 */
	protected $_attributes = array();
	
	/**
	 * Image exception object
	 * 
	 * @var Core_Image_Exception
	 */
	protected $_exception;
	
	/**
	 * Render image tag attributes to string
	 * 
	 * @return string
	 */
	protected function _renderAttributes()
	{
		$return = '';
		foreach ($this->_attributes as $key => $value) {
			$return .= ' ' . $key . '="' . $value . '"';
		}
		
		return $return;
	}
	
	/**
	 * Main helper method
	 * 
	 * @param  string $path           Path to image file
	 * @param  array  $attributes     Image tag attributes
	 * @return Core_View_Helper_Image
	 */
	public function image($path, array $attributes = null)
	{
		$this->_exception = null;
		
		if (isset($attributes)) {
			$this->_attributes = $attributes;
		}
		
		try {
			$this->_image = Core_Image_Factory::load($path);
		} catch (Exception $e) {
			$this->_image = Core_Image_Factory::load('/theme/img/front/noimage.png');
		}
		
		return $this;
	}
	
	/**
	 * Proxy methods call to image adapter object
	 * 
	 * @param  string $methodName
	 * @param  array  $args
	 * @return mixed
	 */
	public function __call($methodName, $args)
	{
		if (!($this->getImage() instanceof Core_Image_Adapter_Abstract)) {
			return $this;
		}
		
		if (method_exists($this->getImage(), $methodName)) {
			$return = call_user_func_array(array($this->getImage(), $methodName), $args);
			if ($return instanceof Core_Image_Adapter_Abstract) {
				return $this;
			}
			
			return $return;
		}
	}
	
	/**
	 * Imege object getter
	 * 
	 * @return Core_Image_Abstract
	 * @throws Exception If empty or invalid image instance
	 */
	public function getImage()
	{
		if (null === $this->_image || !($this->_image instanceof Core_Image_Adapter_Abstract)) {
			$this->_exception = new Core_Image_Exception("Invalid image object or not initialized", 500);
			return;
		}
		
		return $this->_image;
	}
	
	/**
	 * Render image tag if possible
	 * 
	 * @return string
	 */
	public function __toString()
	{
		if (null !== $this->_exception) {
			return $this->_exception->getMessage();
		}
		
		try {
			$endTag = ' />';
			if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            	$endTag= '>';
        	}
			
        	$xhtml = '<img src="/' . ltrim($this->getPath(), '/') . '"' . $this->_renderAttributes() . $endTag;
		} catch (Exception $e) {
			try {
				$xhtml = '<img src="/' . ltrim(Core_Image_Factory::getNoImagePath(), '/') . '" exception="' . $e->getMessage() . '"' . $this->_renderAttributes() . $endTag;
			} catch (Exception $e) {
				//TODO last exception
				$xhtml = '';
			}
		}
		
		return $xhtml;
	}
}
