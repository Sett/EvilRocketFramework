<?php
/**
 * Simple imiDefense
 *
 * @author Se#
 */
class Evil_Defense_Imito
{
    /**
     * Table-name without prefix for containing hashes.
     * Table must has next structure: hash - string, uniq; date - timestamp()
     *
     * @var string
     */
    public static $table = 'imdef';

    /**
     * Check passed context for existing
     *
     * @static
     * @param any $context context to check
     * @param string $key private key for using in hash creating
     * @return bool
     */
    public static function check($context, $key)
    {
        $db     = Zend_Registry::get('db');// get current DB adapter
        $config = Zend_Registry::get('config');// get current configuration
        if(is_object($config))
            $config = $config->toArray();

        $table = $config['resources']['db']['prefix'] . self::$table;// create full name of a table
        $hash  = sha1(json_encode($context) . $key . $table);// create hash
        
        if(self::_isNotExist($hash, $db, $table))
            $db->insert(self::$table, array('hash' => $hash, 'date' => time()));
        else
            return false;

        return true;
    }

    /**
     * Check existing of hash
     *
     * @static
     * @param string $hash
     * @param object $db
     * @param string $table
     * @return bool
     */
    protected static function _isNotExist($hash, $db, $table)
    {
        $select  = $db->select()->from($table)->where('hash=?', $hash);
        $records = $db->fetchAll($select);

        if(empty($records))
            return true;

        return false;
    }
}