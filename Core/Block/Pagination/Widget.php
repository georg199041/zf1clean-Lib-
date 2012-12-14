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
 * @package    Core_Block
 * @subpackage Core_Block_Pagination
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Widget.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Core_Block_View
 */
require_once "Core/Block/View.php";

/**
 * @see Zend_Paginator
 */
require_once 'Zend/Paginator.php';

/**
 * Render pagination template from Zend_Paginator like class
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Pagination
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Pagination_Widget extends Core_Block_View
{
	/**
	 * Pagination object
	 * 
	 * @var Zend_Paginator
	 */
	protected $_paginator;
	
	/**
	 * Pagination scrolling style option
	 * 
	 * @var string
	 */
	protected $_scrollingStyle = 'All';
	
	/**
	 * Total items count in result
	 * 
	 * @var int
	 */
	protected $_totalItemsCount = 10;
	
	/**
	 * Items list on page count
	 * 
	 * @var int
	 */
	protected $_itemCountPerPage = 20;
	
	/**
	 * Current page number
	 * Depended by request object
	 * 
	 * @var int
	 */
	protected $_currentPageNumber = 1;
	
	/**
	 * Request object key from wich can retrieve page number
	 * 
	 * @var string
	 */
	protected $_requestPageKey = 'page';
	
	/**
	 * Request object key from wich can retrieve rows on page count
	 * 
	 * @var string
	 */
	protected $_requestRowsKey = 'rows';
	
	/**
	 * Partial script path for use in render method
	 * Must be a valid path and readable
	 * 
	 * @var string
	 */
	protected $_partial;
	
	/**
	 * Instantiate local copy of paginator
	 * For we can use custo, pagination styles on same pages
	 * 
	 * @return Zend_Paginator
	 */
	public function createPaginator()
	{
		$paginator = Zend_Paginator::factory($this->getTotalItemsCount());
		return $paginator;		
	}
	
	/**
	 * Set paginator instance
	 * 
	 * @param  Zend_Paginator $paginator
	 * @return Core_Block_Pagination_Widget
	 */
	public function setPaginator(Zend_Paginator $paginator)
	{
		$this->_paginator = $paginator;
		return $this;
	}
	
	/**
	 * Gets paginator object
	 * Try to instantiate if it not exists
	 * 
	 * @return Zend_Paginator
	 */
	public function getPaginator()
	{
		if (null === $this->_paginator) {
			$this->setPaginator($this->createPaginator());
		}
		
		return $this->_paginator;
	}
	
	/**
	 * Set pagination scrolling style
	 * 
	 * @param  string $style
	 * @return Core_Block_Pagination_Widget
	 */
	public function setScrollingStyle($style)
	{
		$this->_scrollingStyle = $style;
		return $this;
	}

	/**
	 * Get pagination scrolling style
	 * 
	 * @return string
	 */
	public function getScrollingStyle()
	{
		return $this->_scrollingStyle;
	}
	
	/**
	 * Set total items count (from mapper as example)
	 * 
	 * @param  int $count
	 * @return Core_Block_Pagination_Widget
	 */
	public function setTotalItemsCount($count)
	{
		$this->_totalItemsCount = $count;
		return $this;
	}
	
	/**
	 * Get total items count
	 * 
	 * @return int
	 */
	public function getTotalItemsCount()
	{
		return $this->_totalItemsCount;
	}
	
	/**
	 * Set items count per page limit
	 * 
	 * @param  int $count
	 * @return Core_Block_Pagination_Widget
	 */
	public function setItemCountPerPage($count)
	{
		$this->_itemCountPerPage = $count;
		return $this;
	}

	/**
	 * Get items count per page (for build limited db queries as example)
	 * 
	 * @return int
	 */
	public function getItemCountPerPage()
	{
		return $this->_itemCountPerPage;
	}
	
	/**
	 * Set new page number
	 * 
	 * @param  int $page
	 * @return Core_Block_Pagination_Widget
	 */
	public function setCurrentPageNumber($page)
	{
		$this->_currentPageNumber = $page;
		return $this;
	}

	/**
	 * Get current page number
	 * 
	 * @return int
	 */
	public function getCurrentPageNumber()
	{
		return $this->_currentPageNumber;
	}
	
	/**
	 * Set 'page' request key
	 * 
	 * @param  string $key
	 * @return Core_Block_Pagination_Widget
	 */
	public function setRequestPageKey($key)
	{
		$this->_requestPageKey = $key;
		return $this;
	}

	/**
	 * Get 'page' request key
	 * 
	 * @return string
	 */
	public function getRequestPageKey()
	{
		return $this->_requestPageKey;
	}

	/**
	 * Set 'rows' request key
	 * 
	 * @param  string $key
	 * @return Core_Block_Pagination_Widget
	 */
	public function setRequestRowsKey($key)
	{
		$this->_requestRowsKey = $key;
		return $this;
	}

	/**
	 * Get 'rows' request key
	 * 
	 * @return string
	 */
	public function getRequestRowsKey()
	{
		return $this->_requestRowsKey;
	}
	
	/**
	 * Set partial script path
	 * 
	 * @param  string $partial
	 * @return Core_Block_Pagination_Widget
	 */
	public function setPartial($partial)
	{
		$this->_partial = $partial;
		return $this;
	}
	
	/**
	 * Get partial script path
	 * Set it to default if not exists
	 * THis file must be in fule system an be readable
	 * 
	 * @return string
	 */
	public function getPartial()
	{
		if (null === $this->_partial) {
			$this->setPartial('default/pagination.php3');
		}
		
		return $this->_partial;
	}
	
	/**
	 * Render pagination HTML
	 * 
	 * @param  string $name Not set, use BLOCK_DUMMY for preventing render errors
	 * @return string
	 */
	public function render($name)
	{
   		$response = '';
   		$response .= $this->renderBlockChilds(self::BLOCK_PLACEMENT_BEFORE);
		
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
			
			$class = preg_replace('/[^\p{L}\-]/u', '_', $this->getBlockName());
			$response .= '<div class="cbpw-block cbpw-block-' . $class . '">'
			          .  $this->paginationControl($paginator, $this->getScrollingStyle(), $this->getPartial(), array('widget' => $this))
			          . '</div>';
			//$this->setRendered(true);
		} catch (Exception $e) {
			$response .= $e->getMessage();
		}
    	
		$response .= $this->renderBlockChilds(self::BLOCK_PLACEMENT_AFTER);
    	return $response;
	}
}