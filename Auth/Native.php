<?php
/**
 * User: breathless
 * Date: 23.10.10
 * Time: 13:16
 * Class: Evil_Auth_Native
 * Description:
 */
 
    class Evil_Auth_Native implements Evil_Auth_Interface 
    {   
    	// Custom auth | Artemy
    	private function _doCustomAuth($controller, $viewfile)
    	{
    		$login_view = new Zend_View();
        	$login_view->setScriptPath(APPLICATION_PATH.dirname($viewfile));
      
        	// тут мы выдаем сообщения об ошибках
        	// а не выкидываем эксепшны
            if ($controller->getRequest()->isPost())
            {
                $data = $controller->getRequest()->getPost();
                $user = Evil_Structure::getObject('user');	
                $user->where('nickname','=', $data['username']);

                if ($user->load())
                {                       
                    if ($user->getValue('password') == md5($data['password']))
                        return $user->getId();
                    else
                        $login_view->error_message = _('Password incorrect');
                }
                else $login_view->error_message = _('User not found');
                
                $login_view->username = $login_view->escape($data['username']);
            }       	  	

        	$controller->view->form = $login_view->render(basename($viewfile));  	
        	
        	return -1;	
    	}
    	
    	    
        public function doAuth ($controller)
        {
        	// Support custom views for auth form
        	$config = Zend_Registry::get('config');
        	$config = (is_object($config)) ? $config->toArray() : $config;
        	
        	if (isset($config['evil']['auth']['native']['view']) && !empty($config['evil']['auth']['native']['view']))
        	{
				return $this->_doCustomAuth($controller, $config['evil']['auth']['native']['view']);
        	}
        	else
        	{
        		$form = new Evil_Auth_Form_Native();           
        		$controller->view->form = $form;
        		
	            if ($controller->getRequest()->isPost())
	                if ($form->isValid($_POST))
	                {
	                    $data = $form->getValues();
	
	                    $user = Evil_Structure::getObject('user');
	
	                    $user->where('nickname','=', $data['username']);
	
	                    if ($user->load())
	                    {                       
	                        if ($user->getValue('password') == md5($data['password']))
	                            return $user->getId();
	                        else
	                            throw new Evil_Exception('Password Incorrect', 4042);
	                    }
	                    else
	                        throw new Evil_Exception('Unknown user', 4044);
	                }        		
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
