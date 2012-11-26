<?php

require_once "Core/Block/Grid/Widget.php";

require_once "Core/Attributes.php";

class Core_Block_Grid_Column_Default extends Core_Attributes
{
	protected $_colAttribsNames = array('span', 'width');
	
	protected $_thAttribsNames = array('align', 'width');
	
	protected $_grid;
	
	protected $_row;
	
	protected $_colAttribs = array();
	
	protected $_thAttribs = array();
	
	protected $_sortable = false;
	
	protected $_filterable = false;
	
	protected $_filterableType;
	
	protected $_filterableOptions = array();
	
	protected $_filterableAvailableTypes = array(
		Core_Block_Grid_Widget::FILTER_EQUAL,
		Core_Block_Grid_Widget::FILTER_LIKE,
		Core_Block_Grid_Widget::FILTER_SELECT
	);
	
	protected $_view;
	
	protected $_title;
	
	protected $_name;
	
	protected $_value;
	
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}
	
	public function setOptions(array $options)
	{
		foreach ($options as $name => $value) {
			$method = 'set' . ucfirst($name);
			if (method_exists($this, $method)) {
				$this->$method($value);
			} else if (in_array($name, $this->_colAttribsNames)) {
				$this->_colAttribs[$name] = $value;
			} else if ('th-' == substr($name, 0, 3) && in_array(substr($name, 3), $this->_thAttribsNames)) {
				$this->_thAttribs[substr($name, 3)] = $value;
			} else {
				$this->addAttribute($name, $value);
			}
		}
		
		return $this;
	}
	
	public function setGrid(Core_Block_Grid_Widget $grid)
	{
		$this->_grid = $grid;
		return $this;
	}
	
	public function getGrid()
	{
		if (null === $this->_grid) {
			throw new Exception("Can't get grid of column '{$this->getName()}'");
		}
		
		return $this->_grid;
	}
	
	public function setRow($row)
	{
		if (!is_array($row) && !($row instanceof ArrayAccess)) {
			throw new Exception("Row data must be instance of ArrayAccess or an array");
		}
		
		$this->_row = $row;
		return $this;
	}
	
	public function getRow($key = null)
	{
		if (null !== $key) {
			return $this->_row[$key];
		}
		
		return $this->_row;
	}
	
	public function setSortable($value = true)
	{
		$this->_sortable = (bool) $value;
		return $this;
	}
	
	public function isSortable()
	{
		return $this->_sortable;
	}
	
	public function setTitle($value)
	{
		$this->_title = (string) $value;
		return $this;
	}
	
	public function getTitle()
	{
		if (null === $this->_title) {
			$this->setTitle(ucfirst($this->getName()));
		}
		
		return $this->_title;
	}
	
	public function setView(Zend_View_Interface $view)
	{
		$this->_view = $view;
		return $this;
	}
	
	public function getView()
	{
		if (null === $this->_view) {
			require_once 'Zend/Controller/Action/HelperBroker.php';
			$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
			if (null === $viewRenderer->view) {
				$viewRenderer->initView();
			}
			$this->setView($viewRenderer->view);
		}
		
		return $this->_view;
	}
	
	public function setName($value)
	{
		$this->_name = $value;
		return $this;
	}
	
	public function getName()
	{
		if (null === $this->_name) {
			throw new Exception("Column name is not defined");
		}
		
		return $this->_name;
	}
	
	public function setValue($value)
	{
		$this->_value = $value;
		return $this;
	}
	
	public function getValue()
	{
		if (null === $this->_value) {
			if ($this->isFilterable()) {
				$method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->getName())));
				if (method_exists($this->getGrid(), $method)) {
					$options = $this->getGrid()->$method();
					if ($this->_row[$this->getName()]) {
						return ltrim($options[$this->_row[$this->getName()]], '- ') . " ({$this->_row[$this->getName()]})";
					} else {
						return '';
					}
				}
				
			}
			
			return $this->_row[$this->getName()];
		}
		
		return $this->_value;
	}
	
	public function setFilterable($flag = true)
	{
		$this->_filterable = (bool) $flag;
		return $this;
	}
	
	public function isFilterable()
	{
		return $this->_filterable;
	}
	
	public function setFilterableType($type)
	{
		if (!in_array($type, $this->_filterableAvailableTypes)) {
			throw new Exception("Invalid filterable type '{$type}'");
		}
		
		$this->_filterableType = $type;
		return $this;
	}
	
	public function getFilterableType()
	{
		if (null === $this->_filterableType) {
			$this->_filterableType = Core_Block_Grid_Widget::FILTER_EQUAL;
		}
		
		return $this->_filterableType;
	}
	
	public function setFilterableOptions(array $options)
	{
		$this->_filterableOptions = $options;
		return $this;
	}
	
	public function getFilterableOptions()
	{
		return $this->_filterableOptions;
	}
	
	public function renderColAttribs()
	{
		$str = '';
		foreach ($this->_colAttribs as $name => $value) {
			$str .= ' ' . $name . '="' . $value . '"';
		}
		return $str;
	}

	public function renderThAttribs()
	{
		$str = '';
		foreach ($this->_thAttribs as $name => $value) {
			$str .= ' ' . $name . '="' . $value . '"';
		}
		return $str;
	}
	
	public function render()
	{
		return '<span>' . $this->getValue() . '</span>';
	}
}