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
        /**
         * @var array
         */
        private static $_classes = array();

        /**
         * @static
         * @param  $className
         * @param null $args
         * @return
         */
        public static function make($className, $args = null)
        {
            return new $className($args);
        }

        /**
         * @static
         * @param  $className
         * @param null $args
         * @return
         */
        public static function singletone($className, $args = null)
        {
            if (isset(self::$_classes[$className]))
                return self::$_classes[$className];
            else
                return self::$_classes[$className] = new $className($args);
        }

        /**
         * @static
         * @param  $fn
         * @param  $args
         * @return 
         */
        public static function makeFunc($fn, $args)
        {
            return $fn($args);    
        }

    }