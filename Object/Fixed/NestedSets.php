<?php
/**
 * @author Se#
 * @version 0.0.2
 * @description Realize Nested Sets
 * @changeLog
 * 0.0.2 added creating virtual roots, which allows to operate any count of trees at once
 */
class Evil_Object_Fixed_NestedSets extends Evil_Object_Fixed_Required
{
    /**
     * @description where config: selector => function*;
     * function* - name or an array [Class, Method]
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    protected static $_whereConfig = array();

    /**
     * @description required fields
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    protected $_required = array('id'  => 'int',
                                 'rk'  => 'int',
                                 'lk'  => 'int',
                                 'lvl' => 'int',
                                 'type' => 'tinytext');

    /**
     * @description get where-config and call parent::__construct(...)
     * @param string $type table name
     * @param null $id
     * @param string $pathToConfig
     * @author Se#
     * @version 0.0.1
     */
    public function __construct($type, $id = null, $pathToConfig = '')
    {
        $path = empty($pathToConfig) ? __DIR__ . '/nestedSets.json' : $pathToConfig;
        if(is_file($path))
            self::$_whereConfig = json_decode(file_get_contents($path), true);

        return parent::__construct($type, $id);
    }

    /**
     * @description delete a node/branch
     * @param array $data lk, rk
     * @return bool
     * @author Se#
     * @version 0.0.1
     */
    public function erase($data)
    {
        if(!isset($data['lk']) || !isset($data['rk']))
            return false;

        $lk = (int) $data['lk'];
        $rk = (int) $data['rk'];

        // Delete node/branch
        $this->_fixed->delete('(lk>="' . $lk . '")&&(rk<"' . $rk . '")');

        $newRk = $rk-$lk+1;

        // Update parent branch
        $this->update(array('rk' => 'rk-' . $newRk), array('rk>"' . $rk . '"', 'lk<"' . $lk . '"'));
        // Update further nodes
        $this->update(array('lk' => 'lk-' . $newRk, 'rk' => 'rk-' . $newRk), 'lk>"' . $lk . '"');

        return true;
    }

    /**
     * @description create a new node
     * @param array $data
     * @return bool|int
     * @author Se#
     * @version 0.0.1
     */
    public function create($data)
    {
        if(isset($data['parentId']))
        {
            $parentNode = $this->load($data['parentId'])->_data;
            unset($data['parentId']);
            if(empty($parentNode))
                 return false;

            $parentRK = isset($parentNode[0]['rk']) ? $parentNode[0]['rk'] : null;
            $data['lvl'] = isset($parentNode[0]['lvl']) ? $parentNode[0]['lvl']+1 : 0;
        }
        else
            $parentRK = isset($data['rk']) ? $data['rk'] : null;


        if(empty($parentRK) || empty($data['lvl']))
            return $this->createTree($data); // means we create new tree

        $parentRK = (int) $parentRK;

        // Update nodes that are above parent branch
        $this->update(array('lk' => 'lk+2', 'rk' => 'rk+2'), 'lk > ' . $parentRK);
        // Update parent branch
        $this->update(array('rk' => 'rk+2'), array('rk>=' . $parentRK, 'lk<=' . $parentRK));

        // Add new node
        $requiredData = array('lk' => $parentRK, 'rk' => $parentRK+1);
        $data['lvl'] + 1;// inc level
        $resultData = $requiredData + $data;// merge required data and dynamic data

        $this->_fixed->insert($resultData);

        return Zend_Registry::get('db')->lastInsertId();
    }


    /**
     * @description create a new tree
     * @param array $data
     * @return int
     * @author Se#
     * @version 0.0.1
     */
    public function createTree($data)
    {
        // TODO: make possible creating several trees in one table

        $root = $this->_fixed->fetchRow($this->_fixed->select()->where('type="root"'));

        if($root)
            $data['parentId'] = $this->createVirtual($root);
        else // initialize global tree
            $this->_fixed->insert(array('lk' => 1, 'rk' => 2, 'lvl' => 1, 'type' => 'root'));

        return $this->create($data);
    }

    /**
     * @description create virtual root
     * @param $root
     * @return bool|int
     * @author Se#
     * @version 0.0.1
     */
    public function createVirtual($root)
    {
        $root = is_object($root) ? $root->toArray() : $root;
        $data['parentId'] = isset($root['id']) ? $root['id'] : 0;
        $data['type']     = 'virtual';
        return $this->create($data);
    }

