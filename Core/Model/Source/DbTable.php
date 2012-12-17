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
 * @package    Core_Model
 * @subpackage Core_Model_Source
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DbTable.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Zend_Db_Table_Abstract
 */
require_once "Zend/Db/Table/Abstract.php";

/**
 * @see Core_Model_Source_Interface
 */
require_once "Core/Model/Source/Interface.php";

/**
 * DbTable pattern implementation of DataMapper pattern source
 *
 * @category   Core
 * @package    Core_Model
 * @subpackage Core_Model_Source
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Core_Model_Source_DbTable
	extends Zend_Db_Table_Abstract
	implements Core_Model_Source_Interface
{
	/**
	 * Table primary key name
	 * 
	 * @var string
	 */
	protected $_primaryName;
	
	/**
	 * Current class name
	 * 
	 * @var string
	 */
	protected $_className;

	/**
	 * Self cache container
	 * 
	 * @var Zend_Cache_Core
	 */
	protected static $_cache;
	
	/**
	 * Filter container
	 *
	 * @var Zend_Filter_Interface
	 */
	protected static $_filter;
	
	/**
	 * Override default logic - inflect class name as table name
	 * 
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::_setupTableName()
	 * @return Core_Model_Source_DbTable
	 */
	protected function _setupTableName()
	{
		$className = $this->getClassName();
		$suffix = substr($className, strrpos($className, '_') + 1);
		$prefix = substr($className, 0, strpos($className, '_'));
		$this->_name = $this->getFilter()->filter($prefix) . '_' . $this->getFilter()->filter($suffix);
		return $this;
	}
	
	/**
	 * Advanced constructor
	 */
	protected function _setup()
	{
		parent::_setup();
		
		//$tables = $this->getAdapter()->listTables();
		//if (!in_array($this->getName(), $tables)) {
			//$this->install();
		//}
	}
	
	/**
	 * Installation state method
	 */
	public function install(){}
	
	/**
	 * Acessor method
	 * 
	 * @return string
	 */
	public function getName()
	{
		if (null === $this->_name) {
			$className = $this->getClassName();
			$suffix = substr($className, strrpos($className, '_') + 1);
			$this->setName($this->getFilter()->filter($suffix));
		}
		
		return $this->_name;
	}
	
	/**
	 * Acessor method
	 * @param  string $name
	 * @return Core_Model_Source_DbTable
	 */
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	/**
	 * Get current class name
	 * 
	 * @return string
	 */
	public function getClassName()
	{
		if (null === $this->_className) {
			$this->setClassName(get_class($this));
		}
		
		return $this->_className;
	}
	
	/**
	 * Set new current class name
	 * 
	 * @param  string $name
	 * @return Core_Model_Source_DbTable
	 */
	public function setClassName($name)
	{
		$this->_className = $name;
		return $this;
	}
	
	/**
	 * Get filter inflector
	 * Setup if not provided
	 *
	 * @return Zend_Filter_Interface
	 */
	public function getFilter()
	{
		if (null === self::$_filter) {
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_Word_CamelCaseToUnderscore());
			$filter->addFilter(new Zend_Filter_StringToLower());
			$this->setFilter($filter);
		}
	
		return self::$_filter;
	}
	
	/**
	 * Set new filter instance
	 *
	 * @param  Zend_Filter_Interface $filter
	 * @return Core_Model_Source_DbTable
	 */
	public function setFilter(Zend_Filter_Interface $filter)
	{
		self::$_filter = $filter;
		return $this;
	}
	
	/**
     * Support method for fetching rows (adding fetch mode).
     * @see Zend_Db_Table_Abstract::_fetch
     *
     * @param  Zend_Db_Table_Select $select  query options.
     * @param  string               $fetchMode One of Zend_Db::FETCH_* constants
     * @return array An array containing the row results in FETCH_ mode.
     */
    protected function _fetch(Zend_Db_Table_Select $select, $fetchMode = Zend_Db::FETCH_ASSOC)
    {
        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll($fetchMode);
        return $data;
    }
	
    /**
     * Get primary key name
     * 
     * @return string
     */
	public function getPrimaryName()
	{
		if (null === $this->_primaryName) {
			$this->setPrimaryName(current($this->info(self::PRIMARY)));
		}
		
		return $this->_primaryName;
	}
	
	/**
	 * Set primary key name
	 * 
	 * @param  string $name
	 * @return Core_Model_Source_DbTable
	 */
	public function setPrimaryName($name)
	{
		$this->_primaryName = $name;
		return $this;
	}
	
	/**
	 * Get cache frontend
	 * Setup default if not exists
	 *
	 * @return Zend_Cache_Core
	 */
	public static function getCache()
	{
		if (null === self::$_cache) {
			self::setCache(Zend_Cache::factory(
				new Core_Cache_Frontend_Runtime(),
				new Core_Cache_Backend_Runtime()
			));
		}
	
		return self::$_cache;
	}
	
	/**
	 * Set cache frontend
	 *
	 * @param  Zend_Cache_Core $cache
	 * @return Core_Model_Source_DbTable
	 */
	public static function setCache(Zend_Cache_Core $cache)
	{
		self::$_cache = $cache;
		return $this;
	}
	
	/**
	 * Dummy method if cache not used
	 * Test cache record exists
	 * 
	 * @see Zend_Cache_Core::test
	 * @param  string $id
	 * @return boolean
	 */
	public function cacheTest($id)
	{
		if (null === self::getCache()) {
			return false;
		}
		
		return self::getCache()->test($id);
	}
	
	/**
	 * Dummy method if cache not used
	 * Load cached data if possible
	 * 
	 * @see Zend_Cache_Core::load
	 * @param string  $id
	 * @param boolean $doNotTestCacheValidity
	 * @param boolean $doNotUnserialize
	 */
	public function cacheLoad($id, $doNotTestCacheValidity = false, $doNotUnserialize = false)
	{
		if (null === self::getCache()) {
			throw new Exception("Cache frontend not defined in " . $this->getClassName());
		}
		
		return self::getCache()->load($id, $doNotTestCacheValidity, $doNotUnserialize);
	}
	
	/**
	 * Dummy method if cache not used
	 * Save cache data
	 * 
	 * @param  mixed  $data
	 * @param  string $id
	 * @param  array  $tags
	 * @param  int    $specificLifetime
	 * @param  int    $priority
	 * @return boolean
	 */
	public function cacheSave($data, $id = null, $tags = array(), $specificLifetime = false, $priority = 8)
	{
		if (null === self::getCache()) {
			return true;
		}
		
		return self::getCache()->save($data, $id, $tags, $specificLifetime, $priority);
	}
	
	/**
	 * Create Zend_Db_Table_Select object for fetch operations
	 * Based on offset mode
	 * 
     * @param  string|array|Zend_Db_Table_Select $where   OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param  string|array                      $order   OPTIONAL An SQL ORDER clause.
     * @param  int                               $count   OPTIONAL An SQL LIMIT count.
     * @param  int                               $offset  OPTIONAL An SQL LIMIT offset.
     * @param  array|string|Zend_Db_Expr         $columns OPTIONAL The columns to select from this table.
	 * @return Zend_Db_Table_Select
	 */
	public function createSelect($where = null, $order = null, $count = null, $offset = null, $columns = null)
	{
		if (!($where instanceof Zend_Db_Table_Select)) {
			$select = $this->select(true);
		
			if ($where !== null) {
				$this->_where($select, $where);
			}
		
			if ($order !== null) {
				$this->_order($select, $order);
			}
		
			if ($count !== null || $offset !== null) {
				$select->limit($count, $offset);
			}
		
			if ($columns !== null) {
        		$select->reset(Zend_Db_Table_Select::COLUMNS);
				$select->columns($columns);
			}
		} else {
			$select = $where;
		}
		
		return $select;
	}
	
	/**
	 * Fetch only primary column
	 * 
	 * @param  array $where
	 * @param  string|array $order
	 * @param  integer $count
	 * @param  integer $offset
	 * @return array
	 */
	public function fetchPrimaryAll(array $where = null, $order = null, $count = null, $offset = null)
	{
		$select = $this->createSelect($where, $order, $count, $offset, $this->getPrimaryName());
		return $this->_fetch($select, Zend_Db::FETCH_COLUMN);
	}
	
	/**
	 * Fetch all rows (use cache)
	 * 
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::fetchAll()
	 * @param  array        $where
	 * @param  string|array $order
	 * @param  int          $count
	 * @param  int          $offset
	 * @return array
	 */
	public function fetchAll(array $where = null, $order = null, $count = null, $offset = null)
	{
		// Fetch identifiers of query
		$identifiers = $this->fetchPrimaryAll($where, $order, $count, $offset);
		if (count($identifiers) == 0) {
			return array();
		}
		
		// Parse cahed data
		$exists    = array();
		$notExists = array();
		foreach ($identifiers as $id) {
			if ($this->cacheTest($this->getClassName() . '_' . $id)) {
				$exists[$id] = $this->cacheLoad($this->getClassName() . '_' . $id);
			} else {
				$notExists[] = $id;
			}
		}
		
		// Modify query for faster load unloaded data
		$select = $this->createSelect();
		$rowset = array();
		if (count($notExists) > 0) {
			$select->where($this->getAdapter()->quoteIdentifier($this->getPrimaryName()) . ' IN (' . implode(',', $notExists) . ')');
			//echo $select;
			$rowset = $this->_fetch($select);
		}
		
		// Combine result
		$return = array();
		foreach ($identifiers as $id) {
			foreach ($exists as $item) {
				if ($id == $item[$this->getPrimaryName()]) {
					$return[] = $item;
				}
			}
			
			foreach ($rowset as $item) {
				if ($id == $item[$this->getPrimaryName()]) {
					$this->cacheSave($item, $this->getClassName() . '_' . $id);
					$return[] = $item;
				}
			}
		}
		
		return $return;
	}

	/**
	 * Fetch single row
	 * 
	 * @param  array        $where
	 * @param  string|array $order
	 * @param  int          $offset
	 * @return null|array
	 */
	public function fetchRow(array $where = null, $order = null, $offset = null)
	{
		$id = $this->fetchPrimaryAll($where, $order, 1, $offset);
		if (count($id) == 0) {
			return null;
		}
		
		$id = current($id);
		if ($this->cacheTest($this->getClassName() . '_' . $id)) {
			return $this->cacheLoad($this->getClassName() . '_' . $id);
		}
		
		$select = $this->createSelect();
		$select->where($this->getAdapter()->quoteIdentifier($this->getPrimaryName()) . ' = ?', $id);
		$rowset = $this->_fetch($select);
		
		if (count($rowset) == 0) {
			return null;
		}
		
		$row = current($rowset);
		$this->cacheSave($row, $this->getClassName() . '_' . $id);
		return $row;
	}
	
	/**
	 * Fetch count of rows
	 * 
	 * @param  array   $where
	 * @param  integer $count
	 * @param  integer $offset
	 * @return number
	 */
	public function fetchCount(array $where = null, $count = null, $offset = null)
	{
		$select = $this->createSelect($where, null, $count, $offset, new Zend_Db_Expr('COUNT(1)'));
		return (int) current($this->_fetch($select, Zend_Db::FETCH_COLUMN));
	}
	
	/**
	 * Prepare tree identifiers array
	 * for tree fetcher
	 * 
	 * @param  array   $rows
	 * @param  array   $options
	 * @param  integer $depth
	 * @param  array   $identifiers
	 * @return array
	 */
	public function prepareTreeIds($rows, $options, $depth, $identifiers = array())
	{
		if (null !== $depth) {
			if ($depth < 1) {
				return $identifiers;
			} else {
				$depth--;
			}
		}
		
		foreach ($rows as $row) {
			if ($row[$options['pColName']] == $options['pColValue']) {
				$identifiers[] = $row[$options['cColName']];
				$cOptions = array_merge($options, array('pColValue' => $row[$options['cColName']]));
				$identifiers = $this->prepareTreeIds($rows, $cOptions, $depth, $identifiers);
			}
		}
		
		return $identifiers;
	}
	
	/**
	 * Fetch tree data
	 * Used identity map cache pattern
	 * 
	 * @param  array        $where
	 * @param  array|string $order
	 * @param  integer      $depth
	 * @param  array        $options
	 * @return array
	 */
	public function fetchTree(array $where = null, $order = null, $depth = null, array $options = array())
	{
		$select = $this->createSelect($where, $order, null, null, array($options['pColName'], $options['cColName']));
		$rows   = $this->_fetch($select);
		
		$identifiers = $this->prepareTreeIds($rows, $options, $depth);
		if (count($identifiers) == 0) {
			return array();
		}
		
		// Parse cahed data
		$exists    = array();
		$notExists = array();
		foreach ($identifiers as $id) {
			if ($this->cacheTest($this->getClassName() . '_' . $id)) {
				$exists[$id] = $this->cacheLoad($this->getClassName() . '_' . $id);
			} else {
				$notExists[] = $id;
			}
		}
		
		// Modify query for faster load unloaded data
		$select = $this->createSelect();
		$rowset = array();
		if (count($notExists) > 0) {
			$select->where($this->getAdapter()->quoteIdentifier($this->getPrimaryName()) . ' IN (' . implode(',', $notExists) . ')');
			//echo $select;
			$rowset = $this->_fetch($select);
		}

		// Combine result
		$return = array();
		foreach ($identifiers as $id) {
			foreach ($exists as $item) {
				if ($id == $item[$this->getPrimaryName()]) {
					$return[] = $item;
				}
			}
				
			foreach ($rowset as $item) {
				if ($id == $item[$this->getPrimaryName()]) {
					$this->cacheSave($item, $this->getClassName() . '_' . $id);
					$return[] = $item;
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Fetch parents branch data
	 * 
	 * @param  array        $where
	 * @param  string|array $order
	 * @param  array        $options
	 * @return array
	 */
	public function fetchBranch(array $where = null, $order = null, array $options = array())
	{
		$select = $this->createSelect($where, $order, null, null, array($options['pColName'], $options['cColName']));
		$rows   = $this->_fetch($select);
			
		$identifiers = array();
		while (null !== $options['pColValue']) {
			foreach ($rows as $row) {
				if ($row[$options['cColName']] == $options['pColValue']) {
					if (null !== $row[$options['pColName']]) {
						$identifiers[] = $row[$options['pColName']];
					}
					
					$options['pColValue'] = $row[$options['pColName']];					
					break;
				}
			}
		}
		
		if (count($identifiers) == 0) {
			return array();
		}
		
		// Parse cahed data
		$exists    = array();
		$notExists = array();
		foreach ($identifiers as $id) {
			if ($this->cacheTest($this->getClassName() . '_' . $id)) {
				$exists[$id] = $this->cacheLoad($this->getClassName() . '_' . $id);
			} else {
				$notExists[] = $id;
			}
		}
			
		// Modify query for faster load unloaded data
		$select = $this->createSelect();
		$rowset = array();
		if (count($notExists) > 0) {
			$select->where($this->getAdapter()->quoteIdentifier($this->getPrimaryName()) . ' IN (' . implode(',', $notExists) . ')');
			//echo $select;
			$rowset = $this->_fetch($select);
		}

		// Combine result
		$return = array();
		foreach ($identifiers as $id) {
			foreach ($exists as $item) {
				if ($id == $item[$this->getPrimaryName()]) {
					$return[] = $item;
				}
			}
				
			foreach ($rowset as $item) {
				if ($id == $item[$this->getPrimaryName()]) {
					$this->cacheSave($item, $this->getClassName() . '_' . $id);
					$return[] = $item;
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Find row by Id
	 * 
	 * @param  integer|string $id
	 * @return array|null
	 */
	public function find($id)
	{
		return $this->fetchRow(array(
			$this->getAdapter()->quoteIdentifier($this->getPrimaryName()) . ' = ?' => $id
		));
	}
	
	/**
	 * Fetch collection of rows by his ids
	 * 
	 * @param  array $idArray
	 * @return array
	 */
	public function findCollection(array $idArray)
	{
		return $this->fetchAll(array(
			$this->getAdapter()->quoteIdentifier($this->getPrimaryName()) . ' IN (?)' => $idArray
		));
	}
}