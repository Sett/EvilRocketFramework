<?php

    /**
     * @author BreathLess
     * @type Driver
     * @description: Redirector
     * @package Evil
     * @subpackage Exception
     * @version 0.1
     * @date 30.10.10
     * @time 13:38
     */

    class Evil_Exception_UserMessage implements Evil_Exception_Interface
    {
        public function __invoke($message)
        {
            echo $message;
            die();
        }
    }