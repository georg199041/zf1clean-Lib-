<?php

require_once "Core/Block/View.php";

class Core_Block_Pagination_Widget extends Core_Block_View
{
	protected $_paginator;
	
	protected $_scrollingStyle = 'All';
	
	protected $_totalItemsCount = 10;
	
	protected $_itemCountPerPage = 1;
	
	protected $_currentPageNumber = 1;
	
	protected $_requestPageKey = 'page';
	
	protected $_requestRowsKey = 'rows';
	
	protected $_partial;
	
	public function createPaginator()
	{
		$paginator = Zend_Paginator::factory($this->getTotalItemsCount());
		return $paginator;		
	}
	
	public function setPaginator(Zend_Paginator $paginator)
	{
		$this->_paginator = $paginator;
		return $this;
	}
	
	public function getPaginator()
	{
		if (null === $this->_paginator) {
			$this->setPaginator($this->createPaginator());
		}
		
		return $this->_paginator;
	}
	
	public function setScrollingStyle($style)
	{
		$this->_scrollingStyle = $style;
		return $this;
	}

	public function getScrollingStyle()
	{
		return $this->_scrollingStyle;
	}
	
	public function setTotalItemsCount($count)
	{
		$this->_totalItemsCount = $count;
		return $this;
	}
	
	public function getTotalItemsCount()
	{
		return $this->_totalItemsCount;
	}
	
	public function setItemCountPerPage($count)
	{
		$this->_itemCountPerPage = $count;
		return $this;
	}

	public function getItemCountPerPage()
	{
		return $this->_itemCountPerPage;
	}
	
	public function setCurrentPageNumber($page)
	{
		$this->_currentPageNumber = $page;
		return $this;
	}

	public function getCurrentPageNumber()
	{
		return $this->_currentPageNumber;
	}
	
	public function setRequestPageKey($key)
	{
		$this->_requestPageKey = $key;
		return $this;
	}

	public function getRequestPageKey()
	{
		return $this->_requestPageKey;
	}

	public function setRequestRowsKey($key)
	{
		$this->_requestRowsKey = $key;
		return $this;
	}

	public function getRequestRowsKey()
	{
		return $this->_requestRowsKey;
	}
	
	public function setPartial($partial)
	{
		$this->_partial = $partial;
		return $this;
	}
	
	public function getPartial()
	{
		if (null === $this->_partial) {
			$this->setPartial('default/pagination.php3');
		}
		
		return $this->_partial;
	}
	
	public function render($name = null)
	{
		try {
			$request   = Zend_Controller_Front::getInstance()->getRequest();
			$paginator = $this->getPaginator();
			
			if (!($rows = (int) $request->getParam($this->getRequestRowsKey()))) {
				$rows = $this->getItemCountPerPage();
			}
			$paginator->setItemCountPerPage($rows);
			
			if (!($page = (int) $request->getParam($this->getRequestPageKey()))) {
				$page = $this->getCurrentPageNumber();
			}
			$paginator->setCurrentPageNumber($page);
			
			Zend_Paginator::setDefaultScrollingStyle($this->getScrollingStyle());
			Zend_View_Helper_PaginationControl::setDefaultViewPartial($this->getPartial());
			if (!in_array(Zend_Layout::getMvcInstance()->getLayoutPath(), $this->getScriptPaths())) {
				$this->addScriptPath(Zend_Layout::getMvcInstance()->getLayoutPath());
			}
			
			//$return = $paginator->render();// BAD WAY
			$return = $this->paginationControl($paginator, $this->getScrollingStyle(), $this->getPartial(), array('widget' => $this));
			return $return;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
}