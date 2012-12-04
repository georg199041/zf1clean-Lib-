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
	protected function _getMetadatas($id = null)
	{
		
	}
	
	protected function _loadMetadatas($id = null)
	{
		
	}
	
	public function load($id, $doNotTestCacheValidity = false)
	{
		if (!$this->test($id)) {
			return false;
		}
		 
		return self::$_container[$id];
	}

    public function test($id)
	{
		return is_file($id) && file_exists($id);
		return isset(self::$_container[$id]) && !is_null(self::$_container[$id]);
	}

    public function save($data, $id, $tags = array(), $specificLifetime = false)
	{
		self::$_container[$id] = $data;
		if (count($tags) > 0) {
			$this->_log("Core_Cache_Backend_Runtime::save() : tags are unsupported by the runtime backend");
		}
		
		return true;
	}

    public function remove($id)
	{
		unset(self::$_container[$id]);
		return true;
	}

    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
	{
		if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
			self::$_container = array();
			$this->_options['cache_dir'];
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
		return false; // Make true
	}
}
