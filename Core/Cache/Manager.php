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
 * @package    Core_Cache
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Manager.php 1.0 2012-11-30 13:20:00Z Pavlenko $
 */

/** @see Zend_Cache_Manager */
require_once 'Zend/Cache/Manager.php';

/**
 * @category   Core
 * @package    Core_Cache
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Cache_Manager extends Zend_Cache_Manager
{
	/**
	 * Get the configuration templates
	 *
	 * @return array
	 */
	public function getCacheTemplates()
	{
		return $this->_optionTemplates;
	}
	
	public function getCache($name)
	{
		$cache = parent::getCache($name);
		if ($cache instanceof Zend_Cache_Core) {
			$r = new Zend_Reflection_Class($cache);
			$p = $r->getProperty('_options');
			$p->setAccessible(true);
			$options = $p->getValue($cache);
			if (!array_key_exists('label', $options)) {
				$options['label'] = ucwords($name);
				$template = (array) $this->getCacheTemplate($name);
				if (!empty($template['frontend']['options']['label'])) {
					$options['label'] = (string) $template['frontend']['options']['label'];
				}
			}
			$p->setValue($cache, $options);
		}
		
		return $cache;
	}
}