<?php

    /**
     * @author BreathLess
     * @type Interface
     * @description: Interface for sensors
     * @package Evil
     * @subpackage Sensor
     * @version 0.1
     * @date 28.11.10
     * @time 16:21
     */

    interface Evil_Sensor_Interface
    {
        /**
         * @abstract
         * @param  $source
         * @param null $args
         * @return void
         */
        public function track($source, $args = null);
    }