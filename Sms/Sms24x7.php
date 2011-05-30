<?php
/**
 * 
 * Класс траспорт для Evil_Sms
 * является оберткой для сервиса 
 * http://sms24x7.ru/api/
 * @author nur
 * @config example
 * @see application.ini
    evil.sms.transport ="Evil_Sms_Sms24x7"
    evil.sms.config.email = "nur.php@gmail.com"
    evil.sms.config.password = "8ZvKkD5"
    evil.sms.config.sender_name = "opencity.ru
 *
 */
class Evil_Sms_Sms24x7 implements Evil_Sms_Interface
{

    private $_apiUrl = 'http://api.sms24x7.ru';

    private $_config = array();

    private $_defaultRequest = array('format' => 'json', 'method' => 'push_msg');

    public function send ($phone, $text)
    {
        if ($this->_validatePhone($phone)) {
            $reqest = array('phone' => $phone, 'text' => $text);
            /**
             * 
             * Запрос формируется мержем массивом с параметрами, причем настройки указанные в конфиге 
             * в критичных местах могут перехзаписаться
             * @var array
             */
            $reqest = array_merge($this->_config, $reqest, $this->_defaultRequest);
            
            $client = new Zend_Http_Client($this->_apiUrl);
            $client->setParameterGet($reqest);
            $result = $client->request('POST');
            $jsonResponse = $result->getBody();
            $json = Zend_Json::decode($jsonResponse);
            if (0 == $json['response']['msg']['err_code']) {
                return true;
            } else {
                $this->_log($json['response']['msg']['text'] . ' ' . var_export($reqest, true),Zend_Log::CRIT);
                return false;
            }
        } else 
        {
            $this->_log('Номер телефона не корректный: ' . var_export($reqest, true) , Zend_Log::ERR);
            return false;
        }
    }

    /**
     * 
     * Логирование ошибок
     * @param string $message
     * @param int $levl
     */
    protected function _log($message,$levl)
    {
        Evil_Log::log(__CLASS__ . ' ' . $message, $levl);
    }
    /**
     * Инит транспорта, вызывается всегда первым
     * @see Evil_Sms_TransportInterface::init()
     */
    public function init (array $config)
    {
        $this->_config = $config;
    }

    /**
     * 
     * Валидация номера телефона
     * пока просто регексп, валидно все, цифры 10-15 штук
     * @param string $phone
     */
    private function _validatePhone ($phone)
    {
        $validator = new Zend_Validate_Regex('/\d{10,15}/');
        return $validator->isValid($phone);
    }
}