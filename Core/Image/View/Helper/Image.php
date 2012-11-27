<?php

require_once "Zend/View/Helper/Abstract.php";

require_once "Core/Image/Factory.php";

require_once "Core/Image/Adapter/Abstract.php";

/**
 * Image view helper proxy class
 *
 * @author     Pavlenko Evgeniy
 * @category   Core
 * @package    Core_Image
 * @version    2.3
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2009 SunNY Creative Technologies. (http://www.sunny.net)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Image_View_Helper_Image extends Zend_View_Helper_Abstract
{
	/**
	 * @var Core_Image_Abstract
	 */
	protected $_image;
	
	protected $_attributes = array();
	
	protected $_exception;
	
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
	 * Proxy to image object
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
	 * @throws Exception           If empty or invalid image instance
	 */
	public function getImage()
	{
		if (null === $this->_image || !($this->_image instanceof Core_Image_Adapter_Abstract)) {
			$this->_exception = new Exception("Invalid image object or not initialized", 500);
			return;
		}
		
		return $this->_image;
	}
	
	/**
	 * Render tag if can
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
