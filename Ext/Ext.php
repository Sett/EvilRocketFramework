<?php
/**
 * Пока очень индусский хелпер для экста
 * @author NuR
 * 
 * подгружается так:
 * $view->addHelperPath(APPLICATION_PATH . '/../library/Evil/Ext','Evil_Ext');   
 * 
 * $this->view->Ext();
 */
require_once 'Zend/View/Helper/Abstract.php';
 
class Evil_Ext_Ext extends Zend_View_Helper_Abstract
{
	protected $_path  = '/js/extjs';
	

	
	public function setPath($path)
	{
		$this->_path = $path;
		return $this;
	}
	public function getPath()
	{
			return $this->_path;
	}
	
	public function Ext()
	{
	   $config = Zend_Registry::get('config');
	    $this->_addScripts( 'jquery-1.5.1.min.js','/js/' );
		$scripts = array(
		 '/adapter/jquery/ext-jquery-adapter.js',
		 '/ext-all.js',
		 '/src/locale/ext-lang-ru.js'
        );
        
		$this->_addScripts($scripts);
		
		

		$styles = array(
		'/resources/css/ext-all-notheme.css',
		'/resources/css/opencity.css');
		$this->_addStyles($styles);
		
		return $this;
	}
	
	public function addFilters()
	{

		$scripts = array(
            '/ux/GridFilters.js',
            '/ux/RangeMenu.js',
            '/ux/RangeMenu.js',
            '/ux/ListMenu.js',
            '/ux/Filter.js',
		    '/ux/StringFilter.js',
            '/ux/DateFilter.js',
            '/ux/ListFilter.js',
            '/ux/ListFilter.js',
            '/ux/NumericFilter.js',
            '/ux/BooleanFilter.js'
		);
		$this->_addScripts($scripts);
		
		$styles = array(
		'/resources/css/GridFilters.css',
		'/resources/css/RangeMenu.css');
		$this->_addStyles($styles);
		
		return $this;
	}
	
	public function addFileUploader()
	{
		$this->_addScripts('/ux/FileUploadField.js');
		
		$this->_addStyles('/resources/css/fileuploadfield.css');
		return $this;
		
	}
	
	
	protected function _addStyles($data,$path = false)
	{
		if(is_array($data))
		{
			foreach ($data as $style)
			{
				$this->_headLink()->appendStylesheet((($path)?$path:$this->getPath()) . $style);
			}
		} else 
		{
			$this->_headLink()->appendStylesheet((($path)?$path:$this->getPath())  . $data);
		}
	}
	
	
	protected function _headScript()
	{
	    return $this->view->headScript();    
	}
	
	protected function _headLink()
	{
         return $this->view->headLink();    
	}
	
	protected function _addScripts($data,$path = false)
	{
		if(is_array($data))
		{
			foreach ($data as $script)
			{
				$this->_headScript()->appendFile( (($path)?$path:$this->getPath())  . $script );
			}
		} else 
		
		$this->_headScript()->appendFile( (($path)?$path:$this->getPath())  . $data );
	}
	public function __call($methodName,$params)
	{
		return $this;
	}
}
