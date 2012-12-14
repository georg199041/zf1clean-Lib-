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
 * @package    Core_Image
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Factory.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * Image factoru class for statical usage
 * 
 * @package    Core_Image
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
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
	
	/**
	 * Associative adapter to extension map
	 * 
	 * @var array
	 */
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
	 * @param  array  $preprocess  Preprocessing image instructions
	 * @return Core_Image_Abstract Image object
	 * @throws Exception           If has some errors
	 */
	public static function load($path, array $preprocess = null)
	{
		$filename = ltrim($path, '/');
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		
		if (file_exists($filename)) {
			// Check extension
			if (!array_key_exists(strtolower($ext), self::$_extToAdapterMap)) {
				require_once 'Core/Image/Exception.php';
				throw new Core_Image_Exception("Adapter for '$ext' type of image unavailable", 500);
			}
			
			// Load class
			$className = 'Core_Image_Adapter_' . ucfirst(self::$_extToAdapterMap[$ext]);
			require_once str_replace('_', '/', $className) . '.php';
			$class = new $className($filename);
			
			// Initialization preprocessing
			if (is_array($preprocess)) {
				foreach ($preprocess as $process) {
					call_user_func_array(array($class, $process['method']), (array) $process['arguments']);
				}
			}
			
			return $class;
		}

		require_once 'Core/Image/Exception.php';
		throw new Core_Image_Exception("File '$path' not found", 500);
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
			require_once 'Core/Image/Exception.php';
			throw new Core_Image_Exception("Empty dummy image path", 500);
		}
		
		return self::$_noImagePath;
	}
}
