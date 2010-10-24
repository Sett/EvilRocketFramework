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
        const STEPS = 3;
        
        public function step1($controller)
        {
            $form = new Evil_Auth_Form_Native();
            $controller->view->form = $form;

            if ($controller->getRequest()->isPost())
                if ($form->isValid($_POST))
                {
                    $data  = $form->getValues();
                    $user = new Evil_Object_2D('user');

                    $user->where('nickname','=', $data['username']);
                    if ($user->load())
                    {                       
                        if ($user->getValue('password') == md5($data['password']))
                        {
                            return true;
                        }
                        else
                            throw new Exception('Password Incorrect');
                    }
                    else
                        throw new Exception('Unknown user');
                }
            return false;
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
