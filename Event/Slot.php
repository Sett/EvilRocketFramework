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
    protected $_object = null;

    protected $_handler = null;

    public function __construct($handler, $object)
    {
        $this->_object = $object;
        $this->_handler = $handler;
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
}
