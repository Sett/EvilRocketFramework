<?php

    /**
     * @author BreathLess
     * @name Evil_Auth_Basic
     * @description: Basic HTTP Auth
     * @package Evil
     * @subpackage Access
     * @version 0.1
     * @date 24.10.10
     * @time 15:12
     */

    class Evil_Auth_Basic implements Evil_Auth_Interface
    {
        /**
         * @throws Exception
         * @param $controller
         * @return
         */
        public function doAuth($controller)
        {
            if (!isset($_SERVER['PHP_AUTH_USER']))
            {
                header('WWW-Authenticate: Basic realm="Login"');
                header('HTTP/1.0 401 Unauthorized');
                exit;
            }
            else
            {
                $user = Evil_Structure::getObject('user');

                $user->where('nickname','=', $_SERVER['PHP_AUTH_USER']);

                if ($user->load())
                {
                    if ($user->getValue('password') == md5($_SERVER['PHP_AUTH_PW']))
                        return $user->getId();
                    else
                        throw new Exception('Password Incorrect');
                }
                else
                    throw new Exception('Unknown user');
            }
        }

        /**
         * Hook
         *
         * @return void
         */
        public function onFailure()
        {
            // TODO: Implement onFailure() method.
        }

        /**
         * Hook
         *
         * @return void
         */
        public function onSuccess()
        {
            // TODO: Implement onSuccess() method.
        }
    
    }