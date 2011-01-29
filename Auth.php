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
         * Tickets table prefix
         *
         * @var string
         */
        protected $_prefix = '';

        /**
         * Application config
         *
         * @var string
         */
        protected $_config = '';

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
            $config = Zend_Registry::get('config');
            if(is_object($config))
                $config = $config->toArray();

            $this->_prefix = $config['resources']['db']['prefix'];
            $this->_config = $config;
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
        private function _seal ($ticketID)
        {
            return sha1($ticketID . $this->_config['evil']['ticket']['key']);
        }

        /**
         * @param  $user
         * @return void
         */
        protected function _upTicket($user, $seal)
        {
            Zend_Registry::get('db')->update($this->_prefix . 'tickets',
                                             array('created' => time()),
                                             '(user="' . $user . '")&&(seal="'. $seal .'")');

            $logger = Zend_Registry::get('logger');
            $logger->log('Updated', LOG_INFO);
        }

        /**
         * @return void
         */
        public function audit ()
        {
            $logger = Zend_Registry::get('logger');
            $cookiePrefix = strtoupper(rtrim($this->_prefix,'_'));

            if (isset($_COOKIE[$cookiePrefix . 'TID']))
            {
                if ($this->_ticket->load($_COOKIE[$cookiePrefix . 'TID']))
                {
                    if (isset($_COOKIE[$cookiePrefix . 'TSL']))
                    {
                        if ($this->_ticket->getValue('seal') == $_COOKIE[$cookiePrefix . 'TSL'])
                        {
                            if ($this->_seal($_COOKIE[$cookiePrefix . 'TID']) == $_COOKIE[$cookiePrefix . 'TSL'])
                            {
                                $logger->log('Audited', Zend_Log::INFO);
                                $this->_upTicket($this->_ticket->getValue('user'), $_COOKIE[$cookiePrefix . 'TSL']);
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
            $id = md5(uniqid(true) . time() . mt_rand(0, 9999));
            $seal = $this->_seal($id);

            $userId = Zend_Registry::get('userid');
            $db     = Zend_Registry::get('db');
            $ticket = null;

            if(-1 != $userId){
                $ticket = $db->fetchAll($db->select()->from($this->_prefix . 'tickets')->where('user=?', $userId)->where('seal=?', $seal));

                if(is_object($ticket))
                    $ticket = $ticket->toArray();
            }

            if(null == $ticket){
                $db->delete($this->_prefix . 'tickets', 'seal="' . $seal . '"');
                $this->_ticket->create($id, array('seal' => $seal, 'user'=> $userId, 'created'=>time()));
                $cookiePrefix = strtoupper(rtrim($this->_prefix,'_'));
                setcookie($cookiePrefix . 'TID', $id, 0, '/');
                setcookie($cookiePrefix . 'TSL', $seal, 0, '/');
            }
            else
                $db->update($this->_prefix . 'tickets', array('created' => time()), 'id="' . $ticket[0]['id'] . '"');
        }

        /**
         * @return void
         */
        public function annulate()
        {
            $cookiePrefix = strtoupper(rtrim($this->_prefix,'_'));
            setcookie($cookiePrefix . 'TID', '', 0, '/');
            setcookie($cookiePrefix . 'TSL', '', 0, '/');
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
            return Evil_Factory::make('Evil_Auth_'.ucfirst($authMethod));
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
