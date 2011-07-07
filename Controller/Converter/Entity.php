<?php
/**
 * Evil_Controller_Converter_Entity
 *
 * Presents some multidimentional array as simple arrays. To explain fast access by index
 *
 * Created by JetBrains PhpStorm.
 * @author Alexander M Artamonov <art.alex.m@gmail.com>
 * @type Object
 * @package Evil
 * @subpackage Core
 * @version 0.1
 * @date 21.06.11
 * @time 12:11
 */
 
class Evil_Controller_Converter_Entity
{
    /**
     * Collection of data as integer index
     *
     * @var array
     */
    protected $_arrays = array();

    /**
     * Collection of data as string key -> integer index of namespace
     *
     * @var array
     */
    protected $_reverse = array();

    /**
     * Unique name for namespace of first keys of array that was given at __construct
     *
     * @var string
     */
    protected $_keysName = '';

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        /**
         * set unique name to store keys
         */
        $this->_keysName = uniqid('ecce');

        /**
         * set keys of array as ['name: string' => [int]]
         */
        $this->_arrays[$this->_keysName] = array_keys($data);

        foreach ($data as $key => $value)
        {
            foreach ($value as $namespace => $v)
            {
                $index = $this->_findIndex($key, $this->_keysName);
                $this->_arrays[$namespace][$index] = $v;
            }
        }
    }

    /**
     * Find values in different namespaces
     *
     * @param string $inName
     * @param string $outNamespace
     * @param string| null $inNamespace
     * @return mixed
     */
    public function find($inName, $outNamespace, $inNamespace = null)
    {
        if (is_null($inNamespace)) $inNamespace = $this->_keysName;
        
        $index = $this->_findIndex($inName, $inNamespace);

        return
                $this->_arrays[$outNamespace][$index];
    }

    /**
     * Find integer index
     *
     * @throws Evil_Exception
     * @param  string $key
     * @param  string $namespace
     * @return int
     */
    protected function _findIndex($key, $namespace)
    {
        if (!isset($this->_reverse[$namespace])) {
            if (TRUE !== ($this->_reverse[$namespace] = array_flip($this->_arrays[$keyName])))
                throw new Evil_Exception('Use wrong type of data \'' . $namespace . '\' to find index');
        }

        return
                isset($this->_reverse[$namespace][$key]) ? $this->_reverse[$namespace][$key] : 0;

    }

    /**
     * To use as function
     *
     * @param  string $inName
     * @param  string $outNamespace
     * @param  null $inNamespace
     * @return mixed
     */
    public function __invoke($inName, $outNamespace, $inNamespace = null)
    {
        return
                $this->find($inName, $outNamespace, $inNamespace);
    }

    /**
     * To avoid changes in index
     *
     * @throws Evil_Exception
     * @param  string $name
     * @param  mixed $value
     * @return bool
     */
    public function __set($name, $value)
    {
        throw new Evil_Exception('Blocked __set to avoid of index corruption');
    }

    /**
     * To avoid changes in index
     *
     * @throws Evil_Exception
     * @param  string $name
     * @return bool
     */
    public function __unset($name)
    {
        throw new Evil_Exception('Blocked __unset to avoid of index corruption');
    }
}
