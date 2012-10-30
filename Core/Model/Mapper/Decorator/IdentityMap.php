<?php

require_once 'Sunny/Model/Mapper/Decorator/Abstract.php';

class Sunny_Model_Mapper_Decorator_IdentityMap extends Sunny_Model_Mapper_Decorator_Abstract
{
	/**
	 * Identity map container
	 * 
	 * @var array
	 */
	public static $_identityMap = array();
	
	/**
	 * Get already loaded identifiers data and check unloaded
	 * 
	 * @param  array $identifiers Identifiers like array(1,2,3)
	 * @param  array $notExists   [OPTIONAL] array for unloaded identifiers
	 * @return array              Exists identifiers data
	 */
	public function getExistsIdentifiers(array $identifiers, &$notExists = null)
	{
		$exists    = array();
		$notExists = array();
		
		foreach ($identifiers as $id) {
			if (array_key_exists($this->getClassName() . '_' . $id, self::$_identityMap)) {
				$exists[$id] = self::$_identityMap[$this->getClassName() . '_' . $id];
			} else {
				$notExists[] = $id;
			}
		}
		
		return $exists;
	}
	
	/**
	 * Add loaded data to identifiers container
	 * 
	 * @param  Sunny_Model_Collection_Abstract $collection Fetched collection
	 * @return Sunny_Model_Mapper_Abstract                 This Sunny_Model_Mapper object
	 */
	public function addExistsIdentifiers(Sunny_Model_Collection_Abstract $collection)
	{
		foreach ($collection as $entity) {
			self::$_identityMap[$this->getClassName() . '_' . $entity->getPrimary()] = $entity;
		}

		return $this;
	}
	
	/**
	 * Modify where clause for optimal build identifiers query
	 * 
	 * @param  array $where     [OPTIONAL] Where clause
	 * @param  array $exists    [OPTIONAL] Exists identifiers
	 * @param  array $notExists [OPTIONAL] Not exists identifiers
	 * @return array|null       Modified Where clause
	 */
	public function modifyWhere(array $where = null, array $exists = null, array $notExists = null)
	{
		if (empty($exists) && empty($notExists)) {
			return $where;
		}
		
		$nin = array();
		if (is_array($exists) && !empty($exists)) {
			$nin = $exists;
		}
		
		$in = array();
		if (is_array($notExists) && !empty($notExists)) {
			$in = $notExists;
		}
		
		if (!empty($nin) || !empty($in)) {
			$where = (array) $where;
		}
		
		// Select optimal query modification
		if (count($nin) < count($in)) {
			$where[$this->getPrimaryName()] = array('$not' => array('$in' => $nin));
		} else {
			$where[$this->getPrimaryName()] = array('$in' => $nin);
		}
		
		return $where;
	}
	
	/**
	 * Fetch all identity map realization
	 * @see Sunny_Model_Mapper_Abstract::fetchAll()
	 * 
	 * @param  array        $where             [OPTIONAL] Where clause
	 * @param  array|string $order             [OPTIONAL] Order clause
	 * @param  integer      $count             [OPTIONAL] Count clause
	 * @param  integer      $offset            [OPTIONAL] Offset clause
	 * @return Sunny_Model_Collection_Abstract Collection result
	 */
	public function fetchAll(array $where = null, $order = null, $count = null, $offset = null)
	{
		// Fetch needed result set identifiers
		$identifiers = $this->fetchPrimaryAll($where, $order, $count, $offset);
		
		// Get & check already loaded data from container
		$exists = $this->getExistsIdentifiers($identifiers, $notExists);
		
		// Fetch unloaded data
		$rowset = $this->fetchAll($this->modifyWhere($where, $exists, $notExists));
		
		// Store new data to container
		$this->addExistsIdentifiers($rowset);
		
		// Formatting result
		$collection = $this->createCollection();
		foreach ($identifiers as $id) {
			foreach ($exists as $item) {
				if ($id == $item->getPrimary()) {
					$collection->append($item);
				}
			}
			
			foreach ($rowset as $item) {
				if ($id == $item->getPrimary()) {
					$collection->append($item);
				}
			}
		}
		
		return $collection;
	}
}
