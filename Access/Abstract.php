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
        /**
         * @var
         */
        protected static $_rules;

        /**
         * Check access
         *
         * @abstract
         * @param string $subject
         * @param string $controller
         * @param string $action
         * @return void
         */
        abstract public function _check ($subject, $controller, $action);

        /**
         * @abstract
         * @return void
         */
        abstract public function init ();

        /**
         * @abstract
         * @param string $subject
         * @param string $controller
         * @param string $action
         * @return void
         */
        abstract public function allowed($subject, $controller, $action);

        /**
         * @abstract
         * @param string $subject
         * @param string $controller
         * @param string $action
         * @return void
         */
        abstract public function denied($subject, $controller, $action);

        /**
         * @throws Evil_Exception
         * @param Zend_Controller_Request_Abstract $request
         * @return void
         */
        public function routeShutdown(Zend_Controller_Request_Abstract $request)
        {
            parent::routeStartup ($request);
            $this->init();
           
            if ($this->denied($request->getParam('id'),
                $request->getControllerName(), $request->getActionName()))
                    throw new Evil_Exception('Access Denied for '.$request->getControllerName().'::'.$request->getActionName(), 403);
        }

        /**
         * @param  $condition
         * @param  $object
         * @param  $subject
         * @return bool
         */
        protected function _resolve($condition, $object, $subject)
        {
            if ('*' !== $condition)
                return self::$condition($object, $subject);
            else
                return true;
        }

        /**
         * @param object $subject
         * @return
         */
        protected function isOwner($subject)
        {
            return ($subject->owner() == Zend_Registry::get('userid'));
        }
    }