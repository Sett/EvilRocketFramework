<?php
/**
 * Bootstrap
 *
 * Created by JetBrains PhpStorm.
 * @author Alexander M Artamonov <art.alex.m@gmail.com>
 * @version 0.1
 * @date 04.05.11
 * @time 9:35
 */

define('DS', DIRECTORY_SEPARATOR);

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)) . DS. '..' . DS . '..' . DS);

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH),
    get_include_path(),
)));

//var_dump(get_include_path());

function testAutoLoader($name)
{
    include_once(str_replace('_', DS, $name) . '.php');
}

spl_autoload_register('testAutoLoader');
