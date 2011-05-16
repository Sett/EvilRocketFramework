<?php
/**
 * @description Abstract action, use default config if there is no personal,
 * use default view if there is no personal view
 * @author Se#
 * @version 0.0.2
 */
abstract class Evil_Action_Abstract implements Evil_Action_Interface
{
    /**
     * @description current table metadata
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    public static $metadata = array();

    /**
     * @description Invoke action, create form and other needed actions
     * @param Zend_Controller_Action $controller
     * @param string $ext
     * @param array $params
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function __invoke(Zend_Controller_Action $controller, $ext = null, $params = null, $getTableFrom = null)
    {
        $getTableFrom = $getTableFrom ? $getTableFrom : 'controller'; // default
        $controllerName = $this->_controllerName($controller, $params, $getTableFrom);// get controller name
        $controller->view->controllerName = $controllerName;

        $configPath = $this->_configPath($controllerName, $ext);
        $class      = 'Zend_Config_' . ucfirst($ext);// construct config-class name
        $config     = new $class($configPath);

        $this->_injectInView($controller, $controllerName);
        $this->_autoLoad($controller, $params, $controllerName);// force autoLoad

        $action     = $params['action'];// get current action
        $table      = $this->_table($config, $action, $controllerName);
        $formConfig = $this->_formConfig($table, $action, $config, $params);// get form config

        // do something, if there is $params['do']
        $data = $this->_do($params, $table, $config, $controller, $formConfig);

        // create form
        $form = $this->_fillForm($data, new Zend_Form($formConfig));
        $controller->view->assign('form', $form);// assign form to the view
        $this->_ifViewNotExistsRenderDefault($controller, $action);
    }

    /**
     * @description prepare table
     * @param object $config
     * @param string $action
     * @param string $controllerName
     * @return Zend_Db_Table
     * @author Se#
     * @version 0.0.1
     */
    protected function _table($config, $action, $controllerName)
    {
        // check if there is optional table name
        $table  = isset($config->$action->tableName) ? $config->$action->tableName : $controllerName;
        $table  = new Zend_Db_Table(Evil_DB::scope2table($table));

        if(method_exists($this, '_changeTable'))
            $table = $this->_changeTable($table, $config, $action, $controllerName);

        return $table;
    }

    /**
     * @description prepare config for form
     * @param object $table
     * @param string $action
     * @param object $config
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _formConfig($table, $action, $config, $params)
    {
        // get form config
        $formConfig = $this->_createFormOptionsByTable($table, $action, $config);
        $formConfig += isset($config->$action) ? $config->$action->toArray() : array();

        if(method_exists($this, '_changeFormConfig'))
            $formConfig = $this->_changeFormConfig($formConfig, $table, $action, $config, $params);

        return $formConfig;
    }

    /**
     * @description construct config path
     * @param string $controllerName
     * @param string $ext
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    protected function _configPath($controllerName, $ext)
    {
        $configPath = APPLICATION_PATH.'/configs/forms/' . $controllerName . '.' . $ext;// construct personal-config path

        if(!file_exists($configPath))// if there is no personal config, use default
            $configPath = dirname(__FILE__) . '/default.' . $ext;

        return $configPath;
    }

    /**
     * @description Do some additional action ($params['do']) if there is $params['do']
     * @param array $params
     * @param object $table
     * @param object|array $config
     * @param object $controller
     * @return bool
     * @author Se#
     * @version 0.0.1
     */
    protected function _do($params, $table, $config, $controller)
    {
        if(isset($params['do']))// do something?
        {
            $do     = $params['do'];// what to do
            $params = $this->_cleanParams($params);// clear params
            $data   = $this->_action($do, $params, $table, $config, $controller);// force action
        }
        else
            $data = $this->_action('default', $params, $table, $config, $controller);

        return $data;
    }

