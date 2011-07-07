<?php
/**
 * @throws Exception
 * @description basic Rest factory
 * @author Se#
 * @version 0.0.1
 */
class Evil_Rest
{
    /**
     * @description factory
     * @static
     * @throws Exception
     * @param string $name
     * @return
     * @author Se#
     * @version 0.0.1
     */
    public static function factory($name)
    {
        $class = 'Evil_Rest_' . ucfirst($name);
        if(is_file(__DIR__ . '/Rest/' . ucfirst($name) . '.php'))
            return new $class();

        throw new Exception(' Unknown Rest class "' . $class . '" ');
    }
}