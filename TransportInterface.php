<?php
/**
 * 
 * Интерфейс транспортов
 * @author nur
 *
 */
interface Evil_TransportInterface {
    
   
    /**
     * 
     * функция отправки сообщения
     * @param string $to
     * @param string $message
     */
    public function send($to,$message);
    
    /**
     * 
     * инит траспорта
     * @param array $config
     */
    public function init(array $config);
}