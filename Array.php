<?php
/**
 * @description Обработчик массивов
 * @author nur, Se#
 * @version 0.0.2
 * @changeLog
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
}