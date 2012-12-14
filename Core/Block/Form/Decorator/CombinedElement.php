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
 * @version    $Id: CombinedElement.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Application_Bootstrap_Bootstrap
 */
require_once "Zend/Application/Bootstrap/Bootstrap.php";

/**
 * Concrete form element decorator
 * Basic usage implementing to add buttons to form input element like as viewed input[type=file]
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Form_Decorator
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
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
			$type  = $btn['type'] != '' ? $btn['type'] : 'button';
			unset($btn['class'], $btn['type'], $btn['label']);
			
			$attribs = '';
			foreach ($btn as $aname => $attr) {
				$attribs .= " {$aname}=\"{$attr}\"";
			}
			
			$xhtml .= '<button class="btn cbfw-tag-addbtn-' . str_replace('_', '-', $el->getName()) . '__' . $name . '" type="' . $type . '"' . $attribs . '>' . $label . '</button>';
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
