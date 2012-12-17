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
 * @subpackage Core_Block_Grid
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Widget.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Core_Block_View
 */
require_once "Core/Block/View.php";

/**
 * @see Core_Block_Grid_Column_Default
 */
require_once 'Core/Block/Grid/Column/Default.php';

/**
 * Base grid class
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Grid
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Grid_Widget extends Core_Block_View
{
	const ORDER_ASC  = 'ASC';	
	const ORDER_DESC = 'DESC';
	
	const FILTER_EQUAL  = 'EQUAL';	
	const FILTER_LIKE   = 'LIKE';	
	const FILTER_SELECT = 'SELECT';

	/**
	 * Messages
	 * 
	 * @var array
	 */
	//protected $_messages = array();
	
	/**
	 * Grid name
	 * 
	 * @var string
	 */
	protected $_name;
	
	/**
	 * Identity column name
	 * 
	 * @var string
	 */
	protected $_idColumnName = 'id';
	
	/**
	 * Defined columns for render
	 * 
	 * @var array
	 */
	protected $_columns = array();
	
	/**
	 * Column filling data
	 * 
	 * @var array|Iterator
	 */
	protected $_data;
	
	/**
	 * Grid route options
	 * 
	 * @var array
	 */
	protected $_routeOptions = array();
	
	/**
	 * Used route name
	 * 
	 * @var string
	 */
	protected $_routeName;
	
	/**
	 * Set all options at once
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_View::setOptions()
	 * @param  array $options
	 * @return Core_Block_Grid_Widget
	 */
	public function setOptions(array $options)
	{
		parent::setOptions($options);
		
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if (!method_exists($this, $method)) {
				$this->addAttribute($key, $value);
			}
		}
		
		return $this;
	}
	
	/*public function getMessage($name, $default = null)
	{
		if (isset($this->_messages[$name])) {
			return $this->_messages[$name];
		}
		
		return $default;
	}
	
	public function setMessage($name, $message)
	{
		$this->_messages[$name] = $message;
		return $this;
	}*/

	/**
	 * Set grid name
	 * 
	 * @param  string $name
	 * @return Core_Block_Grid_Widget
	 */
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	/**
	 * Get (setup if need) grid name
	 * 
	 * @return string
	 */
	public function getName()
	{
		if (null === $this->_name) {
			$className = get_class($this);
			$name = Zend_Filter::filterStatic($className, 'Word_CamelCaseToDash');
			$this->setName(strtolower($name));
		}
		
		return $this->_name;
	}
	
	/**
	 * Set identity column name
	 * 
	 * @param  string $name
	 * @return Core_Block_Grid_Widget
	 */
	public function setIdColumnName($name)
	{
		$this->_idColumnName = $name;
		return $this;
	}
	
	/**
	 * Get identity column name
	 * 
	 * @return string
	 */
	public function getIdColumnName()
	{
		return $this->_idColumnName;
	}
	
	/**
	 * Set all columns objects
	 * 
	 * @param  array $options
	 * @return Core_Block_Grid_Widget
	 */
	public function setColumns(array $options)
	{
		$this->_columns = array();
		$this->addColumns($options);
		return $this;
	}
	
	/**
	 * Get column objects
	 * 
	 * @return array
	 */
	public function getColumns()
	{
		return $this->_columns;
	}
	
	/**
	 * Get column by name
	 * 
	 * @param  string $name
	 * @return Core_Block_Grid_Column_Default
	 */
	public function getColumn($name)
	{
		return $this->_columns[$name];
	}
	
	/**
	 * Add columns list
	 * 
	 * @param  array $value
	 * @return Core_Block_Grid_Widget
	 */
	public function addColumns(array $value)
	{
		foreach ($value as $key => $colOptions) {
			if ($colOptions instanceof Core_Block_Grid_Column_Default) {
				$this->addColumn($colOptions);
			} else if (is_array($colOptions)) {
				if (!is_numeric($key) && !array_key_exists('name', $colOptions)) {
					$colOptions['name'] = $key;
				}
				
				$this->addColumn($colOptions);
			}
		}
		
		return $this;
	}
	
	/**
	 * Add single column
	 * 
	 * @param  array|Core_Block_Grid_Column_Default $element
	 * @throws Core_Block_Exception If column definition invalid
	 * @return Core_Block_Grid_Widget
	 */
	public function addColumn($element/*, $name = null, $options = array()*/)
	{
		if ($element instanceof Core_Block_Grid_Column_Default) {
			$element->setGrid($this);
			$this->_columns[$element->getName()] = $element;
		} else if (is_array($element)) {
			if (!isset($element['type'])) {
				$element['type'] = 'default';
			}
			
			$className = ucfirst(Zend_Filter::filterStatic($element['type'], 'Word_DashToCamelCase'));
			if (false === stripos($className, '_')) {
				$className = 'Core_Block_Grid_Column_' . $className;
			}
			
			if (!@class_exists($className, true)) {
				require_once 'Core/Block/Exception.php';
				throw new Core_Block_Exception("Column class '$className' not found");
			}
			
			unset($element['type']);
			$class = new $className($element);
			$class->setGrid($this);
			$this->_columns[$class->getName()] = $class;
		} else {
			throw new Exception("Invalid column definition");
		}
		
		return $this;
	}
	
	/**
	 * Delete specified column
	 * 
	 * @param unknown_type $name
	 * @return Core_Block_Grid_Widget
	 */
	public function delColumn($name)
	{
		$this->_columns[$name] = null;
		unset($this->_columns[$name]);
		return $this;
	}
	
	/**
	 * Set grid source data
	 * 
	 * @param  array|Iterator $data
	 * @throws Core_Block_Exception If data has invalid type
	 * @return Core_Block_Grid_Widget
	 */
	public function setData($data)
	{
		if (!is_array($data) && !($data instanceof Iterator)) {
			require_once 'Core/Block/Exception.php';
			throw new Core_Block_Exception("Rows data must be instance of Iterator or an array");
		}
		
		$this->_data = $data;
		return $this;
	}
	
	/**
	 * Get all grid data
	 * 
	 * @return array|Iterator
	 */
	public function getData()
	{
		return $this->_data;
	}
	
	/**
	 * Set route options
	 * 
	 * @param  array $options
	 * @return Core_Block_Grid_Widget
	 */
	public function setRouteOptions(array $options)
	{
		$this->_routeOptions = $options;
		return $this;
	}
	
	/**
	 * Get (setup default) route options
	 * 
	 * @return array
	 */
	public function getRouteOptions()
	{
		if (null === $this->_routeOptions) {
			$request = Zend_Controller_Front::getInstance()->getRequest();
			
			$this->_routeOptions['module'] = $request->getModuleName();
			$this->_routeOptions['controller'] = $request->getControllerName();
			$this->_routeOptions['action'] = $request->getActionName();
		}
		
		return $this->_routeOptions;
	}
	
	/**
	 * Set used route name
	 * 
	 * @param  string $name
	 * @return Core_Block_Grid_Widget
	 */
	public function setRouteName($name)
	{
		$this->_routeName = $name;
		return $this;
	}
	
	/**
	 * Get used route name
	 * 
	 * @return string
	 */
	public function getRouteName()
	{
		return $this->_routeName;
	}
	
	/**
	 * Load filters data from request
	 * 
	 * @return array
	 */
	public function getFilterValues()
	{
		$filterValues = array();
		foreach ($this->getColumns() as $column) {
			if ($column->isFilterable()) {
				$value = $this->getRequest()->getParam('filter_' . $column->getName());
				if (null !== $value) {
					$filterValues[$column->getName()] = array(
						'type'  => $column->getFilterableType(),
						'value' => $value
					);
				}
			}
		}
		
		return $filterValues;
	}
	
	/**
	 * Render <col> tag html
	 * 
	 * @return string
	 */
	protected function _renderColAttribs()
	{
		$xhtml = '';
		
		foreach ($this->getColumns() as $column) {
			$xhtml .= '<col ' . $column->renderColAttribs() . '>' . PHP_EOL;
		}
		
		return $xhtml;
	}
	
	/**
	 * Render table header tag
	 * 
	 * @return string
	 */
	protected function _renderThead()
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		
		$xhtml = '<tr>';
		$hasFilters = false;
		$j = 0;
		foreach ($this->getColumns() as $column) {
			if ($column->isFilterable()) {
				$hasFilters = true;
			}
			
			$title = $column->getTitle();
			if ($column->isSortable()) {
				list($field, $direction) = explode(' ', $request->getParam('orderby'));
				$classes  = array();
				
				if (!$direction) {
					$direction = self::ORDER_ASC;
					$classes[] = self::ORDER_ASC;
				} else if ($direction == self::ORDER_ASC) {
					$direction = self::ORDER_DESC;
					$classes[] = self::ORDER_DESC;
				}
				
				if ($field == $column->getName()) {
					$classes[] = 'active';
				}
				
				$options = array_merge_recursive(
					$this->getRouteOptions(),
					array('orderby' => $column->getName() . ' ' . $direction)
				);
				
				$title = '<a class="' . implode(' ', $classes) . '" href="'
					   . $this->url($options, $this->getRouteName())
				       . '"><span>'
				       . $column->getTitle()
				       . '</span></a>';
			}
			
			$position = '';
			if ($j == 0) {
				$position = 'cbgw-columnfirst';
			} else if ($j == count($this->getColumns()) - 1) {
				$position = 'cbgw-columnlast';
			}
				
			$xhtml .= '<th class="cbgw-header ' . $position . ' cbgw-header__' . str_replace('_', '-', $column->getName()) . '" ' . $column->renderThAttribs() . '>' . $title . '</th>' . PHP_EOL;
			$j++;
		}
		
		$xhtml .= '</tr>';
		
		if ($hasFilters) {
			// Check request
			$original  = $this->url($request->getParams());
			$requested = $this->url(array_merge(array(
				'module'     => $request->getModuleName(),
				'controller' => $request->getControllerName(),
				'action'     => $request->getActionName(),
			), $request->getPost()), null, true);
			
			if ($request->isPost() && $request->getPathInfo() != $requested) {
				Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector')->gotoUrlAndExit($requested);
			}
			
			// render
			$filters = '';
			$filtersValues = (array) $request->getParams();
						
			$j = 0;
			foreach ($this->getColumns() as $column) {
				$filter = '';
				if ($column->isFilterable()) {
					$value   = $filtersValues['filter_' . $column->getName()];
					$helper  = 'formText';
					
					switch ($column->getFilterableType()) {
						case self::FILTER_LIKE:
							$filter .= $this->formText(
								'filter_' . $column->getName(),
								$value,
								array_merge($column->getFilterableOptions(), array('requested-value' => $value))
							);
							
							break;
						case self::FILTER_SELECT:
							$helper = 'formSelect';
							
							$filter .= $this->formSelect(
								'filter_' . $column->getName(),
								$value,
								array('requested-value' => $value),
								$column->getFilterableOptions()
							);
							
							break;
						case self::FILTER_EQUAL:
						default:
							$filter .= $this->formText(
								'filter_' . $column->getName(),
								$value,
								array_merge($column->getFilterableOptions(), array('requested-value' => $value))
							);
							
							break;
					}
				}
				
				$position = '';
				if ($j == 0) {
					$position = 'cbgw-columnfirst';
				} else if ($j == count($this->getColumns()) - 1) {
					$position = 'cbgw-columnlast';
				}
				
				$name = str_replace('_', '-', $column->getName());
				$filters .= '<th class="cbgw-filter ' . $position . ' cbgw-filter__' . $name . '">'
						 .  '<div class="cbgw-fwrapper cbgw-fwrapper__' . $name . ' cbgw-fwrapper_' . $helper . '">' . $filter . '</div>'
						 .  '</th>' . PHP_EOL;
				$j++;
			}
			
			$xhtml .= '<tr>' . $filters . '</tr>';
		}
		
