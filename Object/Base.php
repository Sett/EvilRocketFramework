<?php

    /**
     * @author BreathLess
     * @type Class
     * @description: Abstract Class Evil Object
     * @package Evil
     * @subpackage ORM
     * @version 0.1
     * @date 20.11.10
     * @time 13:39
     */

    abstract class Evil_Object_Base
    {
        /**
         * @var array
         */
        protected   $_dnodes      = array();

        /**
         * @var bool
         */
        protected $_loaded = false;

        /**
         * @var <string>
         * Type of object, entity name
         */
        protected $type     = null;

        /**
         *
         * @var <string>
         * ID of object
         */
        protected $_id       = null;

        /**
         *
         * @var <array>
         * Internal data cache. Populating by load() method.
         * Implements State Machine Pattern.
         */
        protected   $_data     = array ();

        /**
         * @return array
         */
        public function data()
        {
        	foreach ($this->_dnodes as $key => $fn)
                $this->_getDValue($key);

        	return $this->_data;
        }

        /**
         * @return void
         */
        public function reset()
        {

        }

         /**
         *
         * @param <string> $id
         * @return ObjectH3D
         *
         * Setter for ID
         */

        public function setId ($id)
        {
            $this->_id = $id;

            return $this;
        }

        /**
         *
         * @return <string>
         * Getter for ID
         */

        public function getId ()
        {
            return $this->_id;
        }

        /**
         * @param  $key
         * @param  $fn
         * @return Evil_Object_Base
         */
        public function addDNode ($key, $fn)
        {
            $this->_dnodes[$key] = $fn;
            return $this;
        }

        /**
         * @param  $key
         * @return
         */
        protected function _getDValue ($key)
        {
        	return $this->_data[$key] = $this->_dnodes[$key]($this->_data);
        }

        /**
         * @param  $key
         * @param string $return
         * @param null $default
         * @return array|null
         */
        public function getValue  ($key, $return = 'var', $default = null)
        {
            if ($return == 'array' and $default == null)
                $default = array();

            if (isset($this->_dnodes[$key]))
                return $this->_getDValue($key);

            if (isset($this->_data[$key]))
            {
                if ($return == 'var' and is_array($this->_data[$key]))
                    return $this->_data[$key][0];
                else
                    return $this->_data[$key];
            }
            else
                return $default;
        }
    }