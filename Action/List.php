<?php

/**
 * @author Se#
 * @type Action
 * @description List Action
 * @package Evil
 * @subpackage Controller
 * @version 0.0.4
 */
class Evil_Action_List extends Evil_Action_Abstract implements Evil_Action_Interface
{
    /**
     * @description construct list
     * @return void
     * @author Se#
     * @version 0.0.2
     */
    protected function _actionDefault()
    {
        $params     = self::$_info['params'];
        $table      = self::$_info['table'];
        $controller = self::$_info['controller'];

        $limit = isset($params['limit']) ? $params['limit'] : 10;
        $select = $table->select()->limit($limit);

        if(isset($params['id']))
        {
            $field = isset($controller->selfConfig['list']['field']) ?
                            $controller->selfConfig['list']['field'] :
                            'id';

            $select->where($field . '=?', $params['id']);
        }

        if(isset(self::$_info['controller']->selfConfig['list']['metadata']))
            $metadata = self::$_info['controller']->selfConfig['list']['metadata'];
        else
            $metadata = self::$metadata;

        $data = $table->fetchAll($select);

        $controller->view->fields = $metadata;
        $controller->view->assign('list', $data);
    }

    /**
     * @description add link to itself
     * @static
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public static function __autoLoad($args = array())
    {
        if(!is_array($args))
            $name = $args->_getParam('controller');
        else
            $name = isset($args['controllerName']) ? $args['controllerName'] : '';

        return '<a href="/' . $name . '/list">List</a>';
    }
}