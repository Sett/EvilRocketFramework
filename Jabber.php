<?php
/**
 * 
 * Jabber client
 * @author nur
 *
 */
class Evil_Jabber {
    
    public static function send($to,$message)
    {
        $client = new Zend_Jabber();
        $client->connect('nurk.in');
        $user = new Zend_Jabber_User('redmine@nurk.in');
        $client->login($user, 'redmine');
        $recipient = new Zend_Jabber_User($to);
        $client->message($recipient, $message);
        
    }
    
}