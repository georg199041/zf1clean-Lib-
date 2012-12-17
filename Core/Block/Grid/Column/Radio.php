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
class Core_Block_Grid_Column_Radio extends Core_Block_Grid_Column_Formelement
{
	/**
	 * Radio options list
	 * 
	 * @var array
	 */
	protected $_radioOptions = array();

	/**
	 * Set options
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_Grid_Column_Default::setOptions()
	 * @param array $options
	 * @return Core_Block_Grid_Column_Radio
	 */
	public function setOptions(array $options)
	{
		if (isset($options['radioOptions'])) {
			$this->setRadioOptions($options['radioOptions']);
		}
	
		unset($options['radioOptions']);
		return parent::setOptions($options);
	}
	
	/**
	 * Set new radio options list
	 * 
	 * @param  array $value
	 * @return Core_Block_Grid_Column_Radio
	 */
	public function setRadioOptions(array $value)
	{
		$this->_radioOptions = $value;
		return $this;
	}
	
	/**
	 * Get radio options list
	 * 
	 * @return array
	 */
	public function getRadioOptions()
	{
		return $this->_radioOptions;
	}
	
	/**
	 * Renders radio tag html
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_Grid_Column_Default::render()
	 * @return string
	 */
	public function render()
	{
		$name = $this->getName() . '[' . $this->getRow($this->getGrid()->getIdColumnName()) . ']';
		
		$formactionOptions = $this->getFormactionOptions();
		foreach ($this->getFormactionBind() as $alias => $field) {
			$formactionOptions[(!is_numeric($alias) ? $alias : $field)] = $this->getRow($field);
		}
		
		$formaction = $this->getGrid()->url($formactionOptions, $this->getFormactionRoute());
		
		return '<span class="cbgw-column_formRadio">' . $this->getGrid()->formRadio(
			$name,
			$this->getValue(),
			array('formaction' => $formaction),
			$this->getRadioOptions()
		) . '</span>';
	}
}