<?php

require_once "Core/Block/Grid/Column/Default.php";

class Core_Block_Grid_Column_Checkbox extends Core_Block_Grid_Column_Default
{
	protected $_checkedValue = '1';
	
	protected $_uncheckedValue = '0';
	
	public function setOptions(array $options)
	{
		if (isset($options['checkedValue'])) {
			$this->setCheckedValue($options['checkedValue']);
		}
		
		if (isset($options['uncheckedValue'])) {
			$this->setUncheckedValue($options['uncheckedValue']);
		}
		
		unset($options['checkedValue'], $options['uncheckedValue']);
		return parent::setOptions($options);
	}
	
	public function setCheckedValue($value)
	{
		$this->_checkedValue = $value;
		return $this;
	}
	
	public function getCheckedValue()
	{
		return $this->_checkedValue;
	}
	
	public function setUncheckedValue($value)
	{
		$this->_uncheckedValue = $value;
		return $this;
	}
	
	public function getUncheckedValue()
	{
		return $this->_uncheckedValue;
	}
	
	public function render()
	{
		$name = $this->getName() . '[' . $this->getRow($this->getGrid()->getIdColumnName()) . ']';
		return $this->getGrid()->formCheckbox($name, $this->getValue(), $this->getAttribs(), array(
			'checkedValue'   => $this->getCheckedValue(),
			'uncheckedValue' => $this->getUncheckedValue()
		));
	}
}