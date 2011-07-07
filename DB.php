<?php
/**
 * Evil DB Front Controller Plugin 
 */
 
    class Evil_DB extends Zend_Controller_Plugin_Abstract
    {
        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
            parent::routeStartup($request);

            $this->controllerDrivenDB($request);
            $this->enableCache ();
        }

        public function controllerDrivenDB($request)
        {
            $controller = $request->getControllerName();
            $config = Zend_Registry::get('config');

            if (isset($config['resources']['db'][$controller]))
            {
                $db = Zend_Db::factory(
                    $config['resources']['db'][$controller]['adapter'],
                    $config['resources']['db'][$controller]['params']);

                Zend_Registry::set('db-prefix',$config['resources']['db'][$controller]['prefix']);
            }
            else
            {
                $db = Zend_Db::factory(
                    $config['resources']['db']['adapter'],
                    $config['resources']['db']['params']);
                Zend_Registry::set('db-prefix',$config['resources']['db']['prefix']);
            }

            if ($config['evil']['db']['profiling'])
            {
                $profiler = new Zend_Db_Profiler_Firebug('DB Queries');
                $profiler->setEnabled(true);
                $db->setProfiler($profiler);
            }

            Zend_Registry::set('db',$db);
            Zend_Db_Table_Abstract::setDefaultAdapter($db);
        }

        public function fallbackDB ()
        {
            // TODO: Fallback support
        }

        public function mirrorDB ()
        {
            // TODO: Mirroring support
        }

        public function shardDB ()
        {
            // TODO: Sharding support
        }

        public function enableCache()
        {
            if (extension_loaded('xcache'))
            {
                  $frontendOptions = array(
                     'lifetime' => 60 * 5, //5min
                     'automatic_serialization' => true
                  );

                  $backendOptions = array();

                  $cache = Zend_Cache::factory('Core',
                                               'XCache',
                                               $frontendOptions,
                                               $backendOptions);

                  Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
            }
            else
                Evil_Log::info('xcache recommended');
        }

        public static function scope2table($scope, $type = '')
        {
            $prefix = Zend_Registry::get ('db-prefix');

            if (substr($scope, strlen($scope)-1) != 's')
               $postfix = 's';
            else
               $postfix = '';

            return $prefix.$scope.$postfix.$type;
        }
    }
