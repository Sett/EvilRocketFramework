<?php

    /**
     * @author BreathLess
     * @type Interface
     * @date 24.10.10
     * @time 12:47
     */

    interface Evil_Object_Interface
    {
        public function load();
        public function data();

        public function where($key, $selector, $value = null);
        public function create($id, $data);
        public function erase();

        public function addNode($key, $value);
        public function delNode($key, $value = null);
        public function setNode($key, $value, $oldvalue = null);
        public function getValue($key, $return = 'var', $default = null);

        public function addDNode($key, $fn);

        public function __get ($name);
        public function __set ($name, $value);
        public function __isset ($name);
        public function __toString ();

    }