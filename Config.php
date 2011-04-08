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

    public function __construct(array $array, $allowModifications = true)
    {
        parent::__construct($array, $allowModifications);
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
                //and then merge
            case 'Zend_Config':
                $this->merge($config);
                break;
            
            case 'json':
                $this->merge(new Zend_Config_Json($config));
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
     * @param  $search
     * @param string $default
     * @param null $messages
     * @return mixed
     */
    public function getKey($search, $default = '')
    {
        $separator = empty($this->_separator)
                ? '.'
                : $this->_separator;

        $keys = explode($separator, $search);

        $mess = $this->_messageWalker($this, $keys);

        if(empty($mess)) {
            $mess = $default;
        }

        return
                $mess instanceof Zend_Config
                        ? $mess->toArray()
                        : $mess;
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
}
