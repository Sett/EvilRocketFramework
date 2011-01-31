<?php
/**
 * User: breathless
 * Date: 23.10.10
 * Time: 13:51
 * Class: Evil_Auth_Form_Native
 * Description:
 */
 
    class Evil_Auth_Form_Native extends Zend_Form
    {
        public function init()
        {
            $username = $this->createElement('text', 'username', array(
                            'label' => _('Username'),
                            'maxlength' => '20',
                            'required' => TRUE
            ));

            $password = $this->createElement('password', 'password', array(
                                'label' => _('Password'),
                                'maxlength' => '20',
                                'required' => TRUE
            ));

            $signin = $this->createElement('submit', 'SignIn', array(
                                'label' => _('Sign In')
            ));

            $this->addElements(array(
                        $username,
                        $password,
                        $signin
            ));
        }
    }
