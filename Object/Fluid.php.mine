<?php
/**
 * @author BreathLess
 * @name Evil_Object_3D
 * @description: 3D implementation of ORM
 * @package Evil
 * @subpackage ORM
 * @version 0.1
 * @date 24.10.10
 * @time 12:43
 */
class Evil_Object_Fluid extends Evil_Object_Base implements 
Evil_Object_Interface
{
    /**
     * List of fluid table keys
     * @var <array>
     */
    private $_fluidschema = array();
    private $_fluid = null;
    public function __construct ($type, $id = null)
    {
        $this->type = $type;
        $this->_fluid = new Zend_Db_Table(Evil_DB::scope2table($type));
        if (null !== $id)
            $this->load($id);
        return true;
    }
    public function where ($key, $selector, $value = null)
    {
        switch ($selector) {
            case '=':
                $data = $this->_fluid->fetchRow(
                $this->_fluid->select()
                    ->where('K = ?', $key)
                    ->where('V = ?', $value));
                break;
            default:
                throw new Exception('Unknown selector ' . $selector);
                break;
        }
        if (empty($data))
            return null;
        else {
            $data = $data->toArray();
            return $this->_id = $data['id'];
        }
    }
    public function create ($id, $data)
    {
        $this->_id = $id;
        foreach ($data as $key => $value)
            $this->addNode($key, $value);
        return $this;
    }
    public function addNode ($key, $value = null)
    {
        if (is_array($key) and ($value === null))
            foreach ($key as $k => $v)
                $this->addNode($k, $v);
        else
            $this->_fluid->insert(
            array('i' => $this->_id, 'k' => $key, 'v' => $value));
        return $this;
    }
    public function delNode ($key, $value = null)
    {
        if (null !== $value and ! empty($value))
            $this->_fluid->delete(
            $this->_fluid->getAdapter()
                ->quoteInto(array('i = ?', 'k = ?', 'v = ?'), 
            array($this->_id, $key, $value)));
        else
            $this->_fluid->delete(
            $this->_fluid->getAdapter()
                ->quoteInto(array('i = ?', 'k = ?'), array($this->_id, $key)));
        return $this;
    }
    public function setNode ($key, $value, $oldvalue = null)
    {
        if (null !== $oldvalue and ! empty($oldvalue)) {
            if (in_array($oldvalue, $this->_data[$key]))
                $this->_fluid->update(array('k' => $key, 'v' => $value), 
                array('i = "' . $this->_id . '"', 'k = "' . $key . '"', 
                'v = "' . $oldvalue . '"'));
        } else {
            if (isset($this->_data[$key]))
                $this->_fluid->update(array('k' => $key, 'v' => $value), 
                array('i = "' . $this->_id . '"', 'k = "' . $key . '"'));
            else
                $this->addNode($key, $value);
        }
        return $this;
    }
    public function incNode ($key, $increment)
    {
        if (isset($this->_data[$key]))
            return $this->setNode($key, $this->_data[$key][0] + $increment);
        else
            return $this->addNode($key, $increment);
    }
    public function load ($id = null)
    {
        if (null !== $id)
            $this->_id = $id;
        $this->_data = array();
        $fluidrows = $this->_fluid->fetchAll('i = "' . $this->_id . '"')->toArray();
        if ($fluidrows) {
            foreach ($fluidrows as $row) {
                unset($row['u']);
                //$this->_fluidnodes[$row['k']] = $row['k'];
                $this->_data[$row['k']][] = $row['v'];
            }
        } else
            return false;
        return true;
    }
	/* (non-PHPdoc)
	 * @see Evil_Object_Interface::erase()
	 */
	public function erase() {
		// TODO Auto-generated method stub
		
	}

        
        /*public function erase()
        {
        	return $this;	
        }*/
    }