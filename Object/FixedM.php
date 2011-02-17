<?php
/**
 * более удобный Where для Evil_Object_Fixed
 * @author nur
 * 
 * obj->where('validTill','>=' ,time())
                      ->where('id','>',0)
                      ->where('1','=','1')
                      ->load();
 *
 */
class Evil_Object_FixedM extends Evil_Object_Fixed
{
    /**
     * List of fixed table keys
     * @var <array>
     */
    private $_fixedschema = array();
    private $_fixed = null;
    private $_select = null;
    
    public function __construct ($type, $id = null)
    {
        $this->_type = $type;
        $this->_fixed = new Zend_Db_Table(Evil_DB::scope2table($type));
        $this->_select = new Zend_Db_Table_Select($this->_fixed);
        $info = $this->_fixed->info();
        $this->_info = $info;
        $this->_fixedschema = $info['cols'];
        if (null !== $id)
            $this->load($id);
        return true;
    }
    public function where ($key, $selector, $value = null)
    {
        switch ($selector) {
            case '=':
            case '<':
            case '>':
            case '<=':
            case '>=':
                $this->_select->where($key . ' ' . $selector . '?', $value);
                break;
            default:
                throw new Exception('Unknown selector ' . $selector);
            break;
        }
        return $this;
    }
    public function load ($id = null)
    {
        if ($this->_loaded)
            return true;
        if (null !== $id)
        {
            self::where('id', '=', $id);
            $this->_id = $id;
        }
        $this->_data = array();
        $data = $this->_fixed->fetchRow($this->_select);
        if ( null !== $data ) {
            $this->_data = $data->toArray();
        } else
            return false;
        $this->_loaded = true;
        return true;
    }
    
    public function loadAll ($id = null)
    {
        if ($this->_loaded)
            return true;
        if (null !== $id)
        {
            self::where('id', '=', $id);
            $this->_id = $id;
        }
        $this->_data = array();
        $data = $this->_fixed->fetchAll($this->_select);
        if (! empty($data)) {
            $this->_data = $data->toArray();;
        } else
            return false;
        $this->_loaded = true;
        return true;
    }
    
    public function create ($id, $data)
    {
        $this->_id = $id;
        $fixedvalues = array('id' => $id);
        foreach ($data as $key => $value)
            if (in_array($key, $this->_fixedschema))
                $fixedvalues[$key] = $value;
            else
                $this->addNode($key, $value);
        $this->_fixed->insert($fixedvalues);
        return $this;
    }
}