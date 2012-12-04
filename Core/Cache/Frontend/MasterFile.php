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
 * @subpackage Core_Cache_Frontend
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: MasterFile.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Zend_Cache_Core
 */
require_once 'Zend/Cache/Core.php';

/**
 * @package    Core_Cache
 * @subpackage Core_Cache_Frontend
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Cache_Frontend_MasterFile extends Zend_Cache_Core
{
    /**
     * Get hash of specified file path id
     * 
     * @param  string $id Cache id (path)
     * @return string     String converted with md5() function
     */
	public function getIdHash($id)
    {
    	$id   = ltrim($id, '/\\');
    	$info = pathinfo($id);
    	return md5($info['dirname'] . DIRECTORY_SEPARATOR . $info['filename']) . '.' . $info['extension'];
    }
	
	/**
	 * Load cache data (path to cached file) (if enabled)
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
		
		$this->_log(__CLASS__ . ": load item '{$id}'", Zend_Log::DEBUG);
		$hash = $this->getIdHash($id);
		if ($this->test($id)) {
			return $this->getBackend()->load($hash);
		}
		
		return false;
	}
	
	/**
	 * Test cache exists (if enabled)
	 * 
	 * @param  string $id Cache id (path to file without basedir)
	 * @return boolean|integer Last modified or false
	 */
	public function test($id)
	{
		if (!$this->getOption('caching')) {
			return false;
		}
		
		$this->_log(__CLASS__ . ": test item '{$id}'", Zend_Log::DEBUG);
		$hash = $this->getIdHash($id);
		return $this->getBackend()->test($hash);
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
		$cid = $data;
		if (null !== $id) {
			$cid = $id;
		}
		$cid = ltrim($cid, '/\\');
		if (!file_exists(PUBLIC_PATH . DIRECTORY_SEPARATOR . $cid)) {
			$this->_log(__CLASS__ . ": item not exists '{$cid}'", Zend_Log::ERR);
			return false;
		}
		
		$mtime = (null === $id) ? filemtime(PUBLIC_PATH . DIRECTORY_SEPARATOR . $cid) : $data;
		if ($mtime == 0) {
			$this->_log(__CLASS__ . ": item mtime invalid '{$cid}'", Zend_Log::ERR);
			return false;
		}
		
		return $this->getBackend()->save($mtime, $this->getIdHash($cid));
	}
	
	/**
	 * Remove specified file cache
	 * 
	 * @param  string  $id Cache id (path to file without basedir)
	 * @return boolean Success flag
	 */
	public function remove($id)
	{
		if (!$this->getOption('caching')) {
			return true;
		}
		
		$this->_log(__CLASS__ . ": remove item '{$id}'", Zend_Log::DEBUG);
		$hash = $this->getIdHash($id);
		return $this->getBackend()->remove($hash);
	}
	
	/**
	 * Clean all or old entries from cache
	 * 
	 * @param  string $mode Cleaning mode
	 * @param  array  $tags [OPTIONAL] Unused in this backend
	 * @return boolean Success flag
	 */
	public function clean($mode = 'all', $tags = array())
	{
		if (!$this->getOption('caching')) {
			return true;
		}
		
		if (!in_array($mode, array(Zend_Cache::CLEANING_MODE_ALL,
				Zend_Cache::CLEANING_MODE_OLD,
				Zend_Cache::CLEANING_MODE_MATCHING_TAG,
				Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
				Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG))) {
			Zend_Cache::throwException('Invalid cleaning mode');
		}
		
		$this->_log(__CLASS__ . ": clean cache", Zend_Log::DEBUG);
		return $this->getBackend()->clean($mode, $tags);
	}
}