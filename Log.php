<?php

    class Evil_Log extends Zend_Controller_Plugin_Abstract
    {
        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
           $logger = new Zend_Log();
           // TODO: Configurable Logger.
           $logger->addWriter(new Zend_Log_Writer_Firebug());

           // TODO: New option: SVN Expose
           exec ('svn info', $svn);
           $logger->log($svn[4], Zend_Log::INFO);
           
           Zend_Registry::set('logger',$logger);
        }
    }