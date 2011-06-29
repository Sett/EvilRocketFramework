<?php

class Evil_Environment extends Zend_Controller_Plugin_Abstract
{
    public static function get($object, $config)
    {
        return Zend_Controller_Front::getInstance()->getPlugin('Evil_Environment')->add($object, $config);
    }

    public function add($object, $args)
    {
        if(is_string($object))
        {
            $method = 'add' . ucfirst($object);
            if(method_exists($this, $method))
                return $this->$method($args);
        }

        return false;
    }

    public function addCss($args)
    {
        if(is_object($args))
        {
            $controller = $args;
            $config     = array();
            $args       = array();
        }
        else
        {
            if(!isset($args['controller']) || !isset($args['config']))
                return false;

            $controller = $args['controller'];
            $config     = $args['config'];
            unset($args['controller']);
        }

        $env = APPLICATION_ENV;

        if(is_file(ROOT . 'public/css/env/' . $env . '.css') && is_file(APPLICATION_PATH . '/views/env/' . $env . '.phtml'))
        {
            $controller->view->headLink()->appendStylesheet($controller->view->baseUrl() . '/css/env/' . $env . '.css');
            $controller->view->addScriptPath(APPLICATION_PATH . '/views/env');// add env folder to the view path

            $partial = $controller->view->partial($env . '.phtml', array($args));

            if(isset($config['return']))
                return $partial;

            echo $partial;
        }

        return true;
    }
}