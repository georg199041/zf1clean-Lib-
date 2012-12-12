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
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Interface.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_View_Interface
 */
require_once 'Zend/View/Interface.php';

/**
 * Interface class for Zend_View compatible template engine implementations
 *
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Renderer_Abstract implements Zend_View_Interface
{
	/**
	 * Template engine container
	 * 
	 * @var mixed
	 */
	protected $_engine;
	
	/**
	 * Magic method
	 * Proxy undefined methods to template engine
	 * 
	 * @param  mixed $method
	 * @param  array $args
	 * @return Core_Block_Renderer_Abstract|mixed
	 */
	public function __call($method, $args)
	{
		$response = call_user_func_array(array($this->getEngine(), $method), $args);
		if ($response === $this->getEngine()) {
			return $this;
		}
		
		return $response;
	}
	
	/**
	 * Return the template engine object, if any
	 *
	 * Required by {@link Zend_View_Interface}
	 * 
	 * If using a third-party template engine, such as Smarty, patTemplate,
	 * phplib, etc, return the template engine object. Useful for calling
	 * methods on these objects, such as for setting filters, modifiers, etc.
	 *
	 * @return mixed
	 */
	public function getEngine()
	{
		if (null === $this->_engine) {
			$this->setEngine(new Zend_View());
		}
		
		return $this->_engine;
	}
	
	/**
	 * Set other template engine
	 * 
	 * @param  string|object $engine String name of static class or object instance
	 * @return Core_Block_Renderer_Abstract
	 */
	public function setEngine($engine)
	{
		if (!is_object($engine) && !is_string($engine)) {
			require_once 'Core/Block/Renderer/Exception.php';
			$e = new Core_Block_Renderer_Exception("Template must be an instance of some class or string name of static class");
			$e->setView($this);
			throw $e;
		}
		
		$this->_engine = $engine;
		return $this;
	}
	
	/**
	 * Set the path to find the view script used by render()
	 * 
	 * Required by {@link Zend_View_Interface}
	 *
	 * @param string|array The directory (-ies) to set as the path. Note that
	 * the concrete view implentation may not necessarily support multiple
	 * directories.
	 * @return void
	*/
	public function setScriptPath($path);
	
	/**
	 * Retrieve all view script paths
	 * 
	 * Required by {@link Zend_View_Interface}
	 *
	 * @return array
	*/
	public function getScriptPaths();
	
	/**
	 * Set a base path to all view resources
	 * 
	 * Required by {@link Zend_View_Interface}
	 *
	 * @param  string $path
	 * @param  string $classPrefix
	 * @return void
	*/
	public function setBasePath($path, $classPrefix = 'Zend_View');
	
	/**
	 * Add an additional path to view resources
	 * 
	 * Required by {@link Zend_View_Interface}
	 * 
	 * @param  string $path
	 * @param  string $classPrefix
	 * @return void
	*/
	public function addBasePath($path, $classPrefix = 'Zend_View');
	
	/**
	 * Assign a variable to the view
	 * 
	 * Required by {@link Zend_View_Interface}
	 * 
	 * @param string $key The variable name.
	 * @param mixed $val The variable value.
	 * @return void
	*/
	public function __set($key, $val);
	
	/**
	 * Allows testing with empty() and isset() to work
	 * 
	 * Required by {@link Zend_View_Interface}
	 * 
	 * @param string $key
	 * @return boolean
	*/
	public function __isset($key);
	
	/**
	 * Allows unset() on object properties to work
	 *
	 * Required by {@link Zend_View_Interface}
	 *
	 * @param string $key
	 * @return void
	*/
	public function __unset($key);
	
	/**
	 * Assign variables to the view script via differing strategies.
	 *
	 * Required by {@link Zend_View_Interface}
	 * 
	 * Suggested implementation is to allow setting a specific key to the
	 * specified value, OR passing an array of key => value pairs to set en
	 * masse.
	 *
	 * @see __set()
	 * @param string|array $spec The assignment strategy to use (key or array of key
	 * => value pairs)
	 * @param mixed $value (Optional) If assigning a named variable, use this
	 * as the value.
	 * @return void
	*/
	public function assign($spec, $value = null);
	
	/**
	 * Clear all assigned variables
	 * 
	 * Required by {@link Zend_View_Interface}
	 * 
	 * Clears all variables assigned to Zend_View either via {@link assign()} or
	 * property overloading ({@link __get()}/{@link __set()}).
	 *
	 * @return void
	*/
	public function clearVars();
	
	/**
	 * Processes a view script and returns the output.
	 * 
	 * Required by {@link Zend_View_Interface}
	 * 
	 * @param string $name The script name to process.
	 * @return string The script output.
	*/
	public function render($name);
}