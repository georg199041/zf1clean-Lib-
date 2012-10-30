<?php

/**
 * A Cache Backend for using {@link Zend_Cache} for caching complex or costly queries and calls
 * which however are related to a specific user's session.
 *
 * Based on Sameer Parwani Registry Backend:
 * @see http://sameerparwani.com/posts/using-zend_registry-as-a-zend_cache-backend/
 */
class Core_Cache_Backend_Runtime extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface
{
	protected static $_container = array();
	
	public function load($id, $doNotTestCacheValidity = false)
	{
		if (!$this->test($id)) {
			return false;
		}
		 
		return self::$_container[$id];
	}

	public function test($id)
	{
		return isset(self::$_container[$id]) && !is_null(self::$_container[$id]);
	}

	public function save($data, $id, $tags = array(), $specificLifetime = false)
	{
		self::$_container[$id] = $data;
		if (count($tags) > 0) {
			$this->_log("Core_Cache_Backend_Runtime::save() : tags are unsupported by the session backend");
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
?>