<?php

require_once "Zend/Db/Table/Abstract.php";

require_once "Core/Model/Source/Interface.php";

abstract class Core_Model_Source_DbTable extends Zend_Db_Table_Abstract implements Core_Model_Source_Interface
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
	 */
	protected function _setupTableName()
	{
		$className = $this->getClassName();
		$suffix = substr($className, strrpos($className, '_') + 1);
		$this->_name = $this->getFilter()->filter($suffix);
		return $this;
	}
	
	/**
	 * Advanced constructor
	 * 
	 * @param array $config
	 */
	protected function _setup()
	{
		parent::_setup();
		
		$this->install();
	}
	
	/**
	 * Installation state method
	 */
	public function install(){}
	
	/**
	 * Acessor method
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
	 * @param Zend_Filter_Interface $filter
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
	 * @param Zend_Cache_Core $cache
	 */
	public static function setCache(Zend_Cache_Core $cache)
	{
		self::$_cache = $cache;
		return $this;
	}
	
	/**
	 * Dummy method if cache not used
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
     * @param string|array|Zend_Db_Table_Select $where   OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order   OPTIONAL An SQL ORDER clause.
     * @param int                               $count   OPTIONAL An SQL LIMIT count.
     * @param int                               $offset  OPTIONAL An SQL LIMIT offset.
     * @param array|string|Zend_Db_Expr         $columns OPTIONAL The columns to select from this table.
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
	 * @param array $where
	 * @param unknown_type $order
	 * @param unknown_type $count
	 * @param unknown_type $offset
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
			$select->where($this->_db->quoteIdentifier($this->getPrimaryName()) . ' IN (' . implode(',', $notExists) . ')');
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
		$select->where($this->_db->quoteIdentifier($this->getPrimaryName()) . ' = ?', $id);
		$rowset = $this-_fetch($select);
		
		if (count($rowset) == 0) {
			return null;
		}
		
		$row = current($rowset);
		$this->cacheSave($row, $this->getClassName() . '_' . $id);
		return $row;
	}
	
	public function fetchCount(array $where = null, $count = null, $offset = null)
	{
		$select = $this->createSelect($where, null, $count, $offset, new Zend_Db_Expr('COUNT(1)'));
		return (int) current($this->_fetch($select, Zend_Db::FETCH_COLUMN));
	}
	
	public function fetchTree()
	{
		
	}
	
	public function fetchBranch()
	{
		
	}
	
	public function find($id)
	{
		
	}
	
	public function findCollection(array $idArray)
	{
		
	}
}