<?php

    /**
     * @author BreathLess
     * @type Interface
     * @date 29.10.10
     * @time 14:20
     */

    interface Evil_Action_Interface
    {
        /**
         * @abstract
         * @param Zend_Controller_Action $controller
         * @return void
         */
        public function __invoke (Zend_Controller_Action $controller);
    }