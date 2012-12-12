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
 * @subpackage Core_Block_Renderer
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: CachedRenderer.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Core_Block_Renderer_Abstract
 */
require_once 'Core/Block/Renderer/Abstract.php';

/**
 * @see Zend_Cache_Core
 */
require_once 'Zend/Cache/Core.php';

/**
 * Block cached template/renderer engine implementations
 *
 * @category   Core
 * @package    Core_Block
 * @subpackage Core_Block_Renderer
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Block_Renderer_CachedRenderer extends Core_Block_Renderer_Abstract
{
	public function getCacheId();
	public function getCache();
	public function setCache($cache);
	
}