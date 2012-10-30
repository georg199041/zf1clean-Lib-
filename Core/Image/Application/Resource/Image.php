<?php

require_once 'Zend/Application/Resource/ResourceAbstract.php';

class Core_Image_Application_Resource_Image extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
    	$options = $this->getOptions();
    	
    	$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		if (null === $viewRenderer->view) {
			$viewRenderer->initView();
		}
		
		$viewRenderer->view->addHelperPath('Core/Image/View/Helper', 'Core_Image_View_Helper');
		
		if (isset($options['noImagePath'])) {
			require_once "Core/Image/Factory.php";
			Core_Image_Factory::setNoImagePath($options['noImagePath']);
		}
		
        return $this;
    }
}
