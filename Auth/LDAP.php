<?php
/**
 * @author Se#
 * @description LDAP-auth
 * @version 0.0.1
 */
/**
 * Server options:
 *
 * * Typical options for OpenLDAP
 * host = s0.foo.net
 * accountDomainName = foo.net
 * accountDomainNameShort = FOO
 * accountCanonicalForm = 3
 * username = "CN=user1,DC=foo,DC=net"
 * password = pass1
 * baseDn = "OU=Sales,DC=foo,DC=net"
 * bindRequiresDn = true
 *
 * * Typical options for Active Directory
 * host = dc1.w.net
 * useStartTls = true
 * accountDomainName = w.net
 * accountDomainNameShort = W
 * accountCanonicalForm = 3
 * baseDn = "CN=Users,DC=w,DC=net"
 */
class Evil_Auth_LDAP implements Evil_Auth_Interface
{
    /**
     * @description do auth
     * @throws Exception
     * @param object $controller
     * @return mixed
     * @author Se#
     * @version 0.0.1
     */
    public function doAuth ($controller)
    {
        if(!($config = $this->getConfig()))
            throw new Exception(' Missed ldap options ');

        if(!isset($config['servers']))
            throw new Exception(' Missed servers options');

        list($login, $password) = $this->getLoginAndPassword($controller, $config);

        $adapter = new Zend_Auth_Adapter_Ldap($config['servers'], $login, $password);
        return $adapter->authenticate();
    }

    /**
     * @description get login and password: they could be set in a $controller or in a $config
     * @param object $controller
     * @param array $config
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public function getLoginAndPassword($controller, $config)
    {
        $login = isset($controller->ldapLogin) ?
                $controller->ldapLogin :
                (isset($config['login']) ?
                        $config['login'] :
                        'test');

        $password = isset($controller->ldapPassword) ?
                        $controller->ldapPassword :
                        (isset($config['password']) ?
                                $config['password'] :
                                'test');

        return array($login, $password);
    }

    /**
     * @description extract LDAP-configuration from the application-config or passed config
     * @param bool|array|object $config
     * @return bool
     * @author Se#
     * @version 0.0.1
     */
    public function getConfig($config = false)
    {
        $config = $config ? $config : Zend_Registry::get('config');
        $config = is_object($config) ? $config->toArray() : $config;

        if(isset($config['evil']) && isset($config['evil']['auth']) && isset($config['evil']['auth']['ldap']))
            return $config['evil']['auth']['ldap'];

        return false;
    }

    /**
     * @return void
     */
    public function onSuccess()
    {
        
    }

    /**
     * @return void
     */
    public function onFailure()
    {

    }
}
