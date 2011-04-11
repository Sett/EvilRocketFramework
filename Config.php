<?php
/**
 * Evil_Config_Json - implements parents and ini extends notations on json
 *
 * Created by JetBrains PhpStorm.
 * @author Alexander M Artamonov <a2m@ruimperium.ru>
 * @date 08.04.11
 * @time 14:16
 * @todo ini extends
 */
 
class Evil_Config extends Zend_Config
{
    protected $_separator = '.';
    protected $_sectionSeparator = ':';

    public function __construct($config = null, $type='array', $allowModifications = true)
    {
        parent::__construct(array(), $allowModifications);
        if (!is_null($config)) {
            $this->append($config, $type);
        }
    }

    /**
     * Merge configs
     *
     * @param  mixed $config
     * @param string $type
     * @return Evil_Config
     */
    public function append($config, $type = 'array')
    {
        switch ($type) {
            case 'array':
                $config = new Zend_Config($config);
                ///and then merge
            case 'Zend_Config':
                $this->merge($config);
                break;
            
            case 'json':
                $this->merge(new Zend_Config_Json($config));
                $this->_extendAsIni();
                break;

            case 'ini':
                $this->merge(new Zend_Config_Ini($config));
                break;
        }
        
        return $this;
    }

    /**
     * Get key by recursive search
     *
     * if $default is an existing class name, the object of this class will be returned
     *
     * @param  string $search
     * @param  mixed $default
     * @param  boolean $load. Try to load class name
     * @return mixed
     */
    public function getKey($search, $default = '', $load = false)
    {
        $separator = empty($this->_separator)
                ? '.'
                : $this->_separator;

        $keys = explode($separator, $search);

        $mess = $this->_messageWalker($this, $keys);

        ///TODO возможно не только создание класса но и вызов к-л. функционального обработчика
        
        if(empty($mess)) {
            $result = class_exists((string)$default, $load) ? new $default : $default;
        } else {
            $result = $mess instanceof Zend_Config
                    ? $mess->toArray()
                    : $mess;
        }

        return
                class_exists((string)$default, $load)
                        ? new $default($result, true)
                        : $result;
    }

    /**
     * Change separator to explode getKey $search
     *
     * @param  $separator
     * @return Evil_Config_Json
     */
    public function setSeparator($separator)
    {
        if (is_string($separator)) {
            $this->_separator = $separator;
        }

        return $this;
    }

    public function toArray()
    {
        return parent::toArray();
    }

    /**
     * Get message from messages array by recursive search into deeper arrays
     *
     * @param  array $messages
     * @param  array $keys
     * @param  int $index
     * @return mixed
     */
    protected function _messageWalker($messages, $keys, $index = 0)
    {
        if (empty($messages->$keys[$index]) ||
            count($keys)<= $index) {
            return '';
        }

        if(count($keys)-1 == $index)
        {
            return
                    $messages instanceof Zend_Config
                            ? $messages->$keys[$index]
                            : $messages;
        }

        return $this->_messageWalker($messages->$keys[$index], $keys, $index+1);
    }

    /**
     * Parse config to find extend ini notation
     *
     * @return void
     */
    protected function _extendAsIni()
    {
        foreach ($this->_data as $key => $value) {
            $namespace = explode(':', $key);
            if (count($namespace) >= 2) {
                $nsp0 = trim($namespace[0]);
                $nsp1 = trim($namespace[1]);
                $this->_data[$nsp0] = $value;
                $this->$nsp0->merge($this->$nsp1);
                unset($this->_data[$key]);
            }
        }
        reset($this->_data);
        /*
        $iniArray = array();
        foreach ($this->_data as $key => $data)
        {
            $pieces = explode($this->_sectionSeparator, $key);
            $thisSection = trim($pieces[0]);
            switch (count($pieces)) {
                case 1:
                    $iniArray[$thisSection] = $data;
                    break;

                case 2:
                    $extendedSection = trim($pieces[1]);
                    $iniArray[$thisSection] = array_merge(array(';extends'=>$extendedSection), $data);
                    break;

                default:
                    require_once 'Zend/Config/Exception.php';
                    throw new Zend_Config_Exception("Section '$thisSection' may not extend multiple sections in $filename");
            }
        }
        var_dump($iniArray);*/
    }
}
