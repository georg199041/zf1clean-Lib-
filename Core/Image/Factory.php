<?php

/**
 * Image factory class
 *
 * @author     Pavlenko Evgeniy
 * @category   Core
 * @package    Core_Image
 * @version    2.3
 * @subpackage Factory
 * @copyright  Copyright (c) 2012 SunNY Creative Technologies. (http://www.sunny.net)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Image_Factory
{
	/**
	 * Dummy image path
	 * 
	 * @var string
	 */
	protected static $_noImagePath;
	
	protected static $_extToAdapterMap = array(
		"gif"  => "gif",
		"jpg"  => "jpeg",
		"jpeg" => "jpeg",
		"png"  => "png",
	);
	
	/**
	 * Load image from path name
	 *
	 * @param  string $path        Path to file
	 * @return Core_Image_Abstract Image object
	 * @throws Exception           If has some errors
	 */
	public static function load($path)
	{
		$filename = ltrim($path, '/');
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		if (file_exists($filename)) {
			if (!array_key_exists($ext, self::$_extToAdapterMap)) {
				throw new Exception("Adapter for '$ext' type of image unavailable", 500);
			}
			
			$className = 'Core_Image_Adapter_' . ucfirst(self::$_extToAdapterMap[$ext]);
			require_once str_replace('_', '/', $className) . '.php';
			return new $className($filename);
		}

		throw new Exception("File '$path' not found", 500);
	}
	
	/**
	 * Set new dummy image path
	 * 
	 * @param string $path
	 */
	public static function setNoImagePath($path)
	{
		self::$_noImagePath = $path;
	}
	
	/**
	 * Get currently used dummy image path
	 * 
	 * @return string
	 */
	public static function getNoImagePath()
	{
		if (null === self::$_noImagePath) {
			throw new Exception("Empty dummy image path", 500);
		}
		
		return self::$_noImagePath;
	}
}
