<?php

require_once "Core/Block/Grid/Column/Formelement.php";

class Core_Block_Grid_Column_Select extends Core_Block_Grid_Column_Formelement
{
	protected $_selectOptions = array();

	public function setOptions(array $options)
	{
		if (isset($options['selectOptions'])) {
			$this->setSelectOptions($options['selectOptions']);
		}
	
		unset($options['selectOptions']);
		return parent::setOptions($options);
	}
	
	public function setSelectOptions(array $value)
	{
		$this->_selectOptions = $value;
		return $this;
	}
	
	public function getSelectOptions()
	{
		return $this->_selectOptions;
	}
	
	public function render()
	{
		$name = $this->getName() . '[' . $this->getRow($this->getGrid()->getIdColumnName()) . ']';
		
		$formactionOptions = $this->getFormactionOptions();
		foreach ($this->getFormactionBind() as $alias => $field) {
			$formactionOptions[(!is_numeric($alias) ? $alias : $field)] = $this->getRow($field);
		}
		
		$formaction = $this->getGrid()->url($formactionOptions, $this->getFormactionRoute());
		
		return '<span class="cbgw-column_formSelect">' . $this->getGrid()->formSelect(
			$name,
			$this->getValue(),
			array('formaction' => $formaction),
			$this->getSelectOptions()
		) . '</span>';
	}
}