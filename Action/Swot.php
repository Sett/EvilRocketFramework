<?php
/**
 * @author Se#
 * @type Action
 * @description: SWOT Action
 * @package Evil
 * @subpackage Controller
 * @version 0.0.2
 * @changeLog extend Abstract
 */
class Evil_Action_Swot extends Evil_Action_Abstract implements Evil_Action_Interface
{
    /**
     * @description invoke
     * @param Zend_Controller_Action $controller
     * @param string $ext
     * @param array $params
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function __invoke(Zend_Controller_Action $controller, $ext = null, $params = null)
    {
        return parent::__invoke($controller, $ext, $params, 'action');
    }

    /**
     * @description create a swot record in a DB
     * @param array $params
     * @param object $table
     * @param object|array $config
     * @param object $controller
     * @return array
     * @author Se#
     * @version 0.0.4
     */
    protected function _actionCreate()
    {
        $params     = self::$_info['params'];
        $table      = self::$_info['table'];
        $controller = self::$_info['controller'];
        $config     = self::$_info['config'];

        $params['objectTable'] = Evil_DB::scope2table($this->_controllerName($controller, $params));
        $params['etime'] = time();
        $params['ctime'] = time();

        foreach(array('s', 'w', 'o', 't') as $item)
        {
            if(empty($params[$item]))
                $params[$item] = ' - ';
        }

        $table->insert($params);

        if(isset($config->swot->create->redirect))
            $controller->_redirect($config->swot->create->redirect);
    }

    protected function _actionList()
    {
        $params     = self::$_info['params'];

        if(isset($params['id']))
        {
            
        }
    }
}