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
 * @version    $Id: Partial.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Core_Block_Grid_Column_Default
 */
require_once "Core/Block/Grid/Column/Default.php";

/**
 * Maximum customized type of column,
 * used templating system for rendering
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Grid_Column
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Grid_Column_Partial extends Core_Block_Grid_Column_Default
{
	/**
	 * Partial script name
	 * 
	 * @var string
	 */
	protected $_partialName;
	
	/**
	 * Partial script contained module
	 * 
	 * @var string
	 */
	protected $_partialModule;
	
	/**
	 * Cusom partial assigned vars
	 * 
	 * @var array
	 */
	protected $_partialVars = array();
	
	/**
	 * Set partial script name
	 * 
	 * @param  string $value
	 * @return Core_Block_Grid_Column_Partial
	 */
	public function setPartialName($value)
	{
		$this->_partialName = (string) $value;
		return $this;
	}
	
	/**
	 * Get partial script name
	 * 
	 * @return string
	 */
	public function getPartialName()
	{
		return $this->_partialName;
	}
	
	/**
	 * Set used partial script module
	 * 
	 * @param  string $value
	 * @return Core_Block_Grid_Column_Partial
	 */
	public function setPartialModule($value = null)
	{
		$this->_partialModule = $value;
		return $this;
	}
	
	/**
	 * Get partial module name
	 * 
	 * @return string
	 */
	public function getPartialModule()
	{
		return $this->_partialModule;
	}
	
	/**
	 * Set added partial vars
	 * 
	 * @param  array $value
	 * @return Core_Block_Grid_Column_Partial
	 */
	public function setPartialVars(array $value)
	{
		$this->_partialVars = $value;
		return $this;
	}
	
	/**
	 * Get partial vars
	 * 
	 * @return array
	 */
	public function getPartialVars()
	{
		return $this->_partialVars;
	}
	
	/**
	 * Render column html
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_Grid_Column_Default::render()
	 * @return string
	 */
	public function render()
	{
		$vars = $this->getPartialVars();
		$vars = array_merge_recursive($vars, array('column' => $this));
		
		return '<span ' . $this->_renderAttribs() . '>' . $this->getGrid()->partial(
			$this->getPartialName(),
			$this->getPartialModule(),
			$vars
		) . '</span>';
	}
}