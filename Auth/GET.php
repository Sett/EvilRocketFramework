<?php
/**
 * @author BreathLess
 * @name Evil_Auth_Basic
 * @description: Basic HTTP Auth
 * @package Evil
 * @subpackage Access
 * @version 0.1
 * @date 24.10.10
 * @time 15:12
 */
class Evil_Auth_GET implements Evil_Auth_Interface
{
    public function doAuth ($controller)
    {
    	
        if (! isset($_GET['userid']) && ! isset($_GET['password'])) {
            throw new Exception("Mailformed request", 403);
            exit();
        } else {
            $user = Evil_Structure::getObject('user');
            $user->where('nickname', '=', $_GET['userid']);
            if ($user->load()) {
                if ($user->getValue('password') == md5($_GET['password'])) {
                    $apiSession = Evil_Structure::getObject('api-sessions');
                    $apiSession->where('userid', '=', $user->getId());
                  
                    if ($apiSession->load()) {
                    	  //Existing record
                    	  Zend_Debug::dump($apiSession);
                    } else {
                        //new record, insert data
                    }
                    return $user->getId();
                } else
                    throw new Exception('Password Incorrect');
            } else
                throw new Exception('Unknown user');
        }
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