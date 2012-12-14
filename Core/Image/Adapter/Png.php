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
 * @package    Core_Image
 * @subpackage Core_Image_Adapter
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Png.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Core_Image_Adapter_Abstract
 */
require_once "Core/Image/Adapter/Abstract.php";

/**
 * Base image processing class
 *
 * @category   Core
 * @package    Core_Image
 * @subpackage Core_Image_Adapter
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
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