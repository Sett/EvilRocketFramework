<?php
/**
 * User: breathless
 * Date: 23.10.10
 * Time: 13:11
 */

    interface Evil_Auth_Interface
    {
        /**
         * @abstract
         * @param  $controller
         * @return void
         */
        public function doAuth ($controller);

        /**
         * @abstract
         * @return void
         */
        public function onSuccess();

        /**
         * @abstract
         * @return void
         */
        public function onFailure();        
    }
