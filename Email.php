<?php
/**
 * 
 * либа для рассылки емейлов
 * @author nur
 *
 */
class Evil_Email implements Evil_TransportInterface {
    
    private  $_transport = 'Zend_Mail_Transport_Sendmail';
    
    
    private $_mailer = NULL;
    
    private $_transportInstance = NULL;

    /**
     * отправка письма на почту
     * @param $to string
     * @param $message string
     * @return bool
     * 
     */
    public function send ($to, $message)
    {
        if($this->_isValidRecipient($to))
        {
            $this->_mailer->clearRecipients();
            $this->_mailer->setBodyText($message);
            $this->_mailer->addTo($to);
            return $this->_mailer->send();
        } else 
        {
            return false;
        }
        
    }
    
    /**
     * 
     * валидация емайл адреса
     * @param string $email
     * @return bool
     */
    private function _isValidRecipient($email)
    {
        $validator = new Zend_Validate_EmailAddress();
        return $validator->isValid($validator);
    }

	/**
	 * инит класса транспорта
	 * @see Evil_TransportInterface::init()
	 * @param $config array
	 */
    public function init (array $config)
    {
         $this->_config = isset($config['transportconfig']) ? $config['transportconfig'] : array();
         $this->_transport = isset($config['transport']) ? $config['transport'] : $this->_transport;
         /**
          * 
          * создаем траспорт и делаем его транспортом по умолчанию
          * @var Zend_Mail_Transport_Abstract
          */
         $defaultTransport = new $this->_transport($this->_config);
         Zend_Mail::setDefaultTransport($defaultTransport);
         
         $this->_mailer = new Zend_Mail();
        
    }

    
    
}