<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Form_Decorator
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: CompositeElement.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Form_Decorator_Abstract
 */
require_once "Zend/Form/Decorator/Abstract.php";

/**
 * Concrete form element decorator for use BEM css model
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Form_Decorator
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Form_Decorator_CompositeElement extends Zend_Form_Decorator_Abstract
{
	/**
	 * Render composite element label tag
	 * 
	 * @return string XHTML
	 */
	public function buildLabel()
	{
		$el    = $this->getElement();
		$label = $el->getLabel();
		if (null !== $el->getTranslator()) {
			$label = $el->getTranslator()->translate($label);
		}
		
		$label = '<span class="cbfw-label-text">' . $label . '</span>';		
		if ($el->isRequired()) {
			$label .= '<span class="cbfw-label-wcard">*</span>';
		}
		
		return '<div class="cbfw-label cbfw-label__' . str_replace('_', '-', $el->getName()) . ' cbfw-label_' . $el->helper . '">'
		     . $el->getView()->formLabel($el->getName(), $label, array('escape' => false))
		     . '</div>';
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
		
		return '<div class="cbfw-tag cbfw-tag__' . str_replace('_', '-', $el->getName()) . ' cbfw-tag_' . $el->helper . '">'
		     . $el->getView()->{$el->helper}($el->getName(), $el->getValue(), $el->getAttribs(), $el->options)
		     . '</div>';
	}
	
	/**
	 * Render error messages tag
	 * 
	 * @return string XHTML
	 */
	public function buildErrors()
	{
		$el       = $this->getElement();
		$messages = $el->getMessages();		
		
		$errors = '';
		if (!empty($messages)) {
			$errors = $el->getView()->formErrors($messages);
		}
		
		return '<div class="cbfw-errors cbfw-errors__' . str_replace('_', '-', $el->getName()) . ' cbfw-errors_' . $el->helper . '">' . $errors . '</div>';
	}
	
	/**
	 * Render description tag
	 * 
	 * @return string XHTML
	 */
	public function buildDescription()
	{
		$el   = $this->getElement();
		$desc = $el->getDescription();
		
		return '<div class="cbfw-description cbfw-description__' . str_replace('_', '-', $el->getName()) . ' cbfw-description_' . $el->helper . '">' . $desc . '</div>';
	}
	
	/**
	 * Render composite decorator
	 * 
	 * (non-PHPdoc)
	 * @see Zend_Form_Decorator_Abstract::render()
	 * 
	 * @param  string $content Initial content for rendering
	 * @return string XHTML
	 */
	public function render($content)
	{
		$element = $this->getElement();
		
		if (!$element instanceof Zend_Form_Element) {
			return $content;
		}
		
		if (null === $element->getView()) {
			return $content;
		}
	
		$separator = $this->getSeparator();
		$placement = $this->getPlacement();
		$type      = $element->getType();
		
		$label     = $this->buildLabel();
		$input     = $this->buildElement();
		$errors    = $this->buildErrors();
		$desc      = $this->buildDescription();
		$required  = $element->isRequired() ? ' cbfw-element_required' : ' ';
		$hasErrors = $element->hasErrors() ? ' cbfw-element_error' : ' ';
	
		$output = '<div class="cbfw-element cbfw-element__' . str_replace('_', '-', $element->getName()) . ' cbfw-element_' . $element->helper . $required . $hasErrors . '">'
		        . $label
		        . $input
		        . $errors
		        . $desc
		        . '</div>';
		
		if ('Zend_Form_Element_Hidden' == $type) {
			$output = $input;
		}
	 
		switch ($placement) {
			case (self::PREPEND):
				return $output . $separator . $content;
			case (self::APPEND):
			default:
				return $content . $separator . $output;
		}
	}
}