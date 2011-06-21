<?php
/**
 * @author Se#
 * @version 0.0.1
 * @description comment action
 */
class Evil_Action_Comment extends Evil_Action_Abstract
{
    protected $_defaultConfig = array();

    /**
     * @description comments list
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected function _actionList()
    {
        $object = Evil_Structure::getObject('comment');
        $params = self::$_info['params'];
        $from = (strpos($params['from'], Zend_Registry::get('db-prefix')) !== false) ?
                $params['from'] :
                Evil_DB::scope2table($params['from']);

        $where = array(
            'objectTable=?' => $from,
            'objectId=?' => $params['id']
        );

        $comments = $object->load(null, null, null, $where);
        
        if(!isset(self::$_info['params']['id']) || !isset(self::$_info['params']['from']))
            self::$_info['controller']->_redirect('/');

        self::$_info['controller']->view->object = self::$_info['table']->fetchRow(self::$_info['table']->select()
                                                          ->where('id=?', self::$_info['params']['id']));

        self::$_info['controller']->getHelper('viewRenderer')->setNoRender(); // turn off native (personal) view
        self::$_info['controller']->view->addScriptPath(__DIR__ . '/Comment/application/views/scripts/');// add current folder to the view path
        self::$_info['controller']->view->list = $comments;
        self::$_info['controller']->view->headLink()
                ->appendStylesheet(self::$_info['controller']->view->baseUrl() . '/css/blog.css');
        self::$_info['controller']->getHelper('viewRenderer')->renderScript('list' . '.phtml');// render default script

    }

    /**
     * @description save comment action
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected function _actionComment()
    {
        $params = self::$_info['params'];
        $data = array();

        $mask = 'comment';
        foreach($params as $param => $value)
        {
            if(strpos($param, $mask) !== false)
                $data[substr($param, strlen($mask))] = $value;
        }

        $data['ctime'] = time();
        $data['etime'] = $data['ctime'];

        $object = Evil_Structure::getObject('comment');
        $object->create($data);

        $controller = substr($data['objectTable'], strpos($data['objectTable'], '_')+1, strlen($data['obejctTable']) -1);

        self::$_info['controller']->_redirect('/' . $controller
                         . '/comment/id/' . $data['objectId'] . '/do/list/from/' . $data['objectTable']);
    }

    /**
     * @description show all comments for current object
     * @param array $params
     * @param object $table
     * @param object $config
     * @param object $controller
     * @return object|array
     * @author Se#
     * @version 0.0.1
     */
    public function _actionDefault($justFetch = false)
    {
        $params     = self::$_info['params'];
        $table      = self::$_info['table'];
        $controller = self::$_info['controller'];

        $object = Evil_Structure::getObject('comment');

        if(!$justFetch)
            $controller->view->headLink()->appendStylesheet($controller->view->baseUrl() . '/css/comments.css');

        if(!isset($params['id']))
            $controller->_redirect('/');

        $db = Zend_Registry::get('db');
        $controller->view->comments = $db->fetchAll($db->select()->
                                         from(Evil_DB::scope2table('comment'))->
                                         where('objectId=?', $params['id'])->
                                         where('objectTable=?', Evil_DB::scope2table($params['controller'])));

        $objectData = $table->fetchRow($table->select()->from($table)->where('id=?', $params['id']));

        $this->_commentsForm($justFetch, $objectData);
        
        return $objectData;
    }

    protected function _commentsForm($justFetch, $objectData)
    {
        $controller = self::$_info['controller'];

        if(!$justFetch && ($commentConfig = $this->_getFromConfig('comment')))
        {
            if(isset($commentConfig['commentForm']))
            {
                $controller->view->commentsForm =
                    new Zend_Form($this->_changeFormConfig($commentConfig['commentForm'], $objectData));
            }
        }
    }

    protected function _loadDefaultConfig()
    {
        $path = __DIR__ . '/Comment/application/configs/comment.json';
        if(is_file($path))
            $this->_defaultConfig = json_decode(file_get_contents($path), true);
    }

