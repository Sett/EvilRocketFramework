<?php
/**
 * @description Обработчик массивов
 * @author nur, Se#
 * @version 0.0.4
 * @changeLog
 * 0.0.4 added methods convert and convertLevel2LRKeys
 * 0.0.3 added methods byField and filter
 * 0.0.2 added methods jut, prepareData
 */
class Evil_Array
{
    /**
     * @description contain operated nodes
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    public static $operated = array();

    /**
     * @description left key, for calculating lk, rk by level
     * @var int
     * @author Se#
     * @version 0.0.1
     */
    protected static $_lk = 0;

    /**
     * @description right key, for calculating lk, rk by level
     * @var int
     * @author Se#
     * @version 0.0.1
     */
	protected static $_rk = 0;
    
    /**
     * Доставалка из многомерных массивов
     * удобно в случае использования ини конфигов
     * @param string $path
     * @param array $inputArray
     * @example   
     * $config = Zend_Registry::get('config');
     * Evil_Array::get('file.upload.maxfilesize', $config);
     */
    public static function get ($path, array $inputArray,$default = null, $detelminer = '.')
    {
        // TODO: $arrayOfPath = is_array($path) ? $path : explode($delimeter, $path);
        $arrayOfPath = explode($detelminer, $path);
        $value = $inputArray;
        foreach ($arrayOfPath as $index)
        {
            if(is_array($value) && isset($value[$index]))
            {
                $value = $value[$index];
            }
             else 
              return $default;
        }
        return $value;
       
    }

    /**
     * @description reformat src-array to the by-level-array.
     * Ex:
     * src = array(
     *  0 => array('id' => 1, 'level' => 1),
     *  1 => array('id' => 2, 'level' => 2),
     *  2 => array('id' => 4, 'level' => 1),
     *  3 => array('id' => 3. 'level' => 2)
     * )
     *
     * result:
     * array(
     *  0 => array(
     *      'id' => 1,
     *      'children' => array(
     *          array(
     *              'id' => 2,
     *              'children' => array()
     *          )
     *      )
     *  ),
     * 
     *  1 => array(
     *      'id' => 4,
     *      'children' => array(
     *          array(
     *              'id' => 3,
     *              'children' => array()
     *          )
     *      )
     *  )
     * )
     * @static
     * @param array $src
     * @param array $needed
     * @param int $cl current level
     * @param int $index
     * @param string $lf level field
     * @return array
     * @author Se#
     * @version 0.0.2
     */
    public static function jit(array $src, array $need, $cl = 0, $i = 0, $lf = 'level', $cf = 'children')
    {
        $result = array();
        $count  = count($src);
        for($i; $i < $count; $i++)
        {
            if(isset(self::$operated[$i]))// do not operate a row second time
                continue;

            if($src[$i][$lf] > $cl)// child
            {
                self::$operated[$i] = true;// mark the current row
                $data = self::prepareData($src[$i], $need);// extract needed fields
                $data[$cf] = self::jit($src, $need, $src[$i][$lf], $i+1);// get children
                $result[] = $data;// save node
                continue;
            }
            break;// if the same or next branch
        }
        return $result;
    }

    /**
     * @description extract $needed fields from the $src array
     * @static
     * @param array $src source array
     * @param array $need needed fields array(field1, field2, ...)
     * @param array $r result
     * @param bool $bf by field
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public static function prepareData(array $src, array $need, $r = array(), $bf = true)
    {
        foreach($need as $field)
        {
            $value = $r[$field] = isset($src[$field]) ? $src[$field] : '';

            if($bf)// if by field
                $r[$field] = $value;
            else
                $r[] = $value;
        }

        return $r;
    }

    /**
     * @description make a new array($field => whole cell| data[$perField]);
     * Example:
     * $users = Array(
     *  0 => array('id' => 2, 'login' => 'user1'),
     *  1 => array('id' => 3, 'login' => 'userN')
     * )
     * $result = Evil_Array::byField($users, null, 'id', 'login');
     *
     * $result :
     * array(
     *  2 => 'user1',
     *  3 => 'userN'
     * )
     * 
     * @static
     * @param array|string $dataOrName array for operating or a table name (will fetch all)
     * @param object|null $db
     * @param string $field
     * @param bool $perField
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public static function byField($dataOrName = array(), $db = null, $field = 'id', $perField = false)
    {
        $db = $db ? $db : Zend_Registry::get('db');
        if(is_string($dataOrName))// name
            $data = $db->fetchAll($db->select()->from(Evil_DB::scope2table($dataOrName)));
        else
            $data = $dataOrName;

        $result = array();
        $count = count($data);
        for($i = 0; $i < $count; $i++)
        {
            $id = isset($data[$i][$field]) ? $data[$i][$field] : 0;
            $result[$id] = $perField && isset($data[$i][$perField]) ? $data[$i][$perField] : $data[$i];
        }

        return $result;
    }

    /**
     * @description summary for Evil_Array filters.
     * Example:
     * $result = Evil_Array::filter('byField', array($users, null, 'id', 'login'));//see byField method
     * @static
     * @param string $filterName
     * @param array $args
     * @return mixed|null
     * @author Se#
     * @version 0.0.1
     */
    public static function filter($filterName,array $args)
    {
        if(is_string($filterName) && method_exists('Evil_Array', $filterName))
            return call_user_func_array(array('Evil_Array', $filterName), $args);

        return null;
    }

    /**
     * @description factory for different types of converting.
     * Ex.: Evil_Array::convert('Level2LRKeys', array($src, $needed, $curLevel, $index, $levelField));
     * @static
     * @param string $type
     * @param array $args
     * @return mixed|null
     * @author Se#
     * @version 0.0.1
     */
    public static function convert($type, array $args)
    {
        if(!is_string($type))
            return null;
        
        $method = 'convert' . $type;

        return method_exists('Evil_Array', $method) ? call_user_func_array(array('Evil_Array', $method), $args) : null;
    }

    /**
     * @static
     * @param array $src source array
     * @param array $needed needed fields
     * @param int $curLevel
     * @param int $index
     * @param string $levelField where from level should be extracted
     * @return array
     * @author Se#
     * @version 0.0.1
     */
	public static function convertLevel2LRKeys(array $src, array $needed, $curLevel = 0, $index = 0, $levelField = 'level')
    {
        $result = array();
        $count  = count($src);

        for($i = $index; $i < $count; $i++)
        {
            if(isset(self::$operated[$i]))// do not operate a row second time
                continue;

            if($src[$i][$levelField] > $curLevel)// child
            {
                self::$operated[$i] = true;
                //$prepared = self::prepareData($src[$i], $needed);
				self::$_lk++;
				self::$_rk = self::$_lk+1;
				$result[$i] = $src[$i];
				$result[$i]['lk'] = self::$_lk;
				$result[$i]['rk'] = self::$_rk;

                // get children for a child
                $result += self::convertLevel2LRKeys($src, $needed, $src[$i][$levelField], $i+1);
				if(isset($result[$i+1]))
				{
					self::$_rk++;
					$result[$i]['rk'] = self::$_rk;
					self::$_lk = self::$_rk;
				}
				else
					self::$_lk++;

                continue;
            }
            elseif($src[$i][$levelField] < $curLevel)// new branch
                return $result;
            elseif($i)// the same branch
                return $result;
        }

        return $result;
    }
}