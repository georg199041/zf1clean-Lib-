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
 * @subpackage Core_Block_Grid_Column
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Literal.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Core_Block_Grid_Column_Default
 */
require_once "Core/Block/Grid/Column/Default.php";

/**
 * Renders column like as static literal string ir value is empty
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Grid_Column
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Grid_Column_Literal extends Core_Block_Grid_Column_Default
{
	/**
	 * Default literal string
	 * 
	 * @var string
	 */
	protected $_defaultLiteral;
	
	/**
	 * Set default literal string
	 * 
	 * @param  string $value
	 * @throws Core_Block_Exception If literal is not a string
	 * @return Core_Block_Grid_Column_Literal
	 */
	public function setDefaultLiteral($value)
	{
		if (!is_string($value) && !is_numeric($value)) {
			require_once 'Core/Block/Exception.php';
			$e = Core_Block_Exception("Literal must be a string or number");
			$e->setView($this->getView());
			throw $e;
		}
		
		$this->_defaultLiteral = $value;
		return $this;
	}

	/**
	 * Get setted default literal string
	 * 
	 * @return string
	 */
	public function getDefaultLiteral()
	{
		return $this->_defaultLiteral;
	}
	
	/**
	 * Override value getter for use default literal
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_Grid_Column_Default::getValue()
	 * @return mixed|string
	 */
	public function getValue()
	{
		if (null === $this->_value) {
			return $this->getDefaultLiteral();
		}
		
		return $this->_value;
	}
}