<?php

    /**
     * @author BreathLess
     * @type Driver
     * @description: Redirector
     * @package Evil
     * @subpackage Exception
     * @version 0.1
     * @date 30.10.10
     * @time 13:38
     */

    class Evil_Exception_UserMessage implements Evil_Exception_Interface
    {
        public function __invoke($message)
        {
			$layout = new Zend_Layout();
			
			// Установка пути к скриптам макета:
			$layout->setLayoutPath(APPLICATION_PATH.'/views/scripts/layouts');
			$layout->setLayout('inner');
			
			$view = new Zend_View();
			$view->setBasePath(APPLICATION_PATH.'/views/');
			$view->error_message = $message;
			
			// установка переменных:
			$layout->content = $view->render('/exeption/activate.phtml');

			echo $layout->render();        	
        	
            //echo $message;
            die();
        }
    }