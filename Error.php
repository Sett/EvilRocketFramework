<?php
/**
 * @description Error dispatcher
 * @author Se#
 * @version 0.0.1
 */
class Evil_Error implements Evil_Error_Interface
{
    /**
     * @description dispatching result
     * @var mixed|null
     * @author Se#
     * @version 0.0.1
     */
    public $result = null;

    /**
     * @description default log, do nothing
     * @static
     * @param string $message
     * @param int|string $code
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public static function log($message, $code)
    {
        $logger = Zend_Registry::get('logger');
        $logger->log($message . ' ; ' . $code);
        
        return array($message, $code);
    }

    /**
     * @description Error-dispatching constructor
     * @param string|int $code
     * @param string $message
     * @author Se#
     * @version 0.0.2
     */
    public function __construct($code, $message = '')
    {
        $args = func_get_args();

        if(is_file(APPLICATION_PATH . '/configs/errors/' . $code . '.json'))
        {
            $errorConfig = json_decode(file_get_contents(APPLICATION_PATH . '/configs/errors/' . $code . '.json'), true);
            $errorClassName = 'Evil_Error_' . ucfirst($code);
            $errorClass = new $errorClassName($code, $message);
            if(!$errorClass instanceof Evil_Error_Interface)
                return $this;
            
            $this->result = $errorClass::dispatch($errorConfig, $args);
        }
        else
        {
            if(is_file(__DIR__ .'/Error/Unknown_error.json'))
            {
                $this->result = self::dispatch(json_decode(file_get_contents(__DIR__ .'/Error/Unknown_error.json'), true),
                                               $args);
            }
            else
                throw new Exception(' Missed configuration for the "unknown error" ');
        }

        return $this;
    }

    /**
     * @description general error dispatching
     * @static
     * @param array $config
     * @param array $curArgs
     * @return mixed|null
     * @author Se#
     * @version 0.0.1
     */
    public static function dispatch($config, $curArgs = array())
    {
        $methods = isset($config['methods']) ? $config['methods'] : array();
        $data    = isset($config['data'])    ? $config['data']    : array();
        $options = isset($config['options']) ? $config['options'] : array();

        $chain         = isset($options['chain']) ? $options['chain'] : false;
        $count         = count($methods);
        $resultsTrace  = array();
        $currentResult = null;
        $env           = self::getEnv();

        for($i = 0; $i < $count; $i++)
        {
            $method = isset($methods[$i]['method']) ? $methods[$i]['method'] : 'time';
            $args = isset($methods[$i]['args']) && is_array($methods[$i]['args']) ? $methods[$i]['args'] : array();
            $args = array(
                array(
                    'data'         => $data,        // needed data
                    'options'      => $options,     // general error options
                    'curArgs'      => $curArgs,     // current arguments (message, etc.)
                    'resultsTrace' => $resultsTrace,// all dispatch results
                    'env'          => $env          // environment information
                ),
                $args
            );

            if(isset($methods[$i]['chain']))
                $chain = $methods[$i]['chain'];// personal chain option

            if($chain)
                $args[] = $currentResult;// pass current result

            if(!isset($resultsTrace[$method]))
                $resultsTrace[$method] = array();

            $currentResult = call_user_func_array($method, $args);
            $resultsTrace[$method][] = $currentResult;// sure-to-have results
        }

        return $currentResult;
    }

    /**
     * @description get:
     * 1. Backtrace;
     * 2. Object-info (class name, methods, properties);
     * 3. Application environment;
     * 4. Git/SVN info;
     * 
     * @static
     * @param null|object $object
     * @param array $config
     * @return array
     * @author Se#
     * @version 0.0.2
     * @changeLog
     *  0.0.2 : svn, git info
     */
    public static function getEnv($object = null, $config = array())
    {
        $result = array();
        $result['backtrace'] = debug_backtrace();
        if(empty($object))
            $result['object'] = 'empty';
        else
        {
            $className = get_class($object);

            $result['object'] = array(
                'json'       => json_encode($object),
                'class'      => $className,
                'methods'    => get_class_methods($className),
                'properties' => get_class_vars($className)
            );
        }

        $result['APPLICATION_ENV'] = defined('APPLICATION_ENV') ? APPLICATION_ENV : 'undefined';
        
        if(!isset($config['git']) || $config['git'])
            $result['git'] = self::getGitInfo();
        elseif(!isset($config['svn']) || $config['svn'])
            $result['svn'] = self::getSvnInfo();

        return $result;
    }

    /**
     * @description get Git info
     * @static
     * @return array|null
     * @author Se#
     * @version 0.0.1
     */
    public static function getGitInfo()
    {
        return self::exec('git log -n 1');
            /**
             * array(
             *      0 => commit commitHash
             *      1 => Author: AuthorName
             *      2 => Date: Thu Jun 9 15:09:47 2011 +0400
             * )
             */
    }

    /**
     * @description get SVN info
     * @static
     * @return array|null
     * @author Se#
     * @version 0.0.1
     */
    public static function getSvnInfo()
    {
        return self::exec('svn info');
    }

    /**
     * @description alias for exec, but return result
     * @static
     * @param string $command
     * @return array|null
     * @author Se#
     * @version 0.0.1
     */
    public static function exec($command)
    {
        exec ($command, $result);
        return $result;
    }

    /**
     * @description default author-extracter
     * @static
     * @return mixed
     * @author Se#
     * @version dev
     */
    public static function getAuthor()
    {
        // TODO: realize
        list($info, $args) = func_get_args();
        if(is_array($info) && isset($info['env']) && isset($info['env']['object']) && is_array($info['env']['object']))
        {
            $className = isset($info['env']['object']['class']) ? $info['env']['object']['class'] : 'Unknown';
            // search the class file
            if(is_array($args) && isset($args['library-path']))
            {
                $path = $args['library-path'] . '/' . str_replace('_', '/', $className) . '.php';
                if(is_file($path))
                {
                    // extract the class author
                    $fileInfo = stat($path);
                }
            }

            return $className;
        }
        
        return 'Unknown';
    }
}