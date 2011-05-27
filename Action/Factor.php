<?php

/**
 * @author Se#
 * @type Action
 * @description: Factor Action
 * @package Evil
 * @subpackage Controller
 * @version 0.0.4
 */

class Evil_Action_Factor extends Evil_Action_Abstract implements Evil_Action_Interface
{
    /**
     * @description operate factor
     * @param array $params
     * @param object $table
     * @param object $config
     * @param object $controller
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected function _actionFactor()
    {
        $params     = self::$_info['params'];
        $controller = self::$_info['controller'];
        $config     = self::$_info['config'];

        $data = array();

        foreach($params as $param => $value)
        {
            if(strpos($param, 'factor') !== false)
                $data[substr($param, 6)] = $value;
        }

        $data['ctime'] = time();
        $data['etime'] = time();
        $data['votes'] = 0;

        $table = new Zend_Db_Table(Evil_DB::scope2table('factor'));

        $table->insert($data);

        if(isset($config->factor->redirect))
            $controller->_redirect($config->factor->redirect);

        $controller->view->result = 'Factor added';
        $controller->_redirect('/' . $this->_controllerName($controller, $params['controller'])
                               .'/factor/id/' . $params['id']);
    }

    /**
     * @description add link "Add factor" to the view->pleaseShow
     * @static
     * @param object $controller
     * @param array $params
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public static function __autoLoad()
    {
        $params     = self::$_info['params'];
        $controller = self::$_info['controller'];

        if(!isset($controller->view->pleaseShow))
            $controller->view->pleaseShow = array();

        
        if(isset($params['id']) && ('factor' != $params['action']))
        {
            $data = array('link' => 'factor/id/' . $params['id'], 'text' => 'Add factor');
            $controller->view->pleaseShow[] = $data;
        }
        //*/
    }

    /**
     * @description show all factors for current object
     * @param array $params
     * @param object $table
     * @param object $config
     * @param object $controller
     * @return object|array
     * @author Se#
     * @version 0.0.1
     */
    public function _actionDefault()
    {
        $params     = self::$_info['params'];
        $table      = self::$_info['table'];
        $controller = self::$_info['controller'];
        $config     = self::$_info['config'];

        if(!file_exists(ROOT . 'public/css/factors.css'))
        {
            $name = 'factors_' . sha1($_REQUEST['PHPSESSID']) . '.css';

            if(!file_exists(ROOT . 'public/css/' . $name))
            {
                fopen(ROOT . 'public/css/' . $name, "w+t");
                file_put_contents(ROOT . 'public/css/' . $name,
                                   file_get_contents(__DIR__ . '/Factor/public/css/factors.css'));
            }
        }
        else
            $name = '/css/factors.css';

        $controller->view->headLink()->appendStylesheet('/css/' . $name);

        if(!isset($params['id']))
            $controller->_redirect('/');

        $db = Zend_Registry::get('db');
        $factors = $db->fetchAll($db->select()->
                                         from(Evil_DB::scope2table('factor'))->
                                         where('objectId=?', $params['id'])->
                                         where('objectTable=?', Evil_DB::scope2table($params['controller'])));

        $controller->view->factors = $factors;
        $controller->view->factorsList = $this->_getFactorsList($config);
        $controller->view->factorForm = new Zend_Form($this->_getFormConfig());
        return $table->fetchRow($table->select()->from($table)->where('id=?', $params['id']));
    }

    /**
     * @description get factors list
     * @param object $config
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _getFactorsList($config)
    {
        $config = isset($config->factor) ?
                $config->factor->toArray() :
                json_decode(file_get_contents(__DIR__ . '/Factor/' . self::$_info['invokeConfig']['paths']['configs']
                                              . 'factor.json'), true);

        $factors = isset($config['factors']) ? $config['factors'] : array('empty' => 'There are no factors');

        return $factors;
    }

    /**
     * @description inject factor fields into form
     * @param array $formConfig
     * @param object $table
     * @param string $action
     * @param object $config
     * @param array $params
     * @return
     * @author Se#
     * @version 0.0.1
     */
    protected function _getFormConfig()
    {
        $config = self::$_info['config'];
        $params = self::$_info['params'];
        $factors = $this->_getFactorsList($config);

        $formConfig = array(
            'class' => 'factor_form',
            'elements' => array(
                'do' => array('type' => 'hidden', 'options' => array('value' => 'factor'))
            )
        );

        $formConfig['elements']['factorobjectId'] = array(
            'type' => 'hidden',
            'options' => array('value' => $params['id'])
        );

        $formConfig['elements']['factorobjectTable'] = array(
            'type' => 'hidden',
            'options' => array('value' => Evil_DB::scope2table($params['controller']))
        );

        $formConfig['elements']['factortype'] = array(
            'type' => 'select',
            'options' => array(
                'label' => 'Choose factor type',
                'multiOptions' => $factors
            )
        );

        $formConfig['elements']['factorcontent'] = array(
            'type' => 'textarea',
            'options' => array('rows' => '5', 'cols' => 40)
        );

        $formConfig['elements']['factorauthor'] = array(
            'type' => 'text',
            'options' => array(
                'label' => 'Please, introduce yourself',
                'value' => 'Get from current user'
            )
        );

        $formConfig['elements']['submit'] = array(
            'type' => 'submit',
            'options' => array(
                'label' => 'Add'
            )
        );

        return $formConfig;
    }
}