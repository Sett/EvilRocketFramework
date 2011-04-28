<?php
 /**
  * 
  * Плугин для автоматической подргурзки ресурсов
  * на данный момент грузит js и css
  * @author nur
  * @example
  *autoload.citizen.js[] = '/js/own/different.js'
  *autoload.index.js[] = '/js/own/c_new_order.js'
  */
class Evil_Autoloader extends Zend_Controller_Plugin_Abstract
{

    protected $_configKey = 'autoload';

    public function postDispatch (Zend_Controller_Request_Abstract $request)
    {
        $controller = $request->getControllerName();
        $config = Zend_Registry::get('config');
        $params = isset($config[$this->_configKey][$controller]) ? $config[$this->_configKey][$controller] : null;
        if (null !== $params) {
            
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            if (null === $viewRenderer->view) {
                $viewRenderer->initView();
            }
            $view = $viewRenderer->view;
            
            $jsArray = isset($params['js']) ? $params['js'] : array();
            foreach ($jsArray as $js) {
                $view->headScript()->appendFile($js);
            }
            
           $cssArray = isset($params['css']) ? $params['css'] : array();
            foreach ($cssArray as $css) {
                $view->headLink()->appendStylesheet($css);
            }
        }
    }
}
    
