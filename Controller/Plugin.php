<?php
/**
 * @description load Evil/Controller/ if there is no such in the base controller directory
 * @author Se#
 * @version 0.0.2
 */
class Evil_Controller_Plugin extends Zend_Controller_Plugin_Abstract
{
    /**
     * @description add Evil/Controller/ directory to the Front Controller
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @author Se#
     * @version 0.0.2
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $controllerName = $request->getControllerName();
        // check if there is asked controller in base controller directory
        if(!is_file(APPLICATION_PATH . '/controllers/' . ucfirst($controllerName) . 'Controller.php'))
        {// check if there is a controller in the Evil/Controller/ directory
            if(!is_file(__DIR__ . '/' . ucfirst($controllerName) . 'Controller.php'))
            {
                $tables = Zend_Registry::get('db')->listTables();
                if(in_array(Evil_DB::scope2table($controllerName), $tables))
                {
                    $path = APPLICATION_PATH . '/configs/evil/controller/restricted.json';
                    $restricted = is_file($path) ? json_decode(file_get_contents($path), true) : array();

                    if(in_array($controllerName, $restricted))
                        return $request;

                    $this->_newClass($controllerName);
                }
                else
                    return $request;
            }

            Zend_Controller_Front::getInstance()->addControllerDirectory(__DIR__);
        }
    }

    /**
     * @description create a new file for the controller
     * @param string $controllerName
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected function _newClass($controllerName)
    {
        $text = "<?php \n /**
 * @description Empty controller
 * @author Auto-created
 * @version 0.0.1
 */\n class ". ucfirst($controllerName) . 'Controller' . " extends Evil_Controller {} \n";

        $f = fopen(__DIR__ . '/' . ucfirst($controllerName) . 'Controller.php', "w+t");
        fputs($f, $text);
        fclose($f);
    }
}