<?php
    /**
     * @author Breathless
     */

    class Evil_Event
    {
        /**
         * @var
         */
        protected static $_queue;

        /**
         * @var null
         */
        protected static $_dynFields = null;

        /**
         * @var
         */
        protected static $_lastOperation;

        /**
         * @var
         */
        protected static $_config;

        /**
         * @static
         * @return void
         */
        public static function init()
        {
            self::$_config = Zend_Registry::get('config');

            if (null === self::$_queue)
            {
                self::$_queue = new Zend_Queue(self::$_config['evil']['event']['queue']['type'],
                                        self::$_config['evil']['event']['queue']['options']);
            }
            
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

        /**
         * @static
         * @param null $time
         * @return float
         */
        public static function eventTime($time = null)
        {
            if (null == $time)
                $time= time();
            
        	return floor($time/self::$_config['evil']['event']['time']['resolution']);
        }

        /**
         * @static
         * @param  $options
         * @return
         */
        public static function fire($options)
        {
            if (null !== self::$_dynFields)
                foreach (self::$_dynFields as $key => $fn)
                    $options[$key] = $fn($options);          

            if (!isset($options['date']))
                $options['date'] = time();

            $options['date'] = self::eventTime($options['date']);
            return self::$_queue->send($options);
        }

        /**
         * @static
         * @param int $count
         * @return array
         */
        private static function _queue ($count = 1)
        {
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

        /**
         * @static
         * @param int $count
         * @return array
         */
        public static function inject ($count = 1)
        {
            $events = array();
            $ui = 0;
            $ci = 0;

            $objects = self::_queue($count);
            
            foreach ($objects as $object)
            {
                $oid = self::_genId($object);

                if (!isset($events[$oid]))
                {
                   $events[$oid] = Evil_Structure::getObject('event');

                   if ($events[$oid]->load($oid))
                    {
                        foreach ($object as $key => $value)
                            if (!in_array($key, self::$_config['evil']['event']['slices']))
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
                        if (!in_array($key, self::$_config['evil']['event']['slices']))
                            $events[$oid]->incNode($key, $value);

                    $ui++;
                }

            }

            return array(
                'Created Objects' => $ci,
                'Updated objects' => $ui,
                'Remain ' => self::$_queue->count());

        }

        /**
         * @static
         * @param  $key
         * @param  $fn
         * @return void
         */
        public static function addDynField ($key, $fn)
        {
            self::$_dynFields[$key] = $fn;
        }
    }

    Evil_Event::init(); // Autoinitialize
