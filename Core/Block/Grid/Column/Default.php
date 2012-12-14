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
 * @package    Core_Block
 * @subpackage Core_Block_Grid_Column
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Default.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_View_Interface
 */
require_once "Zend_View_Interface.php";

/**
 * @see Core_Block_Grid_Widget
 */
require_once "Core/Block/Grid/Widget.php";

/**
 * @see Core_Attributes
 */
require_once "Core/Attributes.php";

/**
 * Base grid column class
 * Rendered as simple text
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Grid_Column
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Grid_Column_Default extends Core_Attributes
{
	/**
	 * Array of attributes that passed to column
	 * 
	 * @var array
	 */
	protected $_colAttribsNames = array('span', 'width');
	
	/**
	 * Array of attributes that passed to column header
	 * 
	 * @var array
	 */
	protected $_thAttribsNames = array('align', 'width');
	
	/**
	 * Grid parent object
	 * 
	 * @var Core_Block_Grid_Widget
	 */
	protected $_grid;
	
	/**
	 * Entrie row data to between column processing
	 * 
	 * @var array|ArrayAccess
	 */
	protected $_row;
	
	/**
	 * Column attributes values
	 * 
	 * @var array
	 */
	protected $_colAttribs = array();
	
	/**
	 * Column header attributes values
	 * 
	 * @var array
	 */
	protected $_thAttribs = array();
	
	/**
	 * Flag of column can be sortable
	 * 
	 * @var boolean
	 */
	protected $_sortable = false;
	
	/**
	 * Flag of column can be filtered
	 * 
	 * @var boolean
	 */
	protected $_filterable = false;
	
	/**
	 * Type of column filtering method
	 * 
	 * @var string
	 */
	protected $_filterableType;
	
	/**
	 * Advanced filter options
	 * Example: select filter type must have variants options
	 * 
	 * @var array
	 */
	protected $_filterableOptions = array();
	
	/**
	 * Available types of filtering
	 * 
	 * @var array
	 */
	protected $_filterableAvailableTypes = array(
		Core_Block_Grid_Widget::FILTER_EQUAL,
		Core_Block_Grid_Widget::FILTER_LIKE,
		Core_Block_Grid_Widget::FILTER_SELECT
	);
	
	/**
	 * Rendering engine
	 * For use it helpers or some other methods
	 * 
	 * @var Zend_View_Interface
	 */
	protected $_view;
	
	/**
	 * Column header title
	 * 
	 * @var string
	 */
	protected $_title;
	
	/**
	 * Column name (like column name in db), required
	 * 
	 * @var string
	 */
	protected $_name;
	
	/**
	 * Column value
	 * 
	 * @var mixed
	 */
	protected $_value;
	
	/**
	 * Constructor
	 * 
	 * @param array $options Instantiate column options
	 */
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}
	
	/**
	 * Setted column options array at once
	 * 
	 * @param  array $options
	 * @return Core_Block_Grid_Column_Default
	 */
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
	
	/**
	 * Set parent grid object
	 * 
	 * @param  Core_Block_Grid_Widget $grid
	 * @return Core_Block_Grid_Column_Default
	 */
	public function setGrid(Core_Block_Grid_Widget $grid)
	{
		$this->_grid = $grid;
		return $this;
	}
	
	/**
	 * Gets grid object
	 * 
	 * @throws Exception If grid object not defined
	 * @return Core_Block_Grid_Widget
	 */
	public function getGrid()
	{
		if (null === $this->_grid) {
			throw new Exception("Can't get grid of column '{$this->getName()}'");
		}
		
		return $this->_grid;
	}
	
	/**
	 * Set all row data
	 * 
	 * @param  array|ArrayAccess $row Row data can be implementation of ArrayAccess object (model as example)
	 * @throws Exception If row data has invalid type
	 * @return Core_Block_Grid_Column_Default
	 */
	public function setRow($row)
	{
		if (!is_array($row) && !($row instanceof ArrayAccess)) {
			throw new Exception("Row data must be instance of ArrayAccess or an array");
		}
		
		$this->_row = $row;
		return $this;
	}
	
	/**
	 * Gets all row data or if key passed try to get row item
	 * 
	 * @param string $key
	 */
	public function getRow($key = null)
	{
		if (null !== $key) {
			return $this->_row[$key];
		}
		
		return $this->_row;
	}
	
	/**
	 * Sets column as sortable
	 * 
	 * @param  boolean $value
	 * @return Core_Block_Grid_Column_Default
	 */
	public function setSortable($value = true)
	{
		$this->_sortable = (bool) $value;
		return $this;
	}
	
	/**
	 * Check if column can be sortable
	 * 
	 * @return boolean
	 */
	public function isSortable()
	{
		return $this->_sortable;
	}
	
	/**
	 * Set column header title
	 * 
	 * @param  string $value
	 * @return Core_Block_Grid_Column_Default
	 */
	public function setTitle($value)
	{
		$this->_title = (string) $value;
		return $this;
	}
	
	/**
	 * Get column header title
	 * 
	 * @return string
	 */
	public function getTitle()
	{
		if (null === $this->_title) {
			$this->setTitle(ucfirst($this->getName()));
		}
		
		return $this->_title;
	}
	
	/**
	 * Sets view engine
	 * 
	 * @param  Zend_View_Interface $view
	 * @return Core_Block_Grid_Column_Default
	 */
	public function setView(Zend_View_Interface $view)
	{
		$this->_view = $view;
		return $this;
	}
	
	/**
	 * Gets view engine
	 * If not exists try to instantiate it from ViewRenderer action helper
	 * 
	 * @return Zend_View_Interface
	 */
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
	
	/**
	 * Set column name
	 * 
	 * @param  string $value
	 * @return Core_Block_Grid_Column_Default
	 */
	public function setName($value)
	{
		$this->_name = $value;
		return $this;
	}
	
	/**
	 * Gets column name
	 * 
	 * @throws Exception If column name not set because it is required
	 * @return string
	 */
	public function getName()
	{
		if (null === $this->_name) {
			throw new Exception("Column name is not defined");
		}
		
		return $this->_name;
	}
	
	/**
	 * Set column value
	 * 
	 * @param  mixed $value
	 * @return Core_Block_Grid_Column_Default
	 */
	public function setValue($value)
	{
		$this->_value = $value;
		return $this;
	}
	
	/**
	 * Get column value
	 * If not set try to get it from row or filter (if column is filterable)
	 * 
	 * @return mixed
	 */
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
	
	/**
	 * Set column as filterable
	 * 
	 * @param unknown_type $flag
	 * @return Core_Block_Grid_Column_Default
	 */
	public function setFilterable($flag = true)
	{
		$this->_filterable = (bool) $flag;
		return $this;
	}
	
	/**
	 * Check if column can be filterable
	 * 
	 * @return boolean
	 */
	public function isFilterable()
	{
		return $this->_filterable;
	}
	
	/**
	 * Set column filterable type (equivalent, like or or one of <select> options)
	 * 
	 * @param  string $type
	 * @throws Exception If type not available
	 * @return Core_Block_Grid_Column_Default
	 */
	public function setFilterableType($type)
	{
		if (!in_array($type, $this->_filterableAvailableTypes)) {
			throw new Exception("Invalid filterable type '{$type}'");
		}
		
		$this->_filterableType = $type;
		return $this;
	}
	
	/**
	 * Get filterable type
	 * 
	 * @return string
	 */
	public function getFilterableType()
	{
		if (null === $this->_filterableType) {
			$this->_filterableType = Core_Block_Grid_Widget::FILTER_EQUAL;
		}
		
		return $this->_filterableType;
	}
	
	/**
	 * Set advanced filterable options
	 * 
	 * @param  array $options
	 * @return Core_Block_Grid_Column_Default
	 */
	public function setFilterableOptions(array $options)
	{
		$this->_filterableOptions = $options;
		return $this;
	}
	
	/**
	 * Get all filterable options array
	 * 
	 * @return array
	 */
	public function getFilterableOptions()
	{
		return $this->_filterableOptions;
	}
	
	/**
	 * Render column attributes to HTML
	 * 
	 * @return string
	 */
	public function renderColAttribs()
	{
		$str = '';
		foreach ($this->_colAttribs as $name => $value) {
			$str .= ' ' . $name . '="' . $value . '"';
		}
		return $str;
	}

	/**
	 * Render column header attributes to html
	 * 
	 * @return string
	 */
	public function renderThAttribs()
	{
		$str = '';
		foreach ($this->_thAttribs as $name => $value) {
			$str .= ' ' . $name . '="' . $value . '"';
		}
		return $str;
	}
	
	/**
	 * Render column to HTML representation
	 * 
	 * @return string
	 */
	public function render()
	{
		return '<span>' . $this->getValue() . '</span>';
	}
}