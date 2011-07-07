<?php

/**
 * @author Se#
 * @type Action
 * @description: Create Action
 * @package Evil
 * @subpackage Controller
 * @version 0.0.3
 */

class Evil_Action_Create extends Evil_Action_Abstract
{
    /**
     * @description create a row in a DB
     * @param array $params
     * @param object $table
     * @param object|array $config
     * @param object $controller
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected function _actionCreate()
    {
        $params = self::$_info['params'];

        foreach($params as $attr => $value)
            $params[$attr] = is_numeric($value) ? (int) $value : htmlspecialchars($value);

        self::$_info['table']->insert($this->_cleanParams($params));

        if(isset(self::$_info['config']->create->redirect))
            self::$_info['controller']->_redirect(self::$_info['config']->create->redirect);

        self::$_info['controller']->view->result = '<span style="color: green; font-size: 24px">Created</span>';

        return $params;
    }
}