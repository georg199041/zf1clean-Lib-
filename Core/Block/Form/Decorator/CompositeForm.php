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
 * @version    $Id: CompositeForm.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Form_Decorator_Abstract
 */
require_once "Zend/Form/Decorator/Abstract.php";

/**
 * Concrete form decorator for use BEM css model
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Form_Decorator
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Form_Decorator_CompositeForm extends Zend_Form_Decorator_Abstract
{
	/**
	 * Render composite decorator
	 * 
	 * @param  string $content Initial content for rendering
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