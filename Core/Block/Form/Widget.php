<?php

require_once "Core/Block/View.php";

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
	 * 
	 * @return Zend_Form
	 */
	public function getForm()
	{
		if (null === $this->_form) {
			$this->setForm(new Zend_Form());
		}
		
		return $this->_form;
	}
	
	/**
	 * Proxy methods call to Zend form
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_View::__call()
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
	 */
	public function render($name = null)
	{
		$class  = preg_replace('/[^\p{L}\-]/u', '_', $this->getBlockName());
		$before = $this->_renderBlocks(self::BLOCK_PLACEMENT_BEFORE);
		
		try {
			$this->setAttribute('enctype', $this->getForm()->getEnctype());
			$this->setAttribute('method', $this->getForm()->getMethod());
			$this->setAttribute('action', $this->getForm()->getAction());
			
			$endTag = '</form>';
			$startTag = $this->form($this->getForm()->getName(), $this->getAttributes(), false);
			$startTag = str_replace($endTag, '', $startTag);
			
			$this->getForm()->addElementPrefixPath('Core_Block_Form_Decorator', 'Core/Block/Form/Decorator', 'decorator');
			$this->getForm()->setElementDecorators(array('CompositeElement'));
			
			$this->getForm()->addDisplayGroupPrefixPath('Core_Block_Form_Decorator', 'Core/Block/Form/Decorator', 'decorator');
			$this->getForm()->setDisplayGroupDecorators(array('CompositeGroup'));
			
			$this->getForm()->addPrefixPath('Core_Block_Form_Decorator', 'Core/Block/Form/Decorator', 'decorator');
			$this->getForm()->setDecorators(array('CompositeForm'));
			
			$response = $this->getForm()->render();
			
			//$this->setRendered(true);
		} catch (Exception $e) {
			$response = $e->getTraceAsString();
			//require_once 'Zend/View/Exception.php';
			//$ve = new Zend_View_Exception($e->getMessage());
			//$ve->setView($this);
			//throw $ve;
		}
    	
		$after = $this->_renderBlocks(self::BLOCK_PLACEMENT_AFTER);
    	return '<div class="cbfw-block cbfw-block-' . $class . '">' . $startTag . $before . $response . $after . $endTag . '</div>';
	}
}