<?php

abstract class Core_Model_Mapper_Abstract
{
	/**
	 * Source container
	 * 
	 * @var Core_Model_Source
	 */
	protected $_source;
	
	/**
	 * Source class name
	 * 
	 * @var string
	 */
	protected $_sourceClassName;
	
	/**
	 * Collection class name
	 * 
	 * @var string
	 */
	protected $_collectionClassName;
	
	/**
	 * Entity class name
	 * 
	 * @var string
	 */
	protected $_entityClassName;
	
	/**
	 * PK name
	 * 
	 * @var string
	 */
	protected $_primaryName = 'id';
	
	/**
	 * Instance class name
	 * 
	 * @var string
	 */
	protected $_className;
	
	/**
	 * Prepare identifiers for full fetch tree data
	 * 
	 * @param  string                          $pColName  Parent record column name
	 * @param  string                          $cColName  Child records column name
	 * @param  string|integer                  $pColValue Initial parent record column value (in $pColName)
	 * @param  Core_Model_Collection_Abstract $rowset    Rowset data for get identifiers
	 * @param  integer                         $depth     [OPTIONAL] Max tree depth
	 * @param  array                           $result    [OPTIONAL] Pre formatted identifiers
	 * @return array                                      Prepared identifiers
	 */
	/*protected function _prepareTreeIdentifiers($pColName, $cColName, $pColValue, Core_Model_Collection_Abstract $rowset, $depth = null, array $result = array())
	{
		if (!is_numeric($depth) || $depth < 1) {
			return $result;
		}
		
		foreach ($rowset as $row) {
			if ($pColValue == $row[$cColName]) {
				$result[] = $row->getPrimary();
				$result = $this->_prepareTreeIdentifiers($pColName, $cColName, $row[$pColName], $rowset, $depth - 1, $result);
			}
		}
		
		return $result;
	}*/
	
	/**
	 * Format rowset data to collection object as tree
	 * 
	 * @param  string         $pColName        Parent record column name
	 * @param  string         $cColName        Child records column name
	 * @param  string|integer $pColValue       Initial parent record column value (in $pColName)
	 * @param  array          $rowset          Fetched data for conversion
	 * @return Core_Model_Collection_Abstract Collection result
	 */
	/*protected function _formatTree($pColName, $cColName, $pColValue, $rowset)
	{
		$collection = $this->createCollection();
		
		foreach ($rowset as $row) {
			if ($pColValue == $row[$cColName]) {
				$entity = $this->create($row);
				$entity->setPrimaryName($this->getPrimaryName());
				$entity->setChilds($this->_formatTree($pColName, $cColName, $row[$pColName], $rowset));
				$collection->append($entity);
			}
		}
		
		return $collection;
	}*/
	
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
	 * @param string $name
	 */
	public function setClassName($name)
	{
		$this->_className = $name;
		return $this;
	}
	
	/**
	 * Get source class name
	 * 
	 * @return string
	 */
	public function getSourceClassName()
	{
		if (null === $this->_sourceClassName) {
			$this->setSourceClassName(str_ireplace('_Mapper_', '_Source_', $this->getClassName()));
		}
	
		return $this->_sourceClassName;
	}
	
	/**
	 * Set new source class name
	 * 
	 * @param string $name
	 */
	public function setSourceClassName($name)
	{
		$this->_sourceClassName = $name;
		return $this;
	}

	/**
	 * Get collection class name
	 *
	 * @return string
	 */
	public function getCollectionClassName()
	{
		if (null === $this->_collectionClassName) {
			$this->setCollectionClassName(str_ireplace('_Mapper_', '_Collection_', $this->getClassName()));
		}
	
		return $this->_collectionClassName;
	}
	
	/**
	 * Set new collection class name
	 *
	 * @param string $name
	 */
	public function setCollectionClassName($name)
	{
		$this->_collectionClassName = $name;
		return $this;
	}

	/**
	 * Get entity class name
	 *
	 * @return string
	 */
	public function getEntityClassName()
	{
		if (null === $this->_entityClassName) {
			$this->setEntityClassName(str_ireplace('_Mapper_', '_Entity_', $this->getClassName()));
		}
	
		return $this->_entityClassName;
	}
	
	/**
	 * Set new entity class name
	 *
	 * @param string $name
	 */
	public function setEntityClassName($name)
	{
		$this->_entityClassName = $name;
		return $this;
	}
	
