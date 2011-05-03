<?php
/**
 * Evil_Event_Observer
 *
 * Created by JetBrains PhpStorm.
 * @author Alexander M Artamonov <art.alex.m@gmail.com>
 * @package Evil
 * @subpackage Evil Event
 * @version 0.1
 * @date 30.04.11
 * @time 17:07
 */
 
class Evil_Event_Observer 
{
    /**
     * Array of handlers
     *
     * @var array of Evil_Event_Slot
     */
    protected $_handlers = array();

    const DS = DIRECTORY_SEPARATOR;

    /**
     * Implements factory interface
     *
     * @static
     * @return Evil_Event_Observer
     */
    public static function factory() {
        return new self;
    }

    /**
     * Init observers
     *
     * @param Evil_Config $events
     * @param null $object
     * @return void
     */
    public function init(Evil_Config $events, $object = null)
    {
        if (!isset($events->defaultPath))
            $events->defaultPath = '';

        foreach ($events->observers as $name => $body) {

            foreach ($body as $handler) {
                
                if (!empty($handler->src)) {
                    $path = $handler->src;
                } else {
                    $path = $events->defaultPath;
                }

                $handlerName = $path
                               . self::DS
                               . $events->handler->prefix
                               . $handler->handler
                               . $events->handler->suffix;

                if (file_exists($handlerName)) {
                    include_once($handlerName);
                    $this->_handlers[$name][] = new Evil_Event_Slot($name, $handler->handler, $object);
                }
            }
        }
    }

    /**
     * Добавляет обработчик события
     *
     * @param  string $event
     * @param  Evil_Event_Slot $handler
     * @return int
     */
    public function addHandler($event, Evil_Event_Slot $handler)
    {
        if (!isset($this->_handlers[$event]) or !is_array($this->_handlers[$event]))
            $this->_handlers[$event] = array($handler);
        else
            $this->_handlers[$event][] = $handler;

        return count($this->_handlers);
    }

    /**
     * Выбрасывает событие
     *
     * @param string|int $event
     * @param mixed|null $args
     * @return void
     */
    public function on($event, $args = null)
    {
        $result = null;

        if (isset($this->_handlers[$event]) && is_array($this->_handlers[$event]))
        {
            $result = array();
            foreach ($this->_handlers[$event] as $handler) {
                $result[] = $handler->dispatch($args);
            }
        }

        return $result;
    }

    /**
     * Overload for use object as function
     *
     * @param  string $event
     * @param null $args
     * @return array() | null @see on()
     */
    public function __invoke($event, $args = null)
    {
        return $this->on($event, $args);
    }
}
