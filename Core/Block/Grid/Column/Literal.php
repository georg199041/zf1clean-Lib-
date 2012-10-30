<?php

require_once "Core/Block/Grid/Column/Default.php";

class Core_Block_Grid_Column_Literal extends Core_Block_Grid_Column_Default
{
	protected $_defaultLiteral;
	
	public function setDefaultLiteral($value)
	{
		if (!is_string($value) && !is_numeric($value)) {
			throw new Exception("Literal must be a string or number");
		}
		
		$this->_defaultLiteral = $value;
		return $this;
	}

	public function getDefaultLiteral()
	{
		return $this->_defaultLiteral;
	}
	
	public function getValue()
	{
		if (null === $this->_value) {
			return $this->getDefaultLiteral();
		}
		
		return $this->_value;
	}
}