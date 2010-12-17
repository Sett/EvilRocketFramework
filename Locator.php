<?php

    /**
     * @author BreathLess
     * @date 16.12.10
     * @time 16:58
     */

    class Evil_Locator
    {
        /**
         * @description File Finder
         */
        public static function ff($name)
        {
            $config = Zend_Registry::get('config');
            if (
                !isset($config['evil']['locator']['places'])
                or !is_array($config['evil']['locator']['places']))
                    $config['evil']['locator']['places'] = array();
            
            $candidates = array_merge(array(
                realpath(APPLICATION_PATH),
                realpath(APPLICATION_PATH . '/../library'),
                $config['evil']['locator']['places']));

            foreach ($candidates as $candidate)
                if (file_exists($candidate.'/'.$name))
                    return $candidate.'/'.$name;

            return null;
        }


    }