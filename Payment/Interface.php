<?php
/**
 * @description basic payment interface
 * @author Se#
 * @version 0.0.1
 */
interface Evil_Payment_Interface
{
    /**
     * @description return HTML-form
     * @abstract
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function getForm();

    /**
     * @description send request
     * @abstract
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function sendRequest();

    /**
     * @description append scripts, etc
     * @abstract
     * @param Zend_Controller_Action $controller
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function prepareController(Zend_Controller_Action $controller);

    /**
     * @description render view
     * @abstract
     * @param Zend_Controller_Action $controller
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function render(Zend_Controller_Action $controller);
}