	/**
	 * Set source object
	 * 
	 * @param Core_Model_Source_Abstract $source Data source object
	 */
    public function setSource(Core_Model_Source_Interface $source)
    {
        $this->_source = $source;
        return $this;
    }
    
	/**
	 * Get source object
	 * 
	 * @return Core_Model_Source_Abstract Source object
	 */
    public function getSource()
    {
        if (null === $this->_source) {
        	$name = $this->getSourceClassName();
            $this->setSource(new $name());
        }
        
        return $this->_source;
    }
    
    /**
     * Create collection object
     * 
     * @param  array $data
     * @return Core_Model_Collection_Abstract
     */
    public function createCollection(array $data = array())
    {
    	$name = $this->getCollectionClassName();
    	$collection = new $name($data);
    	$collection->setMapper($this);
    	//$this->getEntityClassName();
		//$collection->setPrimaryName($this->getPrimaryName());
    	
    	return $collection;
    }
	
    /**
     * Create entity object
     * 
     * @param  array $data
     * @return Core_Model_Collection_Abstract
     */
	public function create(array $data = array())
	{
		$name = $this->getEntityClassName();
		$entity = new $name($data);
		$entity->setMapper($this);
		//$entity->setPrimaryName($this->getPrimaryName());
		
		return $entity;
	}
	
	/**
	 * Save all collection entities
	 * 
	 * @param  Core_Model_Collection_Abstract $collection
	 * @throws Exception
	 * @return Core_Model_Mapper_Abstract
	 */
	public function saveCollection(Core_Model_Collection_Abstract $collection)
	{
		try {
			$this->getSource()->beginTransanction();
			
			$collection->each(function($value, $key) {
				$value->save();
			});
			
			$this->getSource()->commit();
		} catch (Exception $e) {
			$this->getSource()->rollback();
			throw $e;
		}
		
		return $this;
	}
	
	/**
	 * Preprocess save entity
	 * 
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _beforeSave(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Save entity
	 * 
	 * @param Core_Model_Entity_Abstract $entity
	 */
	public function save(Core_Model_Entity_Abstract $entity)
	{
		$this->_beforeSave($entity);
		
		$pk = $this->getSource()->getPrimaryName();
		if ($entity->{$pk}) {
			$this->getSource()->save($entity->toArray());
		} else {
			$id = $this->getSource()->insert($entity->toArray());
			$entity->{$pk} = $id;
		}
		
		$this->_afterSave($entity);
		return $this;
	}
	
	/**
	 * Postprocess save entity
	 * 
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _afterSave(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Delete all collection entities
	 * 
	 * @param  Core_Model_Collection_Abstract $collection
	 * @throws Exception
	 * @return Core_Model_Mapper_Abstract
	 */
	public function deleteCollection(Core_Model_Collection_Abstract $collection)
	{
		try {
			$this->getSource()->beginTransanction();
				
			$collection->each(function($value, $key) {
				$value->delete();
			});
					
			$this->getSource()->commit();
		} catch (Exception $e) {
			$this->getSource()->rollback();
			throw $e;
		}
		
		return $this;
	}
	
	/**
	 * Preprocess delete entity
	 * 
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _beforeDelete(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Delete entity
	 * 
	 * @param  Core_Model_Entity_Abstract $entity
	 * @return Core_Model_Mapper_Abstract
	 */
	public function delete(Core_Model_Entity_Abstract $entity)
	{
		$this->getSource()->delete();
		return $this;
	}
	
	/**
	 * Postprocess delete entity
	 * 
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _afterDelete(Core_Model_Entity_Abstract $entity){}
	
	/*public function fetchTree($pColName, $cColName, $pColValue, array $where = null, $order = null, $depth = null)
	{
		$identifiersWhere = (array) $where;
		$identifiersWhere[$pColName] = $pColValue;
		$rootRowsetIdentifiers = $this->fetchPrimaryAll($identifiersWhere, $order);
		
		$treeStructureData = $this->fetchAllColumns($where, $order, null, null, array($pColName, $cColName));
		
		$identifiers = $this->_prepareTreeIdentifiers($pColName, $cColName, $pColValue, $treeStructureData, $depth);
		
		$rowset = $this->getSource()->fetchAll(array('$in' => $identifiers));
		
		return $this->_formatTree($pColName, $cColName, $pColValue, $rowset);
	}*/
	
