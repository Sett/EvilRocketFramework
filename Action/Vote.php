<?php

/**
 * @author Se#
 * @type Action
 * @description: Update Action
 * @package Evil
 * @subpackage Controller
 * @version 0.0.3
 */
class Evil_Action_Vote extends Evil_Action_Abstract implements Evil_Action_Interface
{
    /**
     * @description default config for "button"
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    public static $buttonConfig = array();

    /**
     * @description set default values for the default options if there are absent
     * @static
     * @param array $config
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public static function grabBasicOptions($config)
    {
        $result = array();

        $result['objectId']    = isset($config['objectId'])    ? $config['objectId']    : 0;
        $result['objectTable'] = isset($config['objectTable']) ? $config['objectTable'] : 0;
        $result['author']      = isset($config['author'])      ? $config['author']      : 0;
        $result['mark']        = isset($config['mark'])        ? $config['mark']        : 0;

        $result['showAmount']  = isset($config['showAmount'])  ? $config['showAmount']  : true;
        $result['from']        = isset($config['from'])        ? $config['from']        : 'index';
        $result['label']       = isset($config['label'])       ? $config['label']       : 'Vote';
        $result['class']       = isset($config['class'])       ? $config['class']       : 'button';
        
        $result['action']      = isset($config['action'])      ? $config['action'] : $result['from'] . '/vote';

        $result['amount'] = $result['showAmount'] ?
                ' (' . Evil_Action_Vote::getVotes($result['objectId'], $result['objectTable']) . ')' :
                '';

        return $result;
    }

    /**
     * @description make button as form
     * @static
     * @param array $config
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    public static function formMake($config)
    {
        $data = Evil_Action_Vote::grabBasicOptions($config);

        return '
                <form action = "/' . $data['action'] . '" method="POST" class="voteForm">
                    <input type="submit" value="' . $data['label'] . $data['amount'] . '" class="' . $data['class'] . '">
                    <input type="hidden" name="objectId" value="' . $data['objectId'] . '">
                    <input type="hidden" name="objectTable" value="' . $data['objectTable'] . '">
                    <input type="hidden" name="author" value="' . $data['author'] . '">
                    <input type="hidden" name="mark" value="' . $data['mark'] . '">
                </form>';
    }

    /**
     * @description make button as link
     * @static
     * @param array $config
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    public static function linkMake($config)
    {
        $data = Evil_Action_Vote::grabBasicOptions($config);

        return '<a href="http://' .
                        $_SERVER['SERVER_NAME'] . '/' . $data['action'] . '/objectId/' . $data['objectId']
                        . '/objectTable/' . $data['objectTable'] . '/author/' . $data['author']
                        . '/mark/' . $data['mark'] . '" ' .
                    'class="' . $data['class'] . '">' .
               $data['label'] . $data['amount'] .
               '</a>';
    }

    /**
     * @description make two functions for working with votes
     * @static
     * @return array
     * @author Se#
     * @version 0.0.2
     */
    public static function __autoLoad()
    {
        return array(
            'button' => function($config)// function for representing button
            {
                $config = $config + Evil_Action_Vote::$buttonConfig;
                $make   = isset($config['make']) ? $config['make'] : 'form';

                if(method_exists('Evil_Action_Vote', $make . 'Make'))
                    return call_user_func_array(array('Evil_Action_Vote', $make . 'Make'), array($config));
                else
                    return 'Unknown Template "' . $make . '" ';
            },
            
            'votes' => function($objectId, $objectTable)// function for getting votes count
            {
                return Evil_Action_Vote::getVotes($objectId, $objectTable);
            }
        );
    }

    /**
     * @description get all votes or an amount of votes for the particular object
     * @static
     * @param string $objectId
     * @param string $objectTable
     * @return int|array
     * @author Se#
     * @version 0.0.1
     */
    public static function getVotes($objectId, $objectTable)
    {
        $session = new Zend_Session_Namespace('evil-votes');// get session

        if(!isset($session->votes) || !isset($session->votes[$objectId . $objectTable]))
        {
            $voteTable = new Zend_Db_Table(Evil_DB::scope2table('vote'));// get table; TODO: get name from a config

            $voteSource = $voteTable->fetchRow($voteTable->select()
                                                        ->where('objectId=?', $objectId)
                                                        ->where('objectTable=?', $objectTable)
                                                        ->order('ctime DESC'));// fetch all
            if($voteSource)
                $mark = is_object($voteSource) ? $voteSource->mark : $voteSource['mark'];
            else
                $mark = 0;

            $session->votes[$objectId . $objectTable] = $mark;
            
            return $mark;
        }

        return $session->votes[$objectId . $objectTable];
    }

    /**
     * @description save vote into a DB and forwards to the next action
     * @param array $forward where to go after action is over
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function _actionDefault()
    {// define if there are required fields
        if(!isset(self::$_info['params']['objectId']) || !isset(self::$_info['params']['objectTable']))
            self::$_info['controller']->_redirect('/');

        $params    = self::$_info['params'];// for the more comfort
        $session   = new Zend_Session_Namespace('evil-votes');// get session
        $voteTable = new Zend_Db_Table(Evil_DB::scope2table('vote'));// get table; TODO: get name from a config

        // If there are votes and count for the current object, than get last mark from it
        if(isset($session->votes) && isset($session->votes[$params['objectId'] . $params['objectTable']]))
            $params['mark'] += $session->votes[$params['objectId'] . $params['objectTable']];
        else
        {   // otherwise get last mark from a DB
            $vote = $voteTable->fetchRow($voteTable->select()
                                                 ->where('objectId=?', $params['objectId'])
                                                 ->where('objectTable=?', $params['objectTable'])->order('ctime DESC'));
            $params['mark'] += $vote ? (is_object($vote) ? $vote->mark : $vote['mark']) : 0;
        }

        $params['ctime'] = time();// set creation time

        // insert data, clean off system params such as controller, action, etc
        $voteTable->insert($this->_cleanParams($params));

        if(isset($session->votes))// Save current mark into session
            $session->votes[$params['objectId'] . $params['objectTable']] = $params['mark'];

        // define where should forward
        $forward = isset(self::$_info['controller']->selfConfig['vote']['forward']) ?
                self::$_info['controller']->selfConfig['vote']['forward'] :
                array('list', self::$_info['controllerName'], null, array());
        // forward
        call_user_func_array(array(self::$_info['controller'], '_forward'), $forward); 
        //self::$_info['controller']->_forward($forward[0], $forward[1], $forward[2], $forward[3]);
    }
}