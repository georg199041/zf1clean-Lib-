<?php

require_once "Core/Block/Grid/Column/Default.php";

class Core_Block_Grid_Column_Editor extends Core_Block_Grid_Column_Default
{
	protected $_editorBindFields = array();
	
	protected $_editorOptions = array();
	
	protected $_editorRoute;
	
	public function setOptions(array $options)
	{
		return parent::setOptions($options);
	}
	
	public function setEditorBindFields(array $fields)
	{
		$this->_editorBindFields = $fields;
		return $this;
	}
	
	public function getEditorBindFields()
	{
		return $this->_editorBindFields;
	}
	
	public function setEditorOptions(array $options)
	{
		$this->_editorOptions = $options;
		return $this;
	}
	
	public function getEditorOptions()
	{
		return $this->_editorOptions;
	}
	
	public function setEditorRoute($name)
	{
		$this->_editorRoute = $name;
		return $this;
	}
	
	public function getEditorRoute()
	{
		return $this->_editorRoute;
	}
	
	public function render()
	{
		return '<span ' . $this->getAttribs() . '>'
			 . $this->getGrid()->formText($this->getName(), $this->getValue())
		     . $this->getGrid()->formButton('save', 'Save')
		     . '</span>';
	}
}