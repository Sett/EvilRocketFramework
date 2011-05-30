<?php

require_once "Zend/Application.php";

/**
 * @description Application, may cache config
 * @author Se#
 * @version 0.0.2
 */
class Evil_Application extends  Zend_Application
{
    /**
     *
     * @var Zend_Cache_Core|null
     */
    protected $_configCache;

    /**
     * @description Constructor
     * @param string $environment
     * @param string|array $options
     * @param string $useCache
     * @param null|Zend_Cache_Core $configCache
     * @author Se#
     * @version 0.0.1
     */
    public function __construct($environment, $options = null, $useCache = 'production',
        Zend_Cache_Core $configCache = null)
    {
        if($useCache && empty($configCache))
            $configCache = $this->_defaultConfigCache($useCache);

        $this->_configCache = $configCache;
        parent::__construct($environment, $options);
    }

    /**
     * @description create default cache core
     * @param string|array $env
     * @return Zend_Cache_Core
     * @author Se#
     * @version 0.0.2
     */
    protected function _defaultConfigCache($env)
    {
        $env = is_array($env) ? $env : array($env);
        $configCache = null;

        //We will cache only in defined environment(s)
        if (in_array(APPLICATION_ENV, $env))
        {
            list($class, $file) = $this->_defineCacheClassAndFile();// Bubble-defining

            if(is_file($file))
            {
                require_once 'Zend/Cache.php';
                require_once 'Zend/Cache/Core.php';
                require_once $file;

                $configCache = new Zend_Cache_Core(array('automatic_serialization'=>true));
                $backend     = new $class();
                
                $configCache->setBackend($backend);
            }
        }

        return $configCache;
    }

    /**
     * @description Bubble-defining cache class and file
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _defineCacheClassAndFile()
    {
        $class = 'Zend_Cache_Backend_Test';
        $file  = 'Zend/Cache/Backend/Test.php';

        $cacheConfig = $this->_getCacheConfig();// return array or false if failed

        if(($cacheConfig != false) && isset($cacheConfig['backend']))
        {
            foreach($cacheConfig['backend'] as $options)
            {
                if(isset($options['check']) && self::parseFunction($options['check'], array($options['name'])))
                {
                    $class = isset($options['class']) ? $options['class'] : 'Zend_Cache_Backend_Test';
                    $file  = isset($options['file'])  ? $options['file']  : 'Zend/Cache/Backend/Test.php';
                    break;
                }
            }
        }

        return array($class, $file);
    }

    /**
     * @description get cache config from application/configs/evil/cache.json (if exists)
     * or from /library/Evil/Application/configs/cache.json, return false if failed
     * @return bool|array
     * @author Se#
     * @version 0.0.1
     */
    protected function _getCacheConfig()
    {
        $generalPath = '/configs/evil/cache.json';

        if(is_file(APPLICATION_PATH . $generalPath))
            $path = APPLICATION_PATH . $generalPath;
        elseif(is_file(__DIR__ . '/Application' . $generalPath))
            $path = __DIR__ . '/Application' . $generalPath;
        else
            return false;

        return json_decode(file_get_contents($path), true);
    }

    /**
     * @description define and call function
     * @static
     * @param string|array $options
     * @param array $args
     * @param null|string|object $defaultClass
     * @return mixed
     * @author Se#
     * @version 0.0.1
     */
    public static function parseFunction($options, $args, $defaultClass = null)
    {
        if(is_string($options) && !empty($defaultClass))
            return call_user_func_array(array($defaultClass, $options), $args);
 
        return call_user_func_array($options, $args);
    }

    /**
     * @description generate cache id
     * @param string $file
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    protected function _cacheId($file)
    {
        return sha1($file . '_' . $this->getEnvironment());
    }

    /**
     * @param string $file
     * @param bool $fromDefault
     * @return Zend_Config
     * @author Se#
     * @version 0.0.1
     */
    protected function _loadConfig($file, $fromDefault = false)
    {// define is default config need
        $default = $fromDefault ? false : $this->_defaultConfig();

        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($this->_configCache === null
            || $suffix == 'php'
            || $suffix == 'inc')
        //No need for caching those
            return $default ? $this->_mergeConfigs($default, parent::_loadConfig($file)) : parent::_loadConfig($file);

        $configMTime = filemtime($file);

        $cacheId = $this->_cacheId($file);
        $cacheLastMTime = $this->_configCache->test($cacheId);

        if (
            $cacheLastMTime !== false
            && $configMTime < $cacheLastMTime
        )//Valid cache?
            return $this->_configCache->load($cacheId, true);
        else
        {
            $config = parent::_loadConfig($file);
            $this->_configCache->save($config, $cacheId, array(), null);

            return $default ? $this->_mergeConfigs($default, $config) : $config;
        }
    }

    /**
     * @description merge configs
     * @param array|string $default
     * @param array|string $config
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _mergeConfigs($default, $config)
    {
        $result = array();

        if(is_string($default))
            return $config;

        foreach($default as $item => $value)
        {
            
            if(isset($config[$item]))
                $result[$item] = $this->_mergeConfigs($value, $config[$item]);
            else
                $result[$item] = $value;
        }

        return $result;
    }

    /**
     * @description get default config
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _defaultConfig()
    {
        return file_exists(__DIR__ . '/Application/configs/default.ini') ?
                $this->_loadConfig(__DIR__ . '/Application/configs/default.ini', true) : array();
    }
}