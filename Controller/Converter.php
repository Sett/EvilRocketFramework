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
 * @time 12:06
 */
 
class Evil_Controller_Converter
{
    protected $_entities = array();

    protected $_defaultEntity = '';

    protected $_enabledTypes = array('boolean','float','string','integer','array','object','null');

    protected $_methods = array('POST', 'GET', 'COOKIE');

    public function addEntity($namespace, array $data)
    {
        $this->_entities[$namespace] = $data;
        $this->_defaultEntity = $namespace;
        return $this;
    }

    public function setDefaultEntity($name)
    {
        if (isset($this->_entities[$name]))
            $this->_defaultEntity = $name;

        return $this;
    }

    public function convert($key, $namespace = null)
    {
        if (is_null($namespace)) $namespace = $this->_defaultEntity;

        return
                isset($this->_entities[$namespace][$key])
                        ? $this->_entities[$namespace][$key]
                        : $key;
    }

    public function convertAll(array $data, $namespace = null)
    {
        if (is_null($namespace)) $namespace = $this->_defaultEntity;

        $newData = array();

        foreach ($data as $key => $value) {
            $newData[$this->convert($key,$namespace)] = $value;
        }

        return $newData;
    }

    public function castType(array $data, $namespace = null)
    {
        if (is_null($namespace)) $namespace = $this->_defaultEntity;

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

    protected function _cast(array $values)
    {
        foreach ($values as $v)
            if (!empty($v)) return $v;

        return  null;
    }
}
