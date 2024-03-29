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
 * @subpackage Core_Block_Form
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Widget.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Core_Block_View
 */
require_once "Core/Block/View.php";

/**
 * @see Zend_Form
 */
require_once 'Zend/Form.php';

/**
 * Concrete Form block class
 * No need template for it
 * Implementing Zend_Form like forms with all it functional
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Form
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Form_Widget extends Core_Block_View
{
	/**
	 * Form container
	 * 
	 * @var Zend_Form
	 */
	protected $_form;
	
	/**
	 * Form only options
	 * 
	 * @var array
	 */
	protected $_formOptions = array();
	
	/**
	 * Set form options
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_View::setOptions()
	 * 
	 * @param array $options Array of Zend_Form like options can be supplied
	 */
	public function setOptions(array $options)
	{
		parent::setOptions($options);

		if (isset($options['formOptions']) && is_array($options['formOptions'])) {
			$this->getForm()->setOptions($options['formOptions']);
		}
	
		return $this;
	}
	
	/**
	 * Set internal form object
	 * 
	 * @param  Zend_Form $form
	 * @return Core_Block_Form_Widget
	 */
	public function setForm(Zend_Form $form)
	{
		$this->_form = $form;
		return $this;
	}
	
	/**
	 * Get form internal object
	 * Instantiate decorators for new form instance
	 * 
	 * @return Zend_Form
	 */
	public function getForm()
	{
		if (null === $this->_form) {
			$form = new Zend_Form();
			
			$form->addElementPrefixPath('Core_Block_Form_Decorator', 'Core/Block/Form/Decorator', 'decorator');
			$form->setElementDecorators(array('CompositeElement'));
			
			$form->addDisplayGroupPrefixPath('Core_Block_Form_Decorator', 'Core/Block/Form/Decorator', 'decorator');
			$form->setDisplayGroupDecorators(array('CompositeGroup'));
			
			$form->addPrefixPath('Core_Block_Form_Decorator', 'Core/Block/Form/Decorator', 'decorator');
			$form->setDecorators(array('CompositeForm'));
			
			$this->setForm($form);
		}
		
		return $this->_form;
	}
	
	/**
	 * Proxy methods call to Zend form
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_View::__call()
	 * 
	 * @param  string $method Method for call in Zend_Form
	 * @param  array  $args   Method arguments array
	 * @return mixed Result wery depended by called method
	 */
	public function __call($method, $args)
	{
		try {
			$result = call_user_func_array(array($this->getForm(), $method), $args);
			if ($result instanceof Zend_Form) {
				return $this;
			}
			
			return $result;
		} catch (Exception $e) {
			return parent::__call($method, $args);
		}
	}
	
	/**
	 * Extended setting action url process
	 * 
	 * @param string|array $action
	 */
	public function setAction($action)
	{
		if (is_string($action) && false !== strpos($action, '*')) {
			$action = Core::urlToOptions($action);
		}
		
		if (is_array($action)) {
			$route = $action['route'];
			unset($action['route']);
			$action = $this->url($action, $route, true);
		}
		
		$this->getForm()->setAction($action);
		return $this;
	}
	
	/**
	 * Extending form set data method to use objects
	 * 
	 * @param  mixed $defaults
	 * @return Core_Block_Form_Widget
	 */
	public function setDefaults($defaults)
	{
		if ($defaults instanceof Core_Model_Entity_Abstract) {
			$defaults = $defaults->toArray();
		}
		
		if (is_array($defaults)) {
			$this->getForm()->setDefaults($defaults);
		}
		
		return $this;
	}
	
	/**
	 * Render form block
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_View::render()
	 * 
	 * @param  string $name Name of script !!! use BLOCK_DUMMY constant for preventing errors
	 * @return string XHTML
	 */
	public function render($name)
	{
		$class  = preg_replace('/[^\p{L}\-]/u', '_', $this->getBlockName());
		$before = $this->renderBlockChilds(self::BLOCK_PLACEMENT_BEFORE);
		
		try {
			$this->setAttribute('enctype', $this->getForm()->getEnctype());
			$this->setAttribute('method', $this->getForm()->getMethod());
			$this->setAttribute('action', $this->getForm()->getAction());
			
			$this->getForm()->setDecorators(array('CompositeForm'));
			$this->getForm()->setDisplayGroupDecorators(array('CompositeGroup'));
			
			$endTag = '</form>';
			$startTag = $this->form($this->getForm()->getName(), $this->getAttributes(), false);
			$startTag = str_replace($endTag, '', $startTag);
			
			$response = $this->getForm()->render();
			
			//$this->setRendered(true);
		} catch (Exception $e) {
			$response = $e->getTraceAsString();
			//require_once 'Zend/View/Exception.php';
			//$ve = new Zend_View_Exception($e->getMessage());
			//$ve->setView($this);
			//throw $ve;
		}
    	
		$after = $this->renderBlockChilds(self::BLOCK_PLACEMENT_AFTER);
    	return '<div class="cbfw-block cbfw-block-' . $class . '">' . $startTag . $before . $response . $after . $endTag . '</div>';
	}
}