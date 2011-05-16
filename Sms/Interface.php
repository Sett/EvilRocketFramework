<?php
/**
 * 
 * Интерфейс для транспортов
 * @author nur
 *
 */
interface Evil_Sms_Interface
{

    public function send ($phone, $text);

    public function init (array $config);
}