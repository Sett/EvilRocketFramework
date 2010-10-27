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
           
            if ($this->denied(Zend_Registry::get('userid'), $request->getParam('id'),
                $request->getControllerName(), $request->getActionName()))
                    throw new Exception('Access Denied');
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

        public function _check ($object, $subject, $controller, $action)
        {
            $decisions = array();
            $user = new Evil_Object_2D('user', $object);
            $role = $user->getValue('role');
            $logger = Zend_Registry::get('logger');

            $conditions = array('controller', 'action', 'object', 'subject', 'role');


            foreach(self::$_rules as $ruleName => $rule)
            {
                $selected = true;
                $logger->log($ruleName.' checking ', Zend_Log::NOTICE);
                foreach ($conditions as $condition)
                    if (isset($rule[$condition]) &&
                        (($rule[$condition] !== $$condition) ||
                        (is_array($rule[$condition]) &&
                        !in_array($$condition, $rule[$condition]))))
                    {
                        $selected = false;
                        $logger->log($condition.' not match with '.$$condition, Zend_Log::WARN);
                    }
                    else
                        $logger->log($condition.' match with '.$$condition, Zend_Log::INFO);                        

                if ($selected)
                {
                    $decisions[(int) $rule['weight']] = $rule['decision'];
                    $logger->log($ruleName.' applicable!', Zend_Log::ALERT);
                }
                else
                    $logger->log($ruleName.' no applicable!', Zend_Log::WARN);
            }

            if (count($decisions)>0)
            {
                $decision = $decisions[max(array_keys($decisions))];
                $logger->info('Вердикт: '.$decision);
            } else
                throw new Exception('No rules applicable');

            return $decision;
        }

        public function allowed($object, $subject, $controller, $action)
        {
            return self::_check($object, $subject, $controller, $action);
        }

        public function denied($object, $subject, $controller, $action)
        {           
            return !self::_check($object, $subject, $controller, $action);
        }

        private function isOwner($object, $subject)
        {
            return ($subject->owner() == $object);
        }
    }