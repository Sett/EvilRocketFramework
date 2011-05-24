<?php
/**
 * 
 * тот же самый Zend_Mail_Transport_Smtp но конструктор изменен, и приведен к формату как у других странспортов
 * у которых в контруктор передается один параметр, с настройками
 * @author nur
 * 
 *
 */
class Evil_Mail_SmtpTransport extends Zend_Mail_Transport_Smtp {
    public function __construct($parameters = null) {
        
         if ($parameters instanceof Zend_Config) {
            $parameters = $parameters->toArray();
        }
        $hostName = isset($parameters['host']) ? $parameters['host'] : null;
        unset($parameters['host']);
        
        parent::__construct($hostName, $parameters);
    }
    
}