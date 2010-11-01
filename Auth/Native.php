<?php
/**
 * User: breathless
 * Date: 23.10.10
 * Time: 13:16
 * Class: Evil_Auth_Native
 * Description:
 */
 
    class Evil_Auth_Native implements Evil_Auth_Interface 
    {       
        public function doAuth ($controller)
        {
            $form = new Evil_Auth_Form_Native();
            $controller->view->form = $form;

            if ($controller->getRequest()->isPost())
                if ($form->isValid($_POST))
                {
                    $data = $form->getValues();

                    $user = Evil_Structure::getObject('user');

                    $user->where('nickname','=', $data['username']);

                    if ($user->load())
                    {                       
                        if ($user->getValue('password') == md5($data['password']))
                            return $user->getId();
                        else
                            throw new Evil_Exception('Password Incorrect', 4042);
                    }
                    else
                        throw new Evil_Exception('Unknown user', 4044);
                }
            return -1;
        }

        public function onFailure()
        {
            // TODO: Implement onFailure() method.
        }

        public function onSuccess()
        {
            // TODO: Implement onSuccess() method.
        }

    }
