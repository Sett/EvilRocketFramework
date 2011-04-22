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
     * @var array
     */
    static protected $_functions = array();

    /**
     * Constructor
     *
     * @param array|null $params
     * @param array|null $functions
     */
    public function __construct(array $params = null, array $functions = null)
    {
        if (!is_null($params)) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $this->_data[$key] = new self($value);
                } else
                    $this->_data[$key] = $value;
            }
        }

        if (!is_null($functions)) {
            self::$_functions = $functions;
        }
    }

    /**
     * Implements one point call
     *
     * @param string $function
     * @param mixed $params
     * @return mixed|null
     */
    public function __call($function, $params = array())
    {
        if (isset(self::$_functions[$function]) && is_callable(self::$_functions[$function])) {
            return call_user_func(self::$_functions[$function], $this->_data, $params);
        }
        return null;
    }

    /**
     * Rewrite __clone() magic method
     *
     * @return void
     */
    protected function __clone()
    {}

    /**
     * Rewrite __toString() magic method
     *
     * @return mixed|null
     */
    public function __toString()
    {
        return $this->__call('__toString');
    }

    /**
     * Rewrite __invoke() magic method
     *
     * @param null $params
     * @return mixed|null
     */
    public function __invoke()
    {
        return $this->__call('__invoke', func_get_args());
    }

    /**
     * Rewrite __get() magic method
     *
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
     * Rewrite __set() magic method
     *
     * @param  string $name
     * @param  mixed $value
     * @return null
     */
    public function __set($name, $value)
    {
        return null;
    }

    /**
     * Rewrite __isset() magic method
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * Rewrite __unset() magic method
     *
     * @param  string $name
     * @return
     */
    public function __unset($name)
    {
        return;
    }
}
