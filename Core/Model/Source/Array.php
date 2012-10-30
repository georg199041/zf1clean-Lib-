<?php

require_once "Core/Model/DataSource/Interface.php";

class Core_Model_DataSource_Array implements Core_Model_DataSource_Interface
{
	protected $_data = array();
	
	protected function _where($select, $where)
	{
		$where = array(
			'field1' => 'val1', // condition
			'field2' => array('$in' => array(1,2,3)),
			'$or' => array(
				'sfield1' => 'sval1', // sub condition
				'sfield2' => 'sval2' // sub condition
			)
		);
		
		if ('field1' == 'val1'
			&& in_array('field2', array(1,2,3))
			&& ('sfield1' == 'sval1' || 'sfield2' == 'sval2')) {
		}
		
		foreach ($data as $row) {
			if ($this->checkAnd($row, $where)) {
				
			}
		}
		
		return $select;
	}
	
	/***/
	public function checkAnd($row, $where)
	{
		foreach ($where as $name => $cond) {
			if ($name == '$or' && is_array($cond)) {
				if (!$this->checkOr($row, $cond)) {
					return false;
				}
			} else if ($name != '$or' && is_array($cond)) {
				if (!$this->checkKeyword($row, $name, $cond)) {
					return false;
				}
			} else if ($name != '$or' && !is_array($cond)) {
				if (!$this->checkEqual($row, $name, $cond)) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/***/
	public function checkOr($row, $where)
	{
		foreach ($where as $name => $cond) {
			if (is_array($cond)) {
				if ($this->checkKeyword($row, $name, $cond)) {
					return true;
				}
			} else {
				if ($this->checkEqual($row, $name, $cond)) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	public function checkKeyword($row, $name, $cond)
	{
		if (array_key_exists('$in', $cond)) {
			return $this->checkIn($row, $name, $cond['$in']);
		} else if (array_key_exists('$gt', $cond)) {
			return $this->checkGreaterThan($row, $name, $cond['$gt']);
		} else if (array_key_exists('$gte', $cond)) {
			return $this->checkGreaterOrEqual($row, $name, $cond['$gte']);
		} else if (array_key_exists('$lt', $cond)) {
			return $this->checkLowerThan($row, $name, $cond['$lt']);
		} else if (array_key_exists('$lte', $cond)) {
			return $this->checkLowerOrEqual($row, $name, $cond['$lte']);
		} else if (array_key_exists('$like', $cond)) {
			return $this->checkLike($row, $name, $cond['$like']);
		} else if (array_key_exists('$or', $cond)) {
			return $this->checkOr($row, $cond['$or']);
		}
		
		return true;
	}
	
	// below check
	public function checkEqual($row, $name, $value)
	{
		if (is_array($value)) {
			return $this->check();
		}
		
		return $row[$name] == $value ? true : false;
	}
	
	
	
	
	
	
	public function checkGreaterThan($row, $name, $value)
	{
		return $row[$name] > $value ? true : false;
	}
	
	public function checkGreaterOrEqual($row, $name, $value)
	{
		return $row[$name] >= $value ? true : false;
	}
	
	public function checkLowerThan($row, $name, $value)
	{
		return $row[$name] < $value ? true : false;
	}
	
	public function checkLowerOrEqual($row, $name, $value)
	{
		return $row[$name] <= $value ? true : false;
	}
	
	public function checkLike($row, $name, $value)
	{
		$start = '';
		if (substr($value, 0, 1) == '%') {
			$value = substr($value, 1);
			$start = '^';
		}
		
		$end = '';
		if (substr($value, -1) == '%') {
			$value = substr($value, 0, strlen($value) - 1);
			$end = '$';
		}
		
		$regex = '/' . $start . '(' . $value . ')' . $end . '/';
		return preg_match($regex, $row[$name]) ? true : false;
	}
	
	public function checkNot($row, $value)
	{
		return !$this->check($row, $value);
	}
	
	public function checkIn($row, $name, $value)
	{
		$value = array_values($value);
		return in_array($row[$name], $value);
	}
	
	public function parseSourceQuery($where)
	{
		foreach ($where as $key => $value) {
			if ($key == '$or') {
				$this->_parseOr($value);
			} else {
				$this->_parseAnd($key, $value);
			}
		}
	}
}