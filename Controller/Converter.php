<?php
/**
 * Evil_Controller_Converter
 *
 * Created by JetBrains PhpStorm.
 * @author Alexander M Artamonov <art.alex.m@gmail.com>
 * @type Utility
 * @package Evil
 * @subpackage Core
 * @version 0.1
 * @date 21.06.11
 * @time 16:10
 */
 
class Evil_Controller_Converter
{
    /**
     * Store configurations of transformations
     *
     * @var array
     */
    protected $_entities = array();

    /**
     * Default name of config
     * Use last added config by default
     *
     * @var string
     */
    protected $_defaultEntity = '';

    /**
     * Anabel types to catsTypes()
     *
     * @var array
     */
    protected $_enabledTypes = array('boolean','float','string','integer','array','object','null');

    /**
     * Global PHP variables to get data in getParameter()
     *
     * @var array
     */
    protected $_methods = array('POST', 'GET', 'COOKIE');

    /**
     * Add configuration
     *
     * @param  string $namespace
     * @param  array $data
     * @return Evil_Controller_Converter
     */
    public function addEntity($namespace, array $data)
    {
        $this->_entities[$namespace] = new Evil_Controller_Converter_Entity($data);
        $this->_defaultEntity = $namespace;
        return $this;
    }

    /**
     * Set default namespace
     *
     * @param  string $name
     * @return Evil_Controller_Converter
     */
    public function chooseEntity($name)
    {
        if (isset($this->_entities[$name]))
            $this->_defaultEntity = $name;

        return $this;
    }

    /**
     * Translate given name in namespace
     *
     * @param  string $key
     * @param  string|null $namespace
     * @param  string|null $inNamespace
     * @return array
     */
    public function convert($key, $namespace = null, $inNamespace = null)
    {
        $newKey = $this->_entities[$this->_defaultEntity]->find($key, $namespace, $inNamespace);
        return
                !is_null($newKey)
                        ? $newKey
                        : $key;
    }

    /**
     * Translate all array keys to given namespace
     *
     * @param array $data
     * @param string|null $namespace
     * @param string|null $inNamespace
     * @return array
     */
    public function convertAll(array $data, $namespace = null, $inNamespace = null)
    {
        $newData = array();

        foreach ($data as $key => $value) {
            $key = $this->convert($key, $namespace, $inNamespace);
            $newData[$key] = $value;
        }

        return $newData;
    }

    /**
     * Set the type of a values in given array
     *
     * @throws Evil_Exception
     * @param array $data
     * @param string|null $namespace
     * @return array
     */
    public function transform(array $data, $namespace = null)
    {

        foreach ($data as $key => &$value) {

            $type = $this->_entities[$namespace][$key];

            if (!in_array($type, $this->_enabledTypes))
                /// FIXME: maybe use some magic keys or check class or existing global function
                throw new Evil_Exception('Anknown type to cast \'' . $type . '\'');

            if (!settype($value, $type))
                throw new Evil_Exception('Cannot cast type \'' . $type . '\' to value of key \'' . $key . '\'');
        }

        return $data;
    }

    /**
     * Get all values in globals
     *
     * @param string $method
     * @param null $custom
     * @param null $conversion
     * @param string|null $namespace
     * @return array
     */
    public function getAllParameters($method='', $custom = null, $conversion = null,  $namespace = null)
    {
        if (is_null($namespace))
            $namespace = $this->_defaultEntity;

        $result = array();

        foreach ($this->_entities[$namespace] as $key => $value) {

            $toCast = array();

            if (empty($method)) {

                foreach ($this->_methods as $method)
                    $toCast[] = $this->getParameter($key, $method);

            } else {
                    $toCast[] = $this->getParameter($key, $conversion, $method);
            }

            $tmp = $this->_cast($toCast);

            if (is_null($custom) || (is_callable($custom) && $custom($tmp))) {
                $result[$key] = $tmp;
            }
        }

        return $result;
    }

    /**
     * Get value from globals
     *
     * @param  string $name
     * @param  null $conversion
     * @param  string $method
     * @return mixed
     */
    public function getParameter($name, $conversion = null, $method='POST')
    {
        $storage = '_'. $method;

        if (!is_array($$storage))
            return null;

        if (isset($$storage[$name]))
            return is_callable($conversion)
                    ? $conversion($$storage[$name])
                    : $$storage[$name];

        return null;
    }

    /**
     * Return the fist non empty value
     *
     * @param array $values
     * @return mixed
     */
    protected function _cast(array $values)
    {
        foreach ($values as $v)
            if (!empty($v)) return $v;

        return  null;
    }
}
