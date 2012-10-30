<?php

class Core_Config_Autoloader
{
	public static function load($root, array $rules)
	{
		if (!is_string($root) && !is_dir($root)) {
			return array();
		}
		
		$iterator    = new RecursiveDirectoryIterator($root);
		$rulesFinded = array();
		
		foreach (new RecursiveIteratorIterator($iterator) as $file) {
			foreach ($rules as $rule) {
				if (preg_match($rule, $file)) {
					$rulesFinded[$rule][] = $file->getPathname();
				}
			}
		}
		
		$config = array();
		foreach ($rules as $rule) {
			if (array_key_exists($rule, $rulesFinded)) {
				foreach ($rulesFinded[$rule] as $path) {
					$configAdd = include $path;
					if (is_array($configAdd)) {
						$config = self::mergeArray($config, $configAdd);
					}
				}
			}
		}
		
		return $config;
	}
	
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