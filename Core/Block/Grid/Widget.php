<?php

require_once "Core/Block/View.php";

class Core_Block_Grid_Widget extends Core_Block_View
{
	const ORDER_ASC = 'ASC';
	
	const ORDER_DESC = 'DESC';
	
	const FILTER_EQUAL = 'EQUAL';
	
	const FILTER_LIKE = 'LIKE';
	
	const FILTER_SELECT = 'SELECT';

	protected $_messages = array();
	
	protected $_name;
	
	protected $_idColumnName = 'id';
	
	protected $_columns = array();
	
	protected $_data;
	
	protected $_routeOptions = array();
	
	protected $_routeName;
	
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
	
	public function getMessage($name, $default = null)
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
	}

	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	public function getName()
	{
		if (null === $this->_name) {
			$className = get_class($this);
			$name = Zend_Filter::filterStatic($className, 'Word_CamelCaseToDash');
			$this->setName(strtolower($name));
		}
		
		return $this->_name;
	}
	
	public function setIdColumnName($name)
	{
		$this->_idColumnName = $name;
		return $this;
	}
	
	public function getIdColumnName()
	{
		return $this->_idColumnName;
	}
	
	public function setColumns(array $options)
	{
		$this->_columns = array();
		$this->addColumns($options);
		return $this;
	}
	
	public function getColumns()
	{
		return $this->_columns;
	}
	
	public function getColumn($name)
	{
		return $this->_columns[$name];
	}
	
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
				throw new Exception("Column class '$className' not found");
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
	
	public function delColumn($name)
	{
		$this->_columns[$name] = null;
		unset($this->_columns[$name]);
		return $this;
	}
	
	public function setData($data)
	{
		if (!is_array($data) && !($data instanceof Iterator)) {
			throw new Exception("Rows data must be instance of Iterator or an array");
		}
		
		$this->_data = $data;
		return $this;
	}
	
	public function getData()
	{
		return $this->_data;
	}
	
	public function setRouteOptions(array $options)
	{
		$this->_routeOptions = $options;
		return $this;
	}
	
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
	
	public function setRouteName($name)
	{
		$this->_routeName = $name;
		return $this;
	}
	
	public function getRouteName()
	{
		return $this->_routeName;
	}
	
	protected function _renderColAttribs()
	{
		$xhtml = '';
		
		foreach ($this->getColumns() as $column) {
			$xhtml .= '<col ' . $column->renderColAttribs() . '>';
		}
		
		return $xhtml;
	}
	
	protected function _renderThead()
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		
		$xhtml = '';
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
				
			$xhtml .= '<th class="cbgw-header ' . $position . ' cbgw-header-' . $column->getName() . '" ' . $column->renderThAttribs() . '>' . $title . '</th>';
			$j++;
		}
		
		if ($hasFilters) {
			$filters = '';
			foreach ($this->getColumns() as $column) {
				$filter = '';
				if ($column->isFilterable()) {
					$value = $request->getParam('filter_' . $column->getName());
					switch ($column->getFilterableType()) {
						case self::FILTER_LIKE:
							$filter .= $this->formText('filter_' . $column->getName(), $value, $column->getFilterableOptions());
							break;
						case self::FILTER_SELECT:
							$filter .= $this->formSelect('filter_' . $column->getName(), $value, null, $column->getFilterableOptions());
							break;
						case self::FILTER_EQUAL:
						default:
							$filter .= $this->formText('filter_' . $column->getName(), $value, $column->getFilterableOptions());
							break;
					}
				}
				
				$filters .= '<th class="cbgw-filter-' . $column->getName() . '">' . $filter . '</th>';
			}
			
			$xhtml .= '</tr><tr>' . $filters;
		}
		
		return '<thead><tr>' . $xhtml . '</tr></thead>';
	}
	
	protected function _renderTbody()
	{
		$xhtml = '';
		if (count($this->getData()) > 0) {
			$i = 0;
			foreach ($this->getData() as $row) {
				$xhtml .= '<tr class="' . (!($i % 2) ? 'odd' : 'even') . '">';
				$j = 0;
				foreach ($this->getColumns() as $name => $column) {
					$position = '';
					if ($j == 0) {
						$position = 'cbgw-columnfirst';
					} else if ($j == count($this->getColumns()) - 1) {
						$position = 'cbgw-columnlast';
					}
					
					$column->setAttribute('class', "cbgw-column {$position} cbgw-column-{$column->getName()}");
					$column->setRow($row);
					$attribs = $column->toHtmlAttributes();
					$xhtml .= "<td {$attribs}>{$column->render()}</td>";
					$j++;
				}
				$xhtml .= '</tr>';
				$i++;
			}
		} else {
			$xhtml .= '<tr><td class="cbgw-body-empty" colspan="'
				   . count($this->getColumns())
			       . '">'
			       . $this->getMessage('emptyList', 'Empty list')
			       . '</td></tr>';
		}
		
		return '<tbody>' . $xhtml . '</tbody>';
	}
	
	protected function _renderTfoot()
	{
		return '';
	}
	
	public function render($name = null)
	{
   		$response = '';
   		$response .= $this->_renderBlocks(self::BLOCK_PLACEMENT_BEFORE);
		
		try {
			$class = preg_replace('/[^\p{L}\-]/u', '_', $this->getBlockName());
			$response .= '<div class="cbgw-block cbgw-block-' . $class . '"><table ' . $this->toHtmlAttributes() . '>'
				   . $this->_renderColAttribs()
			       . $this->_renderThead()
			       . $this->_renderTbody()
			       . $this->_renderTfoot()
			       . '</table></div>';
			//$this->setRendered(true);
		} catch (Exception $e) {
			$response .= $e->getMessage();
		}
		
		$response .= $this->_renderBlocks(self::BLOCK_PLACEMENT_AFTER);
    	return $response;
	}
}