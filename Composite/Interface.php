<?php

    /**
     * @author BreathLess
     * @type Interface
     * @date 24.10.10
     * @time 12:47
     */

    interface Evil_Composite_Interface
    {
        /**
         * @abstract
         * @return void
         */
        function data();

        /**
         * @abstract
         * @param  $key
         * @param  $selector
         * @param null $value
         * @param string $mode
         * @return void
         */
        function where($key, $selector, $value = null, $mode = 'new');

        /**
         * @abstract
         * @param  $key
         * @param  $fn
         * @return void
         */
        function addDNode($key, $fn);
    }