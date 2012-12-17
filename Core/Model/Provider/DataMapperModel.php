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
 * @version    $Id: DataMapperModel.php 24218 2011-07-10 01:22:58Z ramon $
 */

/**
 * Data mapper model generation class
 *
 * @category   Core
 * @package    Core
 * @copyright  Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Core_Model_Provider_DataMapperModel
	extends Zend_Tool_Project_Provider_Abstract
{
	/**
	 * Base application modules path
	 * 
	 * @var string
	 */
	public $modulesPath = 'modules';
	
	/**
	 * Collection relative path
	 * 
	 * @var string
	 */
	public $collectionPath = 'Model/Collection';
	
	/**
	 * Entity relative path
	 * 
	 * @var string
	 */
	public $entityPath = 'Model/Entity';
	
	/**
	 * Mapper relative path
	 * 
	 * @var string
	 */
	public $mapperPath = 'Model/Mapper';
	
	/**
	 * Source relative path
	 * 
	 * @var string
	 */
	public $sourcePath = 'Model/Source';
	
	/**
	 * List of available sources
	 * 
	 * @var array
	 */
	protected $_availableSources = array(
		'DbTable',
	);
	
	/**
	 * Inflector filter
	 * 
	 * @var unknown_type
	 */
	protected $_filter;
	
	/**
	 * Filter instantiate method
	 * 
	 * @return Zend_Filter
	 */
	protected function _getFilter()
	{
		if (null === $this->_filter) {
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_Word_DashToCamelCase());
			$filter->addFilter(new Zend_Filter_Word_UnderscoreToCamelCase());
			
			$this->_filter = $filter;
		}
		
		return $this->_filter;
	}
	
	/**
	 * Apply filter helper method
	 * 
	 * @param  string $string
	 * @return string
	 */
	protected function _applyFilter($string)
	{
		return $this->_getFilter()->filter($string);
	}
	
	/**
	 * Create collection part of model
	 * 
	 * @param  string $name
	 * @param  string $module
	 * @return string
	 */
	protected function _generateCollection($name, $module)
	{
		// Generate class docblock
		$classDoc = new Zend_CodeGenerator_Php_Docblock();
		$classDoc->setShortDescription('Collection for modeling table: ' . $name);
		$classDoc->setTag(array('name' => 'category ', 'description' => 'Application'));
		$classDoc->setTag(array('name' => 'package  ', 'description' => 'Application'));
		$classDoc->setTag(array('name' => 'copyright', 'description' => 'Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)'));
		$classDoc->setTag(array('name' => 'license  ', 'description' => 'http://framework.zend.com/license/new-bsd     New BSD License'));
		
		// Generate class
		$className = $this->_applyFilter($module) . '_' . str_replace('/', '_', $this->collectionPath) . '_' . $this->_applyFilter($name);
		$class = new Zend_CodeGenerator_Php_Class();
		$class->setName($className);
		$class->setDocblock($classDoc);
		$class->setExtendedClass('Core_Model_Collection_Abstract');
		
		// Generate page level docblock
		$fileDoc = new Zend_CodeGenerator_Php_Docblock();
		$fileDoc->setShortDescription('Zend Framework');
		$fileDoc->setLongDescription("LICENSE

This source file is subject to the new BSD license that is bundled
with this package in the file LICENSE.txt.
It is also available through the world-wide-web at this URL:
http://framework.zend.com/license/new-bsd
If you did not receive a copy of the license and are unable to
obtain it through the world-wide-web, please send an email
to license@zend.com so we can send you a copy immediately.");
		$fileDoc->setTag(array('name' => 'category ', 'description' => 'Application'));
		$fileDoc->setTag(array('name' => 'package  ', 'description' => 'Application'));
		$fileDoc->setTag(array('name' => 'copyright', 'description' => 'Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)'));
		$fileDoc->setTag(array('name' => 'license  ', 'description' => 'http://framework.zend.com/license/new-bsd     New BSD License'));
		$fileDoc->setTag(array('name' => 'version  ', 'description' => '$Id: ' . $this->_applyFilter($name) . '.php 0.1 2012-12-21 pavlenko $'));
		
		// Generate file
		$file = new Zend_CodeGenerator_Php_File();
		$file->setDocblock($fileDoc);
		$file->setRequiredFiles(array('Core/Model/Collection/Abstract.php'));
		$file->setClass($class);
		
		$filePath = $this->modulesPath . '/' . $module . '/' . $this->collectionPath;
		if (!is_dir($filePath)) {
			@mkdir($filePath, 0777, true);
		}
		
		$fileName = $filePath . '/' . $this->_applyFilter($name) . '.php';
		
		file_put_contents($fileName, $file->generate());
		echo "Create collection\n    class '{$className}'\n    file  '{$fileName}'\n";
	}

	/**
	 * Create entity part of model
	 *
	 * @param  string $name
	 * @param  string $module
	 * @return string
	 */
	protected function _generateEntity($name, $module)
	{
		// Generate class docblock
		$classDoc = new Zend_CodeGenerator_Php_Docblock();
		$classDoc->setShortDescription('Entity for modeling table: ' . $name);
		$classDoc->setTag(array('name' => 'category ', 'description' => 'Application'));
		$classDoc->setTag(array('name' => 'package  ', 'description' => 'Application'));
		$classDoc->setTag(array('name' => 'copyright', 'description' => 'Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)'));
		$classDoc->setTag(array('name' => 'license  ', 'description' => 'http://framework.zend.com/license/new-bsd     New BSD License'));
	
		// Generate class
		$className = $this->_applyFilter($module) . '_' . str_replace('/', '_', $this->entityPath) . '_' . $this->_applyFilter($name);
		$class = new Zend_CodeGenerator_Php_Class();
		$class->setName($className);
		$class->setDocblock($classDoc);
		$class->setExtendedClass('Core_Model_Entity_Abstract');
	
		// Generate page level docblock
		$fileDoc = new Zend_CodeGenerator_Php_Docblock();
		$fileDoc->setShortDescription('Zend Framework');
		$fileDoc->setLongDescription("LICENSE
	
This source file is subject to the new BSD license that is bundled
with this package in the file LICENSE.txt.
It is also available through the world-wide-web at this URL:
http://framework.zend.com/license/new-bsd
If you did not receive a copy of the license and are unable to
obtain it through the world-wide-web, please send an email
to license@zend.com so we can send you a copy immediately.");
		$fileDoc->setTag(array('name' => 'category ', 'description' => 'Application'));
		$fileDoc->setTag(array('name' => 'package  ', 'description' => 'Application'));
		$fileDoc->setTag(array('name' => 'copyright', 'description' => 'Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)'));
		$fileDoc->setTag(array('name' => 'license  ', 'description' => 'http://framework.zend.com/license/new-bsd     New BSD License'));
		$fileDoc->setTag(array('name' => 'version  ', 'description' => '$Id: ' . $this->_applyFilter($name) . '.php 0.1 2012-12-21 pavlenko $'));
	
		// Generate file
		$file = new Zend_CodeGenerator_Php_File();
		$file->setDocblock($fileDoc);
		$file->setRequiredFiles(array('Core/Model/Entity/Abstract.php'));
		$file->setClass($class);
	
		$filePath = $this->modulesPath . '/' . $module . '/' . $this->entityPath;
		if (!is_dir($filePath)) {
			@mkdir($filePath, 0777, true);
		}
		
		$fileName = $filePath . '/' . $this->_applyFilter($name) . '.php';
	
		file_put_contents($fileName, $file->generate());
		echo "Create entity\n    class '{$className}'\n    file  '{$fileName}'\n";
	}

	/**
	 * Create mapper part of model
	 *
	 * @param  string $name
	 * @param  string $module
	 * @return string
	 */
	protected function _generateMapper($name, $module)
	{
		// Generate class docblock
		$classDoc = new Zend_CodeGenerator_Php_Docblock();
		$classDoc->setShortDescription('Mapper for modeling table: ' . $name);
		$classDoc->setTag(array('name' => 'category ', 'description' => 'Application'));
		$classDoc->setTag(array('name' => 'package  ', 'description' => 'Application'));
		$classDoc->setTag(array('name' => 'copyright', 'description' => 'Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)'));
		$classDoc->setTag(array('name' => 'license  ', 'description' => 'http://framework.zend.com/license/new-bsd     New BSD License'));
	
		// Generate class
		$className = $this->_applyFilter($module) . '_' . str_replace('/', '_', $this->mapperPath) . '_' . $this->_applyFilter($name);
		$class = new Zend_CodeGenerator_Php_Class();
		$class->setName($className);
		$class->setDocblock($classDoc);
		$class->setExtendedClass('Core_Model_Mapper_Abstract');
	
		// Generate page level docblock
		$fileDoc = new Zend_CodeGenerator_Php_Docblock();
		$fileDoc->setShortDescription('Zend Framework');
		$fileDoc->setLongDescription("LICENSE
	
This source file is subject to the new BSD license that is bundled
with this package in the file LICENSE.txt.
It is also available through the world-wide-web at this URL:
http://framework.zend.com/license/new-bsd
If you did not receive a copy of the license and are unable to
obtain it through the world-wide-web, please send an email
to license@zend.com so we can send you a copy immediately.");
		$fileDoc->setTag(array('name' => 'category ', 'description' => 'Application'));
		$fileDoc->setTag(array('name' => 'package  ', 'description' => 'Application'));
		$fileDoc->setTag(array('name' => 'copyright', 'description' => 'Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)'));
		$fileDoc->setTag(array('name' => 'license  ', 'description' => 'http://framework.zend.com/license/new-bsd     New BSD License'));
		$fileDoc->setTag(array('name' => 'version  ', 'description' => '$Id: ' . $this->_applyFilter($name) . '.php 0.1 2012-12-21 pavlenko $'));
	
		// Generate file
		$file = new Zend_CodeGenerator_Php_File();
		$file->setDocblock($fileDoc);
		$file->setRequiredFiles(array('Core/Model/Mapper/Abstract.php'));
		$file->setClass($class);
	
		$filePath = $this->modulesPath . '/' . $module . '/' . $this->mapperPath;
		if (!is_dir($filePath)) {
			@mkdir($filePath, 0777, true);
		}
		
		$fileName = $filePath . '/' . $this->_applyFilter($name) . '.php';
	
		file_put_contents($fileName, $file->generate());
		echo "Create mapper\n    class '{$className}'\n    file  '{$fileName}'\n";
	}

	/**
	 * Create mapper part of model
	 *
	 * @param  string $name
	 * @param  string $module
	 * @param  string $type
	 * @return string
	 */
	protected function _generateSource($name, $module, $type)
	{
		// Generate class docblock
		$classDoc = new Zend_CodeGenerator_Php_Docblock();
		$classDoc->setShortDescription('Source for modeling table: ' . $name);
		$classDoc->setTag(array('name' => 'category ', 'description' => 'Application'));
		$classDoc->setTag(array('name' => 'package  ', 'description' => 'Application'));
		$classDoc->setTag(array('name' => 'copyright', 'description' => 'Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)'));
		$classDoc->setTag(array('name' => 'license  ', 'description' => 'http://framework.zend.com/license/new-bsd     New BSD License'));
	
		// Generate class
		$className = $this->_applyFilter($module) . '_' . str_replace('/', '_', $this->sourcePath) . '_' . $this->_applyFilter($name);
		$class = new Zend_CodeGenerator_Php_Class();
		$class->setName($className);
		$class->setDocblock($classDoc);
		$class->setExtendedClass('Core_Model_Source_' . $type);
	
		// Generate page level docblock
		$fileDoc = new Zend_CodeGenerator_Php_Docblock();
		$fileDoc->setShortDescription('Zend Framework');
		$fileDoc->setLongDescription("LICENSE
	
This source file is subject to the new BSD license that is bundled
with this package in the file LICENSE.txt.
It is also available through the world-wide-web at this URL:
http://framework.zend.com/license/new-bsd
If you did not receive a copy of the license and are unable to
obtain it through the world-wide-web, please send an email
to license@zend.com so we can send you a copy immediately.");
		$fileDoc->setTag(array('name' => 'category ', 'description' => 'Application'));
		$fileDoc->setTag(array('name' => 'package  ', 'description' => 'Application'));
		$fileDoc->setTag(array('name' => 'copyright', 'description' => 'Copyright (c) 2005-2012 SunNY Creative Technologies. (http://www.sunny.net.ua)'));
		$fileDoc->setTag(array('name' => 'license  ', 'description' => 'http://framework.zend.com/license/new-bsd     New BSD License'));
		$fileDoc->setTag(array('name' => 'version  ', 'description' => '$Id: ' . $this->_applyFilter($name) . '.php 0.1 2012-12-21 pavlenko $'));
	
		// Generate file
		$file = new Zend_CodeGenerator_Php_File();
		$file->setDocblock($fileDoc);
		$file->setRequiredFiles(array('Core/Model/Source/' . $type . '.php'));
		$file->setClass($class);
	
		$filePath = $this->modulesPath . '/' . $module . '/' . $this->sourcePath;
		if (!is_dir($filePath)) {
			@mkdir($filePath, 0777, true);
		}
		
		$fileName = $filePath . '/' . $this->_applyFilter($name) . '.php';
	
		file_put_contents($fileName, $file->generate());
		echo "Create source\n    class '{$className}'\n    file  '{$fileName}'\n";
	}
	
	/**
	 * CLI method
	 * 
	 * @param  string $name
	 * @param  string $module
	 * @param  string $sourcetype
	 * @throws Exception
	 */
	public function create($name, $module = 'default', $sourcetype = 'DbTable')
	{
		if (!in_array($sourcetype, $this->_availableSources)) {
			throw new Exception("Invalid source type, must be one of: " . implode(', ', $this->_availableSources));
		}
		
		$this->_generateCollection($name, $module);
		$this->_generateEntity($name, $module);
		$this->_generateMapper($name, $module);
		$this->_generateSource($name, $module, $sourcetype);
	}
}