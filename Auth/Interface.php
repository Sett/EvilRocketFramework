<?php
/**
 * User: breathless
 * Date: 23.10.10
 * Time: 13:11
 */

    interface Evil_Auth_Interface
    {
        public function step1 ($controller);

        public function onSuccess();
        public function onFailure();        
    }
