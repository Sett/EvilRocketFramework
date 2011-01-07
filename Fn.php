<?php

    class Evil_Fn
    {
        protected static $_drivers = array();
        protected static $_functions = array();
        protected static $_domain;

        public static function onInclude()
        {
            $config = Zend_Registry::get('config');
            if (isset($config['evil']['fn']))
                self::$_drivers = $config['evil']['fn'];
        }

        public static function Fn($fn, $code = null)
        {
            self::$_functions[self::$_domain][$fn] = $code;
        }

        public static function run($call)
        {
            list($group) = array_reverse(explode('.', $call['NS']));

            $path = strtr($call['NS'],'.','/');
            if (isset($call['D']))
                $driver = $call['D'];
            else
            {
                if (isset(self::$_drivers[$path]))
                    $driver = self::$_drivers[$path];
                else
                    $driver = $group;
            }

            self::$_domain = $path;

            $driverPath = Evil_Locator::ff('/functions/'.$path.'/'.$driver.'.php');

            if (!empty($driverPath))
            {
                include_once $driverPath;
                $closure = self::$_functions[self::$_domain][$call['F']];
                return $closure ($call);
            }
                else throw new Evil_Exception('driver '.$call['NS'].' not found');
        }
    }

    Evil_Fn::onInclude();