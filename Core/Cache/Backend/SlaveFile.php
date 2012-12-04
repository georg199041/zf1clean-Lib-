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
 * @subpackage Core_Cache_Backend
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SlaveFile.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Zend_Cache_Backend
 */
require_once 'Zend/Cache/Backend.php';

/**
 * @see Zend_Cache_Backend_Interface
 */
require_once 'Zend/Cache/Backend/Interface.php';

/**
 * @package    Core_Cache
 * @subpackage Core_Cache_Backend
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Cache_Backend_SlaveFile extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface
{
    /**
     * Backend specific options
     * 
     * @var array
     */
	protected $_options = array(
        'cache_dir' => null,
    );
	
	/**
	 * Chech if record expired
	 * 
	 * @param  integer $mtime Record modified time
	 * @return boolean
	 */
	public function isExpiriedMTime($mtime)
	{
		return (time() > $mtime + $this->getLifetime(false));
	}
    
    /**
     * Get and check cache dir
     * 
     * @throws Zend_Cache_Exception If 'cache_dir' is not a directory or not writable
     * @return string
     */
    public function getCacheDir()
    {
    	if (!is_dir($this->_options['cache_dir'])) {
            Zend_Cache::throwException('cache_dir must be a directory');
        }
        
        if (!is_writable($this->_options['cache_dir'])) {
            Zend_Cache::throwException('cache_dir is not writable');
        }
        
        return $this->_options['cache_dir'];
    }
    
    /**
     * Get file path by ID
     * 
     * @param  string $id Cache id hash
     * @return string     Path to file
     */
    public function getCachedFilePath($id)
    {
    	return $this->getCacheDir() . DIRECTORY_SEPARATOR . $id;
    }
    
    /**
     * Get metadata mtime
     * 
     * @param  string $id Cache id hash
     * @return integer mtime timestamp
     */
    public function getCachedFileMTime($id)
    {
    	return (int) @file_get_contents($this->getCachedFilePath($id) . '.mtime');
    }
	
    /**
     * Load cached file path
     * 
     * @param  string  $id                     Cache id hash
     * @param  boolean $doNotTestCacheValidity [OPTIONAL] Unused in this backend
     * @return boolean|string                  Path to file if exists or false otherwise
     */
	public function load($id, $doNotTestCacheValidity = false)
	{
		$this->_log(__CLASS__ . ": load item '{$id}'", Zend_Log::DEBUG);
		if (!$this->test($id)) {
			return false;
		}
		 
		return $this->getCachedFilePath($id);
	}

	/**
	 * Test cache exists
	 * 
	 * @param  string $id Cache id hash
	 * @return boolean|integer Last modified of cached file or false if not exists cache
	 */
    public function test($id)
	{
		$this->_log(__CLASS__ . ": test item '{$id}'", Zend_Log::DEBUG);
		$file  = $this->getCachedFilePath($id);
		if (file_exists($file) && is_file($file)) {
			return $this->getCachedFileMTime($id);
		}
		
		return false;
	}
	
	/**
	 * Save file metadata
     * 
     * @param  mixed  $data             Mtime value
     * @param  string $id               Cache id hash
     * @param  array  $tags             [OPTIONAL] Unused in this backend
     * @param  int    $specificLifetime [OPTIONAL] Unused in this backend
     * @param  int    $priority         [OPTIONAL] Unused in this backend
	 * @return boolean Returns true if success
	 */
	public function save($data, $id, $tags = array(), $specificLifetime = false)
	{
		$this->_log(__CLASS__ . ": save item '{$id}'", Zend_Log::DEBUG);
		$mtime = file_put_contents($this->getCachedFilePath($id) . '.mtime', (string) $data);
		if (!$mtime) {
			$this->_log(__CLASS__ . ": can't save item mtime '{$id}'", Zend_Log::DEBUG);
			return false;
		}
		
		return true;
	}

	/**
	 * Remove specified file cache
	 * 
	 * @param  string  $id Cache id hash
	 * @return boolean Success flag
	 */
	public function remove($id)
	{
		$this->_log(__CLASS__ . ": remove item '{$id}'", Zend_Log::DEBUG);
		$file = $this->getCachedFilePath($id);
		return @unlink($file);
	}

	/**
	 * Clean all or old entries from cache
	 * 
	 * @param  string $mode Cleaning mode
	 * @param  array  $tags [OPTIONAL] Unused in this backend
	 * @return boolean Success flag
	 */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
	{
		$this->_log(__CLASS__ . ": clean backend", Zend_Log::DEBUG);
		if ($mode == Zend_Cache::CLEANING_MODE_ALL || $mode == Zend_Cache::CLEANING_MODE_OLD) {
			$dir = new DirectoryIterator($this->getCacheDir());
			foreach ($dir as $file) {
				if (!$file->isFile()) {
					// remove any directories ???
					continue;
				}
				
				if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
					// remove CLEANING_MODE_ALL
					unlink($file->getPathname());
					continue;
				}
				
				if ($this->isExpiriedMTime($file->getMTime())) {
					// remove CLEANING_MODE_OLD
					unlink($file->getPathname());
				}
			}
		}
		
		if ($mode == Zend_Cache::CLEANING_MODE_MATCHING_TAG) {
			$this->_log(__CLASS__ . ": tags are unsupported by this backend", Zend_Log::WARN);
		}
		
		if ($mode == Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG) {
			$this->_log(__CLASS__ . ": tags are unsupported by this backend", Zend_Log::WARN);
		}
		return true;
	}

	/**
	 * Return true if the automatic cleaning is available for the backend
	 *
	 * @return boolean Flag of autocleaning enabled
	 */
	public function isAutomaticCleaningAvailable()
	{
		return false;
	}
}
