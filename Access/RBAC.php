<?php
    /**
     * @author Artemy
     * @name Evil_Access_RBAC Plugin
     * @type Zend Plugin
     * @description: Access Engine for ZF
     * @package Evil
     * @subpackage Access
     * @version 0.2
     * @date 24.10.10
     * @time 14:20
     */

  class Evil_Access_RBAC extends Evil_Access_Abstract
  {
        /**
         * Initialize rules
         *
         * @throws Exception
         * @return bool
         */
        public function init ()
        {
            self::$_rules = json_decode(file_get_contents(APPLICATION_PATH.'/configs/roles.json'), true);
            
            if (!self::$_rules)
                throw new Exception('JSON-encoded file "/configs/roles.json" is corrupted');
            
            return true;
        }  	
  	
        // by Artemy
        /**
         * Check access
         *
         * @param string $subject
         * @param string $controller
         * @param string $action
         * @return bool
         */
        public function _check ($subject, $controller, $action)
        {
        	$decision	= false;
        	$object 	= Zend_Registry::get('userid');
        	$user 		= Evil_Structure::getObject('user', $object);
        	$role 		= $user->getValue('role');
            $logger 	= Zend_Registry::get('logger');

            // Роль для гостя - незарег. пользователя
            $role = $object == -1 ? 'guest' : $role;
            
            // По 3-м возможным вариантам: все, роль пользователя, ID пользователя
            $check = array('all', $role, $object);
            foreach ($check as $__user_role)
            {
            	if (!isset(self::$_rules[$__user_role][$controller])) continue; else
            	$current = self::$_rules[$__user_role][$controller];
            	
	            if (is_array($current))
	            {
	            	if (empty($current))
	            	{
	            		// пустой массив - все методы - разрешаем
	            		$decision = true;
	            		break;
	            	}
	            	elseif (in_array($action, $current))
	            	{
	            		// есть в списке - разрешаем
	            		$decision = true; 
	            		break;          		
	            	}
	            }
            }    
            
            return $decision;
        }

        /**
         * @param string $subject
         * @param string $controller
         * @param string $action
         * @return bool
         */
        public function allowed($subject, $controller, $action)
        {
            return self::_check($subject, $controller, $action);
        }

        /**
         * @param string $subject
         * @param string $controller
         * @param string $action
         * @return bool
         */
        public function denied($subject, $controller, $action)
        {           
            return !self::_check($subject, $controller, $action);
        }        
        
    }