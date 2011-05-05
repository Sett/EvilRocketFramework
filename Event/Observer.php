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
        foreach ($events->observers as $name => $body) {
            foreach ($body as $handler) {
                /// соединяем настройки по умолчанию с настройками текущего handler, в т.ч. src
                /// т.к. Zend_Config v1.11.4 не умеет рекурсивно мержить конфиги
                $handler = new Zend_Config(
                            array_merge_recursive($events->handler->toArray(), $handler->toArray())
                );
//                print_r($events->handler->toArray());
//                print_r($handler->toArray());
                $this->addHandler(new Evil_Event_Slot(
                    $name,
                    $handler,
                    $object
                ));
            }
        }
//        var_dump($this->_handlers);
    }

    /**
     * Добавляет обработчик события
     *
     * @param  string $event
     * @param  Evil_Event_Slot $handler
     * @return int
     */
    public function addHandler(Evil_Event_Slot $handler)
    {
        if (!isset($this->_handlers[$handler->getSignal()]) or !is_array($this->_handlers[$handler->getSignal()]))
            $this->_handlers[$handler->getSignal()] = array($handler);
        else
            $this->_handlers[$handler->getSignal()][] = $handler;

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
