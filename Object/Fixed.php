<?php
/**
 * @author BreathLess
 * @name Evil_Object_Fixed
 * @description: Fixed implementation of ORM, classical table interface  
 * @package Evil
 * @subpackage ORM
 * @version 0.1
 * @date 24.10.10
 * @time 12:43
 */
class Evil_Object_Fixed extends Evil_Object_Base implements Evil_Object_Interface
{
    /**
     * List of fixed table keys
     * @var <array>
     */
    protected $_fixedschema = array();
    protected $_fixed = null;

    protected $_loaded = FALSE;
    public function __construct ($type, $id = null,$data = null)
    {
		
		$this->_type = $type;
		$this->_fixed = new Zend_Db_Table ( Evil_DB::scope2table ( $type ) );
		$info = $this->_fixed->info ();
		$this->_info = $info;
		$this->_fixedschema = $info ['cols'];
		if ($data != null) {
			$this->_id = $id;
			$this->_data = $data;
            $this->_loaded = true;
           }
           else
           {
               if (null != $id)
               {
                    $this->load($id);
               }
           }
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
                $data = $this->_fixed->fetchRow($this->_fixed->select()
                    ->where($key . ' ' . $selector . ' ?', $value));
                break;
            default:
                throw new Exception('Unknown selector ' . $selector);
                break;
        }
        if (empty($data))
            return null;
        else {
        	$this->_loaded = true;
            $data = $data->toArray();
            $this->_data = $data;
            return $this->_id = $data['id'];
        }
    }
    
    
    public function create ($id = null, $data)
    {
        $this->_id = $id;
        $fixedvalues = array('id' => $id);
        foreach ($data as $key => $value)
            if (in_array($key, $this->_fixedschema))
                $fixedvalues[$key] = $value;
            else
                $this->addNode($key, $value);
        $id = $this->_fixed->insert($fixedvalues);
        $this->setId($id);
        return $this;
    }

    public function erase ()
    {
       $this->_fixed->delete($this->_fixed->getAdapter()->quoteInto(array('id = ?'), array($this->_id)));
       return  $this;
    }

    public function addNode ($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function delNode ($key, $value = null)
    {
        return $this;
    }

    public function setNode ($key, $value, $oldvalue = null)
    {
    	if ($value != $oldvalue)
    	{
    		$where = $this->_fixed->getAdapter()->quoteInto('id = ?', $this->_id);
        	$this->_fixed->update(array($key => $value), $where);
    	}
        return $this;
    }

    public function incNode ($key, $increment)
    {
        if (isset($this->_data[$key]))
            return $this->setNode($key, $this->_data[$key] + $increment);
        else
            return $this->addNode($key, $increment);
    }

    public function load ($id = null)
    {
        if ($this->_loaded)
            return true;

        if (null !== $id)
            $this->_id = $id;

        $this->_data = array();
        // Find fixed row, and extract data from
        $data = $this->_fixed->find($this->_id)->toArray();

        if (! empty($data))
            $this->_data = $data[0];
        else
            return false;

        $this->_loaded = true;
        return true;
    }

    public function update (array $data, $id = null)
    {
        if (null == $id)
            $id = $this->getId();

        $where = $this->_fixed->getAdapter()->quoteInto('id = ?', $id);

        $filtered = array();

        foreach ($data as $key => $value)
        {
            if (in_array($key, $this->_fixedschema))
                $filtered[$key] = $value;
        }
                    
        $this->_fixed->update($filtered, $where);
        $this->_loaded = false;
        
        return $this;
    }
}