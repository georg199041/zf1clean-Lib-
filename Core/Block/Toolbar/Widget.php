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
 * @version    $Id: Widget.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Core_Block_View
 */
require_once "Core/Block/View.php";

/**
 * @see Core_Block_Toolbar_Button
 */
require_once "Core/Block/Toolbar/Button.php";

/**
 * @see Core_Block_Toolbar_Link
 */
require_once "Core/Block/Toolbar/Link.php";

/**
 * Base toolbar class
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Toolbar
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Toolbar_Widget extends Core_Block_View
{
	/**
	 * Toolbar buttons
	 * 
	 * @var array
	 */
	protected $_buttons = array();
	
	/**
	 * Toolbar links
	 * 
	 * @var array
	 */
	protected $_links = array();
	
	/**
	 * Toolbar title string
	 * 
	 * @var string
	 */
	protected $_title;
	
	/**
	 * Toolbar object name
	 * 
	 * @var string
	 */
	protected $_name;
	
	/**
	 * Set buttons objects
	 * 
	 * @param  array $buttons
	 * @return Core_Block_Toolbar_Widget
	 */
	public function setButtons(array $buttons)
	{
		$this->_buttons = array();
		$this->addButtons($buttons);
		return $this;
	}
	
	/**
	 * Get buttons objects
	 * 
	 * @return array
	 */
	public function getButtons()
	{
		return $this->_buttons;
	}
	
	/**
	 * Add buttons
	 * 
	 * @param  array $buttons
	 * @throws Core_Block_Exception If added buttons have invalid format
	 * @return Core_Block_Toolbar_Widget
	 */
	public function addButtons(array $buttons)
	{
		foreach ($buttons as $key => $value) {
			if ($value instanceof Core_Block_Toolbar_Button) {
				$this->addButton($value);
			} else if (is_array($value)) {
				if (!is_numeric($key) && !array_key_exists('name', $value)) {
					$value['name'] = $key;
				}
				
				$this->addButton($value);
			} else {
				throw new Core_Block_Exception("Toolbar button must be an array or Core_Block_Toolbar_Button instance");
			}
		}
				
		return $this;
	}
	
	/**
	 * Add single button
	 * 
	 * @param  array|Core_Block_Toolbar_Button $button
	 * @throws Core_Block_Exception If button have invalid format
	 * @return Core_Block_Toolbar_Widget
	 */
	public function addButton($button)
	{
		if ($button instanceof Core_Block_Toolbar_Button) {
			$button->setToolbar($this);
			$this->_buttons[$button->getName()] = $button;
		} else if (is_array($button)) {
			$class = new Core_Block_Toolbar_Button($button);
			$class->setToolbar($this);
			$this->_buttons[$class->getName()] = $class;
		} else {
			throw new Core_Block_Exception("Invalid Toolbar button definition");
		}
		
		return $this;
	}
	
	/**
	 * Get button object by name
	 * 
	 * @param string $name
	 */
	public function getButton($name)
	{
		return $this->_button[$name];
	}
	
	/**
	 * Delete button
	 * 
	 * @param  string $name
	 * @return Core_Block_Toolbar_Widget
	 */
	public function delButton($name)
	{
		$this->_button[$name] = null; // Prevent reference deleting
		unset($this->_button[$name]);
		return $this;
	}
	
	/**
	 * Set links objects
	 * 
	 * @param  array $options
	 * @return Core_Block_Toolbar_Widget
	 */
	public function setLinks(array $options)
	{
		$this->_links = array();
		$this->addLinks($options);
		return $this;
	}
	
	/**
	 * Get links objects
	 * 
	 * @return array
	 */
	public function getLinks()
	{
		return $this->_links;
	}
	
	/**
	 * Add links objects
	 * 
	 * @param  array $options
	 * @return Core_Block_Toolbar_Widget
	 */
	public function addLinks(array $options)
	{
		foreach ($options as $name => $value) {
			if ($value instanceof Core_Block_Toolbar_Link) {
				$this->addLink($value);
			} else if (is_array($value)) {
				if (!is_numeric($key) && !array_key_exists('name', $value)) {
					$value['name'] = $key;
				}
				
				$this->addLink($value);
			}
		}
		
		return $this;
	}
	
	/**
	 * Add single link
	 * 
	 * @param  array|Core_Block_Toolbar_Link $link
	 * @return Core_Block_Toolbar_Widget
	 */
	public function addLink($link)
	{
		if ($link instanceof Core_Block_Toolbar_Link) {
			$link->setToolbar($this);
			$this->_links[$link->getName()] = $link;
		} else if (is_array($link)) {
			$class = new Core_Block_Toolbar_Link($link);
			$class->setToolbar($this);
			$this->_links[$class->getName()] = $class;
		}
		
		return $this;
	}
	
	/**
	 * Get link object by name
	 * 
	 * @param  string $name
	 * @return Core_Block_Toolbar_Link
	 */
	public function getLink($name)
	{
		return $this->_links[$name];
	}
	
	/**
	 * Delete link object
	 * 
	 * @param  string $name
	 * @return Core_Block_Toolbar_Widget
	 */
	public function delLink($name)
	{
		$this->_links[$name]; // Prevent object reference deleting
		unset($this->_links[$name]);
		return $this;
	}
	
	/**
	 * Set toolbar title string
	 * 
	 * @param  string $title
	 * @return Core_Block_Toolbar_Widget
	 */
	public function setTitle($title)
	{
		$this->_title = (string) $title;
		return $this;
	}
	
	/**
	 * Get toolbar title
	 * 
	 * @throws Core_Block_Exception If title not set
	 * @return string
	 */
	public function getTitle()
	{
		if (null === $this->_title) {
			throw new Core_Block_Exception("Toolbar must have a title");
		}
		
		return $this->_title;
	}
	
	/**
	 * Set new name
	 * 
	 * @param  string $name
	 * @return Core_Block_Toolbar_Widget
	 */
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	/**
	 * Get toolbar name
	 * 
	 * @throws Core_Block_Exception If name not set
	 * @return string
	 */
	public function getName()
	{
		if (null === $this->_name) {
			throw new Core_Block_Exception("Toolbar must have a name");
			//return get_class($this);
		}
		
		return $this->_name;
	}
	
	/**
	 * Render buttons list html
	 * 
	 * @return string
	 */
	protected function _renderButtons()
	{
		$buttons = '';		
		foreach ($this->getButtons() as $button) {
			$class = preg_replace('/[^\p{L}]/u', '', $button->getName());
			$buttons .= '<li class="cbtw-button cbtw-button-' . $class . '">' . $button->render() . '</li>';
		}		
		return '<ul class="cbtw-buttons">' . $buttons . '</ul>';
	}
	
	/**
	 * Render links list html
	 * 
	 * @return string
	 */
	protected function _renderLinks()
	{
		$links = '';
		foreach ($this->getLinks() as $link) {
			$class = preg_replace('/[^\p{L}]/u', '', $link->getName());
			$links .= '<li class="cbtw-link cbtw-link-' . $class . '">' . $link->render() . '</li>';
		}
		return '<ul class="cbtw-links">' . $links . '</ul>';
	}
	
	/**
	 * Render entrie toolbar objects
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_View::render()
	 * @param  string $name Not used, pass BLOCK_DUMMY for prevent errors
	 * @return string
	 */
	public function render($name)
	{
   		$response = '';
   		$response .= $this->renderBlockChilds(self::BLOCK_PLACEMENT_BEFORE);
		
		try {
			$this->setRendered(true);
			$class = preg_replace('/[^\p{L}\-]/u', '_', $this->getBlockName());
			$response .= '<div class="cbtw-block cbtw-block-' . $class . '">'
				 . '<div class="cbtw-title">' . $this->getTitle() . '</div>'
				 . $this->_renderButtons()
				 . $this->_renderLinks()
			     . '</div>';
			//$this->setRendered(true);
		} catch (Exception $e) {
			$response .= $e->getMessage();
		}
    	
		$response .= $this->renderBlockChilds(self::BLOCK_PLACEMENT_AFTER);
    	return $response;
	}
}