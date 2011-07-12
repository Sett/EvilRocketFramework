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
    public function init ()
    {
    	Zend_Registry::set('userid', - 1);
        $this->_ticket = Evil_Structure::getObject('ticket');
    }
    public function routeShutdown (Zend_Controller_Request_Abstract $request)
    {
        parent::routeStartup($request);
       
        
        $config = Zend_Registry::get('config');
        $ingore = Evil_Array::get('evil.auth.ignoreControllers', $config);
        if (is_array($ingore))
        {
        	if(in_array($request->getControllerName(),$ingore))
        	{
        		return null;
        	}
        }
       $this->init();
       $this->audit();
    }
    private function _seal ()
    {
        if (! isset($_SERVER['HTTP_USER_AGENT']))
            $_SERVER['HTTP_USER_AGENT'] = '';
            
        if (! isset($_SERVER['REMOTE_ADDR']))
            $_SERVER['REMOTE_ADDR'] = '';
                
         $seal = $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'];    
        return sha1($seal);
    }
    
    protected function _upTicket ($ticket)
    {
        /**
         * зачем апать этот тикет?
         */
        return;
        $ticket->update(array('created' => time()));
        $logger = Zend_Registry::get('logger');
        $logger->log('Updated', LOG_INFO);
    }
    public function audit ()
    {
        $logger = Zend_Registry::get('logger');
        $_request = Zend_Controller_Front::getInstance()->getRequest();
        $ticketId = $_request->getCookie('SCORETID'); //  $_COOKIE['SCORETID'];
        $ticketSalt = $_request->getCookie('SCORETSL');
        $getTicketID = $_request->getParam('token');
        if (null !== $ticketId) {
            if ($this->_ticket->load($ticketId)) {
                if (null !== $ticketSalt) {
                    if ($this->_ticket->getValue('seal') == $ticketSalt) {
                        if ($this->_seal() == $ticketSalt) {
                            $logger->log('Audited', Zend_Log::INFO);
                            
                            $this->_upTicket($this->_ticket);
                            Zend_Registry::set('userid', $this->_ticket->getValue('user'));
                        } else {
                            $logger->log('Stolen seal', Zend_Log::INFO);
                            $this->annulate();
                        }
                    } else {
                        $logger->log('Broken seal', Zend_Log::INFO);
                        $this->annulate();
                    }
                } else {
                    $logger->log('No seal', Zend_Log::INFO);
                    $this->annulate();
                }
            } else {
                $logger->log('Ticket No Exist', Zend_Log::INFO);
                $this->annulate();
            }
        } else 
        {
              $logger->log('No TID', Zend_Log::INFO);
              $this->register();
        }
        
        if (null != $getTicketID) {
            $_ticket = Evil_Structure::getObject('ticket',$getTicketID);
            if ($_ticket->load()) {
                if ($_ticket->getValue('seal') == '') {
                    Zend_Registry::set('userid', $_ticket->getValue('user'));
                    $_ticket->erase();
                    $this->register();
                } else {
                    $logger->log('Used ticket', Zend_Log::INFO);
                }
            }
        }
    }
    public function register ($useSeal = true)
    {
        $id = uniqid(true, true);
        if ($useSeal) {
            $seal = $this->_seal();
        } else {
            $seal = '';
        }
        $userId = Zend_Registry::get('userid');
        $db = Zend_Registry::get('db');
        $this->_ticket->create($id, 
        array('seal' => $seal, 'user' => $userId, 'created' => time()));
            setcookie('SCORETID', $id, 0, '/');
            setcookie('SCORETSL', $seal, 0, '/');
            return $this->_ticket->getId();

    }
    public function annulate ()
    {
        setcookie('SCORETID', '', 0, '/');
        setcookie('SCORETSL', '', 0, '/');
    }
    public function attach ($username)
    {
        $this->_ticket->setNode('user', $username);
    }
    public function detach ()
    {
        $this->_ticket->setNode('user', - 1);
    }
    public static function factory ($authMethod)
    {
        $authMethod = 'Evil_Auth_' . ucfirst($authMethod);
        return new $authMethod();
         // FIXME Refactor to Evil_Factory
    }
    public static function stupidAuth ()
    {
        $config = Zend_Registry::get('config');
        if (is_object($config))
            $config = $config->toArray();
        $config = Zend_Registry::get('config');
        if (! isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Login"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        } else 
            if (($_SERVER['PHP_AUTH_USER'] != $config['evil']['auth']['stupid']['user']) || (($_SERVER['PHP_AUTH_PW']) !=
             $config['evil']['auth']['stupid']['password']))
                die('403');
    }
    /**
     * 
     * verify username and password
     * on success create session and return apiKEY
     * @param string $username
     * @param string $password
     * @return array
     */
    public function createAPIKey ($username, $password, $delayStart = 0)
    {
        $user = Evil_Structure::getObject('user');
        $user->where('nickname', '=', $username);
        if ($user->load()) {
            if ($user->getValue('password') == md5($password)) {
                $userId = $user->getId();
                Zend_Registry::set('userid', $userId);
                $this->attach($userId);
                $ticketID = $this->register(false);
                return $ticketID;
            } else {
                throw new Evil_Exception('Password incorrect');
            }
        } else {
            throw new Evil_Exception('User not found', 4043);
        }
    }
    /**
     * 
     * @param string $key
     * @throws Evil_Exception
     * return userID on success
     */
    public function verifyAPIKey ($key)
    {
        $ticket = Evil_Structure::getObject('ticket', $key);
        if ($ticket->load()) {
            $userId = $ticket->getValue('user');
            Zend_Registry::set('userid', $userId);
            $this->attach($userId);
            $this->register();
            return $userId;
        } else {
            return false;
        }
    }
    /**
     * 
     * Delete apiKEY from database
     * @param string $key
     */
    public static function annulateAPIKey ($key)
    {
        $db = Zend_Registry::get('db');
        $config = Zend_Registry::get('config');
        if (is_object($config))
            $config = $config->toArray();
        $prefix = $config['resources']['db']['prefix'];
        $db->delete($prefix . 'apisessions', 'apisession="' . $key . '"');
    }
}
