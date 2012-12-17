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
 * @version    $Id: Link.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Zend_Config
 */
require_once "Zend/Config.php";

/**
 * @see Core_Attributes
 */
require_once "Core/Attributes.php";

/**
 * @see Core_Block_Toolbar_Widget
 */
require_once "Core/Block/Toolbar/Widget.php";

/**
 * @see Core_Block_Exception
 */
require_once "Core/Block/Exception.php";

/**
 * Toolbar link implementation class
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Toolbar
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Toolbar_Link extends Core_Attributes
{
	/**
	 * Link title
	 * 
	 * @var string
	 */
	protected $_title;
	
	/**
	 * Parent toolbar object
	 * 
	 * @var Core_Block_Toolbar_Widget
	 */
	protected $_toolbar;
	
	/**
	 * Link url options
	 * 
	 * @var array
	 */
	protected $_urlOptions = array();
	
	/**
	 * Link building route name
	 * 
	 * @var string
	 */
	protected $_urlRoute;

	/**
	 * Link item name
	 * 
	 * @var string
	 */
	protected $_name;
	
	/**
	 * Constructor
	 * 
	 * @param array|Zend_Config $options
	 */
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		} else if ($options instanceof Zend_Config) {
			$this->setOptions($options->toArray());
		}
	}
	
	/**
	 * Set all link option at once
	 * 
	 * @param  array $options
	 * @return Core_Block_Toolbar_Link
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $key => $val) {
			$method = 'set' . ucfirst($key);
			if (method_exists($this, $method)) {
				$this->$method($val);
			} else {
				$this->setAttribute($key, $val);
			}
		}
		
		return $this;
	}
	
	/**
	 * Set new link title
	 * 
	 * @param  string $title
	 * @return Core_Block_Toolbar_Link
	 */
	public function setTitle($title)
	{
		$this->_title = $title;
		return $this;
	}
	
	/**
	 * Gets link title
	 * 
	 * @throws Core_Block_Exception If title not set because it reqiured
	 * @return string
	 */
	public function getTitle()
	{
		if (null === $this->_title) {
			throw new Core_Block_Exception("Toolbar item '{$this->getName()}' must have title");
		}
		
		return $this->_title;
	}
	
	/**
	 * Set parent tollbar object
	 * 
	 * @param  Core_Block_Toolbar_Widget $toolbar
	 * @return Core_Block_Toolbar_Link
	 */
	public function setToolbar(Core_Block_Toolbar_Widget $toolbar)
	{
		$this->_toolbar = $toolbar;
		return $this;
	}
	
	/**
	 * Get parent toolbar object
	 * 
	 * @throws Core_Block_Exception If parent toolbar object not set because it reqiured
	 * @return Core_Block_Toolbar_Widget
	 */
	public function getToolbar()
	{
		if (null === $this->_toolbar) {
			throw new Core_Block_Exception("Toolbar object not found");
		}
		
		return $this->_toolbar;
	}
	
	/**
	 * Set url options
	 * 
	 * @param  string|array $options
	 * @return Core_Block_Toolbar_Link
	 */
	public function setUrlOptions($options)
	{
		if (is_array($options)) {
			$this->_urlOptions = $options;
		} else if (is_string($options)) {
			$this->_urlOptions = Core::urlToOptions($options);
		}
		
		return $this;
	}
	
	/**
	 * Gets url options
	 * 
	 * @return array
	 */
	public function getUrlOptions()
	{
		return $this->_urlOptions;
	}
	
	/**
	 * Set used route name
	 * 
	 * @param  string $name
	 * @return Core_Block_Toolbar_Link
	 */
	public function setUrlRoute($name)
	{
		$this->_urlRoute = $name;
		return $this;
	}
	
	/**
	 * Get used route name
	 * 
	 * @return string
	 */
	public function getUrlRoute()
	{
		return $this->_urlRoute;
	}

	/**
	 * Set link name
	 * 
	 * @param  string $name
	 * @return Core_Block_Toolbar_Link
	 */
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	/**
	 * Get link name
	 * 
	 * @throws Core_Block_Exception If link name not set because it reqiured
	 * @return string
	 */
	public function getName()
	{
		if (null === $this->_name) {
			throw new Core_Block_Exception("Toolbar item must have name");
		}
	
		return $this->_name;
	}
	
	/**
	 * Render link html
	 * 
	 * @return string
	 */
	public function render()
	{
		$this->setAttribute('href', $this->getToolbar()->url($this->getUrlOptions(), $this->getUrlRoute(), true));
		$this->setAttribute('class', null); // TODO: parse active link
		
		return '<a ' . $this->toHtmlAttributes() . '><span>' . $this->getTitle() . '</span></a>';
	}
}
