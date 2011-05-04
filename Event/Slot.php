<?php
/**
 * Evil_Event_Slot
 *
 * Created by JetBrains PhpStorm.
 * @author Alexander M Artamonov <a2m@ruimperium.ru>
 * @type ?
 * @package OpenCity
 * @subpackage Core
 * @version 0.9
 * @date 03.05.11
 * @time 12:09
 */
 
class Evil_Event_Slot
{
    /**
     * Caller
     *
     * @var object | null
     */
    protected $_object = null;

    /**
     * Handler for event
     *
     * @var null
     */
    protected $_handler = null;

    protected $_signal = '';

    public function __construct($signal, $handler, $object)
    {
        $this->_object = $object;
        $this->_signal = $signal;

        try {

            if (class_exists($handler->handler)) {

                $this->_handler = isset($handler->handler->constructor)
                        ? new $handler->handler($handler->handler->constructor)
                        : new $handler->handler;
            }

        } catch (Exception $e) {
            if (is_callable($handler->handler) || $this->_initFunction($handler)) {
                $this->_handler = $handler->handler;
            }
        }
    }

    /**
     * Execute the event handler 
     *
     * @param array $args
     * @return mixed|null
     */
    public function dispatch(array $args = null)
    {
        if (is_callable($this->_handler)) {
            return call_user_func($this->_handler, $args, $this->_object);
        }

        return null;
    }

    /**
     * Overload for get function
     *
     * @param  string $name
     * @return mixed | null
     */
    public function __get($name)
    {
        if (isset($this->$name))
            return $this->$name;

        return null;
    }

    /**
     * Load handler function file
     *
     * @param  Zend_Config $handler
     * @param  Zend_Config $events
     * @return bool
     */
    protected function _initFunction(Zend_Config $handler)
    {
        $handlerName = $handler->prefix . $handler->handler . $handler->suffix;

        var_dump($handlerName);

        try {
            if (Zend_Loader::loadFile($handlerName, $handler->src)) {
                return true;
            }
        } catch (Exception $e) {}

        return false;
    }
}
