<?php

    /**
     * @author BreathLess
     * @name Evil Controller
     * @type Zend Controller
     * @description: CRUD From Codeine
     * @package Evil
     * @subpackage Code
     * @version 0.1
     * @date 29.10.10
     * @time 13:59
     */

    class Evil_Controller extends Zend_Controller_Action 
    {
        /**
         * @param  $methodName
         * @param  $args
         * @return mixed
         */
        public function __call($methodName, $args)
        {
            if (strpos($methodName, 'Action') !== false)
            {
                $methodClass = 'Evil_Action_'.ucfirst(substr($methodName, 0, strpos($methodName, 'Action')));
                $method = new $methodClass();
                $method ($this);
            }
            else
                return call_user_func_array(array(&$this, $methodName), $args);
        }
    }