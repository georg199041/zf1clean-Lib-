<?php

require_once "Core/Block/Toolbar/Link.php";

class Core_Block_Toolbar_Button extends Core_Block_Toolbar_Link
{
	protected $_iconClass;
	
	public function setIconClass($class)
	{
		$this->_iconClass = (string) $class;
		return $this;
	}
	
	public function getIconClass()
	{
		if (null === $this->_iconClass) {
			return preg_replace('/[^\p{L}]/u', '', (string) $this->getName());
		}
		
		return $this->_iconClass;
	}
	
	public function render()
	{
		$this->setAttribute('href', $this->getToolbar()->url($this->getUrlOptions(), $this->getUrlRoute(), true));
		$this->delAttribute('class');
		
		return '<a ' . $this->toHtmlAttributes() . '>'
			 . '<span class="cbtw-button-icon ' . $this->getIconClass() . '"></span>'
			 . '<span class="cbtw-button-text">' . $this->getTitle() . '</span>'
			 . '</a>';
	}
}