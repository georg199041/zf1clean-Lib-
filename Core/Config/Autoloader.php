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
 * @package    Core_Config
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Autoloader.php 0.1 2012-12-12 pavlenko $
 */

/**
 * Config autoloader provides precompile config strategy for use it before application instantiated
 *
 * @category   Core
 * @package    Core_Config
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Config_Autoloader
{
	/**
	 * Load config files by specified filter regex patterns
	 * 
	 * @param  string $root Root directory inside wich loader must find files
	 * @param  array  $rules Array of regex rules to compare with file names
	 * @return array It always must return array
	 */
	public static function load($root, array $rules)
	{
		if (!is_string($root) && !is_dir($root)) {
			return array();
		}
		
		$iterator    = new RecursiveDirectoryIterator($root);
		$rulesFinded = array();
		
		foreach (new RecursiveIteratorIterator($iterator) as $file) {
			foreach ($rules as $rule) {
				//$rulesFinded[$rule] = array();
				if (preg_match($rule, $file)) {
					$rulesFinded[$rule][] = $file->getPathname();
				}
			}
		}

		foreach ($rulesFinded as $rule => &$list) {
			if (empty($rulesFinded[$rule])) {
				unset($rulesFinded[$rule]);
			} else {
				asort($list);
			}
		}
		//echo '<br><br><br><br><br>';
		$config = array();
		foreach ($rules as $rule) {
			if (array_key_exists($rule, $rulesFinded)) {
				foreach ($rulesFinded[$rule] as $path) {
					//echo $path . '<br>';
					$configAdd = require $path;
					if (is_array($configAdd)) {
						$config = self::mergeArray($config, $configAdd);
					}
				}
			}
		}
		
		return $config;
	}
	
	/**
	 * Merge config arrays (provider custom merging rules)
	 * 
	 * @param  array $config1
	 * @param  array $config2
	 * @return array It must always return array
	 */
	public static function mergeArray($config1, $config2)
	{
		foreach ($config2 as $key => $val) {
			if (is_integer($key)) {
				$config1[] = $val;
			} else if (is_array($val) && isset($config1[$key]) && is_array($config1[$key])) {
				$config1[$key] = self::mergeArray($config1[$key], $val);
			} else {
				$config1[$key] = $val;
			}
		}
		
		return $config1;
	}
}