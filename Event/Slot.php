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

    protected $_config = null;

    public function __construct($signal, Zend_Config $handler, $object)
    {
        $this->_object = $object;
        $this->_signal = $signal;
        $this->_config = $handler;

        $this->_init($handler);

        ///TODO make dynamic class callable
        $this->_handler = $handler->handler;
    }

    /**
     * Execute the event handler 
     *
     * @param array $args
     * @return mixed|null
     */
    public function dispatch(array $args = null)
    {
        /// Set default argument values
        if (isset($this->_config->default)) {
            $args = array_merge_recursive($this->_config->default->toArray(), $args);
        }

        if (is_callable($this->_handler)) {
            return call_user_func($this->_handler, $args, $this->_object);
        } else
            if (is_object($this->_handler))
                if (is_callable(array($this->_handler, $this->_config->method))) {
                    $method = $this->_config->method;
                    return $this->_handler->$method($args);
                }

        return null;
    }

    /**
     * Getter for $_signal
     *
     * @return string
     */
    public function getSignal()
    {
        return $this->_signal;
    }

    /**
     * Getter for $_handler
     *
     * @return mixed
     */
    public function getHandler()
    {
        return $this->_handler;
    }

    /**
     * Load handler function file
     *
     * @param  Zend_Config $handler
     * @param  Zend_Config $events
     * @return bool
     */
    protected function _init(Zend_Config $handler)
    {
        $handlerName = $handler->prefix
                       . str_replace('_', DIRECTORY_SEPARATOR, $handler->handler)
                       . $handler->suffix;

        if (!empty($handler->src))

            /// List paths from last to fist, because last is more probable
            $path = $handler->src->toArray();
            $pathIndex = count($path);

            while (--$pathIndex >= 0)
            {
                try {
                    if (include_once ($path[$pathIndex] . DIRECTORY_SEPARATOR . $handlerName)) {
                        return true;
                    }
                } catch (Exception $e) {}
            }

        return false;
    }
}
