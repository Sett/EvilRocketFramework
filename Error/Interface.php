<?php
/**
 * @description basic error interface
 * @author Se#
 * @version 0.0.1
 */
interface Evil_Error_Interface
{
    /**
     * @description dispatch an error
     * @static
     * @abstract
     * @param array config
     * @return mixed
     * @author Se#
     * @version 0.0.1
     */
    public static function dispatch($config);
}