//		$xhtml = $this->form('cbgw-filter-form', array(
//			'method' => 'get',
//			'action' => $this->url($this->getRouteOptions(), $this->getRouteName())
//		), $xhtml . '<span class="cbgw-filter-form-submit"><button>Apply filter</button></span>');
		
		return '<thead>' . $xhtml . '</thead>';
	}
	
	/**
	 * Rendet table body rows
	 * 
	 * @return string
	 */
	protected function _renderTbody()
	{
		$xhtml = '';
		if (count($this->getData()) > 0) {
			$i = 0;
			foreach ($this->getData() as $row) {
				$xhtml .= '<tr class="' . (!($i % 2) ? 'odd' : 'even') . '">' . PHP_EOL;
				$j = 0;
				foreach ($this->getColumns() as $name => $column) {
					$position = '';
					if ($j == 0) {
						$position = 'cbgw-columnfirst';
					} else if ($j == count($this->getColumns()) - 1) {
						$position = 'cbgw-columnlast';
					}
					
					$column->setAttribute('class', "cbgw-column {$position} cbgw-column__{$column->getName()}");
					$column->setRow($row);
					$attribs = $column->toHtmlAttributes();
					$xhtml .= "<td {$attribs}>{$column->render()}</td>" . PHP_EOL;
					$j++;
				}
				$xhtml .= '</tr>' . PHP_EOL;
				$i++;
			}
		} else {
			$xhtml .= '<tr>' . PHP_EOL
				   . '<td class="cbgw-body-empty" colspan="'
				   . count($this->getColumns())
			       . '">'
			       . $this->getMessage('emptyList', 'Empty list')
			       . '</td>' . PHP_EOL
			       . '</tr>' . PHP_EOL;
		}
		
		return '<tbody>' . PHP_EOL . $xhtml . PHP_EOL . '</tbody>';
	}
	
	/**
	 * Render table footer tag (currently not used)
	 * 
	 * @return string
	 */
	protected function _renderTfoot()
	{
		return '';
	}
	
	/**
	 * Render entrie grid html
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_View::render()
	 * @param string $name script name for render (use  BLOCK_DUMMY for prevent errors)
	 */
	public function render($name)
	{
		$class = preg_replace('/[^\p{L}\-]/u', '_', $this->getBlockName());
   		
   		$pre = $this->renderBlockChilds(self::BLOCK_PLACEMENT_BEFORE);
		
		try {
			$response = '<table ' . $this->toHtmlAttributes() . '>' . PHP_EOL
				   . $this->_renderColAttribs() . PHP_EOL
			       . $this->_renderThead() . PHP_EOL
			       . $this->_renderTbody() . PHP_EOL
			       . $this->_renderTfoot() . PHP_EOL
			       . '</table>';
			//$this->setRendered(true);
		} catch (Exception $e) {
			$response = $e->getMessage();
		}
		
		$post = $this->renderBlockChilds(self::BLOCK_PLACEMENT_AFTER);
		
		return '<div class="cbgw-block cbgw-block-' . $class . '" action="' . $this->url($this->getRouteOptions(), $this->getRouteName(), true, true) . '">'
    		 . $pre . $response . $post
    		 . '</div>';
	}
}