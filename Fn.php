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
            $pieces = explode('.', $call['NS']);
            
            list($group) = array_reverse($pieces);

            $path = strtr($call['NS'],'.','/');
            if (isset($call['D']))
                $driver = $call['D'];
            else
            {
                $driver = $group;

                if (isset(self::$_drivers[$pieces[0]]))
                {
                    $iter = self::$_drivers[$pieces[0]];
                    $sz = sizeof($pieces);
                    for($ic = 1; $ic<$sz; $ic++)
                        if (isset($iter[$pieces[$ic]]))
                            $iter = $iter[$pieces[$ic]];
                        else
                            $iter = null;

                    if (null !== $iter)
                        $driver = $iter;
                }
            }

            self::$_domain = $path;

            $driverPath = Evil_Locator::ff('/functions/'.$path.'/'.$driver.'.php');

            if (!empty($driverPath))
            {
                include_once $driverPath;
                $closure = self::$_functions[self::$_domain][$call['F']];
                return $closure ($call);
            }
                else throw new Exception('driver '.'/functions/'.$path.'/'.$driver.'.php'.' not found');
        }
    }

    Evil_Fn::onInclude();