<?php
/**
 * 
 * Класс для отправки смс сообщений
 * поддерживает разные транспорты
 * @author nur
 *
 */
class Evil_Sms
{

    /**
     * 
     * Отправка сообщения
     * @param string $to номер телефона, в международном формате 
     * @example 7902xxxxxxx
     * @param string $message
     * @return bool
     * @throws Zend_Exception
     */
    public static function send ($to, $message)
    {
        $config = Zend_Registry::get('config');
        /**
         * 
         * настройки для транспорта
         * @var array
         */
        $smsConfig = (array) $config['evil']['sms']['config'];
        $transport = new $config['evil']['sms']['transport']();
        /**
         * создаем, инициализируем, отправляем
         */
        if ($transport instanceof Evil_Sms_Interface) {
            $transport->init($smsConfig);
            return $transport->send($to, $message);
        } else {
            throw new Zend_Exception($smsConfig['transport'] . ' not instace of Evil_Sms_Interface');
        }
    }
}