<?php

    class Evil_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
    {
        private $_config;
        public function run ()
        {

           Zend_Registry::set('config', $this->_config = parent::getOptions());

           $front = $this->getResource('FrontController');
           header('Content-type: text/html;charset=utf-8');

           foreach ($this->_config['bootstrap']['plugins'] as $plugin)
                $front->registerPlugin(new $plugin);

           Zend_Controller_Front::run($this->_config['resources']['frontController']['controllerDirectory']);
        }

        protected function _initView()
        {
            Zend_Layout::startMvc(array(
                'layoutPath' => APPLICATION_PATH.'/views/scripts/layouts/',
                'layout'=> 'layouts/layout'
            ));

            $layout = Zend_Layout::getMvcInstance();

            $view = $layout->getView();
           $view->addHelperPath(APPLICATION_PATH.'/views/helpers');
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
                'ViewRenderer'
            );

            $viewRenderer->setViewSuffix('phtml');
            $viewRenderer->setView($view);

            return $view;
        }
        /*
        protected function _setRouter()
        {
           $router = new Score_Router();
           // If router not a Zend_Controller_Router_Abstract, throw the Exception
           if (!($router instanceof Zend_Controller_Router_Abstract))
               throw new Exception('Incorrect config file: routes');
           return $router;
        }
        */
        protected function _initPlaceholders()
            {
                $this->bootstrap('View');
                $config = Zend_Registry::get('config');

                $view = $this->getResource('View');
                $view->doctype('XHTML1_STRICT');
                $view->headTitle($config['system']['title']);
            }


    }
