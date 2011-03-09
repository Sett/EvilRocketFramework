<?php
/**
 * 
 * Посмотровщик массивов
 * @author nur
 */
class Evil_Array
{
    /**
     * 
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
            if(is_array($value))
            {
                $value = $value[$index];
            }
             else 
              return $default;
        }
        return $value;
       
    }
}