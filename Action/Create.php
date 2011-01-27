<?php

    /**
     * @author BreathLess
     * @type Action
     * @description: Create Action
     * @package Evil
     * @subpackage Controller
     * @version 0.1
     * @date 29.10.10
     * @time 15:21
     */

    class Evil_Action_Create implements Evil_Action_Interface
    {
        /**
         * @param Zend_Controller_Action $controller
         * @return void
         */
        public function __invoke (Zend_Controller_Action $controller)
        {
            $config = new Zend_Config_Ini(
                APPLICATION_PATH.'/configs/forms/'.$controller->getRequest()->getControllerName().'.ini');
            $form = new Zend_Form($config->get('create'));
            
            $controller->view->assign('form', $form);
        }
    }