    /**
     * @description If view not exists, render default
     * @param object $controller
     * @param string $action
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected function _ifViewNotExistsRenderDefault($controller, $action)
    {
        // construct view path
        $viewPath = APPLICATION_PATH . '/views/scripts/' . $controller->getHelper('viewRenderer')->getViewScript();

        if(!file_exists($viewPath))// if there is no personal view, use default
        {
            $controller->getHelper('viewRenderer')->setNoRender(); // turn off native (personal) view
            $controller->view->addScriptPath(__DIR__ . '/Views');// add current folder to the view path
            $controller->getHelper('viewRenderer')->renderScript($action . '.phtml');// render default script
        }
    }

    /**
     * @description inject functions into view
     * @param object $controller
     * @param string $controllerName
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    protected function _injectInView($controller, $controllerName)
    {
        if(!isset($controller->view->evilAutoloads))
            $controller->view->evilAutoloads = array();

        $controller->view->evilAutoloads['pleaseShow'] = function($data) use ($controllerName)
        {
            $links = '';

            foreach($data as $action)
            {
                if(is_array($action))
                {
                    $link = isset($action['link']) ? $action['link'] : '';
                    $text = isset($action['text']) ? $action['text'] : ucfirst($link);
                }
                else
                {
                    $link = $action;
                    $text = ucfirst($link);
                }

                $links .= '<a href="http://' . $_SERVER['SERVER_NAME']. '/' . $controllerName . '/' . $link . '">' . $text . '</a> ';
            }

            return $links;
        };
    }

    /**
     * @description Simple autoLoad for actions
     * @param object $controller
     * @param array $params
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected function _autoLoad($controller, $params)
    {
        $dir = new RecursiveDirectoryIterator(__DIR__);
        foreach($dir as $item => $value)
        {
            if(!strpos($item, '.php'))
                continue;

            $info = pathinfo($item);
            $name = 'Evil_Action_' . $info['filename'];

            if(method_exists($name, 'autoLoad'))
                $name::autoload($controller, $params);
        }
    }

    /**
     * @description fill form fields
     * @param array $data
     * @param object $form
     * @return object
     * @author Se#
     * @version 0.0.1
     */
    protected function _fillForm($data, $form)
    {
        if(!empty($data) && !is_string($data))
        {
            foreach($data as $field => $value)
            {
                if(isset($form->$field))
                    $form->$field->setValue($value);
            }
        }

        return $form;
    }

    /**
     * @description delete control params
     * @param array $params
     * @return
     * @author Se#
     * @version 0.0.1
     */
    protected function _cleanParams($params)
    {
        if(isset($params['do']))
            unset($params['do']);

        if(isset($params['controller']))
            unset($params['controller']);

        if(isset($params['action']))
            unset($params['action']);

        if(isset($params['module']))
            unset($params['module']);

        if(isset($params['submit']))
            unset($params['submit']);

        return $params;
    }

    /**
     * @description get current controller name
     * @param Zend_Controller_Action $controller
     * @param array $params
     * @return
     * @author Se#
     * @version 0.0.1
     */
    protected function _controllerName(Zend_Controller_Action $controller, $params, $getTableFrom = 'controller')
    {
        return isset($params[$getTableFrom]) ?
                    $params[$getTableFrom] :
                    $controller->getRequest()->getControllerName();
    }

    /**
     * @description check is there method with the $action name, and call it if it so
     * @param string $action
     * @param array $params
     * @param object $table
     * @param array|object $config
     * @param object $controller
     * @return bool
     * @author Se#
     * @version 0.0.1
     */
    protected function _action($action, $params, $table, $config, $controller)
    {
        $action = '_action' . ucfirst($action);
        if(method_exists($this, $action))
            return $this->$action($params, $table, $config, $controller);

        return false;
    }

    /**
     * @description create options for form by table scheme
     * @param object $table
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _createFormOptionsByTable($table, $action, $config)
    {
        $actionConfig = isset($config->$action) ? $config->$action->toArray() : array();

        $metadata = $table->info('metadata');// get metadata
        self::$metadata = $metadata;// save for different aims
        $options = array(// set basic options
           'method' => 'post',
           'elements' => array(

           )
        );

        foreach($metadata as $columnName => $columnScheme)
        {
            if($columnScheme['PRIMARY'])// don't show if primary key
                continue;

            $typeOptions = $this->_getFieldType($columnScheme['DATA_TYPE']);// return array('type'[, 'options'])

            $attrOptions = array('label' => ucfirst($columnName));
            if(isset($actionConfig['default']))
                $attrOptions += $actionConfig['default'];

            $options['elements'][$columnName] = array(
                'type' => $typeOptions[0],
                'options' =>  $attrOptions
            );

            if(isset($typeOptions[1]))// if there is some additional options, merge it with the basic options
                $options['elements'][$columnName]['options'] += $typeOptions[1];
        }

        $options['elements']['do'] = array('type' => 'hidden', 'options' => array('value' => $action));// add submit button
        $options['elements']['submit'] = array('type' => 'submit');// add submit button

        return $options;
    }

    /**
     * @description convert mysql type to the HTML-type and add (if it needs) options for the HTML-type
     * @param string $type
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _getFieldType($type)
    {
        switch($type)
        {
            case 'text' : return array('textarea', array('rows' => 5));
            case 'int'  : return array('text');
            default     : return array('text');
        }
    }
}