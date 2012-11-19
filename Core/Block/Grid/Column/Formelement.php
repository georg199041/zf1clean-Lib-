<?php

class Core_Block_Grid_Column_Formelement extends Core_Block_Grid_Column_Default
{
	protected $_formactionOptions = array();
	
	protected $_formactionRoute;
	
	protected $_formactionBind = array();
	
	public function setFormactionOptions($value)
	{
		if (is_array($value)) {
			$this->_formactionOptions = $value;
		} else if (is_string($value)) {
			$this->_formactionOptions = Core::urlToOptions($value);
		}
		
		return $this;
	}
	
	public function getFormactionOptions()
	{
		return $this->_formactionOptions;
	}
	
	public function setFormactionRoute($value)
	{
		$this->_formactionRoute = (string) $value;
		return $this;
	}
	
	public function getFormactionRoute()
	{
		return $this->_formactionRoute;
	}
	
	public function setFormactionBind(array $value)
	{
		$this->_formactionBind = $value;
		return $this;
	}
	
	public function getFormactionBind()
	{
		return $this->_formactionBind;
	}
}