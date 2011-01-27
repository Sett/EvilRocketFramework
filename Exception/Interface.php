<?php

    /**
     * @author BreathLess
     * @type Driver
     * @description: Interface for Interceptor
     * @package Evil
     * @subpackage Exception
     * @version 0.1
     * @date 30.10.10
     * @time 13:38
     */

    interface Evil_Exception_Interface
    {
        /**
         * @abstract
         * @param  $message
         * @return void
         */
        public function __invoke($message);
    }