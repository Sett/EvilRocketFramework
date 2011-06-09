<?php
/**
 * @description Class for working with TatFondBank
 * @author Se#
 * @version 0.0.1
 */
class Evil_Payment_Tfb extends Evil_Payment implements Evil_Payment_Interface
{
    /**
     * @description payment form for user
     * @return Zend_Form|null
     * @author Se#
     * @version 0.0.2
     */
    public function getForm()
    {
        $path = isset($this->_config['pathToFormConfig']) ?
                $this->_config['pathToFormConfig'] :
                __DIR__ . '/Tfb/application/configs/form.json';

        if(is_file($path))
            return new Zend_Form(json_decode(file_get_contents($path), true));

        return null;
    }

    /**
     * @description send request to the TFB
     * @return bool
     * @author Se#
     * @version 0.0.2
     */
    public function sendRequest($args = array())
    {
        $method = '_send' . ucfirst($this->_config['method']) . 'Request';
        $requestConfig = isset($this->_config[$this->_config['method']]) ?
                $this->_config[$this->_config['method']] :
                array();

        if(isset($this->_config['method']) && method_exists($this, $method))
            return call_user_func_array(array($this, $method), array($args, $requestConfig));

        return $this->_sendRestRequest($args, $requestConfig);
    }

    /**
     * @description send REST request
     * @throws Exception
     * @return string
     * @author Se#
     * @version 0.0.3
     */
    protected function _sendRestRequest($args, $config)
    {
        if(!isset($args['action']) && !isset($config['default']['action']))
            throw new Exception(' Missed "action" in the arguments and configuration');

        $do = isset($args['action']) ? $args['action'] : $config['default']['action'];

        $doConfig = isset($config['actions'][$do]) ? $config['actions'][$do] : array();
        $default  = isset($config['default']) ? $config['default'] : array();

        $url      = $this->_getUrl($args, $doConfig, $default) ;
        $required = $this->_getRequired($args, $doConfig, $default);
        $data     = isset($args['data']) ? $args['data'] : array();
        $data     = $this->_checkDataByRequired($data, $required);
        $client   = new Zend_Http_Client($url);
        echo '<pre>';
        print_r($data);
        foreach($data as $name => $value)
            $client->setParameterPost($name, $value);
        
        $result   = $client->request('POST')->getRawBody();

        return $result;
    }

    /**
     * @description append needed scripts, etc.
     * @param Zend_Controller_Action $controller
     * @return Zend_Controller_Action
     * @author Se#
     * @version 0.0.1
     */
    public function prepareController(Zend_Controller_Action $controller)
    {
        //<script type="text/javascript" src="../../js/jquery.payment.js"></script>

        // jQuery user interface
        $controller->view->headScript()->appendFile($controller->view->baseUrl() . '/js/jquery-ui-1.8.2.custom.min.js');
        $controller->view->headScript()->appendFile($controller->view->baseUrl() . '/js/jquery/jquery.timers-1.2.js');
        $controller->view->headScript()->appendFile($controller->view->baseUrl() . '/js/jquery/jquery.url.js');
        
        // jQuery lightness user interface css
        $controller->view->headLink()->appendStylesheet('http://jquery-ui.googlecode.com/svn/tags/1.7.2/themes/ui-lightness/jquery-ui.css');

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
    public function render(Zend_Controller_Action $controller, $personal = 'payment/tfb.phtml')
    {
        if(!is_file(APPLICATION_PATH . '/views/scripts/' . $personal))
        {
            $default = isset($this->_config['defaultViewName']) ? $this->_config['defaultViewName'] : 'index.phtml';

            $controller->getHelper('viewRenderer')->setNoRender(); // turn off native (personal) view
            $controller->view->addScriptPath(__DIR__ . '/Tfb/application/views/scripts/');// add current folder to the view path
            $controller->view->form = $this->getForm();
            $controller->getHelper('viewRenderer')->renderScript($default);// render
        }
    }
}