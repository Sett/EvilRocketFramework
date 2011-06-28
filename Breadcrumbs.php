<?php
/**
 * @author Se#
 * @version 0.0.2
 * @description Realize auto-breadcrumbs
 */
class Evil_Breadcrumbs extends Zend_Controller_Plugin_Abstract
{
    /**
     * @description contain all paths in ->pages
     * @var Zend_Session_Namespace
     * @author Se#
     * @version 0.0.1
     */
    protected static $_pages = null;

    /**
     * @description contain breadcrumbs configuration
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    protected static $_config = array();

    /**
     * @description add page to the paths if it is not in paths yet
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @author Se#
     * @version 0.0.2
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $this->addPage($request->getControllerName(),$request->getActionName());
    }

    /**
     * @description return self config
     * @static
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public static function getConfig()
    {
        return self::$_config;
    }

    /**
     * @description return self pages-object
     * @static
     * @return null|Zend_Session_Namespace
     * @author Se#
     * @version 0.0.1
     */
    public static function getPages()
    {
        return self::$_pages;
    }

    /**
     * @description define is the current controller appendix
     * @param string $controller
     * @return bool
     * @author Se#
     * @version 0.0.1
     */
    public function isAppendix($controller)
    {
        $cfg = self::$_config;

        if(isset($cfg['controller'][$controller])
           && is_array($cfg['controller'][$controller])
           && isset($cfg['controller'][$controller]['appendTo'])
        )
            return $cfg['controller'][$controller]['appendTo'];

        return false;
    }

    /**
     * @description add page to the paths
     * @param string $controller
     * @param string $action
     * @param bool $get
     * @return bool
     * @author Se#
     * @version 0.0.1
     */
    public function addPage($controller, $action, $get = false)
    {
        if(!self::$_pages)
            $this->initialize();

        // If exist controller in pages
        if(isset(self::$_pages->pages[$controller]))
        {// if action already exist
            if(isset(self::$_pages->registry[$controller.$action]))
                return true;
            else
            {
                list($controllerLabel, $actionLabel) = $this->getLabel($controller, $action);
                self::$_pages->pages[$controller]['pages'][] = array(
                    'controller' => $controller,
                    'action' => $action,
                    'label' => $actionLabel
                );
                self::$_pages->registry[$controller.$action] = $actionLabel;
            }
        }
        else
        {
            list($label, $actionLabel) = $this->getLabel($controller, $action);

            self::$_pages->registry[$controller.$action] = $actionLabel;
            self::$_pages->pages[$controller] = array(
                'controller' => $controller,
                'action' => 'index',
                'label' => $label,
                'pages' => $this->_pagesConfig($controller, $action, $actionLabel)
            );
        }
    }

    /**
     * @description define is sub-pages needed
     * @param string $controller
     * @param string $action
     * @param string $actionLabel
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _pagesConfig($controller, $action, $actionLabel)
    {
        $pagesConfig = array(
                array(
                    'controller' => $controller,
                    'action' => $action,
                    'label' => $actionLabel,
                    'pages' => array()
                ));

        if('index' == $action)
            $pagesConfig = array();

        return $pagesConfig;
    }

    /**
     * @description get label for current controller and action
     * @param string $controller
     * @param string $action
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public function getLabel($controller, $action)
    {
        $actionLabel = '';

        if(isset(self::$_config['controller'][$controller]))
        {
            if(is_array(self::$_config['controller'][$controller]))
            {
                if(isset(self::$_config['controller'][$controller]['label']))
                    $label = self::$_config['controller'][$controller]['label'];
                else
                    $label = ucfirst($controller);

                if(isset(self::$_config['controller'][$controller][$action]))
                    $actionLabel = _(self::$_config['controller'][$controller][$action]);
            }
            else
                $label = self::$_config['controller'][$controller];
        }
        else
            $label = ucfirst($controller);

        if(empty($actionLabel))
        {
            if(isset(self::$_config['action'][$action]))
                $actionLabel = self::$_config['action'][$action];
            else
                $actionLabel = ucfirst($action);
        }

        return array($label, $actionLabel);
    }

    /**
     * @description initialize self pages-object and config
     * @param bool $pages
     * @param bool $config
     * @return void
     * @author Se#
     * @version 0.0.2
     */
    public function initialize($pages = true, $config = true)
    {
        if($pages)
        {
            self::$_pages = new Zend_Session_Namespace('evil-pages');
            self::$_pages->pages = array();
        }

        if($config)
        {
            if(is_file(APPLICATION_PATH . '/configs/breadcrumbs.json'))
                self::$_config = json_decode(file_get_contents(APPLICATION_PATH . '/configs/breadcrumbs.json'), true);
            else
                self::$_config = json_decode(file_get_contents(__DIR__ . '/Breadcrumbs/default.json'), true);
        }
    }

    /**
     * @description return breadcrumbs
     * @return null|Zend_Navigation
     * @author Se#
     * @version 0.0.2
     */
    public function breadcrumbs()
    {
        if(!empty(self::$_pages))
        {

            $pages = self::$_pages->pages;
             // Создаем новый контейнер на основе нашей структуры
            $container = new Zend_Navigation($pages);

            return $container;
        }

        return null;
    }

}