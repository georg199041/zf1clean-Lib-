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
 * @subpackage Core_Image_Cache_Frontend
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Image.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Zend_Cache_Core
 */
require_once 'Zend/Cache/Core.php';

/**
 * Image cache frontend adapter class
 * 
 * @category   Core
 * @package    Core_Image
 * @subpackage Core_Image_Cache_Frontend
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Image_Cache_Frontend_Image extends Zend_Cache_Core
{
	/**
	 * Adapter only specific options
	 * 
	 * @var array
	 */
	protected $_specificOptions = array(
		'image_master_check_mtime' => false,
	);
    
    /**
     * Cleanup path from starting slashes
     * 
     * @param  string $path
     * @return string
     */
	protected function _path($path)
    {
    	return ltrim($path, '/\\');
    }
    
    /**
     * Get cache backend and check instance of it
     * 
     * @throws Core_Image_Cache_Exception
     * @return Core_Image_Cache_Backend_Image
     */
    public function getBackend()
    {
    	require_once 'Core/Image/Cache/Backend/Image.php';
    	if (!($this->_backend instanceof Core_Image_Cache_Backend_Image)) {
    		require_once 'Core/Image/Cache/Exception.php';
    		throw new Core_Image_Cache_Exception("This cache frontend can work only with associated backend");
    	}
    	
    	return $this->_backend;
    }
	
	/**
	 * Load cache data (path to cached file) (if enabled) COMPLETE
	 * 
	 * @param  string  $id                     Cache id
	 * @param  boolean $doNotTestCacheValidity [OPTIONAL] Unused in this frontend
	 * @param  boolean $doNotUnserialize       [OPTIONAL] Unused in this frontend
	 * @return boolean|string                  Path to cached file or false if file not exists
	 */
	public function load($id, $doNotTestCacheValidity = false, $doNotUnserialize = false)
	{
		if (!$this->getOption('caching')) {
			return false;
		}

		// clean path
		$id = $this->_path($id);
		
		$this->_log(__CLASS__ . ": load image '{$id}' cache", Zend_Log::DEBUG);
		return $this->getBackend()->load($id);
	}
	
	/**
	 * Test cache exists (if enabled) COMPLETE
	 * 
	 * @param  string $id Cache id (path to file without basedir)
	 * @return boolean|integer Last modified or false
	 */
	public function test($id)
	{
		// check cache enabled
		if (!$this->getOption('caching')) {
			return true;
		}
		
		// clean path
		$id = $this->_path($id);
		
		// debug
		$this->_log(__CLASS__ . ": test image '$id' cache exists", Zend_Log::DEBUG);
		
		// check mater file
		$mtime = $this->getBackend()->test($id);
		if ($this->getOption('image_master_check_mtime')) {
			if (!file_exists($id) || !(is_file($id) && is_readable($id))) {
				$this->_log(__CLASS__ . ": master image '$id' not exists or not readable", Zend_Log::ERR);
				return false;
			}
			
			if ($mtime < filemtime($id)) {
				return false;
			}
		}
		
		return $mtime;
	}
	
	/**
	 * Save file info
	 * 
     * @param  mixed  $data             Data (uses as id argument if single)
     * @param  string $id               [OPTIONAL] Cache id hash
     * @param  array  $tags             [OPTIONAL] Unused in this backend
     * @param  int    $specificLifetime [OPTIONAL] Unused in this backend
     * @param  int    $priority         [OPTIONAL] Unused in this backend
	 * @return boolean Returns true if success
	 */
	public function save($data, $id = null, $tags = array(), $specificLifetime = false, $priority = 8)
	{
		// check cache enabled
		if (!$this->getOption('caching')) {
			return true;
		}
		
		// clean path
		$data = $this->_path($data);
		
		// debug
		$this->_log(__CLASS__ . ": save image '$data'", Zend_Log::DEBUG);
		
		// check file
		if (!file_exists($data) || !(is_file($data) && is_readable($data))) {
			$this->_log(__CLASS__ . ": master image '$data' not exists or not readable", Zend_Log::ERR);
			return false;
		}
		
		// forward to backend
		$result = $this->getBackend()->save($data, /* Required argument by interface */ $data, $tags, $specificLifetime);
		if (!$result) {
			$this->_log(__CLASS__ . ": failed to save image '{$data}' cache -> removing it", 4);
			$this->getBackend()->remove($data);
			return false;
		}
		
		// Is success return resized image path
		return $result;
	}
	
	/**
	 * Remove cache record
	 * 
	 * @param  string $id
	 * @return boolean
	 */
	public function remove($id)
	{
		if (!$this->getOption('caching')) {
			return true;
		}
		
		// clean path
		$id = $this->_path($id);
		
		// debug
		$this->_log(__CLASS__ . ": remove image '{$id}' cache", Zend_Log::DEBUG);
		return $this->getBackend()->remove($id);
	}
	
	/**
	 * Clean cache
	 * 
	 * @param  string $mode
	 * @param  tags   $tags
	 * @return boolean
	 */
	public function clean($mode = 'all', $tags = array())
	{
		if (!$this->getOption('caching')) {
			return true;
		}
		
		// check valid method selected
		if (!in_array($mode, array(Zend_Cache::CLEANING_MODE_ALL,
				Zend_Cache::CLEANING_MODE_OLD,
				Zend_Cache::CLEANING_MODE_MATCHING_TAG,
				Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
				Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG))) {
			Zend_Cache::throwException('Invalid cleaning mode');
		}
		
		// debug
		$this->_log(__CLASS__ . ": clean cache", Zend_Log::DEBUG);
		return $this->getBackend()->clean($mode, $tags);
	}
}