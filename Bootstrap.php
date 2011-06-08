<?php

    class Evil_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
    {
        private $_config;
        public function run ()
        {
           Zend_Registry::set('config', $this->_config = parent::getOptions());

           $front = $this->getResource('FrontController');
           $front->setParam('bootstrap', $this);
           if (isset($this->_config['bootstrap']['plugins']))
               foreach ($this->_config['bootstrap']['plugins'] as $plugin)
                    $front->registerPlugin(new $plugin);
           $front->run($this->_config['resources']['frontController']['controllerDirectory']);
          
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
            $view->setEncoding('UTF-8');        
            $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
            $config = parent::getOptions();
            $view->headTitle($config['system']['title']);
            $view->doctype('XHTML1_STRICT');

            return $view;
        }
        
        
         protected function _initModifiedFrontController ()
        {
            $this->bootstrap('FrontController');
            $front = $this->getResource('FrontController');
            $response = new Zend_Controller_Response_Http();
            $response->setHeader('Content-Type', 'text/html; charset=UTF-8', true);
            $front->setResponse($response);
        }
    }
