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
 * @version    $Id: Abstract.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Core_Block_Grid_Column_Formelement
 */
require_once "Core/Block/Grid/Column/Formelement.php";

/**
 * Renders grid column as checkbox form element
 * You can configure checked and unchecked values
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Grid_Column
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Grid_Column_Checkbox extends Core_Block_Grid_Column_Formelement
{
	/**
	 * Chackbox checked value (placed in tag directly)
	 * 
	 * @var string
	 */
	protected $_checkedValue = '1';
	
	/**
	 * Checkbox unchecked value (placed in prepended hidden field like as in Zend_Form)
	 * 
	 * @var string
	 */
	protected $_uncheckedValue = '0';
	
	/**
	 * Configure column
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_Grid_Column_Default::setOptions()
	 * @param array $options Appay of column options with type specific options
	 * @return Core_Block_Grid_Column_Checkbox
	 */
	public function setOptions(array $options)
	{
		if (isset($options['checkedValue'])) {
			$this->setCheckedValue($options['checkedValue']);
		}
		
		if (isset($options['uncheckedValue'])) {
			$this->setUncheckedValue($options['uncheckedValue']);
		}
		
		unset($options['checkedValue'], $options['uncheckedValue']);
		return parent::setOptions($options);
	}
	
	/**
	 * Set new checked value
	 * 
	 * @param  string $value
	 * @return Core_Block_Grid_Column_Checkbox
	 */
	public function setCheckedValue($value)
	{
		$this->_checkedValue = $value;
		return $this;
	}
	
	/**
	 * Get setted checked value
	 * 
	 * @return string
	 */
	public function getCheckedValue()
	{
		return $this->_checkedValue;
	}
	
	/**
	 * Set new unchecked value
	 * 
	 * @param  string $value
	 * @return Core_Block_Grid_Column_Checkbox
	 */
	public function setUncheckedValue($value)
	{
		$this->_uncheckedValue = $value;
		return $this;
	}
	
	/**
	 * Get setted unchecked value
	 * 
	 * @return string
	 */
	public function getUncheckedValue()
	{
		return $this->_uncheckedValue;
	}
	
	/**
	 * Main render method
	 * 
	 * @return XHTML
	 */
	public function render()
	{
		$name = $this->getName() . '[' . $this->getRow($this->getGrid()->getIdColumnName()) . ']';
		
		$formactionOptions = $this->getFormactionOptions();
		foreach ($this->getFormactionBind() as $alias => $field) {
			$formactionOptions[(!is_numeric($alias) ? $alias : $field)] = $this->getRow($field);
		}
		
		$formaction = $this->getGrid()->url($formactionOptions, $this->getFormactionRoute());
		
		return '<span class="cbgw-column_formCheckbox">' . $this->getGrid()->formCheckbox(
			$name,
			$this->getValue(),
			array('formaction' => $formaction),
			array(
				'checkedValue'   => $this->getCheckedValue(),
				'uncheckedValue' => $this->getUncheckedValue(),
			)
		) . '</span>';
	}
}