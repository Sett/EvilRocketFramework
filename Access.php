<?php
    /**
     * @author BreathLess
     * @name Evil_Access Plugin
     * @type Zend Plugin
     * @description: Access Engine for ZF
     * @package Evil
     * @subpackage Access
     * @version 0.2
     * @date 24.10.10
     * @time 14:20
     */

  class Evil_Access extends Zend_Controller_Plugin_Abstract
    {
        private static $_rules;
      
        public function routeShutdown(Zend_Controller_Request_Abstract $request)
        {
            parent::routeStartup ($request);
            $this->init();
           
            if ($this->denied($request->getParam('id'),
                $request->getControllerName(), $request->getActionName()))
                    throw new Evil_Exception('Access Denied for '.$request->getControllerName().'::'.$request->getActionName(), 403);
        }

        public function init ()
        {
            self::$_rules = json_decode(file_get_contents(APPLICATION_PATH.'/configs/access.json'), true);
            return true;
        }

        private function _resolve($condition, $object, $subject)
        {
            if ('*' !== $condition)
                return self::$condition($object, $subject);
            else
                return true;
        }

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
                $logger->log($ruleName.' checking ', Zend_Log::NOTICE);
                foreach ($conditions as $condition)
                {
                    //if (isset($rule[$condition])) var_dump($$condition, $rule[$condition]);
                    
                    if (isset($rule[$condition]))
                    {                        
                        if (is_array($rule[$condition]))
                        {
                            if (!in_array($$condition, $rule[$condition]))
                            {
                                $selected = false;
                                //var_dump($ruleName.' => '.$condition.'['.($$condition).'] in not in ['.implode(',', $rule[$condition]).']');
                                 
                                // Уже flase - нам не надо дальше ничего проверять |Artemy
                            	break;
                            }
                        }
                        elseif ($rule[$condition] != $$condition)
                        {
                            $selected = false;
                            // var_dump($ruleName.' => '.$condition.'['.($$condition).'] != '.$rule[$condition]);

                            // Уже flase - нам не надо дальше ничего проверять |Artemy
                            break;
                        }
                    }

                    /*
                    if ($selected == false)
                        $logger->log($condition.' not match with '.$$condition, Zend_Log::WARN);
                    else
                        $logger->log($condition.' match with '.$$condition, Zend_Log::INFO);
                     
                     */                                 
                }

                if ($selected)
                {
                    $decisions[(int) $rule['weight']] = $rule['decision'];
                    $logger->log($ruleName.' applicable!', Zend_Log::ALERT);
                }
                else
                {
                    $logger->log($ruleName.' no applicable!', Zend_Log::WARN);
                }
            }
            //var_dump($controller.":".$action);
			//var_dump($decisions);
            if (count($decisions)>0)
            {
                $decision = $decisions[max(array_keys($decisions))];
                $logger->info('Вердикт: '.$decision);
            } else
                throw new Exception('No rules applicable');

            Zend_Wildfire_Plugin_FirePhp::groupEnd('Access');
            return $decision;
        }

        public function allowed($subject, $controller, $action)
        {
            return self::_check($subject, $controller, $action);
        }

        public function denied($subject, $controller, $action)
        {           
            return !self::_check($subject, $controller, $action);
        }

        private function isOwner($subject)
        {
            return ($subject->owner() == Zend_Registry::get('userid'));
        }
    }