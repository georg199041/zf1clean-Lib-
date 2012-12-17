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
 * @subpackage Core_Model_Collection
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Core_Model_Collection_Exception
 */
require_once "Core/Model/Collection/Exception.php";

/**
 * Base collection class
 *
 * @category   Core
 * @package    Core_Model
 * @subpackage Core_Model_Collection
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Model_Collection_Abstract implements Iterator, Countable, ArrayAccess
{
	/**
	 * Data container
	 * 
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * Internal data pointer
	 * 
	 * @var integer
	 */
	protected $_pointer = 0;
	
	/**
	 * Constructor
	 * 
	 * @param array $data Initial data for container
	 */
	public function __construct(array $data = null)
	{
		if (is_array($data)) {
			$this->fill(array_values($data));
		}
	}
	
	/**
	 * Search implementation (via magic method)
	 * 
	 * @param  string $method
	 * @param  array  $arguments
	 * @throws Core_Model_Collection_Exception If invalid method called
	 */
	public function __call($method, $arguments)
	{
		if (@preg_match('/(find(?:One|All)?By)(.+)/', $method, $match)) {
			return $this->{$match[1]}($match[2], $arguments[0]);
		}
        
		throw new Core_Model_Collection_Exception("Unrecognized method '$method()'");
	}

	/**
	 * Populate mapper object
	 * 
	 * @param  Core_Model_Mapper_Abstract $mapper
	 * @return Core_Model_Collection_Abstract
	 */
	public function setMapper(Core_Model_Mapper_Abstract $mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}
	
	/**
	 * Get mapper object
	 * 
	 * @throws Core_Model_Collection_Exception If mapper is not setted
	 * @return Core_Model_Mapper_Abstract
	 */
	public function getMapper()
	{
		if (null === $this->_mapper) {
			throw new Core_Model_Collection_Exception("Collection self-operarions can't use without mapper object");
		}
	
		return $this->_mapper;
	}
	
	/**
	 * Search entity by specified field value
	 * 
	 * @param  string $key
	 * @param  mixed  $value
	 * @return boolean|Core_Model_Entity_Abstract
	 */
	public function findOneBy($key, $value)
	{
		foreach ($this->_data as $row) {
			echo $row[$key];
			if ($row[$key] == $value) {
				return $row;
			}
		}
		
		return false;
	}
	
	/**
	 * Find Collection of items by specified field value
	 * 
	 * @param  string $key
	 * @param  mixed  $value
	 * @return Core_Model_Collection_Abstract
	 */
	public function findAllBy($key, $value)
	{
		$collection = clone $this;
		$collection->clear();
		
		foreach ($this->_data as $row) {
			if ($row[$key] == $value) {
				$collection->push($row);
			}
		}
		
		return $collection;
	}
	
	/**
	 * Clear collection items list
	 * 
	 * @return Core_Model_Collection_Abstract
	 */
	public function clear()
	{
		$this->_data = array();
		return $this;
	}
	
	/**
	 * Fill all data
	 * 
	 * @param  array $data
	 * @return Core_Model_Collection_Abstract
	 */
	public function fill(array $data)
	{
		$this->_data = $data;
		return $this;
	}
	
	/**
	 * Save collection
	 * 
	 * @return Core_Model_Collection_Abstract
	 */
	public function save()
	{
		$this->getMapper()->saveCollection($this);
		return $this;
	}
	
	/**
	 * Delete all collection
	 * 
	 * @return Core_Model_Collection_Abstract
	 */
	public function delete()
	{
		$this->getMapper()->deleteCollection($this);
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Countable::count()
	 * 
	 * @return integer
	 */
	public function count()
	{
		return count($this->_data);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Iterator::current()
	 * 
	 * @return mixed Current positioned element
	 */
	public function current()
	{
		if (false === $this->valid()) {
			return null;
		}
		
		return $this->_data[$this->_pointer];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Iterator::key()
	 * 
	 * @return integer Current position number
	 */
	public function key()
	{
		return $this->_pointer;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Iterator::next()
	 */
	public function next()
	{
		$this->_pointer++;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Iterator::rewind()
	 */
	public function rewind()
	{
		$this->_pointer = 0;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Iterator::valid()
	 * @return boolean
	 */
	public function valid()
	{
		return ($this->_pointer >= 0 && $this->_pointer < $this->count());
	}
	
	/**
	 * Iterate over container with callback
	 * 
	 * @param  mixed $callable
	 * @throws InvalidArgumentException
	 */
	public function each($callable)
	{
		if (!is_callable($callable)) {
			throw new InvalidArgumentException("Argument must be a callback");
		}
		
		foreach ($this->_data as $key => &$value) {
			call_user_func_array($callable, array(&$value, $key));
		}
		
		return $this;
	}
	
	/**
	 * Push element to end of container
	 * 
	 * @param mixed $value
	 */
	public function push($value)
	{
		$this->_data[] = $value;
		return $this;
	}
	
	/**
	 * Sort an array and maintain index association
	 * 
	 * @param  string  $flags   Sorting flags like in asort() function
	 * @param  boolean $reverse Use reverse sorting
	 * @return Core_Model_Collection_Abstract
	 */
	public function asort($flags = SORT_REGULAR, $reverse = false)
	{
		$reverse = (bool) $reverse;
		
		if (!$reverse) {
			asort($this->_data, $flags);
		} else {
			arsort($this->_data, $flags);
		}
		
		return $this;
	}
	
	/**
	 * Sort an array by key
	 * 
	 * @param  string  $flags   Sorting flags like in asort() function
	 * @param  boolean $reverse Use reverse sorting
	 * @return Core_Model_Collection_Abstract
	 */
	public function ksort($flags = SORT_REGULAR, $reverse = false)
	{
		$reverse = (bool) $reverse;
		
		if (!$reverse) {
			ksort($this->_data, $flags);
		} else {
			krsort($this->_data, $flags);
		}
		
		return $this;
	}
	
	/**
	 * Convert collection data to array representation
	 * 
	 * @return array
	 */
	public function toArray()
	{
		$return = array();
		foreach ($this->_data as $item) {
			if (is_object($item) && method_exists($item, 'toArray')) {
				$return[] = $item->toArray();
			} else {
				$return[] = $item;
			}
		}
		
		return $return;
	}
	
	/**
	 * Sort an array with a user-defined comparison function
	 * and maintain index association
	 * 
	 * @param  mixed $callable
	 * @throws InvalidArgumentException If not callable object passed
	 * @return Core_Model_Collection_Abstract
	 */
	public function uasort($callable)
	{
		if (!is_callable($callable)) {
			throw new InvalidArgumentException("Argument must be a callback");
		}
		
		uasort($this->_data, $callable);
		return $this;
	}
	
	/**
	 * Sort an array by keys using a user-defined comparison function
	 * 
	 * @param  mixed $callable
	 * @throws InvalidArgumentException If not callable object passed
	 * @return Core_Model_Collection_Abstract
	 */
	public function uksort($callable)
	{
		if (!is_callable($callable)) {
			throw new InvalidArgumentException("Argument must be a callback");
		}
		
		uksort($this->_data, $callable);
		return $this;
	}
	
	/**
	 * Check index exists
	 * 
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 * @param  int $offset
	 * @return mixed
	 */
	public function offsetExists($offset)
	{
		return isset($this->_data[(int) $offset]);
	}
	
	/**
	 * Get row from collection by specified index
	 * 
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 * @param  int $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->_data[(int) $offset];
	}
	
	/**
	 * Set row data to specified index
	 * 
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 * @param  int   $offset
	 * @param  mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->_data[] = $value;
		} else {
			$this->_data[(int) $offset] = $value;
		}
	}
	
	/**
	 * Delete row by specified index
	 * 
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 * @param int $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->_data[(int) $offset]);
	}
}
