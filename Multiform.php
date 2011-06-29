<?php

class Evil_Multiform extends Zend_Form
{
    private static $_instanceCounter = 0;

    public static $_idParam = 'formid';
    
    public function __construct($options = null)
    {
        parent:: __construct($options);

        self::$_instanceCounter++;
        $idEl = new Zend_Form_Element_Hidden(self::$_idParam);
        $idEl->setRequired(false);
        $idEl->setValue($this->_getFormid());
        $this->setAttrib('id', $this->_getFormid());
        $this->addElement($idEl);
    }
    
    protected function _getFormid()
    {
    	return sprintf('form-%s-instance-%d', $this->_getFormType(), self::$_instanceCounter);
    }

    protected function  _getFormType()
    {
        return get_class($this);
    }
    
    public function isValid($data)
    {
    	if(isset($data[self::$_idParam]) && $data[self::$_idParam] == $this->_getFormid() )
    	{
    		//unset($data[self::$_idParam]);
    		return parent::isValid($data);
    		
    	} else 
    	{
    		//ничо
    		return false;
    	}
    }
}