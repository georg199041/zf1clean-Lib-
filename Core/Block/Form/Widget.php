<?php

require_once "Core/Block/View.php";

class Core_Block_Form_Widget extends Core_Block_View
{
	protected $_form;
	
	protected $_formOptions;
	
	public function setOptions(array $options)
	{
		parent::setOptions($options);

		if (isset($options['formOptions']) && is_array($options['formOptions'])) {
			$this->getForm()->setOptions($options['formOptions']);
		}
	
		return $this;
	}
	
	public function setForm(Zend_Form $form)
	{
		$this->_form = $form;
		return $this;
	}
	
	public function getForm()
	{
		if (null === $this->_form) {
			$this->setForm(new Zend_Form());
		}
		
		return $this->_form;
	}
	
	public function __call($method, $args)
	{
		$result = call_user_func_array(array($this->getForm(), $method), $args);
		if ($result instanceof Zend_Form) {
			return $this;
		}
		
		return $result;
	}
	
	public function render($name = null)
	{
		try {
			return $this->getForm()->render();
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
}