	/**
	 * Preprocess entity
	 * 
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _beforeFind(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Find entity by pk value
	 * 
	 * @param  numeric $id
	 * @return null|Core_Model_Entity_Abstract
	 */
	public function find($id)
	{
		$entity->create();
		$this->_beforeFind($entity);
		
		$row = $this->getSource()->find($id);
		if ($row) {
			$entity->fill($row);
			$this->_afterFind($entity);
			return $entity;
		}
		
		return null;
	}
	
	/**
	 * Postprocess entity
	 * 
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _afterFind(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Preprocess collection
	 * 
	 * @param Core_Model_Collection_Abstract $collection
	 */
	protected function _beforeFindCollection(Core_Model_Collection_Abstract $collection){}
	
	/**
	 * Find collection by pk values
	 * 
	 * @param  array $idArray
	 * @return Core_Model_Collection_Abstract
	 */
	public function findCollection(array $idArray)
	{
		$collection = $this->createCollection();
		$this->_beforeFetchAll($collection);
		
		$rowset = $this->getSource()->findCollection($idArray);
		foreach ($rowset as $row) {
			$collection->push($this->create($row));
		}
		
		$this->_afterFetchAll($collection);
		return $collection;
	}
	
	/**
	 * Postprocess collection
	 * 
	 * @param Core_Model_Collection_Abstract $collection
	 */
	protected function _afterFindCollection(Core_Model_Collection_Abstract $collection){}
	
	/**
	 * Preprocess entity
	 * 
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _beforeFetchRow(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Fetch single row
	 * 
	 * @param array $where
	 * @param string|array $order
	 * @return null|Core_Model_Entity_Abstract
	 */
	public function fetchRow(array $where = null, $order = null)
	{
		$entity = $this->create();		
		$this->_beforeFetchRow($entity);
		
		$row = $this->getSource()->fetchRow($where, $order);
		if ($row) {
			$entity->fill($row);
			$this->_afterFetchRow($entity);
			return $entity;
		}
		
		return null;
	}
	
	/**
	 * Postprocess entity
	 * 
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _afterFetchRow(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Preprocessing collection
	 */
	protected function _beforeFetchAll(Core_Model_Collection_Abstract $collection){}
	
	/**
	 * Fetch collection
	 * 
	 * @param array $where
	 * @param string|array $order
	 * @param integer $count
	 * @param integer $offset
	 * @return Core_Model_Collection_Abstract
	 */
	public function fetchAll(array $where = null, $order = null, $count = null, $offset = null)
	{
		$collection = $this->createCollection();		
		$this->_beforeFetchAll($collection);
		
		$rowset = $this->getSource()->fetchAll($where, $order, $count, $offset);
		foreach ($rowset as $row) {
			$collection->push($this->create($row));
		}
		
		$this->_afterFetchAll($collection);		
		return $collection;
	}
	
	/**
	 * Post process collection
	 * 
	 * @param Core_Model_Collection_Abstract $collection
	 */
	protected function _afterFetchAll(Core_Model_Collection_Abstract $collection){}
	
	public function fetchTree(){}
	public function fetchBranch(){}
	
	/**
	 * Fetch total count of entities from data source
	 * 
	 * @param  array $where [OPTIONAL] Where clause
	 * @return integer      Total count of objects
	 */
	public function fetchCount(array $where = null)
	{
		return (int) $this->getSource()->fetchCount($where);
	}
	
	/**
	 * Convert page number to offset value for fetch operations
	 * 
	 * @param  null|integer $count Count value for conversion
	 * @param  null|integer $page  Page value for conversion
	 * @return null|integer        Converted value if $count and $page passed
	 */
	public function pageToOffset($count = null, $page = null)
	{
		return (null !== $count && null !== $page) ? $count * $page - $page : null;
	}
	
	/**
	 * Fetch only pk fields
	 * 
	 * @param array $where
	 * @param string|array $order
	 * @param integer $count
	 * @param integer $offset
	 * @return array
	 */
	public function fetchPrimaryAll(array $where = null, $order = null, $count = null, $offset = null)
	{
		return (array) $this->getSource()->fetchPrimaryAll($where, $order, $count, $offset);
	}
}
