<?php

    /**
     * @author BreathLess
     * @type Library
     * @description: Abstract Factory
     * @package Evil
     * @subpackage Core
     * @version 0.1
     * @date 30.10.10
     * @time 13:42
     */

    class Evil_Factory
    {
        public static function make($className)
        {
            return new $className();
        }
    }