<?php

    class Evil_Log extends Zend_Controller_Plugin_Abstract
    {
        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
           $logger = new Zend_Log();

           // TODO: Configurable Logger.
           $logger->addWriter(new Zend_Log_Writer_Firebug());

           $config = Zend_Registry::get('config');
           if (isset($config['evil']['log']['expose']['svn']) and $config['evil']['log']['expose']['svn'])
           {
               exec ('svn info', $svn);
               $logger->log($svn[4], Zend_Log::INFO);
           }

            $columnMapping = array('lvl' => 'priority', 'msg' => 'message');
            $dbWriter = new Zend_Log_Writer_Db(Zend_Registry::get('db'), Zend_Registry::get('db-prefix').'log', $columnMapping);

            $onlyCrit = new Zend_Log_Filter_Priority(Zend_Log::CRIT);
            $dbWriter->addFilter($onlyCrit);

            $logger->addWriter($dbWriter);

            Zend_Registry::set('logger',$logger);
        }

        public static function info($message)
        {
            if (Zend_Registry::isRegistered('logger'))
            {
                $logger = Zend_Registry::get('logger');
                $logger->log($message, Zend_Log::INFO);
            }
        }
    }