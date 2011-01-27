<?php
    /**
     * @author BreathLess
     * @name Evil_Access_Weighted Plugin
     * @type Zend Plugin
     * @description: Access Engine for ZF
     * @package Evil
     * @subpackage Access
     * @version 0.2
     * @date 24.10.10
     * @time 14:20
     */
  class Evil_Access_Weighted extends Evil_Access_Abstract
  {
        /**
         * Initialize rules
         *
         * @throws Exception
         * @return bool
         */
        public function init ()
        {
            self::$_rules = json_decode(file_get_contents(APPLICATION_PATH.'/configs/access.json'), true);
            
            if (!self::$_rules)
            throw new Exception('JSON-encoded file "/configs/access.json" is corrupted');
            
            return true;
        }  	  	

        /**
         * Check access
         *
         * @throws Exception
         * @param string $subject
         * @param string $controller
         * @param string $action
         * @return
         */
        public function _check ($subject, $controller, $action)
        {
            $decisions = array();
            $object = Zend_Registry::get('userid');
            $user = new Evil_Object_Fixed('user', $object);
            $role = $user->getValue('role');
            $logger = Zend_Registry::get('logger');
            Zend_Wildfire_Plugin_FirePhp::group('Access');
            
            $conditions = array('controller', 'action', 'object', 'subject', 'role');
          
            foreach(self::$_rules as $ruleName => $rule)
            {
                $selected = true;
                foreach ($conditions as $condition)
                {
                    if (isset($rule[$condition]))
                    {                        
                        if (is_array($rule[$condition]))
                        {
                            if (!in_array($$condition, $rule[$condition]))
                            {
                                $selected = false;
                            	break;
                            }
                        }
                        elseif ($rule[$condition] != $$condition)
                        {
                            $selected = false;
                            break;
                        }
                    }
                }

                if ($selected)
                {
                    $decisions[(int) $rule['weight']] = $rule['decision'];
                    $logger->log($ruleName.' applicable!', Zend_Log::INFO);
                }
            }
            if (count($decisions)>0)
            {
                $decision = $decisions[max(array_keys($decisions))];
                $logger->info('Вердикт: '.$decision);
            } else
                throw new Exception('No rules applicable');

            Zend_Wildfire_Plugin_FirePhp::groupEnd('Access');
            return $decision;
        }

        /**
         * @param string $subject
         * @param string $controller
         * @param string $action
         * @return
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