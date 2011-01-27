<?php

    /**
     * @author BreathLess
     * @type Interface
     * @date 24.10.10
     * @time 12:47
     */

    interface Evil_Object_Interface
    {
        /**
         * @abstract
         * @return void
         */
        public function load();

        /**
         * @abstract
         * @return void
         */
        public function data();


        /**
         * @abstract
         * @param  $key
         * @param  $selector
         * @param null $value
         * @return void
         */
        public function where($key, $selector, $value = null);

        /**
         * @abstract
         * @param  $id
         * @param  $data
         * @return void
         */
        public function create($id, $data);

        /**
         * @abstract
         * @return void
         */
        public function erase();


        /**
         * @abstract
         * @param  $key
         * @param  $value
         * @return void
         */
        public function addNode($key, $value);

        /**
         * @abstract
         * @param  $key
         * @param null $value
         * @return void
         */
        public function delNode($key, $value = null);

        /**
         * @abstract
         * @param  $key
         * @param  $value
         * @param null $oldvalue
         * @return void
         */
        public function setNode($key, $value, $oldvalue = null);

        /**
         * @abstract
         * @param  $key
         * @param string $return
         * @param null $default
         * @return void
         */
        public function getValue($key, $return = 'var', $default = null);


        /**
         * @abstract
         * @param  $key
         * @param  $fn
         * @return void
         */
        public function addDNode($key, $fn);
    }