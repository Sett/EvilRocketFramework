<?php

    class Evil_Log extends Zend_Controller_Plugin_Abstract
    {
        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
           $logger = new Zend_Log();
           $writer = new Zend_Log_Writer_Firebug();
           $logger->addWriter($writer);

           Zend_Registry::set('logger',$logger);
        }
    }