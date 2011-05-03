<?php
/**
 * User: Ilnur
 * Date: 26.04.11
 * Time: 13:16
 * Class: Evil_Auth_Soa
 * Description:
 */
 
    class Evil_Auth_Soa implements Evil_Auth_Interface 
    {   
    	/**
    	 * Custom Auth
    	 * @param Zend_Controller_Action $controller
    	 * @param String $viewfile
    	 */ 
    	private function _doCustomAuth($controller, $viewfile)
    	{
    		$login_view = new Zend_View();
        	$login_view->setScriptPath(APPLICATION_PATH.dirname($viewfile));
        	
        	$config = Zend_Registry::get('config');
        	$config = (is_object($config)) ? $config->toArray() : $config;
        	
        	// FIXME get it from controller
        	if (isset($config['resources']['auth']['namespace']) 
        	    && !empty($config['resources']['auth']['namespace']))
        	{
        	    $namespace = $config['resources']['auth']['namespace'];
        	} else {
        	    $namespace = 'Auth';
        	}
        	
        	// require http post method
            if ($controller->getRequest()->isPost())
            {
                $data = $controller->getRequest()->getPost();
                
                // @todo create new method
                // auth on SOA_Service_Auth
                $call = array(
	                'service' => 'Auth', // FIXME $namespace
	                'method' => 'keyGet',
	                'data' => array(
	                    'login' => $data['username'],
	                    'password' => $data['password'],
	                    // FIXME change to 'timeout' => $config['evil']['auth']['soa']['timeout']
	                    'timeout' => 3000
	                 )
                );
                $result = $controller->rpc->make($call);

                if (isset($result['result'][0]) 
                    && $result['result'][0] == 'Success'
                    && isset($result['result'][2]['key']))
                {
//                    $session = array();
//                    $session['key'] = $result['result'][2]['key'];
//                    $session['setExpirationSeconds'] = $result['result'][2]['endtime'] - microtime(true);
//                    Zend_Registry::set('session');
//                    $session = new Zend_Session_Namespace($namespace);
//                    $session->key = $result['result'][2]['key'];
//                    // FIXME
//                    $session->setExpirationSeconds($result['result'][2]['endtime'] - microtime(true));
                    
                    // get user info
                    $call = array(
	                	'service' => 'Auth', // FIXME $namespace
	                	'method' => 'userInfo',
	                	'data' => array(
	                    	'key' => $result['result'][2]['key'],
                            'array' => 1
	                    )
                    );
                    $result = $controller->rpc->make($call);
                    
                    if (isset($result['result'][0]) 
                        && $result['result'][0] == 'Success'
                        && isset($result['result'][2]['user']))
                    {
                        // insert into local users table
                        $user = $result['result'][2]['user'];

//                        $session->user = $user;
                        
                        // FIXME $role = (empty($user['role']) ? $config['evil']['auth']['soa']['defaultrole'] : $user['role']);
                        $role = (empty($user['role']) ? 'citizen' : $user['role']);
                        $login = $user['login'];                        
                        
                        $evilUser = Evil_Structure::getObject('user');
                        $evilUser->where('nickname', '=', $user['login']);
                        
                        $data = array(
    						'nickname' => $login,
                        	'password' => $result['result'][2]['key'],//'do not store any password on local system',
                        	'role' => $role
                        );
                        
                        // cache user info in local system 
                        if ($evilUser->load())
                        {
                            $evilUser->update($data);
                            return $evilUser->getId();
                        } else {
                            $data['uid'] = uniqid();
                            $evilUser->create(null, $data);
                            
                            // reload for get id
                            $evilUser->where('nickname', '=', $user['login']);
                            
                            if ($evilUser->getId())
                            {
                                return $evilUser->getId();
                            }
                        }
                    }
                }
                $login_view->error_message = _('User not found');
                
                $login_view->username = $login_view->escape($data['username']);
            }       	  	

        	$controller->view->form = $login_view->render(basename($viewfile));  	
        	
        	return -1;	
    	}
    	
    	    
    	/**
    	 * Auth user
    	 * @param Zend_Controller_Action $controller
    	 */
        public function doAuth ($controller)
        {
        	// Support custom views for auth form
        	$config = Zend_Registry::get('config');
        	$config = (is_object($config)) ? $config->toArray() : $config;

        	if (!isset($controller->rpc))
        	{
        	    throw new Evil_Exception('RPC not specified in controller');
        	}
        	        	
        	if (isset($config['evil']['auth']['soa']['view']) && !empty($config['evil']['auth']['soa']['view']))
        	{
				return $this->_doCustomAuth($controller, $config['evil']['auth']['soa']['view']);
        	}       	
        	else
        	{
        	    // FIXME
        	    /*
        		$form = new Evil_Auth_Form_Native();           
        		$controller->view->form = $form;
        		
	            if ($controller->getRequest()->isPost())
	                if ($form->isValid($_POST))
	                {
	                    $data = $form->getValues();
	                    
	                    $call = array(
	                        'service' => 'Auth',
	                        'method' => 'keyGet',
	                        'data' => array(
	                            'login' => $data['username'],
	                            'password' => $data['password'],
	                            // FIXME change to 'timeout' => $config['evil']['auth']['soa']['timeout']
	                            'timeout' => 3000
	                        )
	                    );
	                    $result = $controller->rpc->make($call);
	
	                    print __METHOD__ . "\n";
	                    var_dump($result);
	                }
	                */
        	}
            
            return -1;
        }
        
        /**
         * @todo make more normal name
         * Unauth user
         * @param Zend_Controller_Action $controller
         */
        public function doUnAuth($controller) {
            
            // FIXME get it from controller
//            if (isset($config['resources']['auth']['namespace']) 
//        	    && !empty($config['resources']['auth']['namespace']))
//        	{
//        	    $namespace = $config['resources']['auth']['namespace'];
//        	} else {
//        	    $namespace = 'Auth';
//        	}

            $uid = Zend_Registry::get('userid');
            
            var_dump($uid);
        
            if (!isset($uid))
            {
                // FIXME redirect to error page
                return;
            }
        
            $evilUser = Evil_Structure::getObject('user');
            $evilUser->where('id', '=', $uid);
            if (!$evilUser->load())
            {
                return -1;
            }
        
            $key = $evilUser->getValue('password');
            $login = $evilUser->getValue('nickname');
            
            var_dump($key, $login);
        	
//        	$session = new Zend_Session_Namespace($namespace);
        	
            if (!empty($key) && !empty($login))
            {
                $call = array(
                	'service' => 'Auth', // FIXME $namespace
                	'method' => 'keyBreak',
                    'data' => array('key' => $key)
                );
                $result = $controller->rpc->make($call);

                var_dump($result);
                // FIXME
//                if (isset($result['result'][0]) 
//                    && $result['result'][0] == 'Success')
//                {}                    
                return $evilUser->getId();
            }
            return -1; 
        }

        public function onFailure()
        {
            // TODO: Implement onFailure() method.
        }

        public function onSuccess()
        {
            // TODO: Implement onSuccess() method.
        }

    }
