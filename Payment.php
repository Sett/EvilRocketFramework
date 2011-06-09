<?php
/**
 * @description basic Payment class
 * @author Se#
 * @version 0.0.1
 */
abstract class Evil_Payment implements Evil_Payment_Interface
{
    /**
     * @description payment config
     * @var array|mixed
     * @author Se#
     * @version 0.0.1
     */
    protected $_config = array();

    /**
     * @description request object or data
     * @var null
     * @author Se#
     * @version 0.0.1
     */
    protected $_request = null;

    /**
     * @description payment form for user
     * @var null
     * @author Se#
     * @version 0.0.1
     */
    protected $_form = null;

    /**
     * @description Constructor
     * @param string|array $pathToConfig
     * @author Se#
     * @version 0.0.1
     */
    public function __construct($pathToConfig = '')
    {
        if(is_array($pathToConfig) && !empty($pathToConfig))
        {
            $this->_config = $pathToConfig;
            return true;
        }

        $realPath = is_file($pathToConfig) ? $pathToConfig : __DIR__ . '/Payment/configs/' . get_class($this). '.json';
        $this->_config = json_decode(file_get_contents($realPath), true);
    }

    /**
     * @description basic payment form
     * @return null
     * @author Se#
     * @version 0.0.1
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * @description basic request sending
     * @return null
     * @author Se#
     * @version 0.0.1
     */
    public function sendRequest()
    {
        return $this->_request;
    }

    /**
     * @description append scripts, etc
     * @abstract
     * @param Zend_Controller_Action $controller
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function prepareController(Zend_Controller_Action $controller)
    {
        return $controller;
    }

    /**
     * @description render view
     * @abstract
     * @param Zend_Controller_Action $controller
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function render(Zend_Controller_Action $controller)
    {
        return $controller;
    }

    /**
     * @description fire several methods with the same args
     * @param string|array $methodList
     * @param array $args
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function fire($methodList, $args)
    {
        $methodList = is_array($methodList) ? $methodList : array($methodList);
        $count = count($methodList);

        for($i = 0; $i < $count; $i++)
        {
            if(method_exists($this, $methodList[$i]))
                call_user_func_array(array($this, $methodList[$i]), $args);
        }
    }

    /**
     * @param Zend_Http_Client $client
     * @param array $data
     * @return Zend_Http_Client
     * @author Se#
     * @version 0.0.1
     */
    protected function _setClientData(Zend_Http_CLient $client, $data)
    {
        foreach($data as $name => $value)
            $client->setParameterPost($name, $value);

        return $client;
    }

    /**
     * @description check data for the required fields
     * @throws Exception
     * @param array $data
     * @param array $required
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _checkDataByRequired($data, $required)
    {
        $diff = array_diff_key($data, $required);

        if(!empty($diff))
            throw new Exception(' Missed some required parameters ');

        /*
         * TODO: realize type and other checking
         */

        return $data;
    }

    /**
     * @description get required fields for request
     * @param array $args passed parameters
     * @param array $config personal configuration
     * @param array $default configuration
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _getRequired($args, $config, $default = array())
    {
        $argsRequired    = isset($args['required'])    ? $args['required']    : array();
        $configRequired  = isset($config['required'])  ? $config['required']  : array();
        $defaultRequired = isset($default['required']) ? $default['required'] : array();

        return $argsRequired + $configRequired + $defaultRequired;
    }

    /**
     * @description get url for a request
     * @param array $args passed parameters
     * @param array $config personal do-configuration
     * @param array $default configuration
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    protected function _getUrl($args, $config, $default = array())
    {
        $base = isset($args['base']) ? // if passed through the arguments
                    $args['base'] :
                    (isset($config['url']) ? // if set in the personal do-config
                            $config['url'] :
                            (isset($default['base']) ? // if set default url
                                    $default['base'] :
                                    ''));

        $url = isset($args['url']) ? // if passed through the arguments
                $args['url'] :
                (isset($config['url']) ? // if set in the personal do-config
                        $config['url'] :
                        (isset($default['url']) ? // if set default url
                                $default['url'] :
                                ''));

        return $base . $url;
    }
}