<?php

    /**
     * @author BreathLess
     * @name Evil_Auth Plugin
     * @type Zend Plugin
     * @description: Auth Engine for ZF
     * @package Evil
     * @subpackage Access
     * @version 0.2
     * @date 24.10.10
     * @time 14:20
     */

    class Evil_Auth extends Zend_Controller_Plugin_Abstract 
    {
        /**
         * @var $_ticket
         * @description Ticket Object
         */
        private $_ticket;

        /**
         * @return void
         */
        public function init()
        {
            $this->_ticket = Evil_Structure::getObject('ticket');
            Zend_Registry::set('userid', -1);
        }

        /**
         * @param Zend_Controller_Request_Abstract $request
         * @return void
         */
        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
            parent::routeStartup($request);
            $this->init();
            $this->audit();
        }

        /**
         * @param Zend_Controller_Request_Abstract $request
         * @return void
         */
        public function routeShutdown(Zend_Controller_Request_Abstract $request)
        {
            parent::routeShutdown($request);
        }

        /**
         * @return string
         */
        private function _seal ()
        {
            if (!isset($_SERVER['HTTP_USER_AGENT']))
                $_SERVER['HTTP_USER_AGENT'] = '';

            return sha1($_SERVER['HTTP_USER_AGENT']);
        }

        /**
         * @param  $user
         * @return void
         */
        protected function _upTicket($user)
        {
            $config = Zend_Registry::get('config');
            if(is_object($config))
                $config = $config->toArray();

            $prefix = $config['resources']['db']['prefix'];

            Zend_Registry::get('db')->update($prefix . 'tickets',
                                             array('created' => time()),
                                             'user="' . $user . '"');

            $logger = Zend_Registry::get('logger');
            $logger->log('Updated', LOG_INFO);
        }

        /**
         * @return void
         */
        public function audit ()
        {
            $logger = Zend_Registry::get('logger');

            if (isset($_COOKIE['SCORETID']))
            {
                if ($this->_ticket->load($_COOKIE['SCORETID']))
                {
                    if (isset($_COOKIE['SCORETSL']))
                    {
                        if ($this->_ticket->getValue('seal') == $_COOKIE['SCORETSL'])
                        {
                            if ($this->_seal() == $_COOKIE['SCORETSL'])
                            {
                                $logger->log('Audited', Zend_Log::INFO);
                                $this->_upTicket($this->_ticket->getValue('user'));
                                Zend_Registry::set('userid', $this->_ticket->getValue('user'));
                            }
                            else
                            {
                                $logger->log('Stolen seal', Zend_Log::INFO);
                                $this->annulate();
                            }
                        }
                        else
                        {
                            $logger->log('Broken seal', Zend_Log::INFO);
                            $this->annulate();
                        }
                    }
                    else
                    {
                        $logger->log('No seal', Zend_Log::INFO);                        
                        $this->annulate();
                    }
                }
                else
                {
                    $logger->log('Ticket No Exist', Zend_Log::INFO);
                    $this->annulate();
                }
            }
            else
            {
                $logger->log('No TID', Zend_Log::INFO);
                $this->register();
            }

        }

        /**
         * @return void
         */
        public function register()
        {
            $id = uniqid(true);
            $seal = $this->_seal();

            $userId = Zend_Registry::get('userid');
            $db     = Zend_Registry::get('db');
            $ticket = null;

            if(-1 != $userId){
                $ticket = $db->fetchAll($db->select()->from('score_' . 'tickets')->where('user=?', $userId)->where('seal=?', $seal));

                if(is_object($ticket))
                    $ticket = $ticket->toArray();
            }

            if(empty($ticket)){
                $db->delete('score_tickets', 'seal="' . $seal . '"');
                $this->_ticket->create($id, array('seal' => $seal, 'user'=> -1, 'created'=>time()));
                setcookie('SCORETID', $id, 0, '/');
                setcookie('SCORETSL', $seal, 0, '/');
            }
            else
                $db->update('score_tickets', array('created' => time()), 'id="' . $ticket[0]['id'] . '"');
        }

        /**
         * @return void
         */
        public function annulate()
        {
            setcookie('SCORETID', '', 0, '/');
            setcookie('SCORETSL', '', 0, '/');
        }

        /**
         * @param  $username
         * @return void
         */
        public function attach($username)
        {
            $this->_ticket->setNode('user', $username);
        }

        /**
         * @return void
         */
        public function detach()
        {
            $this->_ticket->setNode('user', -1);
        }

        /**
         * @static
         * @param  $authMethod
         * @return
         */
        public static function factory ($authMethod)
        {
            $authMethod = 'Evil_Auth_'.ucfirst($authMethod);
            return new $authMethod();
            // FIXME Refactor to Evil_Factory
        }

        /**
         * @static
         * @return void
         */
        public static function stupidAuth()
        {
            $config = Zend_Registry::get('config');
            
            if (!isset($_SERVER['PHP_AUTH_USER']))
            {
                header('WWW-Authenticate: Basic realm="Login"');
                header('HTTP/1.0 401 Unauthorized');
                exit;
            }
            else
                if (($_SERVER['PHP_AUTH_USER'] != $config['evil']['auth']['stupid']['user']) ||
                            (($_SERVER['PHP_AUTH_PW']) != $config['evil']['auth']['stupid']['password']))
                    die('403');
        }

        /**
         * @static
         * @param  $username
         * @param  $password
         * @return void
         */
        public static function createAPIKey ($username, $password)
        {
            $user = Evil_Structure::getObject('user');

            if ($user->load($username))
            {
                if (md5($user->getValue('password')) == $password)
                {
                    Evil_Fn::run(array(
                        ''
                    ));
                }
            }
        }

        /**
         * @static
         * @param  $key
         * @return void
         */
        public static function verifyAPIKey ($key)
        {

        }

        /**
         * @static
         * @param  $key
         * @return void
         */
        public static function annulateAPIKey ($key)
        {

        }
    }
