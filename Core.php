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
 * @version    $Id: Core.php 0.1 2012-12-12 pavlenko $
 */

/**
 * Application base class
 * Basic loader method implemented here
 *
 * @category   Core
 * @package    Core
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
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
	 * @param  string  $className Class name to instantiate
	 * @param  boolean $singleton Use single instance (if true place instance to registry)
	 * @param  mixed   $options   Instantiate options
	 * @throws Exception If class not exists
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
	 * @param  boolean $singleton
	 * @return object
	 */
	public static function getFilter($className, $singleton = true)
	{
		return self::getClass($className, $singleton);
	}
	
	/**
	 * Filter value with specified filter class
	 * 
	 * @param  mixed  $value
	 * @param  string $filterClass
	 * @return Zend_Filter_Inteface
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
	 * @param  string  $name
	 * @param  boolean $singleton
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
	 * @param  string  $name
	 * @param  boolean $singleton
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