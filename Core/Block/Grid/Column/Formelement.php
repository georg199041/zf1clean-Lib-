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
 * @version    $Id: Editor.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Core_Block_Grid_Column_Default
 */
require_once "Core/Block/Grid/Column/Default.php";

/**
 * Renders column like as inline grid editor
 * Warning this column now is uncomplete
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Grid_Column
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Grid_Column_Formelement extends Core_Block_Grid_Column_Default
{
	/**
	 * Formaction attribute url building options
	 * 
	 * @var array
	 */
	protected $_formactionOptions = array();
	
	/**
	 * Formaction attribute url building route name
	 * 
	 * @var string
	 */
	protected $_formactionRoute;
	
	/**
	 * Formaction attribute url building added fields names
	 * 
	 * @var array
	 */
	protected $_formactionBind = array();
	
	/**
	 * Set formaction route options
	 * 
	 * @param  array $value
	 * @return Core_Block_Grid_Column_Formelement
	 */
	public function setFormactionOptions($value)
	{
		if (is_array($value)) {
			$this->_formactionOptions = $value;
		} else if (is_string($value)) {
			$this->_formactionOptions = Core::urlToOptions($value);
		}
		
		return $this;
	}
	
	/**
	 * Get formaction route options
	 * 
	 * @return string
	 */
	public function getFormactionOptions()
	{
		return $this->_formactionOptions;
	}
	
	/**
	 * Set formaction route name
	 * 
	 * @param  string $value
	 * @return Core_Block_Grid_Column_Formelement
	 */
	public function setFormactionRoute($value)
	{
		$this->_formactionRoute = (string) $value;
		return $this;
	}
	
	/**
	 * Get formaction route name
	 * 
	 * @return string
	 */
	public function getFormactionRoute()
	{
		return $this->_formactionRoute;
	}
	
	/**
	 * Set formaction added fields names
	 * 
	 * @param  array $value
	 * @return Core_Block_Grid_Column_Formelement
	 */
	public function setFormactionBind(array $value)
	{
		$this->_formactionBind = $value;
		return $this;
	}
	
	/**
	 * Get formaction added fields names
	 * 
	 * @return array
	 */
	public function getFormactionBind()
	{
		return $this->_formactionBind;
	}
}