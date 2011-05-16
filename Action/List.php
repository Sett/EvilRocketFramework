<?php

/**
 * @author Se#
 * @type Action
 * @description List Action
 * @package Evil
 * @subpackage Controller
 * @version 0.0.2
 */

class Evil_Action_List extends Evil_Action_Abstract implements Evil_Action_Interface
{
    protected function _actionDefault($params, $table, $config, $controller)
    {
        $limit = isset($params['limit']) ? $params['limit'] : 10;
        $controller->view->fields = empty(self::$metadata) ? $table->info('metadata') : self::$metadata;
        $controller->view->assign('list', $table->fetchAll($table->select()->limit($limit)));
    }

    public static function autoLoad($controller, $params)
    {
        if(!isset($controller->view->pleaseShow))
            $controller->view->pleaseShow = array();
        
        $controller->view->pleaseShow[] = 'list';
    }
}