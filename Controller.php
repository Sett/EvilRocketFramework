<?php

    /**
     * @author BreathLess, Se#
     * @name Evil Controller
     * @type Zend Controller
     * @description: More than a CRUD From Codeine
     * @package Evil
     * @subpackage Code
     * @version 0.2.2
     * @date 25.05.10
     * @time 10:58
     */

    class Evil_Controller extends Zend_Controller_Action 
    {
        /**
         * @description self config, mast be placed at APP_PATH /configs/Controllers/ControllerName.json
         * @var array
         * @author Se#
         * @version 0.0.1
         */
        public $selfConfig = array();

        /**
         * @description initialize self config (if it exists)
         * @return void
         * @author Se#
         * @version 0.0.1
         */
        public function init()
        {
            $path = APPLICATION_PATH . '/configs/Controllers/' . $this->_getParam('controller') . '.json';
            $action = $this->_getParam('action');

            if(file_exists($path))
            {
                $this->selfConfig = json_decode(file_get_contents($path), true);

                // Append CSS if it needs
                if(isset($this->selfConfig[$action]['css']))
                {
                    foreach($this->selfConfig[$action]['css'] as $css)
                        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . $css);
                }
                // Append JS if it needs
                if(isset($this->selfConfig[$action]['js']))
                {
                    foreach($this->selfConfig[$action]['js'] as $js)
                        $this->view->headScript()->appendFile($this->view->baseUrl() . $js);
                }
            }
            else
                Evil_Action_Abstract::autoLoad($this);
        }

        /**
         * @description Operate unknown action - try to load Evil_Action_$action
         * @param string $methodName
         * @param array $args
         * @return mixed
         * @version 0.0.1
         */
        public function __call($methodName, $args)
        {
            if (strpos($methodName, 'Action') !== false)
            {
                $config = Zend_Registry::get('config');
                if(isset($config['evil']['controller']['action']['extension']))
                    $this->selfConfig['ext'] = $config['evil']['controller']['action']['extension'];
                else
                    $this->selfConfig['ext'] = 'ini';

                //$methodClass = 'Evil_Action_'.ucfirst($this->_getParam('action'));// Se#
                // Se#:
                $namespace   = self::getNamespace($this->selfConfig);
                $methodClass = $namespace . ucfirst(substr($methodName, 0, strpos($methodName, 'Action')));// BreathLess

                $method = new $methodClass();
                $method ($this, $this->_getAllParams());// call __invoke
            }
            else
                return call_user_func_array(array(&$this, $methodName), $args);
        }

        /**
         * @description get action namespace
         * @static
         * @param array $selfConfig
         * @return string
         * @author Se#
         * @version 0.0.1
         */
        public static function getNamespace($selfConfig = array())
        {
            if(!empty($selfConfig) && isset($selfConfig['namespace']))
                $namespace = $selfConfig['namespace'];
            else
            {
                $namespace = 'Evil_Action_';

                if(is_file(APPLICATION_PATH . '/configs/evil/controller.json'))
                {
                    $config = json_decode(file_get_contents(APPLICATION_PATH . '/configs/evil/controller.json'), true);
                    if(isset($config['namespace']))
                        $namespace = $config['namespace'];
                }
            }
            
            return $namespace;
        }

        /**
         * turn of layout and view
         *
         * @param boolean view
         * @param boolean layout
         * @access protected
         * @return boolean
         */
        protected function turnOff($view = true, $layout = true)
        {
            // turn off all unnessesary
            if($view)   $this->_helper->viewRenderer->setNoRender();
            if($layout) $this->_helper->layout()->disableLayout();
            return true;
        }
    }