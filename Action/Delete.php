<?php

/**
 * @author Se#
 * @type Action
 * @description: Delete Action
 * @package Evil
 * @subpackage Controller
 * @version 0.0.2
 */

class Evil_Action_Delete extends Evil_Action_Abstract implements Evil_Action_Interface
{
    protected function _actionDelete($params, $table, $config, $controller)
    {
        if(!isset($params['id']))
            $controller->_redirect('/');

        $table->delete('id="' . $params['id'] . '"');

        $controller->_redirect('/' . $this->_controllerName($controller, $params) . '/list');
    }

    protected function _actionDefault($params, $table, $config, $controller)
    {
        if(isset($params['id']))
            return $this->_actionDelete($params, $table, $config, $controller);

        $limit = isset($params['limit']) ? $params['limit'] : 10;
        $controller->view->assign('rows', $table->fetchAll($table->select()->limit($limit)));
    }
}