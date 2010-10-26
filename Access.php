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
      
        public function routeStartup (Zend_Controller_Request_Abstract $request)
        {
            parent::routeStartup ($request);
            $this->init();
        }

        public function init ()
        {
            self::$_rules = new H2D_Composite('access');
            self::$_rules = self::$_rules->fetchAll()->toArray();
            return true;
        }

        private function _resolve($condition, $object, $subject)
        {
            if ('*' !== $condition)
                return self::$condition($object, $subject);
            else
                return true;
        }

        public function _check ($object, $subject, $action)
        {
            $decisions = array();
            $logger = Zend_Registry::get('logger');

            foreach(self::$_rules as $rule)
            {
                $objects = explode(';', $rule['object']);
                $actions = explode(',', $rule['action']);
                if ($rule['action'] == '*' or in_array($action, $actions))
                {
                    if ($rule['subject'] == '*' or $rule['subject'] == $subject)
                    {
                        if ($rule['object'] == '*' or in_array($object[0], $objects) or in_array($object[1], $objects))
                        {
                            if (self::_resolve($rule['condition'], $object, $subject))
                            {
                                $decisions[(int) $rule['weight']] = $rule['decision'];
                                $logger->info($rule['comment'].' = '.$rule['decision'].' с весом '.$rule['weight']);
                            }
                        }
                    }
                }
            }
            $decision = $decisions[max(array_keys($decisions))];
            $logger->info('Вердикт: '.$decision);
            return $decision;
        }

        public function allowed($object, $subject, $action)
        {
            return self::_check($object, $subject, $action);
        }

        public function denied($object, $subject, $action)
        {
            return !self::_check($object, $subject, $action);
        }

        private function isOwner($object, $subject)
        {
            return ($subject->owner() == $object);
        }
    }