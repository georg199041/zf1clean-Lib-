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
 * @package    Core_Model
 * @subpackage Core_Model_Entity
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Exception.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Core_Model_Collection_Abstract
 */
require_once "Core/Model/Collection/Abstract.php";

/**
 * @see Core
 */
require_once "Core.php";

/**
 * Entity base class
 *
 * @category   Core
 * @package    Core_Model
 * @subpackage Core_Model_Entity
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Model_Entity_Abstract implements ArrayAccess
{
	/**
	 * Data container
	 * 
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * Parent collection object
	 * 
	 * @var Core_Model_Collection_Abstract
	 */
	protected $_collection;
	
	/**
	 * Mapper container
	 * 
	 * @var Core_Model_Mapper_Abstract
	 */
	protected $_mapper;
	
	/**
	 * Internal setter
	 * 
	 * @param  string $key
	 * @param  mixed $val
	 * @return Core_Model_Entity_Abstract
	 */
	protected function _set($key, $val)
	{
		$key = Core::useFilter($key, 'Zend_Filter_Word_CamelCaseToUnderscore');
		$this->_data[strtolower($key)] = $val;
		return $this;
	}
	
	/**
	 * Internal getter
	 * 
	 * @param  string $key
	 * @return mixed
	 */
	protected function _get($key)
	{
		$key = Core::useFilter($key, 'Zend_Filter_Word_CamelCaseToUnderscore');
		return $this->_data[strtolower($key)];
	}
	
	/**
	 * Internal checker
	 * 
	 * @param  string $key
	 * @return boolean
	 */
	protected function _has($key)
	{
		$key = Core::useFilter($key, 'Zend_Filter_Word_CamelCaseToUnderscore');
		return isset($this->_data[strtolower($key)]);
	}
	
	/**
	 * Internal deleter
	 * 
	 * @param  string $key
	 * @return Core_Model_Entity_Abstract
	 */
	protected function _del($key)
	{
		$key = Core::useFilter($key, 'Zend_Filter_Word_CamelCaseToUnderscore');
		$this->_data[strtolower($key)] = null;
		unset($this->_data[strtolower($key)]);
		return $this;
	}
	
	/**
	 * Constructor
	 * 
	 * @param array $data Initial data of object
	 */
	public function __construct(array $data = null)
	{
		if (is_array($data)) {
			$this->fill($data);
		}
	}
	
	/**
	 * Methodical call all methotds
	 * 
	 * @param  string $method
	 * @param  mixed  $arguments
	 * @return mixed
	 */
	public function __call($method, $arguments)
	{
		$type = substr($method, 0, 3);
		$name = substr($method, 3);
		
		if (in_array($type, array('get', 'set', 'has', 'del')) && strlen($method) > 3) {
			array_unshift($arguments, $name);
			return call_user_func_array(array($this, '_' . $type), $arguments);
		}
	}
	
	/**
	 * Set mapper for self-operations
	 * 
	 * @param  Core_Model_Mapper_Abstract $mapper
	 * @return Core_Model_Entity_Abstract
	 */
	public function setMapper(Core_Model_Mapper_Abstract $mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}
	
	/**
	 * Get mapper for self-operations
	 * 
	 * @throws Exception
	 * @return Core_Model_Mapper_Abstract
	 */
	public function getMapper()
	{
		if (null === $this->_mapper) {
			require_once 'Core/Model/Entity/Exception.php';
			throw new Core_Model_Entity_Exception("Entity self-operarions can't use without mapper object");
		}
		
		return $this->_mapper;
	}
	
	/**
	 * Fill entity data
	 * 
	 * @param  array $data
	 * @return Core_Model_Entity_Abstract
	 */
	public function fill(array $data)
	{
		$this->_data = $data;
		return $this;
	}
	
	/**
	 * Save entity
	 * 
	 * @return Core_Model_Entity_Abstract
	 */
	public function save()
	{
		$this->getMapper()->save($this);
		return $this;
	}
	
	/**
	 * Delete entity
	 * 
	 * @return Core_Model_Entity_Abstract
	 */
	public function delete()
	{
		$this->getMapper()->delete($this);
		return $this;
	}
	
	/**
	 * Converts object to Array representation
	 * 
	 * @return array
	 */
	public function toArray()
	{
		return $this->_data;
	}
	
	/**
	 * Set parent collection or reset collection property
	 * 
	 * @param  Core_Model_Collection_Abstract $collection
	 * @return Core_Model_Entity_Abstract
	 */
	public function setCollection(Core_Model_Collection_Abstract $collection = null)
	{
		$this->_collection = $collection;
		return $this;
	}
	
	/**
	 * Get parent collection if exists
	 * 
	 * @return null|Core_Model_Collection_Abstract
	 */
	public function getCollection()
	{
		return $this->_collection;
	}
	
	/**
	 * Alias of has method
	 * Required by ArrayAccess
	 * 
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 * @param string $offset
	 */
	public function offsetExists($offset)
	{
		$method = 'has' . Core::useFilter($offset, 'Zend_Filter_Word_UnderscoreToCamelCase');
		return $this->$method();
	}
	
	/**
	 * Alias of has method
	 * 
	 * @param  string $offset
	 * @return boolean
	 */
	public function __isset($offset)
	{
		$method = 'has' . Core::useFilter($offset, 'Zend_Filter_Word_UnderscoreToCamelCase');
		return $this->$method();
	}
	
	/**
	 * Alias of get
	 * Required by ArrayAccess
	 * 
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 * @param  string $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		$method = 'get' . Core::useFilter($offset, 'Zend_Filter_Word_UnderscoreToCamelCase');
		return $this->$method();
	}
	
	/**
	 * Alias of get
	 * 
	 * @param  string $offset
	 * @return mixed|null
	 */
	public function __get($offset)
	{
		$method = 'get' . Core::useFilter($offset, 'Zend_Filter_Word_UnderscoreToCamelCase');
		return $this->$method();
	}
	
	/**
	 * Alias of set
	 * Required by ArrayAccess
	 * 
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 * @param  string $offset
	 * @param  mixed  $value
	 * @return mixed
	 */
	public function offsetSet($offset, $value)
	{
		$method = 'set' . Core::useFilter($offset, 'Zend_Filter_Word_UnderscoreToCamelCase');
		$this->$method($value);
	}
	
	/**
	 * Alias of set
	 * 
	 * @param  mixed $offset
	 * @param  mixed $value
	 */
	public function __set($offset, $value)
	{
		$method = 'set' . Core::useFilter($offset, 'Zend_Filter_Word_UnderscoreToCamelCase');
		$this->$method($value);
	}
	
	/**
	 * Alias of uns
	 * Required by ArrayAccess
	 * 
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 * @param  string $offset
	 * @return mixed
	 */
	public function offsetUnset($offset)
	{
		$method = 'del' . Core::useFilter($offset, 'Zend_Filter_Word_UnderscoreToCamelCase');
		$this->$method();
	}
	
	/**
	 * Alias of uns
	 * 
	 * @param  mixed $offset
	 * @return mixed
	 */
	public function __unset($offset)
	{
		$method = 'del' . Core::useFilter($offset, 'Zend_Filter_Word_UnderscoreToCamelCase');
		$this->$method();
	}
}
