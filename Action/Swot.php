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
     * @version 0.0.3
     */
    protected function _actionCreate($params, $table, $config, $controller)
    {
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

    protected function _actionList($params, $table, $config, $controller)
    {
        if(isset($params['id']))
        {
            
        }
    }
}