<?php
/**
 * @author Se#
 * @version 0.0.1
 * @description Realize Fixed Object with required fields
 */
class Evil_Object_Fixed_Required extends Evil_Object_Fixed
{
    /**
     * @var array
     * @description required fields
     * @author Se#
     * @version 0.0.1
     */
    protected $_required = array();

    /**
     * @description create an object
     * @throws Exception
     * @param string $type
     * @param null|int $id
     * @author Se#
     * @version 0.0.1
     */
    public function __construct($type, $id = null)
    {
        parent::__construct($type, $id);

        foreach($this->_required as $field => $tp)
        {
            if(!isset($this->_info['metadata'][$field]) || ($tp != $this->_info['metadata'][$field]['DATA_TYPE']))
                throw new Exception('Missed or wrong type required by ' . get_class($this)
                                    . ' field "'.$field.'" in "'.$this->_info['name'] . '"');
        }
    }
}