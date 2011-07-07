<?php

    /**
     * @author BreathLess
     * @type Interface
     * @date 24.10.10
     * @time 12:47
     */

    interface Evil_Composite_Interface
    {
        function data();
        function where($key, $selector, $value = null, $offset = 0, $count = 500, $orderBy = 'id DESC');
        function addDNode($key, $fn);
    }