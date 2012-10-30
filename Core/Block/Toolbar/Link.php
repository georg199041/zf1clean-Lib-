<?php

require_once "Zend/Config.php";

require_once "Core/Attributes.php";

require_once "Core/Block/Toolbar/Widget.php";

class Core_Block_Toolbar_Link extends Core_Attributes
{
	protected $_title;
	
	protected $_toolbar;
	
	protected $_urlOptions = array();
	
	protected $_urlRoute;

	protected $_name;
	
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		} else if ($options instanceof Zend_Config) {
			$this->setOptions($options->toArray());
		}
	}
	
	public function setOptions(array $options)
	{
		foreach ($options as $key => $val) {
			$method = 'set' . ucfirst($key);
			if (method_exists($this, $method)) {
				$this->$method($val);
			} else {
				$this->setAttribute($key, $val);
			}
		}
		
		return $this;
	}
	
	public function setTitle($title)
	{
		$this->_title = $title;
		return $this;
	}
	
	public function getTitle()
	{
		if (null === $this->_title) {
			throw new Exception("Toolbar item '{$this->getName()}' must have title");
		}
		
		return $this->_title;
	}
	
	public function setToolbar(Core_Block_Toolbar_Widget $toolbar)
	{
		$this->_toolbar = $toolbar;
		return $this;
	}
	
	public function getToolbar()
	{
		if (null === $this->_toolbar) {
			throw new Exception("Toolbar object not found");
		}
		
		return $this->_toolbar;
	}
	
	public function setUrlOptions($options)
	{
		if (is_array($options)) {
			$this->_urlOptions = $options;
		} else if (is_string($options)) {
			list($module, $controller, $action, $params) = explode('/', trim($options, '/'), 4);
			if (null !== $module) {
				$this->_urlOptions['module'] = $module;
				if (null !== $controller) {
					$this->_urlOptions['controller'] = $controller;
					if (null !== $action) {
						$this->_urlOptions['action'] = $action;
						if (null !== $params) {
							$i = 1;
							$params = explode('/', $params);
							foreach ($params as $val) {
								if (!($i % 2)) {
									$this->_urlOptions[$params[$i - 2]] = $val;
								}
								
								$i++;
							}
						}
					}
				}
			}
		}
		
		return $this;
	}
	
	public function getUrlOptions()
	{
		return $this->_urlOptions;
	}
	
	public function setUrlRoute($name)
	{
		$this->_urlRoute = $name;
		return $this;
	}
	
	public function getUrlRoute()
	{
		return $this->_urlRoute;
	}

	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	public function getName()
	{
		if (null === $this->_name) {
			throw new Exception("Toolbar item must have name");
		}
	
		return $this->_name;
	}
	
	public function render()
	{
		$this->setAttribute('href', $this->getToolbar()->url($this->getUrlOptions(), $this->getUrlRoute(), true));
		$this->setAttribute('class', null); // TODO: parse active link
		
		return '<a ' . $this->toHtmlAttributes() . '><span>' . $this->getTitle() . '</span></a>';
	}
}
