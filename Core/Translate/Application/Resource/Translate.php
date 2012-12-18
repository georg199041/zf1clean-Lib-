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
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Manager.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * @see Core_Application_Bootstrap_Abstract
 */
require_once 'Core/Application/Bootstrap/Abstract.php';

/**
 * @see Core_Application_Module_Bootstrap
 */
require_once 'Core/Application/Module/Bootstrap.php';

/**
 * @see Core_Translate_Manager
 */
require_once 'Core/Translate/Manager.php';

/**
 * Resource for setting translation options
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Core
 * @package    Core_Translate
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Translate_Application_Resource_Translate
	extends Zend_Application_Resource_ResourceAbstract
{
	const DEFAULT_REGISTRY_KEY = 'Zend_Translate';

	/**
	 * Translate aggregator object
	 * 
	 * @var Core_Translate_Manager
	 */
	protected $_translate;
	
	/**
	 * Set traslator to manager with retrieved by bootstrap key
	 * 
	 * @param  array|Zend_Config $options
	 * @throws Zend_Application_Resource_Exception If translate manager not instantiated inside $this before call
	 */
	protected function _setTranslator($options)
	{
		if (!($this->_translate instanceof Core_Translate_Manager)) {
			require_once 'Zend/Application/Resource/Exception.php';
			throw new Zend_Application_Resource_Exception(
				'Translator can be configured only inside Core_Translate_Application_Resource_Translate::getTranslate()'
			);
		}
		
		if ($this->getBootstrap() instanceof Core_Application_Module_Bootstrap) {
			// This is module bootstrap
			$this->_translate->setTranslator($this->getBootstrap()->getModuleName(), $options);
		} else if ($this->getBootstrap() instanceof Core_Application_Bootstrap_Abstract) {
			// This is application bootstrap
			$this->_translate->setTranslator(Core_Translate_Manager::APPLICATION_TRANSLATOR_KEY, $options);
		}
	}
	
	/**
	 * Defined by Zend_Application_Resource_Resource
	 *
	 * @return Core_Translate_Manager
	 */
	public function init()
	{
		return $this->getTranslate();
	}

	/**
	 * Retrieve translate object
	 *
	 * @return Core_Translate_Manager
	 * @throws Zend_Application_Resource_Exception if registry key was used already but is no instance of Zend_Translate
	 */
	public function getTranslate()
	{
		if (null === $this->_translate) {
			$options = $this->getOptions();

			if (!isset($options['content']) && !isset($options['data'])) {
				require_once 'Zend/Application/Resource/Exception.php';
				throw new Zend_Application_Resource_Exception('No translation source data provided.');
			} else if (array_key_exists('content', $options) && array_key_exists('data', $options)) {
				require_once 'Zend/Application/Resource/Exception.php';
				throw new Zend_Application_Resource_Exception(
					'Conflict on translation source data: choose only one key between content and data.'
				);
			}

			if (empty($options['adapter'])) {
				$options['adapter'] = Zend_Translate::AN_ARRAY;
			}

			if (!empty($options['data'])) {
				$options['content'] = $options['data'];
				unset($options['data']);
			}

			if (isset($options['options'])) {
				foreach($options['options'] as $key => $value) {
					$options[$key] = $value;
				}
			}

			unset($options['registry_key']); // PREVENT USE NON DEFAULT KEY

			if (Zend_Registry::isRegistered(self::DEFAULT_REGISTRY_KEY)) {
				$translate = Zend_Registry::get(self::DEFAULT_REGISTRY_KEY);
				if (!($translate instanceof Core_Translate_Manager)) {
					require_once 'Zend/Application/Resource/Exception.php';
					throw new Zend_Application_Resource_Exception('Translator already registered in registry but is no instance of Core_Translate_Manager');
				}
				
				$this->_translate = $translate;
				$this->_setTranslator($options);
			} else {
				$this->_translate = new Core_Translate_Manager();
				$this->_setTranslator($options);
				Zend_Registry::set(self::DEFAULT_REGISTRY_KEY, $this->_translate);
			}
		}

		return $this->_translate;
	}
}