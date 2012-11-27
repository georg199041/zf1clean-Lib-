<?php

/**
 * Image resizer base class
 *
 * @author     Pavlenko Evgeniy
 * @category   Core
 * @package    Core_Image
 * @version    2.3
 * @subpackage Abstract
 * @copyright  Copyright (c) 2005-2009 SunNY Creative Technologies. (http://www.sunny.net)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Core_Image_Adapter_Abstract
{
	const ALIGN_CENTER = 'ALIGN_CENTER';
	const ALIGN_TOP    = 'ALIGN_TOP';
	const ALIGN_BOTTOM = 'ALIGN_BOTTOM';
	const ALIGN_LEFT   = 'ALIGN_LEFT';
	const ALIGN_RIGHT  = 'ALIGN_RIGHT';
	
	/**
	 * Image resource container
	 * 
	 * @var resource
	 */
	protected $_resource;
	
	/**
	 * Path to image file
	 * 
	 * @var string
	 */
	protected $_path;
	
	/**
	 * @var integer
	 */
	protected $_width;
	
	/**
	 * @var integer
	 */
	protected $_height;
	
	/**
	 * @var integer
	 */
	protected $_offsetTop = 0;
	
	/**
	 * @var integer
	 */
	protected $_offsetLeft = 0;
	
	/**
	 * @var integer
	 */
	protected $_newWidth;
	
	/**
	 * @var integer
	 */
	protected $_newHeight;
	
	/**
	 * @var integer
	 */
	protected $_newOffsetTop = 0;
	
	/**
	 * @var integer
	 */
	protected $_newOffsetLeft = 0;
	
	/**
	 * Path to save image to
	 * 
	 * @var string
	 */
	protected $_savePath;
	
	/**
	 * Background color code
	 * 
	 * @var integer
	 */
	protected $_backgroundColor;
	
	/**
	 * Preserved transparensy flag
	 * 
	 * @var boolean
	 */
	protected $_transparensyPreserved = true;
	
	/**
	 * Need resize smaller images
	 * 
	 * @var boolean
	 */
	protected $_needResizeSmallerThan = false;
	
	/**
	 * @var string
	 */
	protected $_method;
	
	/**
	 * @var integer
	 */
	protected $_compression = 85;
	
	/**
	 * @var integer
	 */
	protected static $_lifeTime = 2592000;
	
	/**
	 * Load image from file (specified in extension)
	 * 
	 * @param  string $filename Path to file
	 * @return false|resource   Resource id if success
	 */
	abstract protected function _load($filename);
	
	/**
	 * Save image to filesystem
	 */
	abstract protected function _save();
	
	/**
	 * Retrieve new save path
	 * 
	 * @return string New path
	 */
	protected function _resolveSavePath()
	{
		$parts = explode('/', $this->getPath());
		$filename = $parts[count($parts) - 1];
		
		$method = $this->getMethod();
		$methodName = $method['name'];
		unset($method['name']);
		
		$folder = 'cache_' . $methodName;
		foreach ($method as $arg) {
			$folder .= '_' . $arg;
		}
		
		$parts[count($parts) - 1] = $folder;
		if (!file_exists(implode('/', $parts))) {
			@mkdir(implode('/', $parts), 0777, true);
		}
		
		$parts[count($parts)] = $filename;
		return implode('/', $parts);
	}
	
	/**
	 * Load image process
	 * 
	 * @param  string $filename    Path to file
	 * @throws Exception           If file not found or cann't load
	 * @return Core_Image_Abstract
	 */
	public function __construct($filename)
	{
		if (!file_exists($filename)) {
			throw new Exception("File '$filename' not exists", 500);
		}
		
		$resource = $this->_load($filename);
		if (false === $resource) {
			throw new Exception("Can't load file '$filename'", 500);
		}
		
		$this->_resource = $resource;
		$this->_path = $filename;
	}
	
	/**
	 * Clean memory on destruct
	 */
	public function __destruct()
	{
		imagedestroy($this->_resource);
	}
	
	/**
	 * Create new blank image resource
	 * 
	 * @return resource
	 */
	public function createBlank()
	{
		$resource = imagecreatetruecolor($this->getNewWidth(), $this->getNewHeight());
		imagealphablending($resource, false);
		imagesavealpha($resource,true);
		
		imagefilledrectangle(
			$resource,
			0,
			0,
			$this->getNewWidth(),
			$this->getNewHeight(),
			$this->getBackgroundColor($resource)
		);
		
		return $resource;
	}
	
	/**
	 * Save image to file
	 * 
	 * @param string $savePath New path for save
	 */
	public function save($savePath = null)
	{
		if (null !== $savePath) {
			$this->setSavePath($savePath);
		}
		
		$this->_save();
		return $this;
	}
	
	/**
	 * Reset options
	 */
	public function reset()
	{
		$this->_path = $this->getSavePath();
		$this->_width = null;
		$this->_height = null;
		$this->_offsetTop = 0;
		$this->_offsetLeft = 0;
		$this->_newWidth = null;
		$this->_newHeight = null;
		$this->_newOffsetTop = 0;
		$this->_newOffsetLeft = 0;
		$this->_savePath = null;
		$this->_method = null;
	}
		
	/**
	 * Check if transparency preserved
	 * 
	 * @return boolean
	 */
	public function isTransparensyPreserved()
	{
		return $this->_transparensyPreserved;
	}
	
	/**
	 * Set transparency preserved flag
	 * 
	 * @param  boolean $flag
	 */
	public function setTransparensyPreserved($flag = true)
	{
		$this->_transparensyPreserved = (bool) $flag;
		return $this;
	}
	
	/**
     * Set time before die of image
 	 *
 	 * @param  integer $value
 	 */
	public function setLifeTime($value)
	{
		self::$_lifeTime = (int) $value;
		return $this;
	}
	
	
	/**
	 * Get time before die of image
	 *
	 * @return  integer $value
	 */
	public function getLifeTime()
	{
		return self::$_lifeTime;
	}

	/**
	 * Check if need processing smaller than parameters
	 *
	 * @return boolean
	 */
	public function isNeedResizeSmallerThan()
	{
		if ($this->_needResizeSmallerThan
			|| $this->getSrcWidth()  > $this->getNewWidth()
			|| $this->getSrcHeight() > $this->getNewHeight()) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Set need processing smaller than parameters flag
	 *
	 * @param  boolean $flag
	 */
	public function setNeedResizeSmallerThan($flag = false)
	{
		$this->_needResizeSmallerThan = (bool) $flag;
		return $this;
	}
	
	/**
	 * Get file path
	 * 
	 * @return string
	 */
	public function getPath()
	{
		return $this->_path;
	}
	
	/**
	 * Get original resource width
	 * 
	 * @return integer
	 */
	public function getSrcWidth()
	{
		return imagesx($this->_resource);
	}
	
	/**
	 * Get original resource height
	 * 
	 * @return integer
	 */
	public function getSrcHeight()
	{
		return imagesy($this->_resource);
	}
	
	/**
	 * Get resize source width
	 * 
	 * @return integer
	 */
	public function getWidth()
	{
		if (null === $this->_width) {
			$this->setWidth($this->getSrcWidth());
		}
		
		return $this->_width;
	}
	
	/**
	 * Set resize source width
	 * 
	 * @param integer $width
	 */
	public function setWidth($width)
	{
		$this->_width = (int) $width;
		return $this;
	}
	
	/**
	 * Get resize source height
	 * 
	 * @return integer
	 */
	public function getHeight()
	{
		if (null === $this->_height) {
			$this->setHeight($this->getSrcHeight());
		}
		
		return $this->_height;
	}
	
	/**
	 * Set resize source height
	 * 
	 * @param integer $height
	 */
	public function setHeight($height)
	{
		$this->_height = (int) $height;
		return $this;
	}
	
	/**
	 * Get resize new width
	 * 
	 * @return integer
	 */
	public function getNewWidth()
	{
		if (null === $this->_newWidth) {
			$this->setNewWidth($this->getWidth());
		}
		
		return $this->_newWidth;
	}
	
	/**
	 * Set resize new width
	 * 
	 * @param integer $width
	 */
	public function setNewWidth($width)
	{
		$this->_newWidth = (int) $width;
		return $this;
	}
	
	/**
	 * Get resize new height
	 * 
	 * @return integer
	 */
	public function getNewHeight()
	{
		if (null === $this->_newHeight) {
			$this->setNewHeight($this->getHeight());
		}
		
		return $this->_newHeight;
	}
	
	/**
	 * Set resize new height
	 * 
	 * @param integer $height
	 */
	public function setNewHeight($height)
	{
		$this->_newHeight = (int) $height;
		return $this;
	}
	
	/**
	 * Get resize source offset top
	 * 
	 * @return integer
	 */
	public function getOffsetTop()
	{
		return $this->_offsetTop;
	}
	
	/**
	 * Set resize source offset top
	 * 
	 * @param integer $offset
	 */
	public function setOffsetTop($offset)
	{
		$this->_offsetTop = (int) $offset;
		return $this;
	}
	
	/**
	 * Get resize source offset left
	 * 
	 * @return integer
	 */
	public function getOffsetLeft()
	{
		return $this->_offsetLeft;
	}
	
	/**
	 * Set resize source offset left
	 * 
	 * @param integer $offset
	 */
	public function setOffsetLeft($offset)
	{
		$this->_offsetLeft = (int) $offset;
		return $this;
	}
	
	/**
	 * Get resize new offset top
	 * 
	 * @return integer
	 */
	public function getNewOffsetTop()
	{
		return $this->_newOffsetTop;
	}
	
	/**
	 * Set resize new offset top
	 * 
	 * @param integer $offset
	 */
	public function setNewOffsetTop($offset)
	{
		$this->_newOffsetTop = (int) $offset;
		return $this;
	}
	
	/**
	 * Get resize new offset left
	 * 
	 * @return integer
	 */
	public function getNewOffsetLeft()
	{
		return $this->_newOffsetLeft;
	}
	
	/**
	 * Set resize new offset left
	 * 
	 * @param integer $offset
	 */
	public function setNewOffsetLeft($offset)
	{
		$this->_newOffsetLeft = (int) $offset;
		return $this;
	}
	
	/**
	 * Get save path (generate if needed)
	 * 
	 * @return string
	 */
	public function getSavePath()
	{
		if (null === $this->_savePath) {
			$this->setSavePath($this->_resolveSavePath());
		}
		
		return $this->_savePath;
	}
	
	/**
	 * Set new save path
	 * 
	 * @param string $savePath
	 */
	public function setSavePath($savePath)
	{
		$this->_savePath = $savePath;
		return $this;
	}
	
	/**
	 * Get background color for image
	 * 
	 * @param  resource Optional image resource
	 * @return integer
	 */
	public function getBackgroundColor($image = null)
	{
		if (null !== $image) {
			$image = $this->_resource;
		}
		
		if (null === $this->_backgroundColor) {
			if ($this->isTransparensyPreserved()) {
				$this->_backgroundColor = IMG_COLOR_TRANSPARENT;
			} else {
				$this->_backgroundColor = imagecolorallocate($image, 255, 255, 255);
			}
		}
		
		return $this->_backgroundColor;
	}
	
	/**
	 * Set background color for image
	 * 
	 * @param string   Hex code 24 bit or 32 bit for use alpha channel
	 * @param resource Optional image resource
	 */
	public function setBackgroundColor($hexCode, $image = null)
	{
		if (null === $image) {
			$image = $this->_resource;
		}
		
		if (!preg_match('/^#(([0-9a-fA-F]){6}|([0-9a-fA-F]){6})$/i', $hexCode)) {
			echo '!match';
			return $this;
		}
		
		echo 'match';
		
		$red   = substr($hexCode, 1, 2);
		$green = substr($hexCode, 3, 2);
		$blue  = substr($hexCode, 5, 2);
		
		if (strlen($hexCode) == 9) {
			$alpha = substr($hexCode, 7, 2);
			$this->_backgroundColor = imagecolorallocatealpha($image, $red, $green, $blue, $alpha);
		} else {
			$this->_backgroundColor = imagecolorallocate($image, $red, $green, $blue, $alpha);
		}
		
		return $this;
	}
	
	/**
	 * Get compression (only for jpeg or png)
	 * 
	 * @return integer
	 */
	public function getCompression()
	{
		return $this->_compression;
	}
	
	/**
	 * Set new compression between 1 and 100
	 * 
	 * @param integer $compression
	 */
	public function setCompression($compression)
	{
		if ($compression <= 0) {
			$compression = 1;
		}
		
		if ($compression > 100) {
			$compression = 100;
		}
		
		$this->_compression = $compression;
		return $this;
	}
	
	/**
	 * Get used resize method name
	 * 
	 * @return string
	 */
	public function getMethod()
	{
		if (null === $this->_method) {
			$this->setMethod('resize');
		}
		
		return $this->_method;
	}
	
	/**
	 * Set used resize method
	 * 
	 * @param array $method
	 */
	public function setMethod(array $method)
	{
		$this->_method = $method;
		return $this;
	}
	
	/**
	 * Basic resize method
	 * 
	 * @param integer $width    Optional width
	 * @param integer $height   Optional height
	 * @param string  $savePath Optional savepath
	 */
	public function resize($width = null, $height = null, $savePath = null)
	{
		if (null !== $width) {
			$this->setNewWidth($width);
		}
		
		if (null !== $height) {
			$this->setNewHeight($height);
		}
		
		if (!$this->isNeedResizeSmallerThan()) {
			return $this;
		}
		
		if (null !== $savePath) {
			$this->setSavePath($savePath);
		}
		
		$this->setMethod(array('name' => __FUNCTION__, $this->getNewWidth(), $this->getNewHeight()));
		
		if (is_file($this->getSavePath()) && filemtime($this->getSavePath()) <= $this->_lifeTime) {
			$this->reset();
			return $this;
		}
		
		$resource = $this->createBlank();
		
		imagecopyresampled(
			$resource,
			$this->_resource,
			$this->getNewOffsetLeft(),
			$this->getNewOffsetTop(),
			$this->getOffsetLeft(),
			$this->getOffsetTop(),
			$this->getNewWidth(),
			$this->getNewHeight(),
			$this->getWidth(),
			$this->getHeight()
		);
		
		$this->_resource = $resource;
		$this->save($savePath);
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Resize by percentage of original
	 * 
	 * @param integer $percentage Percentage
	 * @param string  $savePath   Optional savepath
	 */
	public function resizeToPercentage($percentage, $savePath = null)
	{
		$this->setNewWidth(round($this->getSrcWidth() * $percentage / 100));
		$this->setNewHeight(round($this->getSrcHeight() * $percentage / 100));
			
		if (!$this->isNeedResizeSmallerThan()) {
			return $this;
		}
		
		$this->setMethod(array('name' => __FUNCTION__, $percentage));
		$this->resize(null, null, $savePath);		
		return $this;
	}
	
	/**
	 * Resize by width value
	 * 
	 * @param integer $width
	 * @param string  $savePath Optional savepath
	 */
	public function resizeToWidth($width, $savePath = null)
	{
		$this->setNewWidth($width);
		$this->setNewHeight(round($this->getNewWidth() * $this->getSrcHeight() / $this->getSrcWidth()));
			
		if (!$this->isNeedResizeSmallerThan()) {
			return $this;
		}
		
		$this->setMethod(array('name' => __FUNCTION__, $width));
		$this->resize(null, null, $savePath);
		return $this;
	}
	
	/**
	 * Resize by height value
	 * 
	 * @param integer $height
	 * @param string  $savePath Optional savepath
	 */
	public function resizeToHeight($height, $savePath = null)
	{
		$this->setNewHeight($height);
		$this->setNewWidth(round($this->getNewHeight() * $this->getSrcWidth() / $this->getSrcHeight()));
			
		if (!$this->isNeedResizeSmallerThan()) {
			return $this;
		}
		
		$this->setMethod(array('name' => __FUNCTION__, $height));
		$this->resize(null, null, $savePath);
		return $this;
	}
	
	/**
	 * Resize by fit to canvas dimensions
	 * 
	 * @param integer $width
	 * @param integer $height
	 * @param integer $hAlign   Optional horisontal align
	 * @param integer $vAlign   Optional vertical align
	 * @param string  $savePath Optional savepath
	 */
	public function resizeToFitCanvas($width, $height, $hAlign = null, $vAlign = null, $savePath = null)
	{
		$this->setNewWidth($width);
		$this->setNewHeight($height);
		
		if (null === $hAlign) {
			$hAlign = self::ALIGN_CENTER;
		}
		
		if (null === $vAlign) {
			$vAlign = self::ALIGN_CENTER;
		}
		
		if (!$this->isNeedResizeSmallerThan()) {
			return $this;
		}
		
		
		if (null !== $savePath) {
			$this->setSavePath($savePath);
		}
		
		$this->setMethod(array('name' => __FUNCTION__, $width, $height));
		
		if (is_file($this->getSavePath()) && filemtime($this->getSavePath()) <= $this->_lifeTime) {
			$this->reset();
			return $this;
		}
		
		
		
		
		$resource = $this->createBlank();
		
		// AR > 1: horisontal; AR < 1: vertical;
		$srcAR = $this->getSrcWidth() / $this->getSrcHeight(); // test 3/2; 1.5
		$newAR = $this->getNewWidth() / $this->getNewHeight(); // test 4/3; 1.333
		
		if ($newAR > $srcAR) {
			$this->setNewWidth(round($this->getNewHeight() * $this->getSrcWidth() / $this->getSrcHeight()));
			
			switch ($hAlign) {
				case self::ALIGN_CENTER:
					$this->setNewOffsetLeft(abs($width - $this->getNewWidth()) / 2);
					break;
				case self::ALIGN_RIGHT:
					$this->setNewOffsetLeft(abs($width - $this->getNewWidth()));
					break;
				case self::ALIGN_LEFT:
					$this->setNewOffsetLeft(0);
					break;
				default:
					$this->setNewOffsetLeft($hAlign);
					break;
			}
		} else if ($newAR < $srcAR) {
			$this->setNewHeight(round($this->getNewWidth() * $this->getSrcHeight() / $this->getSrcWidth()));
			
			switch ($vAlign) {
				case self::ALIGN_CENTER:
					$this->setNewOffsetTop(abs($height - $this->getNewHeight()) / 2);
					break;
				case self::ALIGN_BOTTOM:
					$this->setNewOffsetTop(abs($height - $this->getNewHeight()));
					break;
				case self::ALIGN_TOP:
					$this->setNewOffsetTop(0);
					break;
				default:
					$this->setNewOffsetTop($vAlign);
					break;
			}
		}
		
		imagecopyresampled(
			$resource,
			$this->_resource,
			$this->getNewOffsetLeft(),
			$this->getNewOffsetTop(),
			$this->getOffsetLeft(),
			$this->getOffsetTop(),
			$this->getNewWidth(),
			$this->getNewHeight(),
			$this->getWidth(),
			$this->getHeight()
		);
		
		$this->_resource = $resource;
		$this->save($savePath);
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Resize by crop to fill dimensions
	 * 
	 * @param integer $width
	 * @param integer $height
	 * @param integer $hAlign   Optional horisontal align
	 * @param integer $vAlign   Optional vertical align
	 * @param string  $savePath Optional savepath
	 */
	public function resizeToCrop($width, $height, $hAlign = null, $vAlign = null, $savePath = null)
	{
		
		$this->setNewWidth($width);
		$this->setNewHeight($height);
		
		if (null === $hAlign) {
			$hAlign = self::ALIGN_CENTER;
		}
		
		if (null === $vAlign) {
			$vAlign = self::ALIGN_CENTER;
		}

		if (!$this->isNeedResizeSmallerThan()) {
			return $this;
		}
		
		
		if (null !== $savePath) {
			$this->setSavePath($savePath);
		}
		
		$this->setMethod(array('name' => __FUNCTION__, $width, $height));
		
		if (is_file($this->getSavePath()) && filemtime($this->getSavePath()) <= $this->_lifeTime) {
			$this->reset();
			return $this;
		}
		
		$resource = $this->createBlank();
		
		// AR > 1: horisontal; AR < 1: vertical;
		$srcAR = $this->getSrcWidth() / $this->getSrcHeight(); // test 3/2; 1.5
		$newAR = $this->getNewWidth() / $this->getNewHeight(); // test 4/3; 1.333
		
		if ($newAR < $srcAR) {
			$this->setNewWidth(round($this->getNewHeight() * $this->getSrcWidth() / $this->getSrcHeight()));
			
			switch ($hAlign) {
				case self::ALIGN_CENTER:
					$this->setOffsetLeft(abs($width - $this->getNewWidth()) / 2);
					break;
				case self::ALIGN_RIGHT:
					$this->setOffsetLeft(abs($width - $this->getNewWidth()));
					break;
				case self::ALIGN_LEFT:
					$this->setOffsetLeft(0);
					break;
				default:
					$this->setOffsetLeft($hAlign);
					break;
			}
		} else if ($newAR >= $srcAR) {
			$this->setNewHeight(round($this->getNewWidth() * $this->getSrcHeight() / $this->getSrcWidth()));
			
			switch ($vAlign) {
				case self::ALIGN_CENTER:
					$this->setOffsetTop(abs($height - $this->getNewHeight()) / 2);
					break;
				case self::ALIGN_BOTTOM:
					$this->setOffsetTop(abs($height - $this->getNewHeight()));
					break;
				case self::ALIGN_TOP:
					$this->setOffsetTop(0);
					break;
				default:
					$this->setOffsetTop($vAlign);
					break;
			}
		}
		
		imagecopyresampled(
			$resource,
			$this->_resource,
			$this->getNewOffsetLeft(),
			$this->getNewOffsetTop(),
			$this->getOffsetLeft(),
			$this->getOffsetTop(),
			$this->getNewWidth(),
			$this->getNewHeight(),
			$this->getWidth(),
			$this->getHeight()
		);
		
		$this->_resource = $resource;
		$this->save($savePath);
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Resize to long side value
	 * 
	 * @param integer $size     Long side dimension
	 * @param string  $savePath Optional savepath
	 */
	public function resizeToLongSide($size, $savePath = null)
	{
		if ($this->getSrcWidth() > $this->getSrcHeight()) {
			$this->setNewWidth($size);
			$this->setNewHeight(round($this->getNewWidth() * $this->getSrcHeight() / $this->getSrcWidth()));
		} else {
			$this->setNewHeight($size);
			$this->setNewWidth(round($this->getNewHeight() * $this->getSrcWidth() / $this->getSrcHeight()));
		}

		if (!$this->isNeedResizeSmallerThan()) {
			return $this;
		}
		
		$this->setMethod(array('name' => __FUNCTION__, $size));
		$this->resize(null, null, $savePath);
		return $this;
	}
	
	/**
	 * Resize to short side value
	 * 
	 * @param integer $size     Long side dimension
	 * @param string  $savePath Optional savepath
	 */
	public function resizeToShortSide($size, $savePath = null)
	{
		if ($this->getSrcWidth() > $this->getSrcHeight()) {
			$this->setNewHeight($size);
			$this->setNewWidth(round($this->getNewHeight() * $this->getSrcWidth() / $this->getSrcHeight()));
		} else {
			$this->setNewWidth($size);
			$this->setNewHeight(round($this->getNewWidth() * $this->getSrcHeight() / $this->getSrcWidth()));
		}

		if (!$this->isNeedResizeSmallerThan()) {
			return $this;
		}
		
		$this->setMethod(array('name' => __FUNCTION__, $size));
		$this->resize(null, null, $savePath);
		return $this;
	}
	
	/**
	 * Resize to fit bounds dimension
	 * 
	 * @param integer $width
	 * @param integer $height
	 * @param string  $savePath Optional savepath
	 */
	public function resizeToFitBounds($width, $height, $savePath = null)
	{
		$this->setNewWidth($width);
		$this->setNewHeight($height);
		
		// AR > 1: horisontal; AR < 1: vertical;
		$srcAR = $this->getSrcWidth() / $this->getSrcHeight(); // test 3/2; 1.5
		$newAR = $this->getNewWidth() / $this->getNewHeight(); // test 4/3; 1.333
		
		if ($newAR < $srcAR) {
			$this->setNewHeight(round($this->getNewWidth() * $this->getSrcHeight() / $this->getSrcWidth()));
		} else if ($newAR > $srcAR) {
			$this->setNewWidth(round($this->getNewHeight() * $this->getSrcWidth() / $this->getSrcHeight()));
		}

		if (!$this->isNeedResizeSmallerThan()) {
			return $this;
		}
		
		$this->setMethod(array('name' => __FUNCTION__, $width, $height));
		$this->resize(null, null, $savePath);
		return $this;
	}
}