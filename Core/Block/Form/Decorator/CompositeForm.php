<?php

class Core_Block_Form_Decorator_CompositeForm extends Zend_Form_Decorator_Abstract
{
	/**
	 * Render composite decorator
	 * 
	 * @return string
	 */
	public function render($content)
	{
		$el = $this->getElement();
		
		if ((!$el instanceof Zend_Form) && (!$el instanceof Zend_Form_DisplayGroup)) {
			return $content;
		}
		
		$separator = $this->getSeparator();
		$elements  = '';
		
		foreach ($el as $item) {
			$item->setView($el->getView());
			$item->setTranslator($el->getTranslator());
			
			$elements .= $item->render();
		}
		
		$xhtml = '<div class="cbfw-form cbfw-form__' . str_replace('_', '-', $el->getName()) . '">'
		       . $el->getView()->form($el->getName(), $el->getAttribs(), $elements)
		       . '</div>';
		
		switch ($this->getPlacement()) {
			case self::PREPEND:
				return $xhtml . $separator . $content;
			case self::APPEND:
			default:
				return $content . $separator . $xhtml;
		}
	}
}