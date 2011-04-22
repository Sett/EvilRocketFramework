<?php
/**
 * Evil_Object_Readable - Implements only read access to its data after construction
 *
 * Created by JetBrains PhpStorm.
 * @author Alexander M Artamonov <a2m@ruimperium.com>
 * @package Evil
 * @subpackage Evil_Object
 * @version 0.1
 * @date 22.04.11
 * @time 10:22
 */
 
class Evil_Object_Readable 
{
    /**
     * Array to store data
     *
     * @var array|\Evil_Object_Readable
     */
    protected $_data = array();

    /**
     * Some functions to implement on _data before return it
     *
     * @var array|null
     */
    protected $_functions = array();

    /**
     * @param array|null $params
     * @param array|null $functions
     */
    public function __construct(array $params = null, array $functions = null)
    {
        if (!is_null($params)) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $this->_data[$key] = new self($value, $functions);
                }
                $this->_data[$key] = $value;
            }
        }

        if (!is_null($functions)) {
            $this->_functions = $functions;
        }

    }

    /**
     * @param string $function
     * @param array $params
     * @return mixed|null
     */
    public function __call($function, $params = array())
    {
        if (isset($this->_functions[$function])) {
            return call_user_func($this->_functions[$function], $this->_data, $params);
        }
        return null;
    }

    /**
     * @return null
     */
    public function __clone()
    {
        return null;
    }

    /**
     * @param  string $name
     * @return array|Evil_Object_Readable|null
     */
    public function __get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        return null;
    }

    /**
     * @param  string $name
     * @param  mixed $value
     * @return null
     */
    public function __set($name, $value)
    {
        return null;
    }
}
