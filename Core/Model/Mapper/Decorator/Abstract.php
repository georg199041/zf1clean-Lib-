<?php

require_once 'Sunny/Model/Mapper/Abstract.php';

abstract class Sunny_Model_Mapper_Decorator_Abstract extends Sunny_Model_Mapper_Abstract
{
	/**
	 * Mapper container
	 * 
	 * @var Sunny_Model_Mapper_Abstract
	 */
	protected $_mapper;
	
	/**
	 * Constructor
	 */
	public function __construct(Sunny_Model_Mapper_Abstract $mapper)
	{
		$this->_mapper = $mapper;
	}
	
	/**
	 * Proxy to original object
	 */
	public function __call($method, $arguments)
	{
		if (method_exists($this->_mapper, $method)) {
			$return = call_user_func_array(array($this->_mapper, $method), $arguments);
			if ($return instanceof Sunny_Model_Mapper_Abstract) {
				return $this;
			}
			
			return $return;
		}
		
		require_once 'Sunny/Model/Mapper/Decorator/Exception.php';
		throw new Sunny_Model_Mapper_Decorator_Exception("Mapper method '$method' was not found in mapper '" . $this->_mapper->getClassName() . "'");
	}
}
