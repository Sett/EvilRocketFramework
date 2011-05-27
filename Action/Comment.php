<?php
/**
 * @author Se#
 * @version 0.0.1
 * @description comment action
 */
class Evil_Action_Comment extends Evil_Action_Abstract
{
    /**
     * @description comments list
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected function _actionList()
    {
        if(!isset(self::$_info['params']['id']) || !isset(self::$_info['params']['from']))
            self::$_info['controller']->_redirect('/');

        $commentTable = new Zend_Db_Table(Evil_DB::scope2table('comment'));

        self::$_info['controller']->view->object = self::$_info['table']->fetchRow(self::$_info['table']->select()
                                                          ->where('id=?', self::$_info['params']['id']));

        $comments = $commentTable->fetchAll($commentTable->select()
                                                    ->where('objectId=?', self::$_info['params']['id'])
                                                    ->where('objectTable=?',
                                                            Evil_DB::scope2table(self::$_info['params']['from'])));

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
        $topicTable = new Zend_Db_Table(Evil_DB::scope2table('comment'));
        $data = array();

        foreach($params as $param => $value)
        {
            if(strpos($param, 'topic') !== false)
                $data[substr($param, 5)] = $value;
        }

        $data['ctime'] = time();
        $data['etime'] = $data['ctime'];

        $topicTable->insert($data);

        self::$_info['controller']->_redirect('/' . self::$_info['controllerName']
                         . '/comment/id/' . $data['objectId'] . '/do/list/from/' . self::$_info['controllerName']);
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
        
        if(!$justFetch)
            $controller->view->headLink()->appendStylesheet($controller->view->baseUrl() . '/css/comments.css');

        if(!isset($params['id']))
            $controller->_redirect('/');

        $db = Zend_Registry::get('db');
        $controller->view->comments = $db->fetchAll($db->select()->
                                         from(Evil_DB::scope2table('comment'))->
                                         where('objectId=?', $params['id'])->
                                         where('objectTable=?', Evil_DB::scope2table($params['controller'])));

        if(!$justFetch)
            $controller->view->commentsForm =
                new Zend_Form($this->_changeFormConfig($controller->selfConfig['comment']['commentForm']));
        
        return $table->fetchRow($table->select()->from($table)->where('id=?', $params['id']));
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
    protected function _changeFormConfig($formConfig)
    {
        $params = self::$_info['params'];
        $controller = self::$_info['controller'];
        $rowData = $this->_actionDefault(true)->toArray();

        $submit  = $formConfig['elements']['submit'];
        unset($formConfig['elements']['submit']);
        $submit['options']['label'] = 'Comment';

        foreach($formConfig['elements'] as $name => $element)
            $formConfig['elements'][$name]['options']['readOnly'] = true;

        $formConfig['elements']['topicobjectId'] = array(
            'type' => 'hidden',
            'options' => array('value' => $params['id'])
        );

        $formConfig['elements']['topictitle'] = array(
            'type' => 'text',
            'options' => array(
                'value' => 'Re: ' . $rowData['title'],
                'readOnly' => true,
                'label' => isset($controller->selfConfig['comment']['comment']['title']) ?
                        $controller->selfConfig['comment']['comment']['title'] :
                        'Title'
            )
        );

        $formConfig['elements']['topicobjectTable'] = array(
            'type' => 'hidden',
            'options' => array('value' => Evil_DB::scope2table($params['controller']))
        );


        $formConfig['elements']['topiccontent'] = array(
            'type' => 'textarea',
            'options' => array(
                'rows' => '5',
                'label' => 'Input text'
            )
        );

        if(isset($controller->selfConfig['comment']['comment']['content']))
            $formConfig['elements']['topiccontent']['options']['label'] =
                    $controller->selfConfig['comment']['comment']['content'];

        $formConfig['elements']['topicauthor'] = array(
            'type' => 'text',
            'options' => array(
                'label' => 'Please, introduce yourself',
                'value' => 'Get from current user'
            )
        );

        $formConfig['elements']['submit'] = $submit;

        return $formConfig;
    }
}