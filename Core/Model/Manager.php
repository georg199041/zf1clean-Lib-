<?php

//TODO: all registry functional
class Core_Model_Manager
{
	protected static $_instance;
	
	protected $_objects = array();
	
	protected $_inflector;
	
	protected $_specRules = array(
		':namespace' => 'Word_DashToCamelCase',
		':name'      => 'Word_DashToCamelCase'
	);
	
	protected $_specList = array(
		'mapper' => ':namespace_Model_Mapper_:name'
	);
	
	protected function __construct(array $options = null)
	{
		if (is_array($options)) {
			//$this->setOptions($options);
		}
	}
	
	public static function getInstance(array $options = null)
	{
		if (null === self::$_instance) {
			self::$_instance = new self($options);
		}
		
		return self::$_instance;
	}
	
	public function getInflector($spec)
	{
		if (null === $this->_inflector) {
			$this->_inflector = new Zend_Filter_Inflector();
			$this->_inflector->setRules($this->_specRules);
		}
		
		if (!array_key_exists($spec, $this->_specList)) {
			throw new Exception("Spec for key '$spec' not found");
		}
		
		$this->_inflector->setTarget($this->_specList[$spec]);
		return $this->_inflector;
	}
	
	public function getClass($className, $singleton = true)
	{
		if (array_key_exists($className, $this->_objects)) {
			return $this->_objects[$className];
		}
		
		if (@class_exists($className, true)) {
			$class = new $className();
			if (!$singleton) {
				return $class;
			}
				
			$this->_objects[$className] = $class;
			return $this->_objects[$className];
		}
		
		throw new Exception("Class '$className' not found");
	}
	
	public function getMapper($spec, $singleton = true)
	{
		if (!preg_match('/^([a-z\-])+\/([a-z\-])+$/i', $spec)) {
			throw new Exception("Invalid name format '$spec'");
		}
		
		list($s['namespace'], $s['name']) = explode('/', $spec);
		$className = $this->getInflector('mapper')->filter($s);
		
		return $this->getClass($className, $singleton);
	}
}