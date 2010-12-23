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

        public function init()
        {
            $this->_ticket = Evil_Structure::getObject('ticket');
            Zend_Registry::set('userid', -1);
        }

        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
            parent::routeStartup($request);
            $this->init();
            $this->audit();
        }

        public function routeShutdown(Zend_Controller_Request_Abstract $request)
        {
            parent::routeShutdown($request);
        }

        private function _seal ()
        {
            if (!isset($_SERVER['HTTP_USER_AGENT']))
                $_SERVER['HTTP_USER_AGENT'] = '';
            $logger = Zend_Registry::get('logger');
            $logger->log('seal HUA: ' . $_SERVER['HTTP_USER_AGENT'] . '; sha1: ' . sha1($_SERVER['HTTP_USER_AGENT']), LOG_CRIT);
            return sha1($_SERVER['HTTP_USER_AGENT']);
        }

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
                                $logger->log('Audited ' . $this->_ticket->getValue('user'), Zend_Log::INFO);
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

        public function register()
        {
            $id = uniqid(true);
            $seal = $this->_seal();

            $userId = Zend_Registry::get('userid');
            $db     = Zend_Registry::get('db');
            $ticket = null;

            $logger = Zend_Registry::get('logger');
            $logger->log('No TID uid=' . $userId, LOG_CRIT);

            if(-1 != $userId){
                $ticket = $db->fetchAll($db->select()->from('score_' . 'tickets')->where('user=?', $userId)->where('seal=?', $seal));

                if(is_object($ticket))
                    $ticket = $ticket->toArray();
            }

            if(empty($ticket)){
                if(!$db->delete('score_tickets', 'seal="' . $seal . '"'))
                    $logger->log('Can not delete', LOG_CRIT);
                else
                    $logger->log('Deleted, seal=' . $seal, LOG_CRIT);

                $this->_ticket->create($id, array('seal' => $seal, 'user'=> -1, 'created'=>time()));
                setcookie('SCORETID', $id, 0, '/');
                setcookie('SCORETSL', $seal, 0, '/');
            }
            else
                $db->update('score_tickets', array('created' => time()), 'id="' . $ticket[0]['id'] . '"');
        }

        public function annulate()
        {
            setcookie('SCORETID', '', 0, '/');
            setcookie('SCORETSL', '', 0, '/');
        }

        public function attach($username)
        {
            $this->_ticket->setNode('user', $username);
        }

        public function detach()
        {
            $this->_ticket->setNode('user', -1);
        }

        public static function factory ($authMethod)
        {
            $authMethod = 'Evil_Auth_'.ucfirst($authMethod);
            return new $authMethod();
        }
    }