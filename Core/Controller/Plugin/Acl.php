<?php

/**
 * Access control plugin
 *
 * @author     Pavlenko Evgeniy
 * @category   Core
 * @package    Core_Controller
 * @version    2.3
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012 SunNY Creative Technologies. (http://www.sunny.net)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
	/**
	 * Default registry key
	 * 
	 * @var string
	 */
	const REGISTRY_KEY = 'Zend_Acl';
	
	/**
	 * ACL container
	 * 
	 * @var Zend_Acl
	 */
	protected $_acl;
	
	/**
	 * List of resources for each will not check access
	 * 
	 * @var array
	 */
	protected $_excludedResources = array();
	
	/**
	 * Error page route
	 * 
	 * @var string
	 */
	protected $_route = 'default/error/error';
	
	/**
	 * Constructor
	 * 
	 * @param array|Zend_Config $options
	 */
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		} else if ($options instanceof Zend_Config) {
			$this->setOptions($options->toArray());
		}
	}
	
	/**
	 * Set plugin options
	 * 
	 * @param  array $options
	 * @throws Exception If recursion detected
	 * @return Core_Controller_Plugin_Acl
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $key => $val) {
			if (false !== stripos('options', $key)) {
				throw new Exception("Recursion detected");
			}
			
			$method = 'set' . ucfirst($key);
			if (method_exists($this, $method)) {
				$this->$method($val);
			}
		}
		
		return $this;
	}
	
	/**
	 * Set acl object
	 * 
	 * @param  Zend_Acl $acl
	 * @return Core_Controller_Plugin_Acl
	 */
	public function setAcl(Zend_Acl $acl)
	{
		$this->_acl = $acl;
		return $this;
	}
	
	/**
	 * Get acl object
	 * Try to load at first access if possible
	 * Otherwise fallback it to false
	 * 
	 * @return false|Zend_Acl
	 */
	public function getAcl()
	{
		if (null === $this->_acl) {
			$this->_acl = false;
			
			if (Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
				$acl = Zend_Registry::get(self::REGISTRY_KEY);
				
				if ($acl instanceof Zend_Acl) {
					$this->setAcl($acl);
				}
			}
		}
		
		return $this->_acl;
	}
	
	/**
	 * Set excluded resources params
	 * Example list item:
	 * <code>
	 * $item = array(
	 *     'module'     => '<module_name>',
	 *     'controller' => '<controller_name>',
	 *     'action'     => '<action_name>'
	 * );
	 * //OR
	 * $item = '<module_name>/<controller_name>/<action_name>';
	 * </code>
	 * 
	 * @param  array $options
	 * @return Core_Controller_Plugin_Acl
	 */
	public function setExcludedResources(array $options)
	{
		$this->_excludedResources = array();
		foreach ($options as $rule) {
			if (is_string($rule)) {
				if (count(explode('/', $rule)) == 3) {
					$this->_excludedResources[] = $rule;
				} else {
					throw new Exception("Invalid rule format");
				}
			} else if (is_array($rule)) {
				if (is_string($rule['module'])
					&& is_string($rule['controller'])
					&& is_string($rule['action'])
				) {
					$this->_excludedResources[] = "{$rule['module']}/{$rule['controller']}/{$rule['action']}";
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Get excluded resources list
	 * 
	 * @return array
	 */
	public function getExcludedResources()
	{
		return $this->_excludedResources;
	}
	
	/**
	 * Set error page route in format <module_name>/<controller_name>/<action_name>
	 * 
	 * @param  string $route
	 * @throws Exception If route string has invalid format
	 * @return Core_Controller_Plugin_Acl
	 */
	public function setRoute($route)
	{
		if (is_array($route)) {
			$route = array_intersect_key($route, $this->getRoute());
			if (count($route) != 3) {
				throw new Exception("Route array must have 3 elements ('module','controller','action')");
			}
			
			$this->_route = "{$route['module']}/{$route['controller']}/{$route['action']}";
		} else if (is_string($route)) {
			$parts = explode('/', $route);
			if (count($parts) != 3) {
				throw new Exception("String must be in format module/controller/action");
			}
			
			$this->_route = $route;
		} else {
			throw new Exception("Invalid route type");
		}
		
		return $this;
	}
	
	/**
	 * Get current error page route
	 * If $asArray == true returns as array
	 * 
	 * @param  boolean $asArray
	 * @return string|array
	 */
	public function getRoute($asArray = false)
	{
		if ($asArray) {
			$r = array();
			list($r['module'], $r['controller'], $r['action']) = explode('/', $this->_route);
			return $r;
		}
		
		return $this->_route;
	}
	
	/**
	 * Main processing method
	 * 
	 * @param  Zend_Controller_Request_Abstract $request
	 * @throws Exception If role not implements valid interface
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		if (false === ($acl = $this->getAcl())) {
			return; // ACL not configured
		}
		
		$resource = "{$request->getModuleName()}/{$request->getControllerName()}/{$request->getActionName()}";
		foreach ($this->getExcudedResources() as $rule) {
			if ($rule == $resource) {
				return; // Resource allowed for all access
			}
		}
		
		$restricted = false;
		if (!$acl->has($resource) && preg_match('/^admin/i', $request->getControllerName())) {
			$restricted = true; // Undefined resource handle restrictions
		}
		
		$role = 'GUEST';
		if (Zend_Auth::getInstance()->hasIdentity()) {
			$role = Zend_Auth::getInstance()->getIdentity();
			if (!$role instanceof Zend_Acl_Role_Interface) {
				throw new Exception("Role must implements Zend_Acl_Role_Interface");
			}
			
			$role = $role->getRoleId();
		}
		
		if (!$restricted && !$acl->isAllowed($role, $resource)) {
			$restricted = true; // Not allowed access
		}
		
		if ($restricted) {
			$this->_gotoErrorPage($request);
		}
	}
	
	/**
	 * Redirect processing
	 * 
	 * @param Zend_Controller_Request_Abstract $request
	 */
	protected function _gotoErrorPage(Zend_Controller_Request_Abstract $request)
	{
		$clone = clone $request;
		$request->clearParams();
		
		$r = $this->getRoute(true);
		$request->setModuleName($r['module']);
		$request->setControllerName($r['controller']);
		$request->setActionName($r['action']);
		
		$request->setParam(self::REGISTRY_KEY . '_Request', $clone);
		$request->setDispatched(true);
	}
}