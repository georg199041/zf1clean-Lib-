<?php

require_once "Core/Block/Grid/Column/Default.php";

class Core_Block_Grid_Column_Button extends Core_Block_Grid_Column_Default
{
	//protected $_buttons = array();
	
	public function render()
	{
		// TODO:
		return '<button><img src="/uploads/jquery-php.gif" height="16" /></button>';
	}
}