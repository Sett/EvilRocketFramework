<?php

    /**
     * @author BreathLess
     * @type Class
     * @description: Codeine Layout port
     * @package Evil
     * @subpackage Render
     * @version 0.1
     * @date 28.10.10
     * @time 11:46
     */

    class Evil_Layout extends Zend_Controller_Plugin_Abstract
    {
        public function routeShutdown (Zend_Controller_Request_Abstract $request)
        {
            $layout = Zend_Layout::getMvcInstance();
            $layoutConfig = Zend_Json::decode(file_get_contents(APPLICATION_PATH.'/configs/layout.json'), true);
            $layoutName = 'layout';

            if (isset($layoutConfig[$request->getControllerName()]))
            {
                if (is_array($layoutConfig[$request->getControllerName()]) && 
                    isset($layoutConfig[$request->getControllerName()][$request->getActionName()]))
                    $layoutName = $layoutConfig[$request->getControllerName()][$request->getActionName()];
                else
                    $layoutName = $layoutConfig[$request->getControllerName()];
            }

            $layout->setLayout('layouts/'.$layoutName);

            parent::routeShutdown ($request);
            
        }

    }