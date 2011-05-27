<?php
/**
 * @description Create sub-object
 * @author Se#
 * @version 0.0.1
 */
class Evil_Action_Sub extends Evil_Action_Abstract
{
    /**
     * @description prepare form
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _actionDefault()
    {
        if(!isset(self::$_info['params']['id']))
            self::$_info['controller']->_forward('index', self::$_info['controllerName'], null, self::$_info['params']);

        $params = self::$_info['params'];// for comfort (=

        $config = isset(self::$_info['controller']->selfConfig['sub']) ?
                self::$_info['controller']->selfConfig['sub'] :
                $this->_getDefaultActionConfig();

        $params = $this->_required($config, $params);

        $subForm = $this->_createFormOptionsByTable(true);
        self::$_info['controller']->view->subForm = new Zend_Form($subForm);

        return $params;
    }

    /**
     * @description set required fields
     * @param array $config
     * @param array $params
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _required($config, $params)
    {
        if(isset($config['required']))
        {
            $success = true;
            foreach($config['required'] as $field)
            {
                if(!is_string($field))
                {
                    $operated = $this->_operateField($params, $field);
                    if($operated)
                        $params[$operated['attribute']] = $operated['value'];
                }

                if(!isset($params[$field]))
                {
                    $success = false;
                    break;
                }
            }

            if(!$success)
            {
                $row = self::$_info['table']->fetchRow(self::$_info['table']->select()->where('id=?', $params['id']));
                if(!$row)
                    self::$_info['controller']->_forward('index', self::$_info['controllerName'], null, $params);

                $row->toArray();

                foreach($config['required'] as $field)
                {
                    if(is_string($field))
                        $params[$field] = isset($row[$field]) ? $row[$field] : 'Missed';
                    else
                    {
                        $result = $this->_operateField($row, $field);
                        $params[$result['attribute']] = $result['value'];
                    }
                }
            }
        }

        return $params;
    }

    /**
     * @description create an object-record + fixes a sub-relationship
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected function _actionSub()
    {
        $params = $this->_cleanParams(self::$_info['params']);
        $src = $params['id'];
        unset($params['id']);
        self::$_info['table']->insert($params);
        $dest = Zend_Registry::get('db')->lastInsertId();

        Zend_Registry::get('db')->insert(Evil_DB::scope2table(self::$_info['controllerName'], '-sub'),
                                         array(
                                             'src' => $src,
                                             'dest' => $dest
                                         ));

        self::$_info['controller']->_forward('list', self::$_info['controllerName'], null, $params);
    }
}