<?php
/**
 * @author Se#
 * @type Action
 * @description: Update Action
 * @package Evil
 * @subpackage Controller
 * @version 0.1
 * @date 29.10.10
 * @time 15:21
 */
class Evil_Action_Update extends Evil_Action_Abstract implements Evil_Action_Interface
{
    /**
     * @description update row in a DB
     * @param array $params
     * @param object $table
     * @param object|array $config
     * @param object $controller
     * @return null|object|array
     * @author Se#
     * @version 0.0.2
     */
    protected function _actionUpdate()
    {
        $params     = $this->_cleanParams(self::$_info['params']);
        $table      = self::$_info['table'];

        $table->update($params, 'id="' . $params['id'] . '"');
        return $table->fetchRow($table->select()->from($table)->where('id=?', $params['id']));
    }

    /**
     * @description default action
     * @param array $params
     * @param object $table
     * @param object|array $config
     * @param object $controller
     * @return null
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

    /**
     * @description prepare link to itself
     * @static
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public static function __autoLoad()
    {
        $params     = self::$_info['params'];
        $controller = self::$_info['controller'];

        if(isset($params['id']) && ('update' != $params['action']))
        {
            $data = array('link' => 'update/id/' . $params['id'], 'text' => 'Edit');

            $controller->view->pleaseShow[] = $data;
        }
    }
}