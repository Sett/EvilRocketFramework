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
            if ($this->denied(Zend_Registry::get('userid'),
                $request->getControllerName().'/'.$request->getActionName(),
                $request->getParam('id')))
                    throw new Exception('Access Denied');
        }

        public function init ()
        {
            self::$_rules = new Evil_Composite_H2D('rule');
            self::$_rules->where('active','=', '1');

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

            foreach(self::$_rules->_items as $rule)
            {
                $objects = $rule->getValue('object', 'array');
                $actions = $rule->getValue('action', 'array');
                $subjects = $rule->getValue('subject', 'array');

                if ($actions == array('*') or in_array($action, $actions))
                {
                    if ($subjects == array('*') or in_array($subject, $subjects))
                    {
                        if ($objects == array('*') or in_array($object, $objects))
                        {
                            //if (self::_resolve($rule['condition'], $object, $subject))
                            {
                                $decisions[(int) $rule->getValue('weight')] = $rule->getValue('decision');
                            }
                        }
                    }
                }
            }

            if (count($decisions)>0)
            {
                $decision = $decisions[max(array_keys($decisions))];
                $logger->info('Вердикт: '.$decision);
            } else
                throw new Exception('No rules applicable');

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