<?php
/**
 * 
 * Jabber client
 * @author nur
 *
 */
class Evil_Jabber
{
    public static function send ($to, $message)
    {
        $client = new Zend_Jabber();
        $client->connect('jabber.ru');
        $user = Zend_Jabber_User::getInstance('opencity@jabber.ru');
        $client->login($user, 'opencity');
        $recipient = Zend_Jabber_User::getInstance($to);
        $client->message($recipient, $message);
        
    }
    
}