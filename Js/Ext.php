<?php
/**
 * Append needed js for ExtJS
 * @author Se#
 */
class Evil_Js_Ext
{
    /**
     * Append needed js to the controller according to the application environment
     *
     * @static
     * @param Zend_Controller_Action $controller
     * @return bool
     */
    public static function append($controller)
    {
        $basePath = 'http://scorecdn.s3.amazonaws.com/extJS/';// just for comfort

        switch (APPLICATION_ENV)
        {
            case 'production':
                $controller->view->headScript()->appendFile($basePath . 'adapter/jquery/ext-jquery-adapter.js');
                $controller->view->headScript()->appendFile($basePath . 'ext-all.js');
            break;

            case 'development':
                $controller->view->headScript()->appendFile($basePath . 'adapter/jquery/ext-jquery-adapter-debug.js');
                $controller->view->headScript()->appendFile($basePath . 'ext-all-debug.js');
            break;

            default:
                $controller->view->headScript()->appendFile($basePath . 'adapter/jquery/ext-jquery-adapter-debug.js');
                $controller->view->headScript()->appendFile($basePath . 'ext-all-debug.js');
            break;
        }
        //$this->view->headScript()->appendFile($this->view->baseUrl() . '/js/ext/ext-lang-ru.js');
        $controller->view->headLink()->appendStylesheet($basePath . 'resources/css/ext-all.css');
        $controller->view->headLink()->appendStylesheet($basePath . 'resources/css/xtheme-gray.css');
        return true;
    }
}