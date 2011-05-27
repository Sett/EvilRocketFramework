<?php
/**
 * @author Se#
 * @description Static page action
 * @version 0.0.1
 */
class Evil_Action_Spage extends Evil_Action_Abstract
{
    protected function _actionDefault($params, $table, $config, $controller)
    {
        $params = $this->_cleanParams($params);
        $path = isset($controller->selfConfig['spage']['path']) ?
                $controller->selfConfig['spage']['path'] :
                '/public/pages/';

        $ext = isset($controller->selfConfig['spage']['ext']) ?
                $controller->selfConfig['spage']['ext'] :
                'phtml';

        $page = '';

        foreach($params as $name => $value)
            $page .= $name . '_' . $value . '_';

        $page = substr($page, 0, strlen($page)-1) . '.' . $ext;

        $controller->getHelper('viewRenderer')->setNoRender(); // turn off native (personal) view
        $controller->view->addScriptPath(ROOT . $path);// add current folder to the view path
        $controller->getHelper('viewRenderer')->renderScript($page);// render default script
    }
}