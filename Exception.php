<?php

    /**
     * @author BreathLess
     * @type Library
     * @description: Evil Exception, port from Codeine
     * @package Evil
     * @subpackage Exception
     * @version 0.1
     * @date 30.10.10
     * @time 13:29
     */

    class Evil_Exception extends Zend_Exception
    {
        public function __construct($message, $code = 0)
        {
        	//  Не могу сказать почему, но на сервере это не работает
        	// Локально - работает. @Artemy
            // Версия зенда, Zend_Config_Json только с ZF 1.11, @BreathLess
        	
            // $exceptionConfig = new Zend_Config_Json(APPLICATION_PATH.'/configs/exception.json');          
            // $exceptionConfig = $exceptionConfig->toArray();
            if (file_exists(APPLICATION_PATH.'/configs/exception.json'))
                $exceptionConfig = json_decode(file_get_contents(APPLICATION_PATH.'/configs/exception.json'), true);
           	
            if(isset($exceptionConfig[$code]))
            {           		            	
                $exceptionClass = Evil_Factory::make('Evil_Exception_'.$exceptionConfig[$code]);
                
                if (is_callable($exceptionClass)) 
                    $exceptionClass($message);
                
                // For compatibility with php 5.2 by #Artemy
                elseif (method_exists($exceptionClass, '__invoke'))
                	$exceptionClass->__invoke($message);
            } else {
            	parent::__construct($message, $code);
            }
        }
    }