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
 * @package    Core
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Attributes.php 0.1 2012-12-12 pavlenko $
 */

/**
 * Attributes aggregator class
 * Can use as html element attributes aggregator and renderer
 *
 * @category   Core
 * @package    Core
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Attributes
{
	/**
	 * Attributes container
	 * 
	 * @var array
	 */
	protected $_attributes = array();
	
	/**
	 * Set array of attributes at once
	 * 
	 * @param array $attributes
	 */
	public function setAttributes(array $attributes)
	{
		$this->_attributes = array();
		$this->addAttributes($attributes);
		return $this;
	}
	
	/**
	 * Add array of attributes
	 * 
	 * @param array $attributes
	 */
	public function addAttributes(array $attributes)
	{
		foreach ($attributes as $key => $val) {
			$this->addAttribute($key, $val);
		}
		
		return $this;
	}
	
	/**
	 * Get all attributes
	 * 
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}
	
	/**
	 * Delete array of attributes
	 * 
	 * @param array $keys
	 */
	public function delAttributes(array $keys)
	{
		foreach ($keys as $key) {
			$this->delAttribute($key);
		}
		
		return $this;
	}
	
	/**
	 * Check for has all attributes
	 * 
	 * @param  array $keys
	 * @return boolean
	 */
	public function hasAttributes(array $keys)
	{
		$exists = true;
		foreach ($keys as $key) {
			if (!$this->hasAttribute($key)) {
				$exists = false;
				break;
			}
		}
		
		return $exists;
	}
	
	/**
	 * Set attribute
	 * 
	 * @param numeric|string $key
	 * @param mixed $val
	 */
	public function setAttribute($key, $val = null)
	{
		$this->_attributes[$key] = $val;
		return $this;
	}
	
	/**
	 * Add attribute
	 * 
	 * @param numeric|string $key
	 * @param mixed $val
	 */
	public function addAttribute($key, $val = null)
	{
		return $this->setAttribute($key, $val);
	}
	
	/**
	 * Get attribute
	 * 
	 * @param numeric|string $key
	 */
	public function getAttribute($key)
	{
		return $this->_attributes[$key];
	}
	
	/**
	 * Delete attribute
	 * 
	 * @param numeric|string $key
	 */
	public function delAttribute($key)
	{
		if (isset($this->_attributes[$key])) {
			unset($this->_attributes[$key]);
		}
		
		return $this;
	}
	
	/**
	 * Check for has attribute
	 * 
	 * @param  numeric|string $key
	 * @return boolean
	 */
	public function hasAttribute($key)
	{
		return isset($this->_attributes[$key]) ? true : false;
	}
	
	/**
	 * Render attributes to use in html/xml tags
	 * 
	 * @return string
	 */
	public function toHtmlAttributes()
	{
		$str = '';
		foreach ($this->getAttributes() as $key => $val) {
			$str .= " {$key}=\"{$val}\"";
		}
		
		return trim($str);
	}
}