    protected function _getFromConfig($name)
    {
        if(empty($this->_defaultConfig))
            $this->_loadDefaultConfig();

        if(isset(self::$_info['controller']->selfConfig[$name]))
            return self::$_info['controller']->selfConfig[$name];
        elseif(isset($this->_defaultConfig[$name]))
            return $this->_defaultConfig[$name];

        return false;
    }

    /**
     * @description inject topic fields into form
     * @param array $formConfig
     * @param object $table
     * @param string $action
     * @param object $config
     * @param array $params
     * @return
     * @author Se#
     * @version 0.0.1
     */
    protected function _changeFormConfig($formConfig, $objectData = array())
    {
        $params = self::$_info['params'];
        $controller = self::$_info['controller'];
        $rowData = $this->_actionDefault(true)->toArray();

        $submit  = $formConfig['elements']['submit'];
        unset($formConfig['elements']['submit']);
        $submit['options']['label'] = 'Comment';

        foreach($formConfig['elements'] as $name => $element)
            $formConfig['elements'][$name]['options']['readOnly'] = true;

        if('comment' == self::$_info['controllerName'])
        {
            $objectId = $objectData['objectId'];
            $objectTable = isset($objectData['objectTable']) ?
                    $objectData['objectTable'] : '';
        }
        else
        {
            $objectId = $params['id'];
            $objectTable = Evil_DB::scope2table($params['controller']);
        }

        $formConfig['elements']['commentobjectId'] = array(
            'type' => 'hidden',
            'options' => array('value' => $objectId)
        );

        $formConfig['elements']['commenttitle'] = array(
            'type' => 'text',
            'options' => array(
                'value' => 'Re: ' . $rowData['title'],
                'readOnly' => true,
                'label' => isset($controller->selfConfig['comment']['comment']['title']) ?
                        $controller->selfConfig['comment']['comment']['title'] :
                        'Title'
            )
        );

        $formConfig['elements']['commentobjectTable'] = array(
            'type' => 'hidden',
            'options' => array('value' => $objectTable)
        );

        $formConfig['elements']['commentcontent'] = array(
            'type' => 'textarea',
            'options' => array(
                'rows' => '5',
                'label' => 'Input text'
            )
        );

        if(isset($controller->selfConfig['comment']['comment']['content']))
            $formConfig['elements']['commentcontent']['options']['label'] =
                    $controller->selfConfig['comment']['comment']['content'];



        $formConfig['elements']['commentauthor'] = array(
            'type' => 'text',
            'options' => array(
                'label' => 'Please, introduce yourself',
                'value' => $this->_getUserName()
            )
        );

        $formConfig['elements']['submit'] = $submit;
        $formConfig = $this->_appendCommentData($formConfig);

        return $formConfig;
    }

    protected function _getUserName()
    {
        $controller = self::$_info['controller'];

        $name = 'Guest';
        if(-1 != Zend_Registry::get('userid'))
        {
            if(isset($controller->selfConfig['comments']['author']['table']))
            {
                $table = $controller->selfConfig['comments']['author']['table'];
                $field = $controller->selfConfig['comments']['author']['idField'];
                $nameField = $controller->selfConfig['comments']['author']['nameField'];
            }
            else
            {
                $table = 'user';
                $field = 'id';
                $nameField = 'name';
            }

            $table = new Zend_Db_Table(Evil_DB::scope2table($table));
            $user = $table->fetchRow($table->select()->where($field . '=?', Zend_Registry::get('userid')));
            if($user)
            {
                $user = $user->toArray();
                $name = isset($user[$nameField]) ? $user[$nameField] : 'Missed "' . $nameField . '" ';
            }
        }

        return $name;
    }

    protected function _appendCommentData($formConfig)
    {
        $params = self::$_info['params'];
        if('comment' == self::$_info['controllerName'])
        {
            $id = isset($params['id']) ? $params['id'] : 1;

            if(!isset($formConfig['elements']))
                return $formConfig;

            $formConfig['elements']['commentparentId'] = array(
                'type' => 'hidden',
                'options' => array(
                    'value' => $id
                )
            );
        }
        
        return $formConfig;
    }
}