<?php

/**
 * Base collection
 * 
 * Be careful when iterate over foreach loop with pass value as reference
 * use each method of collection
 *
 * @author     Pavlenko Evgeniy
 * @category   Core
 * @package    Core_Model
 * @version    1.1
 * @subpackage Collection
 * @copyright  Copyright (c) 2012 SunNY Creative Technologies. (http://www.sunny.net)
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
	
	public function __call($method, $arguments)
	{
		if (@preg_match('/(find(?:One|All)?By)(.+)/', $method, $match)) {
			return $this->{$match[1]}($match[2], $arguments[0]);
		}
        
		require_once 'Zend/Db/Table/Row/Exception.php';
        throw new Zend_Db_Table_Row_Exception("Unrecognized method '$method()'");
	}

	public function setMapper(Core_Model_Mapper_Abstract $mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}
	
	public function getMapper()
	{
		if (null === $this->_mapper) {
			throw new Exception("Collection self-operarions can't use without mapper object");
		}
	
		return $this->_mapper;
	}
	
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
	 * @param boolean $reverse Use reverse sorting
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
	 * @param boolean $reverse Use reverse sorting
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
	 * @throws InvalidArgumentException
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
	 * @throws InvalidArgumentException
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
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return isset($this->_data[(int) $offset]);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		return $this->_data[(int) $offset];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
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
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset)
	{
		unset($this->_data[(int) $offset]);
	}
}
