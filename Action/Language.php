<?php

class Evil_Action_Language extends Evil_Action_Abstract
{
    public static function autoLoad()
    {
        $params     = self::$_info['params'];
        $controller = self::$_info['controller'];

        $controller->view->headLink()->appendStylesheet($controller->view->baseUrl() . '/css/language.css');
        $controller->view->addScriptPath(__DIR__ . '/Views');// add current folder to the view path

        $langList = file_exists(__DIR__ . '/Configs/language.json') ?
                json_decode(file_get_contents(__DIR__ . '/Configs/language.json')) :
                array('Ru');

        echo $controller->view->partial('language.phtml',
                                        array('langList' => $langList,
                                             'controllerName' => $params['controller'],
                                             'actionName' => $params['action']));
    }

    protected function _actionDefault()
    {
        $params     = self::$_info['params'];
        $controller = self::$_info['controller'];

        if(isset($params['lang']))
        {
            $session = new Zend_Session_Namespace('evil-language');
            $session->language = $params['lang'];

            $path = __DIR__ . '/Configs/Language/' . $params['lang'] . '.json';

            if(file_exists($path))
                $session->vocabulary = json_decode(file_get_contents($path), true);
            else
                $session->vocabulary = array();
        }

        $controller->_redirect('/' . $params['controller'] . '/' . $params['prevAction']);
    }

    public static function isA($word)
    {
        $session = new Zend_Session_Namespace('evil-language');
        if(isset($session->vocabulary) && isset($session->vocabulary[$word]))
            return $session->vocabulary[$word];

        return $word;
    }
}