    /**
     * @description update row
     * @param array $data
     * @param array|string $where
     * @return Evil_Object_Fixed_NestedSets
     * @author Se#
     * @version 0.0.1
     */
    public function update($data, $where)
    {
        foreach($data as $field => $value)
            $data[$field] = new Zend_Db_Expr($value);// to allow rightKey = rightKey + 2

        $this->_fixed->update($data, $where);

        return $this;
    }

    /**
     * @description general where
     * @param string|array $key
     * @param string|array $selector
     * @param string|array $value
     * @return Evil_Object_Fixed_NestedSets|mixed
     * @author Se#
     * @version 0.0.1
     */
    public function where($key, $selector, $value = null)
    {
        if(is_array($key))
        {
            if(!is_array($selector) || !is_array($value))
                return $this;

            $count = count($key);
            for($i = 0; $i < $count; $i++)
            {
                if(isset($key[$i]) && isset($selector[$i]) && isset($value[$i]))
                    $this->where($key[$i], $selector[$i], $value[$i]);
                else
                    return $this;
            }
        }

        if(!is_string($key) || !is_string($selector))
            return $this;

        $mask = isset(self::$_whereConfig['mask']) ?
                self::$_whereConfig['mask'] :
                array(':' => 'whereIn');

        if(isset($mask[$selector]) && method_exists($this, $mask[$selector]))
            return call_user_func_array(array($this, $mask[$selector]), array($key, $selector, $value));

        if(isset(self::$_whereConfig[$selector]))
            return $this->runWhere(self::$_whereConfig[$selector], array($this->_fixed->select(), $key, $selector, $value));

        return $this->simpleWhere($key, $selector, $value);
    }

    /**
     * @description run optional function
     * @param array|string $function
     * @param array $args array(this->_fixed->select(), key, selector, value)
     * @param bool $useClass
     * @param string $defaultClass
     * @return mixed
     * @author Se#
     * @version 0.0.1
     */
    public function runWhere($function, $args, $useClass = true, $defaultClass = '')
    {
        $class = null;

        if(is_string($function))
        {
            if($useClass)
                $class = empty($defaultClass) ? $this : $defaultClass;

            $method = $function;
        }
        else
            list($class, $method) = $function;

        return empty($class) ?
                call_user_func_array($method, $args) :
                call_user_func_array(array($class, $method), $args);
    }

    /**
     * @description where key IN (value)
     * @param string $key
     * @param mixed $selector
     * @param string|array $value
     * @return Evil_Object_Fixed_NestedSets
     * @author Se#
     * @version 0.0.1
     */
    public function whereIn($key, $selector, $value)
    {
        $value = is_array($value) ? $value : array($value);
        $this->_fixed->select()->where($key . ' IN ("' . implode('","', $value) . '")');
        return $this;
    }

    /**
     * @description simple where key selector value
     * @param string $key
     * @param string $selector
     * @param mixed $value
     * @return Evil_Object_Fixed_NestedSets
     * @author Se#
     * @version 0.0.1
     */
    public function simpleWhere($key, $selector, $value)
    {
        $this->_fixed->select()->where($key . $selector . '?', $value);
        return $this;
    }

    /**
     * @description load node/branch
     * @throws Exception
     * @param null|string $id
     * @param null|string $lk
     * @param null|string $rk
     * @param null|array where some additional where clause
     * @return Evil_Object_Fixed_NestedSets
     * @author Se#
     * @version 0.0.2
     */
    public function load ($id = null, $lk = null, $rk = null, $where = null)
    {
        if (null !== $id)
            $this->_id = $id;
        else
        {
            $select = $this->_fixed->select()->order('lk ASC');
            $select = $this->_applyWhere($select, $where);
            return $this->_fixed->fetchAll($select);
        }

        if(empty($lk) || empty($rk))
        {
            // get node info
            parent::load($id);
            $this->_loaded = false;
            $node = $this->_data;

            if(isset($node['lk']) && isset($node['rk']))
            {
                $lk = $node['lk'];
                $rk = $node['rk'];
            }
            else
                throw new Exception(' Unknown node "' . $id . '"');
        }

        $select = $this->_fixed->select()->where('lk>=?', $lk)->where('rk<=?', $rk)->order('lk ASC');

        
        $this->_data = $this->_fixed->fetchAll($select);
        $this->_data = is_object($this->_data) ? $this->_data->toArray() : $this->_data;

        return $this;
    }

    protected function _applyWhere($select, $where)
    {
        if(!empty($where))
        {
            if(is_array($where))
            {
                foreach($where as $clause => $value)
                    $select->where($clause, $value);
            }
            else
                $select->where($where, null);
        }

        return $select;
    }

    public function getCount($id)
    {

    }
}