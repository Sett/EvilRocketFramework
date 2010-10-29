<?php

    /**
     * @author BreathLess
     * @date 29.10.10
     * @time 16:47
     */

    class Evil_Object
    {
        public static function factory ($type, $id = null)
        {
            $config = Zend_Registry::get('config');
            
            if (!isset($config['evil']['object']['map'][$type]))
                $mapper = 'Fixed';
            else
                $mapper = $config['evil']['object']['map'][$type];

            $className = 'Evil_Object_'.$mapper;
            return new $className($type, $id);
        }
    }