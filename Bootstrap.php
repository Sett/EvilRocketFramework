<?php

    class Evil_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
    {
        private $_config;
        public function run ()
        {
           Zend_Registry::set('config', $this->_config = parent::getOptions());

           $front = $this->getResource('FrontController');
       // $front->getResponse()->setHeader('Content-type','text/html;charset=utf-8');
           $front->setParam('bootstrap', $this);
           header('Content-type: text/html;charset=utf-8');
           if (isset($this->_config['bootstrap']['plugins']))
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

            $config = parent::getOptions();
            $view->headTitle($config['system']['title']);
            $view->doctype('XHTML1_STRICT');

            return $view;
        }
    }
