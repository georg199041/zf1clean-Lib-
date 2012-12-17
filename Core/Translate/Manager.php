<?php

require_once 'Zend/Translate.php';

require_once 'Zend/Translate/Adapter.php';

require_once 'Zend/Controller/Front.php';

class Core_Translate_Manager
{
	protected $_translators = array();
	
	protected function _validateTranslator($translator)
	{
		return (is_array($translator) || $translator instanceof Zend_Translate_Adapter);
	}
	
	public function setTranslators(array $translators)
	{
		foreach ($translators as $name => $translator) {
			$this->setTranslator($name, $translator);
		}
		
		return $this;
	}
	
	public function getTranslators()
	{
		foreach ($this->_translators as $name => $translator) {
			$this->getTranslator($name);
		}
		
		return $this->_translators;
	}
	
	public function setTranslator($name, $translator)
	{
		if (!$this->_validateTranslator($translator)) {
			require_once "Core/Translate/Exception.php";
			throw new Core_Translate_Exception("Invalid translator definition for name '{$name}'");
		}
		
		$this->_translators[$name] = $translator;
		return $this;
	}
	
	public function getTranslator($name)
	{
		if (isset($this->_translators[$name]) && is_array($this->_translators[$name])) {
			// Provide lazy load instantiation
			$this->_translators[$name] = new Zend_Translate($this->_translators[$name]);
		}
		
		return $this->_translators[$name];
	}
	
	public function translate($messageId, $locale = null)
	{
		$module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
		foreach ($this->getTranslators() as $name => $translator) {
			if ($name == $module && $translator->isTranslated($messageId, false, $locale)) {
				return $translator->translate($messageId, $locale);
			}
		}
		
		if ($this->getTranslator('application') instanceof Zend_Translate_Adapter) {
			return $this->getTranslator('application')->translate($messageId, $locale);
		}
		
		return $messageId;
	}
}