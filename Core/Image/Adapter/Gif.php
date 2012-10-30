<?php

require_once "Core/Image/Adapter/Abstract.php";

/**
 * Image GIF adapter
 *
 * @author     Pavlenko Evgeniy
 * @version    2.3
 * @copyright  Copyright (c) 2012 SunNY Creative Technologies. (http://www.sunny.net)
 */
class Core_Image_Adapter_Gif extends Core_Image_Adapter_Abstract
{
	/**
	 * Load gif file
	 * 
	 * @param  string $filename
	 * @return resource
	 */
	protected function _load($filename)
	{
		return imagecreatefromgif($filename);
	}
	
	/**
	 * Save image to file
	 */
	protected function _save()
	{
		imagegif($this->_resource, $this->getSavePath());
	}
}