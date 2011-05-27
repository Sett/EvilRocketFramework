<?php
/**
 * @description Abstract action, use default config if there is no personal,
 * use default view if there is no personal view
 * @author Se#
 * @version 0.0.3
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
     * @description different current info, ex. controller, action, table, etc
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    protected static $_info = array();

    /**
     * @description Invoke action, create form and other needed actions
     * @param Zend_Controller_Action $controller
     * @param string $ext
     * @param array $params
     * @return void
     * @author Se#
     * @version 0.0.5
     */
    public function __invoke(Zend_Controller_Action $controller, $params = null, $getTableFrom = null)
    {
        $personalPath = APPLICATION_PATH . '/configs/invoke.json';
        $generalPath  = __DIR__ . '/Abstract/application/configs/invoke.json';
        // decide what config get
        $path = file_exists($personalPath) ? $personalPath : (file_exists($generalPath) ? $generalPath : false);

        if($path)
        {
            $invokeConfig = json_decode(file_get_contents($path), true);// load config
            self::$_info['invokeConfig'] = $invokeConfig;
            if(isset($invokeConfig['method-to-variable']))
            {
                $args = func_get_args();
                // operate
                foreach($invokeConfig['method-to-variable'] as $variable)
                {
                    list($class, $method) = $this->_prepareArgsFromConfig($variable);
                    
                    if(method_exists($class, $method))
                        self::$_info[$method] = $class->$method($args);
                }
            }
        }
    }

    /**
     * @description set controller->view->form
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function setFormIntoView()
    {
        self::$_info['controller']->view->form = self::$_info['fillForm'];
    }

    /**
     * @description Simple autoLoad for actions
     * @param object $controller
     * @param array $params
     * @return void
     * @author Se#
     * @version 0.0.2
     */
    public function autoLoad()
    {
        $data = array();

        if(isset(self::$_info['invokeConfig']['autoLoad']))
        {

            foreach(self::$_info['invokeConfig']['autoLoad'] as $autoLoad)
            {
                if(is_string($autoLoad))
                {
                    $class = $autoLoad;
                    $method = '__autoLoad';
                }
                else
                {
                    $class = $autoLoad[0];
                    $method = $autoLoad[1];
                }

                $data[$class] = call_user_func_array(array($class, $method), array(self::$_info));
            }
        }

        self::$_info['controller']->view->autoLoad = $data;

        return $data;
    }

    /**
     * @description Return config
     * @param string $ext
     * @return
     * @author Se#
     * @version 0.0.1
     */
    public function config()
    {
        $configPath = $this->_configPath(self::$_info['controller']->selfConfig['ext']);
        
        // construct config-class name
        $class = 'Zend_Config_' . ucfirst(self::$_info['controller']->selfConfig['ext']);

        return $configPath ? new $class($configPath) : self::$_info['controller']->selfConfig;
    }

    /**
     * @description set controller->view->controllerName
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function setControllerNameIntoView()
    {
        self::$_info['controller']->view->controllerName = self::$_info['controllerName'];
    }

    /**
     * @description get current controller name
     * @param Zend_Controller_Action $controller
     * @param array $params
     * @return string
     * @author Se#
     * @version 0.0.2
     */
    public function controllerName($args)
    {
        $from = $args[2] ? $args[2] : 'controller'; // default

        return isset(self::$_info['params'][$from]) ?
                    self::$_info['params'][$from] :
                    self::$_info['controller']->getRequest()->getControllerName();
    }

    /**
     * @description extract request params
     * @param array $args
     * @return
     * @author Se#
     * @version 0.0.1
     */
    public function params($args)
    {
        return $args[1];
    }

    /**
     * @description extract controller
     * @param array $args
     * @return
     * @author Se#
     * @version 0.0.1
     */
    public function controller($args)
    {
        return $args[0];
    }

    /**
     * @description decide in what class should call a method
     * @param array|string $args
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _prepareArgsFromConfig($args)
    {
        if(is_string($args))
            return array($this, $args);
        else
            return array($args[0], $args[1]);
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
    public function table()
    {
        $config         = self::$_info['config'];
        $action         = self::$_info['params']['action'];
        $controllerName = self::$_info['controllerName'];

        // check if there is optional table name
        $table  = isset($config->$action->tableName) ? $config->$action->tableName : $controllerName;
        $table  = new Zend_Db_Table(Evil_DB::scope2table($table));

        if(method_exists($this, '_changeTable'))
            $table = $this->_changeTable($table);

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
    public function formConfig()
    {
        $action     = self::$_info['params']['action'];
        $config     = is_object(self::$_info['config']) ? self::$_info['config']->toArray() : self::$_info['config'];

        // get form config
        if(isset($config[$action]['form']['merge']) || !isset($config[$action]['form']))
        {
            $formConfig = $this->_createFormOptionsByTable();
            $formConfig += isset($config[$action]['form']) ? $config[$action]['form'] : array();
        }
        else
            $formConfig = $config[$action]['form'];

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
    protected function _configPath($ext)
    {   
        $basePath = isset(self::$_info['controller']->selfConfig['configBasePath']) ?
                self::$_info['controller']->selfConfig['configBasePath'] :
                '/configs/forms/';
        // construct personal-config path
        $configPath = APPLICATION_PATH . $basePath . self::$_info['controllerName'] . '.' . $ext;

        if(!file_exists($configPath))// if there is no personal config, use default
            return false;

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
    public function data()
    {
        $params     = self::$_info['params'];
        $table      = self::$_info['table'];
        $config     = self::$_info['config'];
        $controller = self::$_info['controller'];

        if(isset($params['do']))// do something?
        {
            $this->_prepareDataForAction();
            //self::$_info['params'] = $this->_cleanParams(self::$_info['params']);// clear params
        }
        else
            self::$_info['params']['do'] = 'default';

        $data   = $this->_action();// force action
        return $data;
    }

    /**
     * @description prepare data for action
     * @param string $do
     * @param array $params
     * @param config $table
     * @param config $config
     * @param object $controller
     * @return
     * @author Se#
     * @version 0.0.1
     */
    protected function _prepareDataForAction()
    {
        $params = self::$_info['params'];
        $controller = self::$_info['controller'];


        if(!empty($controller->selfConfig[$params['action']][__FUNCTION__]))
        {
            $curConfig = $controller->selfConfig[$params['action']][__FUNCTION__];

            foreach($curConfig as $field => $actConfig)
            {
                $value = isset($params[$field]) ? $params[$field] : null;

                if(is_string($actConfig))
                    $params[$field] = call_user_func($actConfig, $value);
                else
                {
                    $class = isset($actConfig['class']) ? $actConfig['class'] : $this;
                    if(method_exists($class, $actConfig['method']))
                        $params[$field] = call_user_func_array(array($class, $actConfig['method']), array($value));
                }
            }
        }

        return self::$_info['params'] = $params;
    }

    /**
     * @description If view not exists, render default
     * @param object $controller
     * @param string $action
     * @return void
     * @author Se#
     * @version 0.0.2
     */
    public function ifViewNotExistsRenderDefault()
    {
        $controller = self::$_info['controller'];

        if(!isset($controller->view->evilAutoloads))
            $controller->view->evilAutoloads = array();
        
        if($this->_skipFunction(__FUNCTION__))
            return true;

        // construct view path
        $viewPath = APPLICATION_PATH . '/views/scripts/' . $controller->getHelper('viewRenderer')->getViewScript();

        if(!file_exists($viewPath))// if there is no personal view, use default
        {
            $path = __DIR__ . '/' .
                    ucfirst(self::$_info['params']['action']) . '/' .
                    self::$_info['invokeConfig']['paths']['views'];

            $controller->getHelper('viewRenderer')->setNoRender(); // turn off native (personal) view
            $controller->view->addScriptPath($path);// add current folder to the view path
            $controller->getHelper('viewRenderer')->renderScript(self::$_info['params']['action'] . '.phtml');// render default script
        }
    }

    /**
     * @description define is it need to skip a function
     * @param string $functionName
     * @return bool
     * @author Se#
     * @version 0.0.1
     */
    protected function _skipFunction($functionName)
    {
        $actionConfig = $this->_getActionConfig();
        // If it needs to skip this function, skip it
        if(isset($actionConfig[$functionName]) && ('skip' == $actionConfig[$functionName]))
            return true;
    }

    /**
     * @description return action config or do-config if there is "do" parameter in params
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _getActionConfig()
    {// like cache
        if(isset(self::$_info['actionConfig']))
            return self::$_info['actionConfig'];

        if(isset(self::$_info['params']['do']))
        {// If in the controller config exists cell for current action and in it exists cell for do, return it
            if(isset(self::$_info['controller']
                    ->selfConfig[self::$_info['params']['action']]['actions'][self::$_info['params']['do']]))
                return self::$_info['actionConfig'] = self::$_info['controller']
                    ->selfConfig[self::$_info['params']['action']]['actions'][self::$_info['params']['do']];
        }

        // If in the controller config exists cell for current action, return it
        if(isset(self::$_info['controller']->selfConfig[self::$_info['params']['action']]))
            return self::$_info['actionConfig'] = self::$_info['controller']
                ->selfConfig[self::$_info['params']['action']];

        return array();
    }

    /**
     * @description fill form fields
     * @param array $data
     * @param object $form
     * @return object
     * @author Se#
     * @version 0.0.1
     */
    public function fillForm()
    {
        $data = self::$_info['data'];

        $form = new Zend_Form(self::$_info['formConfig']);

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
    protected function _action()
    {
        if(!isset(self::$_info['params']['do']))
            self::$_info['params']['do'] = 'default';
        
        $action = '_action' . ucfirst(self::$_info['params']['do']);
        if(method_exists($this, $action))
            return $this->$action();

        return false;
    }

    /**
     * @description create options for form by table scheme
     * @param object $table
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _createFormOptionsByTable($ignorePersonalConfig = false)
    {
        $table  = self::$_info['table'];
        $action = self::$_info['params']['action'];
        $config = self::$_info['config'];

        if(!$ignorePersonalConfig)
        {
            $controllerConfig = isset(self::$_info['controller']->selfConfig[$action]['form']) ?
                    self::$_info['controller']->selfConfig[$action]['form'] :
                    array('elements');

            $actionConfig = isset($config->$action) ? $config->$action->toArray() : array();
        }
        else
        {
            $controllerConfig = array();
            $actionConfig = array();
        }

        $metadata = $table->info('metadata');// get metadata
        self::$metadata = $metadata;// save for different aims
        $options = array(// set basic options
           'method' => 'post',
           'elements' => array()
        );

        $options = array_merge_recursive($options, $controllerConfig);

        foreach($metadata as $columnName => $columnScheme)
        {
            if($columnScheme['PRIMARY'])// don't show if primary key
                continue;

            $typeOptions = $this->_getFieldType($columnScheme['DATA_TYPE']);// return array('type'[, 'options'])

            $attrOptions = array('label' => ucfirst($columnName));
            if(isset($actionConfig['default']))
                $attrOptions += $actionConfig['default'];

            $options = $this->_setFormField($options, $columnName, $attrOptions, $typeOptions);
        }

        $options['elements']['do'] = array('type' => 'hidden', 'options' => array('value' => $action));// add submit button
        $options['elements']['submit'] = array('type' => 'submit');// add submit button

        return $options;
    }

    /**
     * @description set form field
     * @param array $options
     * @param string $columnName
     * @param array $attrOptions
     * @param array $typeOptions
     * @return
     * @author Se#
     * @version 0.0.1
     */
    protected function _setFormField($options, $columnName, $attrOptions, $typeOptions)
    {
        if(isset($options['elements'][$columnName]) && ('ignore' == $options['elements'][$columnName]))
            unset($options['elements'][$columnName]);
        else
        {
            if(isset($options['elements'][$columnName]))
            {
                $options['elements'][$columnName]['type'] = isset($options['elements'][$columnName]['type']) ?
                        $options['elements'][$columnName]['type'] :
                        $typeOptions[0];

                $options['elements'][$columnName]['options'] = isset($options['elements'][$columnName]['options']) ?
                        $options['elements'][$columnName]['options'] + $attrOptions :
                        $attrOptions;
            }
            else
            {
                $options['elements'][$columnName] = array(
                    'type' => $typeOptions[0],
                    'options' =>  $attrOptions
                );
            }

            if(isset($typeOptions[1]))// if there is some additional options, merge it with the basic options
                $options['elements'][$columnName]['options'] += $typeOptions[1];
        }

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

    /**
     * @description get default action config from Action/application/configs/action.json
     * @param bool $array
     * @return array|mixed|null
     * @author Se#
     * @version 0.0.1
     */
    protected function _getDefaultActionConfig($array = true)
    {
        $path = __DIR__ . '/' . ucfirst(self::$_info['params']['action']) .
                       '/application/configs/' . self::$_info['params']['action'] . '.json';

        if(file_exists($path))
            return json_decode(file_get_contents($path), $array);

        return $array ? array() : null;
    }

    /**
     * @description operate field,
     * 'attribute' => 'fieldName',
     * 'function' => functionName|array(class, method)
     * 'args' => array(arg1, arg2, ...)
     * 
     * @param array $params
     * @param array $field
     * @return array|bool|null
     * @author Se#
     * @version 0.0.1
     */
    protected function _operateField($params, $field)
    {
        $attr = isset($field['attribute']) ? $field['attribute'] : 'unknown';
        $args = isset($field['args']) ? $field['args'] : array();

        if(!isset($params[$attr]))
            return false;

        if(isset($field['function']))
        {
            $value = call_user_func_array($field['function'], array_merge($args + array($params[$attr])));
            return array('value' => $value, 'attribute' => $attr);
        }

        return null;
    }
}