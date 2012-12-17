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
 * @subpackage Core_Model_Mapper
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * Base mapper class
 *
 * @category   Core
 * @package    Core_Model
 * @subpackage Core_Model_Mapper
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
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
	 * Preprocess Save $entity
	 *
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _beforeSaveRow(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Postprocess Save $entity
	 *
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _afterSaveRow(Core_Model_Entity_Abstract $entity){}

	/**
	 * Preprocess Save $collection
	 *
	 * @param Core_Model_Collection_Abstract $collection
	 */
	protected function _beforeSaveRows(Core_Model_Collection_Abstract $collection){}
	
	/**
	 * Postprocess Save $collection
	 *
	 * @param Core_Model_Collection_Abstract $collection
	 */
	protected function _afterSaveRows(Core_Model_Collection_Abstract $collection){}
	
	/**
	 * Preprocess delete $entity
	 *
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _beforeDeleteRow(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Postprocess delete $entity
	 *
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _afterDeleteRow(Core_Model_Entity_Abstract $entity){}

	/**
	 * Preprocess delete $collection
	 *
	 * @param Core_Model_Collection_Abstract $collection
	 */
	protected function _beforeDeleteRows(Core_Model_Collection_Abstract $collection){}
	
	/**
	 * Postprocess delete $collection
	 *
	 * @param Core_Model_Collection_Abstract $collection
	 */
	protected function _afterDeleteRows(Core_Model_Collection_Abstract $collection){}
	
	/**
	 * Preprocess fetch $entity
	 *
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _beforeFetchRow(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Postprocess fetch $entity
	 *
	 * @param Core_Model_Entity_Abstract $entity
	 */
	protected function _afterFetchRow(Core_Model_Entity_Abstract $entity){}
	
	/**
	 * Preprocess fetch collection
	 *
	 * @param Core_Model_Collection_Abstract $collection
	 */
	protected function _beforeFetchRows(Core_Model_Collection_Abstract $collection){}
	
	/**
	 * Postprocess fetch collection
	 *
	 * @param Core_Model_Collection_Abstract $collection
	 */
	protected function _afterFetchRows(Core_Model_Collection_Abstract $collection){}
	
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
	 * @return Core_Model_Mapper_Abstract
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
	 * @param  string $name
	 * @return Core_Model_Mapper_Abstract
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
	 * @param  string $name
	 * @return Core_Model_Mapper_Abstract
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
	 * @param  string $name
	 * @return Core_Model_Mapper_Abstract
	 */
	public function setEntityClassName($name)
	{
		$this->_entityClassName = $name;
		return $this;
	}
	
	/**
	 * Set source object
	 * 
	 * @param  Core_Model_Source_Interface $source Data source object
	 * @return Core_Model_Mapper_Abstract
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
		return $entity;
	}
	
	/**
	 * Save all collection entities
	 * 
	 * @param  Core_Model_Collection_Abstract $collection
	 * @throws Exception If Collection save fails
	 * @return Core_Model_Mapper_Abstract
	 */
	public function saveCollection(Core_Model_Collection_Abstract $collection)
	{
		try {
			$this->getSource()->beginTransanction();
			$this->_beforeSaveRows($collection);
			
			$collection->each(function($value, $key) {
				$value->save();
			});
			
			$this->_afterSaveRows($collection);
			$this->getSource()->commit();
		} catch (Exception $e) {
			$this->getSource()->rollback();
			throw $e;
		}
		
		return $this;
	}
	
	/**
	 * Save entity
	 * 
	 * @param  Core_Model_Entity_Abstract $entity
	 * @return Core_Model_Mapper_Abstract
	 */
	public function save(Core_Model_Entity_Abstract $entity)
	{
		$pk = $this->getSource()->getPrimaryName();
		$this->_beforeSaveRow($entity);
		
		if ($entity->{$pk}) {
			$this->getSource()->update($entity->toArray(), array($pk . ' = ?' => $entity->{$pk}));
		} else {
			$entity->{$pk} = $this->getSource()->insert($entity->toArray());
		}
		
		$this->_afterSaveRow($entity);		
		return $this;
	}
	
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
			$this->_beforeDeleteRows($collection);
				
			$collection->each(function($value, $key) {
				$value->delete();
			});
			
			$this->_afterDeleteRows($collection);
			$this->getSource()->commit();
		} catch (Exception $e) {
			$this->getSource()->rollback();
			throw $e;
		}
		
		return $this;
	}
	
	/**
	 * Delete entity
	 * 
	 * @param  Core_Model_Entity_Abstract $entity
	 * @return Core_Model_Mapper_Abstract
	 */
	public function delete(Core_Model_Entity_Abstract $entity)
	{
		$this->_beforeDeleteRow($entity);
		
		$pk = $this->getSource()->getPrimaryName();
		$this->getSource()->delete(array($pk . ' = ?' => $entity->{$pk}));
		
		$this->_afterDeleteRow($entity);
		return $this;
	}
	
	/**
	 * Find entity by pk value
	 * 
	 * @param  numeric $id
	 * @return Core_Model_Entity_Abstract
	 */
	public function find($id)
	{
		$entity = $this->create();
		$this->_beforeFetchRow($entity);
		
		$row = $this->getSource()->find((int) $id);
		if ($row) {
			$entity->fill($row);
		}
		
		$this->_afterFetchRow($entity);
		return $entity;
	}
	
	/**
	 * Find collection by pk values
	 * 
	 * @param  array $idArray
	 * @return Core_Model_Collection_Abstract
	 */
	public function findCollection(array $idArray)
	{
		$collection = $this->createCollection();
		$this->_beforeFetchRows($collection);
		
		$rowset = $this->getSource()->findCollection($idArray);
		foreach ($rowset as $row) {
			$collection->push($this->create($row));
		}
		
		$this->_afterFetchRows($collection);
		return $collection;
	}
	
	/**
	 * Fetch single row
	 * 
	 * @param  array $where
	 * @param  string|array $order
	 * @return Core_Model_Entity_Abstract
	 */
	public function fetchRow(array $where = null, $order = null)
	{
		$entity = $this->create();		
		$this->_beforeFetchRow($entity);
		
		$row = $this->getSource()->fetchRow($where, $order);
		if ($row) {
			$entity->fill($row);
		}
		
		$this->_afterFetchRow($entity);
		return $entity;
	}
	
	/**
	 * Fetch collection
	 * 
	 * @param  array $where
	 * @param  string|array $order
	 * @param  integer $count
	 * @param  integer $offset
	 * @return Core_Model_Collection_Abstract
	 */
	public function fetchAll(array $where = null, $order = null, $count = null, $offset = null)
	{
		$collection = $this->createCollection();		
		$this->_beforeFetchRows($collection);
		
		$rowset = $this->getSource()->fetchAll($where, $order, $count, $offset);
		foreach ($rowset as $row) {
			$collection->push($this->create($row));
		}
		
		$this->_afterFetchRows($collection);		
		return $collection;
	}
	
	/**
	 * Post format tree structure
	 * 
	 * @param  Core_Model_Collection_Abstract $collection
	 * @param  array $options
	 * @return Core_Model_Collection_Abstract
	 */
	public function formatTree($collection, $options)
	{
		$return = $this->createCollection();
		foreach ($collection as $row) {
			if ($row->{$options['pColName']} == $options['pColValue']) {
				$cOptions = array_merge($options, array('pColValue' => $row->{$options['cColName']}));
				$row->setChilds($this->formatTree(clone $collection, $cOptions));
				$return->push($row);
			}
		}
		
		return $return;
	}
	
	/**
	 * Fetch rows for tree like data structure
	 * 
	 * @param  array $where
	 * @param  array|string $order
	 * @param  integer|null $depth
	 * @param  array $options
	 * @return Core_Model_Collection_Abstract
	 */
	public function fetchTree(array $where = null, $order = null, $depth = null, array $options = array())
	{
		$required = array(
			'pColName'  => $this->getSource()->getName() . '_' . $this->getSource()->getPrimaryName(),
			'cColName'  => $this->getSource()->getPrimaryName(),
			'pColValue' => NULL
		);
		
		$options = array_intersect_key($options, $required);
		$options = array_merge($required, $options);
		
		$collection = $this->createCollection();
		$this->_beforeFetchRows($collection);		
		
		$rowset = $this->getSource()->fetchTree($where, $order, $depth, $options);
		foreach ($rowset as $row) {
			$collection->push($this->create($row));
		}
		
		$this->_afterFetchRows($collection);		
		$collection = $this->formatTree($collection, $options);
		return $collection;
	}
	
	/**
	 * Preformat branch data
	 * 
	 * @param  Core_Model_Collection_Abstract $collection
	 * @param  array $options
	 * @return Core_Model_Entity_Abstract
	 */
	public function formatBranch($collection, $options)
	{
		do {
			foreach ($collection as $entity) {
				if ($entity->{$options['pColValue']}) {
					$parent = clone $entity;//TODO
				}
			}
		} while (null !== $options['pColValue']);
		
		return $parent;
	}
	
	/**
	 * Fetch parents branch collection
	 * 
	 * @param  array $where
	 * @param  string|array $order
	 * @param  array $options
	 * @return Core_Model_Entity_Abstract
	 */
	public function fetchBranch(array $where = null, $order = null, array $options = array())
	{
		$required = array(
			'pColName'  => $this->getSource()->getName() . '_' . $this->getSource()->getPrimaryName(),
			'cColName'  => $this->getSource()->getPrimaryName(),
			'pColValue' => NULL
		);
		
		$options = array_intersect_key($options, $required);
		$options = array_merge($required, $options);
		
		$collection = $this->createCollection();
		$this->_beforeFetchRows($collection);		
		
		$rowset = $this->getSource()->fetchBranch($where, $order, $options);
		foreach ($rowset as $row) {
			$collection->push($this->create($row));
		}
		
		$this->_afterFetchRows($collection);
		$branch = $this->formatBranch($collection, $options);
		return $branch;
	}
	
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
		return (null !== $count && null !== $page) ? $count * $page - $count : null;
	}
	
	/**
	 * Fetch only pk fields
	 * 
	 * @param  array $where
	 * @param  string|array $order
	 * @param  integer $count
	 * @param  integer $offset
	 * @return array
	 */
	public function fetchPrimaryAll(array $where = null, $order = null, $count = null, $offset = null)
	{
		return (array) $this->getSource()->fetchPrimaryAll($where, $order, $count, $offset);
	}
}
