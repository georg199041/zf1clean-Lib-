<?php

require_once "Core/Block/View.php";

require_once "Core/Block/Toolbar/Button.php";

require_once "Core/Block/Toolbar/Link.php";

class Core_Block_Toolbar_Widget extends Core_Block_View
{
	protected $_buttons = array();
	
	protected $_links = array();
	
	protected $_title;
	
	protected $_name;
	
	public function setButtons(array $buttons)
	{
		$this->_buttons = array();
		$this->addButtons($buttons);
		return $this;
	}
	
	public function getButtons()
	{
		return $this->_buttons;
	}
	
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
				throw new Exception("Toolbar button must be an array or Core_Block_Toolbar_Button instance");
			}
		}
				
		return $this;
	}
	
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
			throw new Exception("Invalid Toolbar button definition");
		}
		
		return $this;
	}
	
	public function getButton($name)
	{
		return $this->_button[$name];
	}
	
	public function delButton($name)
	{
		$this->_button[$name] = null; // Prevent reference deleting
		unset($this->_button[$name]);
		return $this;
	}
	
	public function setLinks(array $options)
	{
		$this->_links = array();
		$this->addLinks($options);
		return $this;
	}
	
	public function getLinks()
	{
		return $this->_links;
	}
	
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
	
	public function getLink($name)
	{
		return $this->_links[$name];
	}
	
	public function delLink($name)
	{
		$this->_links[$name]; // Prevent object reference deleting
		unset($this->_links[$name]);
		return $this;
	}
	
	public function setTitle($title)
	{
		$this->_title = (string) $title;
		return $this;
	}
	
	public function getTitle()
	{
		if (null === $this->_title) {
			throw new Exception("Toolbar must have a title");
		}
		
		return $this->_title;
	}
	
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	public function getName()
	{
		if (null === $this->_name) {
			throw new Exception("Toolbar must have a name");
			//return get_class($this);
		}
		
		return $this->_name;
	}
	
	protected function _renderButtons()
	{
		$buttons = '';		
		foreach ($this->getButtons() as $button) {
			$class = preg_replace('/[^\p{L}]/u', '', $button->getName());
			$buttons .= '<li class="cbtw-button cbtw-button-' . $class . '">' . $button->render() . '</li>';
		}		
		return '<ul class="cbtw-buttons">' . $buttons . '</ul>';
	}
	
	protected function _renderLinks()
	{
		$links = '';
		foreach ($this->getLinks() as $link) {
			$class = preg_replace('/[^\p{L}]/u', '', $link->getName());
			$links .= '<li class="cbtw-link cbtw-link-' . $class . '">' . $link->render() . '</li>';
		}
		return '<ul class="cbtw-links">' . $links . '</ul>';
	}
	
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