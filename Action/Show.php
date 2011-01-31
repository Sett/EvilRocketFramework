<?php

    /* Zend Show Action
     * @author BreathLess
     * @type Zend Action
     * @date 29.10.10
     * @time 14:16
     */

    class Evil_Action_Show implements Evil_Action_Interface
    {
        public function __invoke (Zend_Controller_Action $controller)
        {
            $template = new Evil_Template();
            $entity = $controller->getRequest()->getControllerName();

            $object = Evil_Structure::getObject($entity , $controller->_getParam('id'));
            $controller->view->assign('body', $template->mix($object->data(), $entity.'/show'));
        }
        
    }
