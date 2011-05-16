<?php

/**
 * @author BreathLess, Se#
 * @description Show action
 * @type Zend Action
 * @version 0.0.2
 */
class Evil_Action_Show extends Evil_Action_Abstract implements Evil_Action_Interface
{
    protected function _actionDefault($params, $table, $config, $controller)
    {
        if(!isset($params['id']))
            $controller->_redirect('/');

        return $table->fetchRow($table->select()->from($table)->where('id=?', $params['id']));
    }
}
