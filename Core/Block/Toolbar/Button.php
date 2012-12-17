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
 * @subpackage Core_Block_Toolbar
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Button.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Core_Block_Toolbar_Link
 */
require_once "Core/Block/Toolbar/Link.php";

/**
 * Toolbar button implementation class
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Toolbar
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Toolbar_Button extends Core_Block_Toolbar_Link
{
	/**
	 * Button icon css class name
	 * 
	 * @var string
	 */
	protected $_iconClass;
	
	/**
	 * Set button icon css class name
	 * 
	 * @param  string $class
	 * @return Core_Block_Toolbar_Button
	 */
	public function setIconClass($class)
	{
		$this->_iconClass = (string) $class;
		return $this;
	}
	
	/**
	 * Get setted button icon css class name
	 * 
	 * @return mixed|string
	 */
	public function getIconClass()
	{
		if (null === $this->_iconClass) {
			return preg_replace('/[^\p{L}]/u', '', (string) $this->getName());
		}
		
		return $this->_iconClass;
	}
	
	/**
	 * Render html button tag
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_Toolbar_Link::render()
	 * @return string
	 */
	public function render()
	{
		$this->setAttribute('formaction', $this->getToolbar()->url($this->getUrlOptions(), $this->getUrlRoute(), true));
		$this->setAttribute('class', trim($this->getAttribute('class') . ' cbtw-button-icon-' . $this->getIconClass()));
		$this->setAttribute('name', $this->getName());
		$this->setAttribute('value', 'true');
		
		return '<button ' . $this->toHtmlAttributes() . '>' . $this->getTitle() . '</button>';
		/*
		return '<a ' . $this->toHtmlAttributes() . '>'
			 . '<span class="cbtw-button-icon ' . $this->getIconClass() . '"></span>'
			 . '<span class="cbtw-button-text">' . $this->getTitle() . '</span>'
			 . '</a>';*/
	}
}