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
 * @package    Core_Cache
 * @subpackage Core_Cache_Frontend
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: MasterFile.php 24218 2011-07-10 01:22:58Z ramon $
 */


/**
 * @see Zend_Cache_Core
 */
require_once 'Zend/Cache/Core.php';


/**
 * @package    Core_Cache
 * @subpackage Core_Cache_Frontend
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Cache_Frontend_MasterFile extends Zend_Cache_Frontend_File
{
    /**
     * Constructor
     * 
     * Overriding check 'master_files' specific option to
     * not throws an Exception while configuring
     *
     * @param  array $options Associative array of options
     * @return void
     */
	public function __construct(array $options = array())
	{
		while (list($name, $value) = each($options)) {
			$this->setOption($name, $value);
		}
	}
}