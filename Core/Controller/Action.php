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
 * @package    Core_Controller
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Action.php 0.1 2012-12-12 pavlenko $
 */

/**
 * @see Zend_Controller_Action
 */
require_once "Zend/Controller/Action.php";

/**
 * Temporary action class
 *
 * @category   Core
 * @package    Core_Controller
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Core_Controller_Action extends Zend_Controller_Action
{
	/**
	 * Temporary action translate method
	 * 
	 * @deprecated Since we use REST actions it will be removed in future
	 * @param  string $string Untranslated string
	 * @return string
	 */
	public function __($string)
	{
		// translate placeholder
		return $string;
	}
}