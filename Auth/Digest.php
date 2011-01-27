<?php

    /**
     * @author BreathLess
     * @name Evil_Auth_Digest
     * @description: Digest HTTP Auth
     * @package Evil
     * @subpackage Access
     * @version 0.1
     * @date 24.10.10
     * @time 15:12
     */

    class Evil_Auth_Digest implements Evil_Auth_Interface
    {
        /**
         * @param string $txt
         * @return array|bool
         */
        private function _http_digest_parse ($txt)
        {
            $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
            $data = array();
            $keys = implode('|', array_keys($needed_parts));

            preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

            foreach ($matches as $m) {
                $data[$m[1]] = $m[3] ? $m[3] : $m[4];
                unset($needed_parts[$m[1]]);
            }

            return $needed_parts ? false : $data;
        }

        /**
         * @throws Exception
         * @param  $controller
         * @return
         */
        public function doAuth($controller)
        {
            if (!isset($_SERVER['PHP_AUTH_USER']))
            {
                $realm = 'SCORE';
                header('HTTP/1.1 401 Unauthorized');
                header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
                exit;
            }
            else
            {
                if ($data = $this->_http_digest_parse($_SERVER['PHP_AUTH_DIGEST']))
                {
                    $user = Evil_Structure::getObject('user');

                    $user->where('nickname','=', $data['username']);

                    if ($user->load())
                    {
                        $A1 = md5($data['username'] . ':' . $realm . ':' . $user->getValue('password'));
                        $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
                        $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

                        if ($data['response'] == $valid_response)
                            return $user->getId();

                    }
                    else
                        throw new Exception('Unknown user');

                }

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