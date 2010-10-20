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

        protected function enableCache ()
        {
        	$frontendOptions = array(
                'cache_id_prefix' => 'Cache_',
        		'automatic_serialization' => true
            );
            // Rediska options
            $backendOptions = array();

            $cache = Zend_Cache::factory(
                'Core',
                'Rediska_Zend_Cache_Backend_Redis',
                $frontendOptions,
                $backendOptions,
                false,
                true
            );

            Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

            return $this;
        }
    }
