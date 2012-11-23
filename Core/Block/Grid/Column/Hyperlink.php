<?php

require_once "Core/Block/Grid/Column/Default.php";

class Core_Block_Grid_Column_Hyperlink extends Core_Block_Grid_Column_Default
{
	const _BLANK = '_blank';
	
	const _PARENT = '_parent';
	
	const _SELF = '_self';
	
	const _TOP = '_top';
	
	protected $_availableTargets = array(
		self::_BLANK,
		self::_PARENT,
		self::_SELF,
		self::_TOP
	);

	protected $_linkTarget = self::_SELF;
	
	protected $_linkBindFields = array();
	
	protected $_linkOptions = array();
	
	protected $_linkRoute;

	protected $_linkStaticUrl;

	protected $_linkStaticText;

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
	
	public function setLinkTarget($value = null)
	{
		if (is_string($value) && in_array($value, $this->_availableTargets)) {
			$this->_linkTarget = $value;
		} else {
			throw new Exception("Target must be a one of self target constants string");
		}
		
		return $this;
	}
	
	public function getLinkTarget()
	{
		return $this->_linkTarget;
	}
	
	public function setLinkBindFields(array $value)
	{
		$this->_linkBindFields = $value;
		return $this;
	}
	
	public function getLinkBindFields()
	{
		return $this->_linkBindFields;
	}
	
	public function setLinkOptions($value)
	{
		if (is_array($value)) {
			$this->_linkOptions = $value;
		} else if (is_string($value)) {
			$this->_linkOptions = Core::urlToOptions($value);
		}
		
		return $this;
	}
	
	public function getLinkOptions()
	{
		return $this->_linkOptions;
	}
	
	public function setLinkRoute($value)
	{
		$this->linkRoute = $value;
		return $this;
	}
	
	public function getLinkRoute()
	{
		return $this->linkRoute;
	}

	public function setLinkStaticUrl($value)
	{
		$this->_linkStaticUrl = $value;
		return $this;
	}

	public function getLinkStaticUrl()
	{
		return $this->_linkStaticUrl;
	}

	public function setLinkStaticText($value)
	{
		$this->_linkStaticText = $value;
		return $this;
	}

	public function getLinkStaticText()
	{
		return $this->_linkStaticText;
	}
	
	public function render()
	{
		$url = $this->getLinkStaticUrl();
		if (null === $url) {
			$urlOptions = $this->getLinkOptions();
			foreach ($this->getLinkBindFields() as $field) {
				if (null !== $this->getRow($field)) {
					$urlOptions[$field] = $this->getRow($field);
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