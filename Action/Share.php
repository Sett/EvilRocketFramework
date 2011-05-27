<?php
/**
 * @description Share action
 * @author Se#
 * @version 0.0.1
 */
class Evil_Action_Share extends Evil_Action_Abstract
{
    /**
     * @description show|return share-buttons
     * @static
     * @param null|Evil_Controller $controllerObject
     * @return void|array
     * @author Se#
     * @version 0.0.1
     */
    public static function __autoLoad($controllerObject = null, $return = false)
    {
        $path       = __DIR__ . '/Share/application/configs/share.json';
        $controller = is_object($controllerObject) ? $controllerObject : self::$_info['controller'];

        // get config
        if(!isset($controller->selfConfig['share']))
            $config = file_exists($path) ? json_decode(file_get_contents($path), true) : array();
        else
            $config = $controller->selfConfig['share'];
        
        if(isset($config['items']))
        {// get view path
            $viewPath = isset($config['viewPath']) ?
                    self::parsePath($config['viewPath']) :
                    __DIR__ . '/Share/application/views/scripts/items';
            // add view path
            $controller->view->addScriptPath($viewPath);// add current folder to the view path
            self::_appendScripts($controller, $config);

            $result = '<div class="evil-share"><span class="top">Share with</span> ';// initialize result string

            foreach($config['items'] as $name => $options)// options: array(Class, Method)
            {
                $viewPath = __DIR__ . '/Share/application/views/scripts/items/' . $name . '.phtml';

                if(file_exists($viewPath) && method_exists($options[0], $options[1]))
                {// render partial view
                    $result .= $controller->view->partial($name . '.phtml',
                                                          array(
                                                               'data' => call_user_func_array($options,
                                                                           array($controller->_getAllParams()))));
                }
            }

            $result .= '</div>';

            if(!$return)
                echo $result;

            return $result;
        }
    }

    /**
     * @description append css and js to the controller
     * @static
     * @param Evil_Controller $controller
     * @param array $config
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected static function _appendScripts($controller, $config)
    {
        if(!isset($config['css']))
        {
            $name = 'share' . sha1(json_encode($_REQUEST['PHPSESSID'])) . '.css';
            fopen(ROOT . 'public/css/' . $name, "w+t");
            file_put_contents(ROOT . 'public/css/' . $name, file_get_contents(__DIR__ . '/Share/public/css/share.css'));
            $cssPath = '/css/' . $name;
        }
        else
            $cssPath = $config['css'];
        
        $controller->view->headLink()->appendStylesheet($cssPath);
        $controller->view->headScript()->appendFile('/js/share42/share42.js');
    }

    /**
     * @description insert __DIR__ or APPLICATION_PATH into path, if it needs
     * @static
     * @param string $path
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    public static function parsePath($path)
    {
        if(strpos($path, '__DIR__') !== false)
            return str_replace('__DIR__', __DIR__, $path);

        if(strpos($path, 'APPLICATION_PATH') !== false)
            return str_replace('APPLICATION_PATH', APPLICATION_PATH, $path);
    }

    /**
     * @description prepare data for the Twitter
     * @static
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    public static function twitter($params)
    {
        return '';
    }

    /**
     * @description prepare data for the Facebook
     * @static
     * @param array $params
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    public static function facebook($params)
    {
        return '';
    }

    /**
     * @description prepare data for the Redmine
     * @static
     * @param array $params
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    public static function redmine($params)
    {
        return '';
    }

    /**
     * @description prepare data for the Score
     * @static
     * @param array $params
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    public static function score($params)
    {
        return '';
    }
}