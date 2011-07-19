<?php
/**
 * @description Abstract action, use default config if there is no personal,
 * use default view if there is no personal view
 * @author Se#
 * @version 0.0.6
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
     * @version 0.0.7
     */
    public function __invoke(Zend_Controller_Action $controller, $params = null, $getTableFrom = null)
    {
        $invokeConfig = self::getInvokeConfig($params);

        // if there is invoke config
        if($invokeConfig)
        {// save
            self::$_info['invokeConfig'] = $invokeConfig;
            // do we need some operations?
            if(isset($invokeConfig['method-to-variable']))
            {// get args
                $args = func_get_args();

                // operate
                foreach($invokeConfig['method-to-variable'] as $variable)
                {// method can be a string (function) or an array (class, method)
                    list($method, $field) = $this->_prepareArgsFromConfig($variable);
     
                    $result = $args;
                    // check method existing
                    if(is_array($method) && $method[0] && $method[1] && !method_exists($method[0], $method[1]))
                        continue;

                    // check function existing
                    elseif(is_string($method) && !function_exists($method))
                        continue;

                    $result = call_user_func_array($method, array($args, $result));
                    if(!empty($field))// do we need save the result into a field?
                        self::$_info[$field] = $result;
                }
            }
        }
    }

    /**
     * @description get invoke config, may get personal for controller & action
     * @static
     * @return array|mixed
     * @author Se#
     * @version 0.0.2
     */
    public static function getInvokeConfig($params)
    {
        $personalPath = APPLICATION_PATH . '/configs/evil/invoke.json';
        $generalPath  = __DIR__ . '/Abstract/application/configs/invoke.json';
        // decide what config get
        $path   = file_exists($personalPath) ? $personalPath : (file_exists($generalPath) ? $generalPath : false);
        $config = $path ? json_decode(file_get_contents($path), true) : false;

        // if there is personal config for current controller
        if(isset($params['controller']) && is_array($config) && isset($config[$params['controller']]))
        {
            $config = $config[$params['controller']];
            // if there is personal config for current action
            if(isset($params['action']) && isset($config[$params['action']]))
                $config = $config[$params['action']];
        }

        return $config;
    }

    /**
     * @description set controller->view->form
     * @return void
     * @author Se#
     * @version 0.0.2
     */
    public function setFormIntoView()
    {
        if(self::_('fillForm') && self::_('controller'))
            self::_('controller')->view->form = self::_('fillForm');
    }

    /**
     * @description Simple autoLoad for actions
     * @param object $controller
     * @param array $params
     * @return void
     * @author Se#
     * @version 0.0.3
     */
    public static function autoLoad()
    {
        $data = array();

        if(self::_('invokeConfig'))
            $invokeConfig = self::_('invokeConfig');
        elseif(self::_('params'))
            $invokeConfig = self::getInvokeConfig(self::_('params'));
        else
            $invokeConfig = array();

        if(isset($invokeConfig['autoLoad']))
        {
            foreach($invokeConfig['autoLoad'] as $autoLoad)
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

                $args = func_get_args();
                $args = empty($args) ? array(self::$_info) : $args;

                $data[$class] = call_user_func_array(array($class, $method), $args);
            }
        }

        if(self::_('controller'))
            self::_('controller')->view->autoLoad = $data;

        return $data;
    }

    /**
     * @description Return config
     * @param string $ext
     * @return
     * @author Se#
     * @version 0.0.2
     */
    public function config()
    {
        if(!isset(self::$_info['controller']))
            return null;

        if(isset(self::$_info['controller']->selfConfig['ext']))
            $ext = self::$_info['controller']->selfConfig['ext'];
        else
            $ext = 'ini';

        $configPath = self::_configPath($ext);
        
        // construct config-class name
        $class = 'Zend_Config_' . ucfirst(self::$_info['controller']->selfConfig['ext']);

        return $configPath ? new $class($configPath) : self::$_info['controller']->selfConfig;
    }

    /**
     * @description set controller->view->controllerName
     * @return void
     * @author Se#
     * @version 0.0.2
     */
    public function setControllerNameIntoView()
    {
        if(isset(self::$_info['controller']) && isset(self::$_info['controllerName']))
            self::$_info['controller']->view->controllerName = self::$_info['controllerName'];
    }

    /**
     * @description get current controller name
     * @param Zend_Controller_Action $controller
     * @param array $params
     * @return string
     * @author Se#
     * @version 0.0.3
     */
    public function controllerName($args)
    {
        $from = isset($args[2]) ? $args[2] : 'controller'; // default

        return isset(self::$_info['params'][$from]) ?
                    self::$_info['params'][$from] :
                    self::$_info['controller']->getRequest()->getControllerName();
    }

    /**
     * @description extract request params
     * @param array $args
     * @return
     * @author Se#
     * @version 0.0.2
     */
    public function params($args)
    {
        return isset($args[1]) ? $args[1] : array();
    }

    /**
     * @description extract controller
     * @param array $args
     * @return
     * @author Se#
     * @version 0.0.2
     */
    public function controller($args)
    {
        return isset($args[0]) ? $args[0] : null;
    }

    /**
     * @description decide in what class should call a method
     * @param array|string $args
     * @return array
     * @author Se#
     * @version 0.0.2
     */
    protected function _prepareArgsFromConfig($args)
    {
        if(is_string($args))
            return array($args, null);
        elseif(is_array($args))
        {
            $class    = isset($args['class'])  ? $args['class']  : get_class($this);
            $method   = isset($args['method']) ? $args['method'] : 'get';
            $field    = isset($args['field'])  ? $args['field']  : null;

            return array(array($class, $method), $field);
        }
    }

    /**
     * @description prepare table
     * @param object $config
     * @param string $action
     * @param string $controllerName
     * @return Zend_Db_Table
     * @author Se#
     * @version 0.0.2
     */
    public function table()
    {
        $config         = self::getStatic('config');
        $action         = self::getStatic('params', 'action');
        $controllerName = self::getStatic('controllerName');

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
     * @version 0.0.2
     */
    public function formConfig()
    {
        $action = self::getStatic('params', 'action');
        $config = is_object(self::_('config')) ? self::_('config')->toArray() : self::_('config');

        // get form config
        if(isset($config[$action]['form']['merge']) || !isset($config[$action]['form']))
        {
            $formConfig = $this->_applyDefault($this->_createFormOptionsByTable(), $action);
            $formConfig += isset($config[$action]['form']) ? $config[$action]['form'] : array();
        }
        else
            $formConfig = $config[$action]['form'];

        return Evil_Form::callback($formConfig, 'default');
    }

    protected function _applyDefault($config, $action)
    {
        $default = $this->_actConfig('form', $action);
        if(isset($config['elements']))
        {
            foreach($config['elements'] as $field => $options)
            {
                if(isset($default['elements'][$field]['options']['label']))
                    $default['elements'][$field]['options']['label'] = Evil_Translate::a($default['elements'][$field]['options']['label']);

                if(isset($default['elements'][$field]))
                    $config['elements'][$field] = $default['elements'][$field];
            }
        }

        return $config;
    }

    protected function _actConfig($part = null, $action = '')
    {
        $action    = empty($action) ? self::getStatic('params', 'action') : $action;
        $path      = __DIR__ . '/' . ucfirst($action) . '/application/configs/' . $action . '.json';
        $actConfig = array();

        if(is_file($path))
        {
            $actConfig = json_decode(file_get_contents($path), true);

            if($part && isset($actConfig[$part]))
                return $actConfig[$part];
        }

        return $actConfig;
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
                '/configs/';
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
     * @version 0.0.2
     */
    public function data()
    {
        $params = self::getStatic('params');

        if(isset($params['do']))// do something?
            $this->_prepareDataForAction();
        else
            self::$_info['params']['do'] = 'default';

        $result = $this->_action();// force action
        if(isset(self::$_info['params']['partial']))
        {
            $result = is_object($result) ? $result->toArray() : $result;
            die(json_encode($result));
        }

        return $result;
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
     * @version 0.0.2
     */
    protected function _prepareDataForAction()
    {
        $params     = self::getStatic('params');
        $controller = self::getStatic('controller');

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
     * @version 0.0.3
     */
    public function ifViewNotExistsRenderDefault()
    {
        $controller = self::getStatic('controller');

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
     * @version 0.0.2
     */
    public function fillForm()
    {
        $data = self::getStatic('data');

        $form = new Zend_Form(self::getStatic('formConfig'));

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
     * @version 0.0.2
     */
    protected function _createFormOptionsByTable($ignorePersonalConfig = false)
    {
        $table  = self::_('table');
        $action = self::getStatic('params', 'action');
        $config = self::_('config');

        if(!$ignorePersonalConfig)
        {
            $controllerConfig = isset(self::_('controller')->selfConfig[$action]['form']) ?
                    self::_('controller')->selfConfig[$action]['form'] :
                    array('elements' => array());

            $actionConfig = isset($config->$action) ? $config->$action->toArray() : array();
        }
        else
        {
            $controllerConfig = array();
            $actionConfig     = array();
        }

        $metadata = $table->info('metadata');// get metadata
        self::$metadata = $metadata;// save for different aims
        
        $options = array('method' => 'post', 'elements' => array());// basic options
        $options = array_merge_recursive($options, $controllerConfig);

        foreach($metadata as $columnName => $columnScheme)
        {
            if($columnScheme['PRIMARY'])// don't show if primary key
                continue;

            $typeOptions = Evil_Form::getFieldType($columnScheme['DATA_TYPE']);// return array('type'[, 'options'])

            $attrOptions = array('label' => ucfirst($columnName));
            if(isset($actionConfig['default']))
                $attrOptions += $actionConfig['default'];

            $options = Evil_Form::setFormField($options, $columnName, $attrOptions, $typeOptions);
        }

        $options['elements']['do']     = array('type' => 'hidden', 'options' => array('value' => $action));// add submit button
        $options['elements']['submit'] = array('type' => 'submit');// add submit button

        return $options;
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

    /**
     * @description get param form the self::$_info
     * @static
     * @param string $name
     * @return null
     * @author Se#
     * @version 0.0.1
     */
    public static function getStatic()
    {
        $args = func_get_args();

        if(empty($args))
            return self::$_info;

        $root  = self::$_info;
        $count = sizeof($args);

        for($i = 0; $i < $count; $i++)
        {
            if(is_array($root) && isset($root[$args[$i]]))
                $root = $root[$args[$i]];
            else
                return null;
        }

        return $root;
    }

    public static function partial($controllerName, $actionName, $actionDo = 'default')
    {
        $link = 'http://' . $_SERVER['SERVER_NAME'] . '/'
                                  . $controllerName . '/' . $actionName . '/do/' . $actionDo . '/partial/yes';

        $data = file_get_contents($link);
        return $data;
    }

    /**
     * @description get attribute from th self::$_info
     * @param string $name
     * @return mixed
     * @author Se#
     * @version 0.0.1
     */
    public function __get($name)
    {
        $name = (string) $name;
        return isset(self::$_info[$name]) ? self::$_info[$name] : null;
    }

    public static function _($name, $value = null)
    {
        $name = (string) $name;
        $attr = isset(self::$_info[$name]) ? self::$_info[$name] : null;

        if((null !== $attr) && $value)
        {
            self::$_info[$name] = $value;
            $attr = $value;
        }

        return $attr;
    }
}