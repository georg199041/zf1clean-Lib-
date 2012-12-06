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
class Core_Image_Cache_Backend_Image extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface
{
    /**
     * Backend specific options
     * 
     * @var array
     */
	protected $_options = array(
        'cache_dir'        => null,
		'image_processing' => array(),
    );
	
	protected function _saveMetadata($path, $metadata)
	{
		$bytes = @file_put_contents($path . '.metadata', serialize($metadata), LOCK_EX);
		if (false !== $bytes) {
			return true;
		}
		
		return false;
	}
	
	protected function _loadMetadata($path)
	{
		$return = @file_get_contents($path . '.metadata');
		if (false !== $return) {
			return @unserialize($return);
		}
		
		return false;
	}
	
	public function _unlinkMetadata($path)
	{
		return @unlink($path . '.metadata');
	}
	
	protected function _savePath(SplFileInfo $info)
	{
		return md5($info->getPath() . $info->getBasename('.' . $info->getExtension())) . '.' . $info->getExtension();
	}
	
	public function getOption($name)
	{
		return $this->_options[$name];
	}
	
    /**
     * Load cached file path COMPLETE
     * 
     * @param  string  $id                     Cache id hash
     * @param  boolean $doNotTestCacheValidity [OPTIONAL] Unused in this backend
     * @return boolean|string                  Path to file if exists or false otherwise
     */
	public function load($id, $doNotTestCacheValidity = false)
	{
		// debug
		$this->_log(__CLASS__ . ": load image '{$id}' cache", Zend_Log::DEBUG);
		
		// info 
		$info = new SplFileInfo($id);
		return $this->getOption('cache_dir') . '/' . $this->_savePath($info);
	}

	/**
	 * Test cache exists COMPLETE
	 * 
	 * @param  string $id Cache id hash
	 * @return boolean|integer Last modified of cached file or false if not exists cache
	 */
    public function test($id)
	{
		clearstatcache();
		
		// debug
		$this->_log(__CLASS__ . ": test image '{$id}' cache exists", Zend_Log::DEBUG);
		
		// info 
		$info     = new SplFileInfo($id);
		$savePath = $this->getOption('cache_dir') . '/' . $this->_savePath($info);
		
		// check cache exists
		if (file_exists($savePath) && is_file($savePath)) {
			$metadata = $this->_loadMetadata($savePath);
			if (false === $metadata) {
				return false;
			}
			
			if (time() <= $metadata['expire']) {
				return $metadata['mtime'];
			}
		}
		
		return false;
	}
	
	/**
	 * Save file metadata COMPLETE
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
		clearstatcache();
		
		// debug
		$this->_log(__CLASS__ . ": save image '$data' cache", Zend_Log::DEBUG);
		
		// get metadata
		$info     = new SplFileInfo($data);
		$metadata = array(
			'mtime'  => $info->getMTime(),
			'tags'   => $tags,
			'expire' => time() + $this->getLifetime($specificLifetime),
		);
		
		// check processing algorithm
		$imageProcessing = $this->getOption('image_processing');
		if (!is_array($imageProcessing) || empty($imageProcessing)) {
			$this->_log(__CLASS__ . ": can't save image '$data', processing methods not defined", Zend_Log::ERR);
			return false;
		}
		
		// prepend set of save path
		$savePath = $this->getOption('cache_dir') . '/' . $this->_savePath($info);
		array_unshift($imageProcessing, array('method' => 'setSavePath', 'arguments' => array($savePath)));
		
		// try to save metadata
		if (!$this->_saveMetadata($savePath, $metadata)) {
			$this->_log(__CLASS__ . ": can't save image '$data' metadata", Zend_Log::ERR);
			return false;
		}
		
		// save image and return path if success
		try {
			$image = call_user_func_array(array('Core_Image_Factory', 'load'), array($data, $imageProcessing));
			return $image->getPath();
		} catch (Exception $e) {
			$this->_log(__CLASS__ . ": image processing error with message: {$e->getMessage()}", Zend_Log::ERR);
			return false;
		}
	}

	/**
	 * Remove specified file cache
	 * 
	 * @param  string  $id Cache id hash
	 * @return boolean Success flag
	 */
	public function remove($id)
	{
		// debug
		$this->_log(__CLASS__ . ": remove image '{$id}' cache", Zend_Log::DEBUG);
		
		// info
		$info     = new SplFileInfo($id);		
		$savePath = $this->getOption('cache_dir') . '/' . $this->_savePath($info);
		
		// del cache
		if (!is_file($savePath) || !@unlink($savePath)) {
			$this->_log(__CLASS__ . ": can't remove image '$id' cache");
			return false;
		}
		
		// del cache metadata
		if (!$this->_unlinkMetadata($savePath)) {
			$this->_log(__CLASS__ . ": can't remove image '$id' cache metadata");
			return false;
		}
		
		return true;
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
		// debug
		$this->_log(__CLASS__ . ": clean backend", Zend_Log::DEBUG);
		
		// clean available methods
		if ($mode == Zend_Cache::CLEANING_MODE_ALL || $mode == Zend_Cache::CLEANING_MODE_OLD) {
			$dir = new DirectoryIterator($this->getOption('cache_dir'));
			foreach ($dir as $file) {
				if (!$file->isFile()) {
					// if not a file do nothing
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
