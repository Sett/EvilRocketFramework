<?php
    /**
     * @author Breathless
     */

    class Evil_Event
    {
        protected static $_queue;
        protected static $_dynFields = null;
        protected static $_lastOperation;

        protected static $_config;

        public static function init()
        {
            self::$_config = Zend_Registry::get('config');

            if (isset(self::$_config['evil']['event']['dynFields']))
                foreach (self::$_config['evil']['event']['dynFields'] as $field)
                    include Evil_Locator::FF('functions/event/dynfields/'.$field.'.php');
        }

        /**
         * Generate id
         *
         * @static
         * @param  $data
         * @return string
         */
        private static function _genId ($data)
        {
            $h = '';
            foreach (self::$_config['evil']['event']['slices'] as $slice)
                if (isset($data[$slice]))
                    $h.= $data[$slice];

            return sha1($h);
        }

        private static function _eventTime($time)
        {
        	return floor($time/self::$_config['evil']['event']['time']['resolution']);
        }

        public static function _queueInit ()
        {
            if (null === self::$_queue)
            {
                // FIXME
                self::$_queue = new Zend_Queue(
                    'Redis',
                    array(
                            'servers' => array(
                                array('host'     => '127.0.0.1',
                                      'port'     => 6379
                                )),
                            'adapterNamespace' => 'Rediska_Zend_Queue_Adapter',
                            'name' => 'Events',
                            'driverOptions' => array('namespace' => 'Event_'))
                    	);                
            }

            return self::$_queue;
        }

        public static function fire($options)
        {
            self::_queueInit();
            
            if (null !== self::$_dynFields)
                foreach (self::$_dynFields as $key => $fn)
                    $options[$key] = $fn($options);          


            if (!isset($options['date']))
                $options['date'] = time();

            $options['date'] = self::_eventTime($options['date']);
            return self::$_queue->send($options);
        }

        private static function _queue ($count = 1)
        {
            self::_queueInit();

            $queueKey = self::$_config['evil']['event']['slices']['default'];
            
            $events = self::$_queue->receive($count);
            $compilated = array();

            foreach ($events as $event)
            {
                $rid = self::_genId($event->body);

                if (isset($compilated[$rid]))
                {
                    if (isset($compilated[$rid][$event->body[$queueKey]]))
                        $compilated[$rid][$event->body[$queueKey]]++;
                    else
                        $compilated[$rid][$event->body[$queueKey]] = 1;
                }
                else
                {
                    $compilated[$rid] = array($event->body[$queueKey] => 1);
                    
                    foreach(self::$_config['evil']['event']['slices'] as $slice)
                        if ($slice != self::$_config['evil']['event']['slices']['default'])
                            $compilated[$rid][$slice] = $event->body[$slice];
                }
            }

            return $compilated;
        }

        public static function inject ($count = 1)
        {
            $objects = self::_queue($count);

            $events = array();

            $ui = $ci = 0;

            foreach ($objects as $object)
            {
                $oid = self::_genId($object);

                if (!isset($events[$oid]))
                {
                   $events[$oid] = Evil_Structure::getObject('event');

                   if ($events[$oid]->load($oid))
                    {
                        foreach ($object as $key => $value)
                            if (in_array($key, self::$_config['evil']['event']['types']))
                                $events[$oid]->incNode($key, $value);

                        $ui++; // Updated Counter
                    }
                    else
                    {
                        $events[$oid]->create($oid, $object);

                        $ci++; // Created Counter
                    }
                }
                else
                {
                    foreach ($object as $key => $value)
                            $events[$oid]->incNode($key, $value);

                    $ui++;
                }

            }

            return array('Created Objects'=>$ci, 'Updated objects' => $ui, 'Remain ' => self::$_queue->count());

        }

        public static function addDynField ($key, $fn)
        {
            self::$_dynFields[$key] = $fn;
        }
    }

    Evil_Event::init(); // Autoinitialize