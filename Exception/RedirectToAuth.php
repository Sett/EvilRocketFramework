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

    class Evil_Exception_RedirectToAuth
    {
        public function __invoke(Evil_Exception $e)
        {
            header('Location: http://'.$_SERVER['HTTP_HOST'].'/auth');
            die();
        }
    }