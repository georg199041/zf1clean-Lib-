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
class Core_Block_Grid_Column_Editor extends Core_Block_Grid_Column_Default
{
	/**
	 * Editor bind to route fields array
	 * 
	 * @var array
	 */
	protected $_editorBindFields = array();
	
	/**
	 * Editor options
	 * 
	 * @var array
	 */
	protected $_editorOptions = array();
	
	/**
	 * Route name or formatted route constructor string
	 * 
	 * @var string
	 */
	protected $_editorRoute;
	
	/**
	 * Set bind fields array
	 * 
	 * @param  array $fields
	 * @return Core_Block_Grid_Column_Editor
	 */
	public function setEditorBindFields(array $fields)
	{
		$this->_editorBindFields = $fields;
		return $this;
	}
	
	/**
	 * Gets bind fields array
	 * 
	 * @return array
	 */
	public function getEditorBindFields()
	{
		return $this->_editorBindFields;
	}
	
	/**
	 * Sets editor options
	 * 
	 * @param  array $options
	 * @return Core_Block_Grid_Column_Editor
	 */
	public function setEditorOptions(array $options)
	{
		$this->_editorOptions = $options;
		return $this;
	}
	
	/**
	 * Get editor options
	 * 
	 * @return array
	 */
	public function getEditorOptions()
	{
		return $this->_editorOptions;
	}
	
	/**
	 * Set route name
	 * 
	 * @param  string $name
	 * @return Core_Block_Grid_Column_Editor
	 */
	public function setEditorRoute($name)
	{
		$this->_editorRoute = $name;
		return $this;
	}
	
	/**
	 * Get setted route name
	 * 
	 * @return string
	 */
	public function getEditorRoute()
	{
		return $this->_editorRoute;
	}
	
	/**
	 * Renders column
	 * 
	 * @return string HTML
	 */
	public function render()
	{
		return '<span ' . $this->getAttribs() . '>'
			 . $this->getGrid()->formText($this->getName(), $this->getValue())
		     . $this->getGrid()->formButton('save', 'Save')
		     . '</span>';
	}
}