<?php

    class Evil_Log extends Zend_Controller_Plugin_Abstract
    {
        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
           $logger = new Zend_Log();

           // TODO: Configurable Logger.
           $logger->addWriter(new Zend_Log_Writer_Firebug());

           $config = Zend_Registry::get('config');
           $logExp = isset($config['evil']['log']['expose']) ? $config['evil']['log']['expose'] : array();

            if(isset($logExp['use']) && method_exists($this, $logExp['use']) && $logExp[$logExp['use']])
                call_user_func_array(array($this, $config['evil']['log']['expose']['use']), array($logger));

            //$columnMapping = array('lvl' => 'priority', 'msg' => 'message');
            //$dbWriter = new Zend_Log_Writer_Db(Zend_Registry::get('db'), Zend_Registry::get('db-prefix').'log', $columnMapping);

           // $onlyCrit = new Zend_Log_Filter_Priority(Zend_Log::CRIT);
           // $dbWriter->addFilter($onlyCrit);

 //           $logger->addWriter($dbWriter);

            Zend_Registry::set('logger',$logger);
        }

        /**
         * @description get git info
         * @param object $logger
         * @return void
         * @author Se#
         * @version 0.0.1
         */
        public function git($logger)
        {

            exec('git log -n 1', $git);
            /**
             * $git = array(
             *      0 => commit commitHash
             *      1 => Author: AuthorName
             *      2 => Date: Thu Jun 9 15:09:47 2011 +0400
             * )
             */
            if(isset($git[0]) && isset($git[1]) && isset($git[2]))
                $logger->log($git[0] . '; ' . $git[1] . '; ' . $git[2], Zend_Log::INFO);
        }

        /**
         * @description get svn info
         * @param object $logger
         * @return void
         * @author Se#
         * @version 0.0.1
         */
        public function svn($logger)
        {
            exec ('svn info', $svn);
            if(isset($svn[4]))
                $logger->log($svn[4], Zend_Log::INFO);
        }

        public static function info($message)
        {
            if (Zend_Registry::isRegistered('logger'))
            {
                $logger = Zend_Registry::get('logger');
                $logger->log($message, Zend_Log::INFO);
            }
        }
        
    public static function log ($message, $levl = Zend_Log::INFO, $data = null)
    {
        if (Zend_Registry::isRegistered('logger')) {
            $logger = Zend_Registry::get('logger');
            /**
             * прислали что то на вардамп
             */
            if (null != $data) {
                /**
                 * 
                 * сделаем табличку
                 * @var unknown_type
                 */
                $_message = new Zend_Wildfire_Plugin_FirePhp_TableMessage(
                $message);
                $_message->setHeader(array('Data'));
                $_message->addRow(array(($data)));
                $logger->log($_message, $levl);
            } else {
                $logger->log($message, $levl, $extras);
            }
        }
    }
    }