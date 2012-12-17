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
* @version    $Id: Hyperlink.php 0.1 2012-12-12 pavlenko $
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
class Core_Block_Grid_Column_Hyperlink extends Core_Block_Grid_Column_Default
{
	const _BLANK  = '_blank';	
	const _PARENT = '_parent';	
	const _SELF   = '_self';	
	const _TOP    = '_top';
	
	/**
	 * Available targets list for comparison functions
	 * 
	 * @var array
	 */
	protected $_availableTargets = array(
		self::_BLANK,
		self::_PARENT,
		self::_SELF,
		self::_TOP
	);

	/**
	 * Default link target
	 * 
	 * @var string
	 */
	protected $_linkTarget = self::_SELF;
	
	/**
	 * Link added fields
	 * 
	 * @var array
	 */
	protected $_linkBindFields = array();
	
	/**
	 * Lunk url options
	 * 
	 * @var array
	 */
	protected $_linkOptions = array();
	
	/**
	 * Link used route name
	 * 
	 * @var string
	 */
	protected $_linkRoute;

	/**
	 * Static link url
	 * 
	 * @var string
	 */
	protected $_linkStaticUrl;

	/**
	 * Link static text
	 * 
	 * @var string
	 */
	protected $_linkStaticText;

	/**
	 * Set link options
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_Grid_Column_Default::setOptions()
	 * @param array $options
	 * @return Core_Block_Grid_Column_Hyperlink
	 */
	public function setOptions(array $options)
	{
		if (is_string($options['action'])) {
			$this->_linkOptions['action'] = $options['action'];
		}
		
		if (is_string($options['controller'])) {
			$this->_linkOptions['controller'] = $options['controller'];
		}
		
		if (is_string($options['module'])) {
			$this->_linkOptions['module'] = $options['module'];
		}

		if (is_string($options['route'])) {
			$this->setLinkRoute($options['route']);
		}
		
		unset($options['action'], $options['controller'], $options['module'], $options['route']);
		return parent::setOptions($options);
	}
	
	/**
	 * Set new link target
	 * 
	 * @param  string $value
	 * @throws Core_Block_Exception If target not set or invalid
	 * @return Core_Block_Grid_Column_Hyperlink
	 */
	public function setLinkTarget($value = null)
	{
		if (is_string($value) && in_array($value, $this->_availableTargets)) {
			$this->_linkTarget = $value;
		} else {
			require_once 'Core/Block/Exception.php';
			throw new Core_Block_Exception("Target must be a one of self target constants string");
		}
		
		return $this;
	}
	
	/**
	 * Get setted target
	 * 
	 * @return string
	 */
	public function getLinkTarget()
	{
		return $this->_linkTarget;
	}
	
	/**
	 * Set binded fields list
	 * 
	 * @param  array $value
	 * @return Core_Block_Grid_Column_Hyperlink
	 */
	public function setLinkBindFields(array $value)
	{
		$this->_linkBindFields = $value;
		return $this;
	}
	
	/**
	 * Get binded fields list
	 * 
	 * @return array
	 */
	public function getLinkBindFields()
	{
		return $this->_linkBindFields;
	}
	
	/**
	 * Set link url options
	 * 
	 * @param  string|array $value
	 * @return Core_Block_Grid_Column_Hyperlink
	 */
	public function setLinkOptions($value)
	{
		if (is_array($value)) {
			$this->_linkOptions = $value;
		} else if (is_string($value)) {
			$this->_linkOptions = Core::urlToOptions($value);
		}
		
		return $this;
	}
	
	/**
	 * Get link url options
	 * 
	 * @return array
	 */
	public function getLinkOptions()
	{
		return $this->_linkOptions;
	}
	
	/**
	 * Set used route name
	 * 
	 * @param  string $value
	 * @return Core_Block_Grid_Column_Hyperlink
	 */
	public function setLinkRoute($value)
	{
		$this->linkRoute = $value;
		return $this;
	}
	
	/**
	 * Get link route name
	 * 
	 * @return string
	 */
	public function getLinkRoute()
	{
		return $this->linkRoute;
	}

	/**
	 * Set link static url
	 * 
	 * @param  string $value
	 * @return Core_Block_Grid_Column_Hyperlink
	 */
	public function setLinkStaticUrl($value)
	{
		$this->_linkStaticUrl = $value;
		return $this;
	}

	/**
	 * Get link static url
	 * 
	 * @return string
	 */
	public function getLinkStaticUrl()
	{
		return $this->_linkStaticUrl;
	}

	/**
	 * Set new link static text
	 * 
	 * @param  string $value
	 * @return Core_Block_Grid_Column_Hyperlink
	 */
	public function setLinkStaticText($value)
	{
		$this->_linkStaticText = $value;
		return $this;
	}

	/**
	 * Get link static text
	 * 
	 * @return string
	 */
	public function getLinkStaticText()
	{
		return $this->_linkStaticText;
	}
	
	/**
	 * Render link html
	 * 
	 * (non-PHPdoc)
	 * @see Core_Block_Grid_Column_Default::render()
	 * @return string
	 */
	public function render()
	{
		$url = $this->getLinkStaticUrl();
		if (null === $url) {
			$urlOptions = $this->getLinkOptions();
			foreach ($this->getLinkBindFields() as $alias => $field) {
				if (null !== $this->getRow($field)) {
					$urlOptions[(!is_numeric($alias) ? $alias : $field)] = $this->getRow($field);
				}
			}
			
			$url = $this->getGrid()->url($urlOptions, $this->getLinkRoute(), true);
		}
		
		$text = $this->getLinkStaticText();
		if (null === $text) {
			$text = $this->getValue();
		}
		
		if ($text=='') {
			return '<span>нет</span>';
		}
		
		return '<a href="' . $url . '" target="' . $this->getLinkTarget() . '"><span>' . $text . '</span></a>';
	}
}