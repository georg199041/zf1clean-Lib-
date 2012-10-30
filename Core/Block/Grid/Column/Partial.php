<?php

require_once "Core/Block/Grid/Column/Default.php";

class Core_Block_Grid_Column_Partial extends Core_Block_Grid_Column_Default
{
	protected $_partialName;
	
	protected $_partialModule;
	
	protected $_partialVars = array();
	
	public function setPartialName($value)
	{
		$this->_partialName = (string) $value;
		return $this;
	}
	
	public function getPartialName()
	{
		return $this->_partialName;
	}
	
	public function setPartialModule($value = null)
	{
		$this->_partialModule = $value;
		return $this;
	}
	
	public function getPartialModule()
	{
		return $this->_partialModule;
	}
	
	public function setPartialVars(array $value)
	{
		$this->_partialVars = $value;
		return $this;
	}
	
	public function getPartialVars()
	{
		return $this->_partialVars;
	}
	
	public function render()
	{
		$vars = $this->getPartialVars();
		$vars = array_merge_recursive($vars, array('column' => $this));
		
		return '<span ' . $this->_renderAttribs() . '>' . $this->getGrid()->partial(
			$this->getPartialName(),
			$this->getPartialModule(),
			$vars
		) . '</span>';
	}
}