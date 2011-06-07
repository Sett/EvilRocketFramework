<?php
    /**
     * @author BreathLess, Artemy
     * @name Evil_Access Plugin
     * @type Zend Plugin
     * @description: Abstract Access Engine for ZF
     * @package Evil
     * @subpackage Access
     * @version 0.2
     * @date 24.10.10
     * @time 14:20
     */

    abstract class Evil_Access_Abstract extends Zend_Controller_Plugin_Abstract
    {
        protected static $_rules;

        abstract public function _check ($subject, $controller, $action);
        abstract public function init ();
        abstract public function allowed($subject, $controller, $action);
        abstract public function denied($subject, $controller, $action);
          
        public function routeShutdown(Zend_Controller_Request_Abstract $request)
        {
            parent::routeStartup ($request);
            $this->init();
            if( Zend_Controller_Front::getInstance()->getDispatcher()->isDispatchable($request) )  
            {
            if ($this->denied($request->getParam('id'),
                $request->getControllerName(), $request->getActionName()))
                    throw new Evil_Exception('Access Denied for '.$request->getControllerName().'::'.$request->getActionName(), 403);
            } else 
            {
                throw new Evil_Exception('Not found '.$request->getControllerName().'::'.$request->getActionName(), 404);
            }
        }

        protected function _resolve($condition, $object, $subject)
        {
            if ('*' !== $condition)
                return self::$condition($object, $subject);
            else
                return true;
        }
        
        protected function isOwner($subject)
        {
            return ($subject->owner() == Zend_Registry::get('userid'));
        }
    }