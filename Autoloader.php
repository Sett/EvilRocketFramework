<?php
 /**
  * 
  * @description Плугин для автоматической подргурзки ресурсов, на данный момент по умолчанию грузит js и css
  * @author nur, Se#
  * @version 0.0.2
  * @changeLog
  * 0.0.2 Upgrading to load any other resources
  * @example
  *autoload.citizen.js[] = '/js/own/different.js'
  *autoload.index.js[] = '/js/own/c_new_order.js'
  */
class Evil_Autoloader extends Zend_Controller_Plugin_Abstract
{
    /**
     * @description config key
     * @var string
     * @author nur
     * @version 0.0.1
     */
    protected $_configKey = 'autoload';

    /**
     * @description append needed resources
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @author nur, Se#
     * @version 0.0.2
     */
    public function postDispatch (Zend_Controller_Request_Abstract $request)
    {
        $controller = $request->getControllerName();
        $config     = Zend_Registry::get('config');
        $resources  = isset($config[$this->_configKey]['res']) ? $config[$this->_configKey]['res'] : array('js', 'css');

        $params = isset($config[$this->_configKey][$controller]) ? $config[$this->_configKey][$controller] : null;
        if (null !== $params)
        {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            if (null === $viewRenderer->view)
                $viewRenderer->initView();

            $view = $viewRenderer->view;
            foreach($resources as $res => $method)
            {
                if(method_exists($this, $method) && isset($params[$method]))
                    $this->$method($params[$method], $view);
            }
        }
    }

    /**
     * @description append JS
     * @param array $jsArray
     * @param Zend_View $view
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function js(array $jsArray, $view)
    {
        foreach ($jsArray as $js)
            $view->headScript()->appendFile($js);
    }

    /**
     * @description append css
     * @param array $cssArray
     * @param Zend_View $view
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function css(array $cssArray, $view)
    {
        foreach ($cssArray as $css)
            $view->headLink()->appendStylesheet($css);
    }
}
    
