<?php

require_once "Core/Image/Adapter/Abstract.php";

/**
 * Image JPEG adapter
 *
 * @author     Pavlenko Evgeniy
 * @version    2.3
 * @copyright  Copyright (c) 2012 SunNY Creative Technologies. (http://www.sunny.net)
 */
class Core_Image_Adapter_Jpeg extends Core_Image_Adapter_Abstract
{
	/**
	 * Load jpeg file
	 * 
	 * @param  string $filename
	 * @return resource
	 */
	protected function _load($filename)
	{
		return imagecreatefromjpeg($filename);
	}
	
	/**
	 * Save image to file
	 */
	protected function _save()
	{
		imagejpeg($this->_resource, $this->getSavePath(), $this->getCompression());
	}
}