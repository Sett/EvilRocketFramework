<?php

    /**
     * @author BreathLess
     * @type Interface
     * @date 24.10.10
     * @time 12:47
     */

    interface Evil_Composite_Interface
    {
        public function data();

        public function where($key, $selector, $value = null);

        public function addDNode($key, $fn);
    }