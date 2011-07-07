<?php
/**
 * @description Worker for unknown error
 * @author Se#
 * @version 0.0.1
 */
class Evil_Error_UnknownError extends Evil_Error
{
    /**
     * @description log by logger from Zend_Registry::get('logger')
     * @static
     * @return bool
     * @author Se#
     * @version 0.0.1
     */
    public static function log()
    {
        list($info) = func_get_args();

        if(is_array($info) && isset($info['curArgs']) && isset($info['env']))
        {
            $logger = Zend_Registry::get('logger');
            list($code, $message) = $info['curArgs'];
            $logger->log($message . '::' . $code . ' ::json:' . json_encode($env));
        }

        return true;
    }
}