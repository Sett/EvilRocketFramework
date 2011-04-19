<?php

/**
 * $providerList = Evil_Cache::load('uniqueid');
 * Evil_Cache::save(array("data"=>time(),'uniqueid');
 * Простенькая обвязка над Zend_cache
 * @author nur
 *
 */
class Evil_Cache
{
    protected $cache = null;
    private static $instance;
    public static $lifetime = 7200; //2 
    private static function getInstance(){
         if(!isset(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function __construct ()
    {
        if (null == $this->cache) {
            $frontendOptions = array('lifetime' => self::$lifetime, 
            'automatic_serialization' => true);
            $backendOptions = array('cache_dir' => APPLICATION_PATH . '/cache');
            $this->cache = Zend_Cache::factory('Core', 'File', 
            $frontendOptions, $backendOptions);
        }
    }
    public function __call($methodName,$params)
    {
       return  call_user_func_array(array($this->cache,$methodName), $params);
    }
    /**
     * 
     * Вот тут я сомневаюсь что не наклал =(
     * @param unknown_type $methodName
     * @param unknown_type $params
     */
    public static function __callStatic($methodName,$params) {
           return  call_user_func_array(array(self::getInstance()->cache,$methodName), $params);
    }
}