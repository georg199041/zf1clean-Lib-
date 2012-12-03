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
 * @version    $Id: Runtime.php 1.0 2012-11-30 13:20:00Z Pavlenko $
 */

/** @see Zend_Cache_Manager */
require_once 'Zend/Cache/Manager.php';

/**
 * @category   Core
 * @package    Core_Cache
 * @subpackage Core_Cache_Backend
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Cache_Backend_Runtime extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface
{
	/**
	 * Cache storage
	 * 
	 * @var array
	 */
	protected static $_container = array();
	
    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     cache id
     * @param  boolean $doNotTestCacheValidity Ignored in this backend
     * @return string cached datas (or false)
     */
	public function load($id, $doNotTestCacheValidity = false)
	{
		if (!$this->test($id)) {
			return false;
		}
		 
		return self::$_container[$id];
	}

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id cache id
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     * @throws Zend_Cache_Exception
     */
	public function test($id)
	{
		return isset(self::$_container[$id]) && !is_null(self::$_container[$id]);
	}

    /**
     * Save some datas into a cache record
     * 
     * Note: this backend does not supperted tags
     *
     * @param string $data             datas to cache
     * @param string $id               cache id
     * @param array  $tags             array of strings, the cache record will be tagged by each string entry
     * @param int    $specificLifetime Ignored in this backend
     * @return boolean true if no problem
     */
	public function save($data, $id, $tags = array(), $specificLifetime = false)
	{
		self::$_container[$id] = $data;
		if (count($tags) > 0) {
			$this->_log("Core_Cache_Backend_Runtime::save() : tags are unsupported by the runtime backend");
		}
		
		return true;
	}

    /**
     * Remove a cache record
     *
     * @param  string $id cache id
     * @return boolean true if no problem
     */
	public function remove($id)
	{
		unset(self::$_container[$id]);
		return true;
	}

    /**
     * Clean all cache records
     *
     * Note: available only CLEANING_MODE_ALL
     *
     * @param  string $mode clean mode
     * @param  array  $tags array of tags
     * @throws Zend_Cache_Exception
     * @return boolean true if no problem
     */
	public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
	{
		if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
			self::$_container = array();
		}
		
		if ($mode == Zend_Cache::CLEANING_MODE_OLD) {
			$this->_log("Core_Cache_Backend_Runtime::clean() : CLEANING_MODE_OLD is unsupported by the session backend");
		}
		
		if ($mode == Zend_Cache::CLEANING_MODE_MATCHING_TAG) {
			$this->_log("Core_Cache_Backend_Runtime::clean() : tags are unsupported by the session backend");
		}
		
		if ($mode == Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG) {
			$this->_log("Core_Cache_Backend_Runtime::clean() : tags are unsupported by the session backend");
		}
	}

	/**
	 * Return true if the automatic cleaning is available for the backend
	 *
	 * @return boolean
	 */
	public function isAutomaticCleaningAvailable()
	{
		return false;
	}
}
