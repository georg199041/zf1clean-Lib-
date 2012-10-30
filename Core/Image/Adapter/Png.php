<?php

require_once "Core/Image/Adapter/Abstract.php";

/**
 * Image PNG adapter
 *
 * @author     Pavlenko Evgeniy
 * @version    2.3
 * @copyright  Copyright (c) 2012 SunNY Creative Technologies. (http://www.sunny.net)
 */
class Core_Image_Adapter_Png extends Core_Image_Adapter_Abstract
{
	/**
	 * Load png file
	 * 
	 * @param  string $filename
	 * @return resource
	 */
	protected function _load($filename)
	{
		return imagecreatefrompng($filename);
	}
	
	/**
	 * Save image to file
	 */
	protected function _save()
	{
		imagepng(
			$this->_resource,
			$this->getSavePath(),
			round($this->getCompression() * 9 / 100)
		);
	}
}