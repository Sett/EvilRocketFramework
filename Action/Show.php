<?php

/**
 * @author BreathLess, Se#
 * @description Show action
 * @type Zend Action
 * @version 0.0.2
 */
class Evil_Action_Show extends Evil_Action_Abstract implements Evil_Action_Interface
{
    /**
     * @description extract data from a DB
     * @return object|array
     * @author Se#
     * @version 0.0.2
     */
    protected function _actionDefault()
    {
        $params     = self::$_info['params'];
        $table      = self::$_info['table'];
        $controller = self::$_info['controller'];

        if(!isset($params['id']))
            $controller->_redirect('/');

        return $table->fetchRow($table->select()->from($table)->where('id=?', $params['id']));
    }
}
