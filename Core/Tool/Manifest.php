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
 * @package    Core
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Manifest.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * @see Zend_Tool_Framework_Manifest_ProviderManifestable
 */
require_once 'Zend/Tool/Framework/Manifest/ProviderManifestable.php';

/**
 * Providers aggregator manifest class
 *
 * @category   Core
 * @package    Core
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Tool_Manifest
	implements Zend_Tool_Framework_Manifest_ProviderManifestable
{
	/**
	 * Returns array of custom providers instances
	 * 
	 * (non-PHPdoc)
	 * @see Zend_Tool_Framework_Manifest_ProviderManifestable::getProviders()
	 * @return array
	 */
	public function getProviders()
	{
		$providers = array();
		return $providers;
	}
}