<?php

require_once "Core/Block/Grid/Column/Formelement.php";

class Core_Block_Grid_Column_Radio extends Core_Block_Grid_Column_Formelement
{
	protected $_radioOptions = array();

	public function setOptions(array $options)
	{
		if (isset($options['radioOptions'])) {
			$this->setRadioOptions($options['radioOptions']);
		}
	
		unset($options['radioOptions']);
		return parent::setOptions($options);
	}
	
	public function setRadioOptions(array $value)
	{
		$this->_radioOptions = $value;
		return $this;
	}
	
	public function getRadioOptions()
	{
		return $this->_radioOptions;
	}
	
	public function render()
	{
		$name = $this->getName() . '[' . $this->getRow($this->getGrid()->getIdColumnName()) . ']';
		
		$formactionOptions = $this->getFormactionOptions();
		foreach ($this->getFormactionBind() as $alias => $field) {
			$formactionOptions[(!is_numeric($alias) ? $alias : $field)] = $this->getRow($field);
		}
		
		$formaction = $this->getGrid()->url($formactionOptions, $this->getFormactionRoute());
		
		return '<span class="cbgw-column_formRadio">' . $this->getGrid()->formRadio(
			$name,
			$this->getValue(),
			array('formaction' => $formaction),
			$this->getRadioOptions()
		) . '</span>';
	}
}