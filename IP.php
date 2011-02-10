<?php

    class Evil_IP extends Zend_Controller_Plugin_Abstract
    {
        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
            $this->defineIP();
        }

        private function defineIP()
        {
        	if(false == defined('_IP'))
        	{
	            if (isset($_SERVER['HTTP_X_REAL_IP']))
	                define ('_IP', $_SERVER['HTTP_X_REAL_IP']);
	            else
	                define ('_IP', $_SERVER['REMOTE_ADDR']);
        	}
        }

        public static function geoIP()
        {
            switch (APPLICATION_ENV)
            {
                case 'production':
                    if (preg_match('@127\.*@', _IP
                            or preg_match('@10\.*@', _IP)
                                or preg_match('@172\.*@', _IP))
                                    or preg_match('@192\.168\.*@', _IP)
                                        or _IP == '127.0.0.1')
                        $result = 'LO';
                    else
                    	$result = geoip_country_code_by_name(_IP);
                break;

                case 'development':
					$countries = array('74.125.43.103','86.134.153.24','86.122.153.245');
                    $result = geoip_country_code_by_name($countries[array_rand($countries)]);
                break;

                case 'testing':
                    $countries = array('US', 'CN', 'RU', 'RO', 'GN');
                    $result = $countries[array_rand($countries)];
                break;
            }

            return $result;
        }
    }