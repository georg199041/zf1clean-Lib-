<?php

class Core_Block_Form_Decorator_CombinedElement extends Core_Block_Form_Decorator_CompositeElement
{
	/**
	 * Configuration additional buttons
	 * 
	 * @var array
	 */
	protected $_combinedButtons = array();
	
	/**
	 * Render additional buttons
	 * 
	 * @return string
	 */
	protected function _renderCombinedButtons()
	{
		$el = $this->getElement();
		$xhtml = '';
		
		foreach ($this->_combinedButtons as $name => $btn) {
			$label = $btn['label'];
			unset($btn['class'], $btn['type'], $btn['label']);
			
			$attribs = '';
			foreach ($btn as $aname => $attr) {
				$attribs .= " {$aname}=\"{$attr}\"";
			}
			
			$xhtml .= '<button class="btn cbfw-tag-addbtn-' . str_replace('_', '-', $el->getName()) . '__' . $name . '" type="button"' . $attribs . '>' . $label . '</button>';
		}
		
		return $xhtml;
	}
	
	/**
	 * Additional set options
	 * 
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		if (is_array($options['btns'])) {
			$this->_combinedButtons = $options['btns'];
			unset($options['btns']);
		}
		
		return parent::setOptions($options);
	}
	
	/**
	 * Render html form element tag
	 * 
	 * @return string XHTML
	 */
	public function buildElement()
	{
		$el = $this->getElement();
		
		if ('Zend_Form_Element_Hidden' == $el->getType()) {
			return $el->getView()->{$el->helper}($el->getName(), $el->getValue(), $el->getAttribs(), $el->options);
		}
		
		return '<div class="cbfw-tag cbfw-tag__' . str_replace('_', '-', $el->getName()) . ' cbfw-tag_' . $el->helper . ' input-append">'
		     . $el->getView()->{$el->helper}($el->getName(), $el->getValue(), $el->getAttribs(), $el->options)
		     . $this->_renderCombinedButtons()
		     . '</div>';
	}
}
