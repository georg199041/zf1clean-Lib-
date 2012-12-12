<?php

class Core
{
	/**
	 * Objects container
	 * 
	 * @var array
	 */
	protected static $_objects = array();
	
	/**
	 * Autoload class
	 * 
	 * @param  string $className
	 * @param  boolean $singleton
	 * @throws Exception
	 * @return object
	 */
	public static function getClass($className, $singleton = true, $options = null)
	{
		if (!@class_exists($className, true)) {
			throw new Exception("Class '{$className}' not found");
		}
		
		if ($singleton && array_key_exists($className, self::$_objects)) {
			return self::$_objects[$className];
		}
		
		$class = new $className($options);
		if ($singleton) {
			self::$_objects[$className] = $class;
			return self::$_objects[$className];
		}
		
		return $class;
	}
	
	/**
	 * Autoload filter class
	 * 
	 * @param  string $className
	 */
	public static function getFilter($className, $singleton = true)
	{
		return self::getClass($className, $singleton);
	}
	
	/**
	 * Filter value with specified filter class
	 * 
	 * @param  mixed $value
	 * @param  string $filterClass
	 * @return mixed
	 */
	public static function useFilter($value, $filterClass)
	{
		return self::getFilter($filterClass)->filter($value);
	}
	
	/**
	 * Format block class name from name rule
	 * 
	 * @param  string $name
	 * @return string
	 */
	public static function getBlockClassName($name)
	{
		$parts = explode('/', $name);
		foreach ($parts as &$p) {
			$p = self::useFilter($p, 'Zend_Filter_Word_DashToCamelCase');
		}
		
		$namespace = $parts[0];
		$parts[0] = 'Block';
		array_unshift($parts, $namespace);
		
		return implode('_', $parts);
	}
	
	/**
	 * Autoload block
	 * 
	 * @param  string $name
	 * @return object
	 */
	public static function getBlock($name, $singleton = true)
	{
		$className = self::getBlockClassName($name);
		return self::getClass($className, $singleton);
	}
	
	/**
	 * Format mapper name
	 * 
	 * @param  string $name
	 * @return string
	 */
	public static function getMapperClassName($name)
	{
		$parts = explode('/', $name);
		foreach ($parts as &$p) {
			$p = self::useFilter($p, 'Zend_Filter_Word_DashToCamelCase');
		}
		
		$namespace = $parts[0];
		$parts[0] = 'Model_Mapper';
		array_unshift($parts, $namespace);
		
		return implode('_', $parts);
	}
	
	/**
	 * Autoload mapper
	 * 
	 * @param  string $name
	 * @return object
	 */
	public static function getMapper($name, $singleton = true)
	{
		$className = self::getMapperClassName($name);
		return self::getClass($className, $singleton);
	}
	
	/**
	 * Parse url like as in default route use to options array 
	 * 
	 * @param unknown_type $url
	 */
	public static function urlToOptions($url)
	{
		$options = array();
		$front = Zend_Controller_Front::getInstance();
		
		if (false !== strpos($url, '?')) {
			$url = substr($url, 0, strpos($url, '?'));
		}
		
		list($module, $controller, $action, $params) = explode('/', $url, 4);
		
		if (null !== $module) {
			$options['module'] = ($module != '*') ? $module : $front->getRequest()->getModuleName();
		}
			
		if (null !== $controller) {
			$options['controller'] = ($controller != '*') ? $controller : $front->getRequest()->getControllerName();
		}
			
		if (null !== $action) {
			$options['action'] = ($action != '*') ? $action : $front->getRequest()->getActionName();
		}
				
		if (null !== $params) {
			$parts = explode('/', $params);
			$i = 1;
			foreach ($parts as $p) {
				if (!($i % 2)) {
					$options[$parts[$i - 2]] = $p;
				}
			
				$i++;
			}
		}
		
		return $options;
	}
	
	/**
	 * Global session getter
	 * 
	 * @param  string $namespace
	 * @return Zend_Session_Namespace|NULL
	 */
	public static function getSession($namespace)
	{
		return new Zend_Session_Namespace($namespace);
	}
}