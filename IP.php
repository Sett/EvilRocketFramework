<?php

    class Evil_IP extends Zend_Controller_Plugin_Abstract
    {
        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
            $this->defineIP();
        }

        private function defineIP()
        {
            if (isset($_SERVER['HTTP_X_REAL_IP']))
                define ('_IP', $_SERVER['HTTP_X_REAL_IP']);
            else
                define ('_IP', $_SERVER['REMOTE_ADDR']);
        }

        public static function geoIP($mode)
        {
            switch (APPLICATION_ENV)
            {
                case 'production':
                    if (preg_match('@127\.*@', _IP or preg_match('@10\.*@', _IP) or preg_match('@172\.*@', _IP)))
                        $result = 'LO';
                    else
                    	$result = geoip_country_code_by_name(_IP);
                break;

                case 'development':
                    $result = geoip_country_code_by_name('209.159.156.154');
                break;

                case 'testing':
                    $countries = array('US', 'CN', 'RU', 'RO', 'GN');
                    $result = $countries[array_rand($countries)];
                break;
            }

            return $result;
        }
    }