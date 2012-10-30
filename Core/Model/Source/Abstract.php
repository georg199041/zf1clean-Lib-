<?php

abstract class Core_Model_Source_Abstract
{
	/**
	 * Get cache frontend
	 * Setup default if not exists
	 *
	 * @return Zend_Cache_Core
	 */
	public function getCache()
	{
		if (null === $this->_cache) {
			$this->setCache(Zend_Cache::factory(
					new Core_Cache_Frontend_Runtime(),
					new Core_Cache_Backend_Runtime()
			));
		}
	
		return $this->_cache;
	}
	
	/**
	 * Set cache frontend
	 * 
	 * @param Zend_Cache_Core $cache
	 */
	public function setCache(Zend_Cache_Core $cache)
	{
		$this->_cache = $cache;
		return $this;
	}
}
