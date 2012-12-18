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
 * @package    Core_Translate
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Manager.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Translate
 */
require_once 'Zend/Translate.php';

/**
 * @see Zend_Controller_Front
 */
require_once 'Zend/Controller/Front.php';

/**
 * Translate objects aggregator
 * 
 * @category   Core
 * @package    Core_Translate
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Translate_Manager
{
	const APPLICATION_TRANSLATOR_KEY = 'application';
	
	/**
	 * Translators objects list
	 * 
	 * @var array
	 */
	protected $_translators = array();
	
	/**
	 * Check valid translate definition
	 * 
	 * @param  array|Zend_Translate $translator
	 * @return boolean True if valid
	 */
	protected function _validateTranslator($translator)
	{
		return (is_array($translator) || $translator instanceof Zend_Translate);
	}
	
	/**
	 * Set translators list
	 * 
	 * @param  array $translators
	 * @return Core_Translate_Manager
	 */
	public function setTranslators(array $translators)
	{
		foreach ($translators as $name => $translator) {
			$this->setTranslator($name, $translator);
		}
		
		return $this;
	}
	
	/**
	 * Get and instantiate all translators
	 * 
	 * @return array
	 */
	public function getTranslators()
	{
		foreach ($this->_translators as $name => $translator) {
			$this->getTranslator($name);
		}
		
		return $this->_translators;
	}
	
	/**
	 * Set single translator
	 * 
	 * @param  string $name
	 * @param  array|Zend_Translate $translator
	 * @throws Core_Translate_Exception
	 * @return Core_Translate_Manager
	 */
	public function setTranslator($name, $translator)
	{
		if (!$this->_validateTranslator($translator)) {
			require_once "Core/Translate/Exception.php";
			throw new Core_Translate_Exception("Invalid translator definition for name '{$name}'");
		}
		
		$this->_translators[strtolower($name)] = $translator;
		return $this;
	}
	
	/**
	 * Get and instantiate translator
	 * 
	 * @param  string $name
	 * @return Zend_Translate
	 */
	public function getTranslator($name)
	{
		$name = strtolower($name);
		if (isset($this->_translators[$name]) && is_array($this->_translators[$name])) {
			// Provide lazy load instantiation
			$this->_translators[$name] = new Zend_Translate($this->_translators[$name]);
		}
		
		return $this->_translators[$name];
	}
	
	/**
	 * Translate message to specified or automatic locale
	 * 
	 * Opposite to default Zend_Translate this class aggregates many Zend_Translate objects 
	 * for module specific translates available
	 * 
	 * <code>
	 * // Manager must be registered previously
	 * $manager = Zend_Registry::get('Zend_Translate');
	 * 
	 * // Example with auto locale
	 * echo $manager->translate('Untranslated string');
	 * 
	 * // Example with specified locale
	 * echo $manager->translate('Untranslated string', 'ru');
	 * </code>
	 * 
	 * @see Zend_Translate_Adapter
	 * @param  string|array       $messageId
	 * @param  string|Zend_Locale $locale
	 * @return string
	 */
	public function translate($messageId, $locale = null)
	{
		// Check module specific translation
		$module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
		$module = strtolower($module);
		foreach ($this->getTranslators() as $name => $translator) {
			if ($name == $module && $translator->isTranslated($messageId, false, $locale)) {
				return $translator->translate($messageId, $locale);
			}
		}
		
		// Try to translate with global application translation
		if ($this->getTranslator('application') instanceof Zend_Translate) {
			return $this->getTranslator('application')->translate($messageId, $locale);
		}
		
		// If none exists return source
		return $messageId;
	}
}