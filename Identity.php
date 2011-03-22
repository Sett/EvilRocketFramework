<?php
/**
 * Simple 'identity to user ID'
 *
 * TODO: get table schema
 *
 * @author Se#
 * @version 0.0.1
 */
class Evil_Identity
{
    /**
     * Encrypt or not an identity
     *
     * @var bool
     */
    public static $encrypt = false;

    /**
     * Table name without prefix
     *
     * @var string
     */
    public static $table = 'identity';

    /**
     * Table name prefix
     *
     * @var string
     */
    public static $prefix = '';

    /**
     * Record data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Try to load identity-record by identity
     *
     * @param  $identity
     */
    public function __construct($identity)
    {
        if(is_array($identity)){
            $this->_data = $identity;
            return true;
        }

        $db = Zend_Registry::get('db');
        self::$prefix = empty(self::$prefix) ? Zend_Registry::get('db-prefix') : self::$prefix;

        $select = $db->select()->from(self::$prefix . self::$table)->where('identity=?', self::_encrypt($identity));

        $row = $db->fetchRow($select);
        $row = is_object($row) ? $row->toArray() : $row;

        $this->_data = $row;
    }

    /**
     * Encrypt an identity
     *
     * @static
     * @param string $identity
     * @return string
     */
    protected static function _encrypt($identity)
    {
        if(self::$encrypt)
            return sha1($identity . md5($identity));

        return $identity;
    }

    /**
     * @description public alias for _encrypt
     * @static
     * @param  $identity
     * @return string
     */
    public static function encrypt($identity)
    {
        return self::_encrypt($identity);
    }

    /**
     * Get a record attribute
     *
     * @param string $name
     * @return string|number|null
     */
    public function __get($name)
    {
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }

    /**
     * Return list of identities for uid
     *
     * @static
     * @param string $uid
     * @param bool $object
     * @return array
     */
    public static function getList($uid, $object = false)
    {
        $db = Zend_Registry::get('db');
        self::$prefix = empty(self::$prefix) ? Zend_Registry::get('db-prefix') : self::$prefix;

        $list = $db->fetchAll($db->select()->from(self::$prefix . self::$table)->where('uid=?', $uid));

        if($object && !empty($list)){
            foreach($list as $index => $item)
                $list[$index] = new self($item);
        }

        return $list;
    }

    /**
     * Static alias for __construct()
     *
     * @static
     * @param string $identity
     * @return Evil_Identity
     */
    public static function get($identity)
    {
        return new self($identity);
    }

    public static function where($field, $selector, $value)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(self::$prefix . self::$table);

        if(is_array($field) && is_array($selector) && is_array($value))
        {
            foreach($field as $index => $name)
                $select->where($name . $selector[$index] . '?',$value[$index]);
        }
        elseif(is_string($field) && is_string($selector) && is_string($value))
            $select->where($field . $selector . '?', $value);

        return $db->fetchAll($select);
    }

    /**
     * Create an identity record in the current DB
     *
     * @static
     * @param string $identity
     * @param string $uid
     * @return Evil_Identity|null
     */
    public static function create($title, $identity, $uid)
    {
        $db = Zend_Registry::get('db');
        self::$prefix = empty(self::$prefix) ? Zend_Registry::get('db-prefix') : self::$prefix;

        $existed = new self($identity);

        if(null != $existed->uid)
            return $existed;

        if($db->insert(self::$prefix . self::$table, array('identity' => self::_encrypt($identity),
                                                           'uid'      => $uid,
                                                           'title'    => $title)))
            return new self($identity);

        return null;
    }

    public static function delete($title, $identity, $uid)
    {
        $db = Zend_Registry::get('db');
        self::$prefix = empty(self::$prefix) ? Zend_Registry::get('db-prefix') : self::$prefix;

        $existed = new self($identity);

        if(null != $existed->uid)
            return $existed;

        $where =  '(title="' . htmlspecialchars($title) . '")
                 &&(identity="' . self::encrypt($identity) . '")
                 &&(uid="' . htmlspecialchars($uid) . '")';

        if($db->delete(self::$prefix . self::$table, $where))
            return true;

        return null;
    }
}