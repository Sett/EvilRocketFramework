<?php

    /**
     * @author BreathLess
     * @name Evil_Object_Fixed
     * @description: Fixed implementation of ORM, classical table interface  
     * @package Evil
     * @subpackage ORM
     * @version 0.1
     * @date 24.10.10
     * @time 12:43
     */

    class Evil_Object_Fixed implements Evil_Object_Interface
    {
        protected $_loaded = false;

        /**
         * @var <string>
         * Type of object, entity name
         */

        protected $_type     = null;
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
        private   $_data     = array ();

        /**
         * List of fixed table keys
         * @var <array>
         */
        private   $_fixedschema = array();
        private   $_dnodes      = array();

        private   $_fixed   = null;

        public function __construct ($type, $id = null)
        {
           $this->_type = $type;

           $prefix = Zend_Registry::get('db-prefix');

           if (substr($type, strlen($type)-1) != 's')
               $postfix = 's';
           else
               $postfix = '';

           $this->_fixed = new Zend_Db_Table($prefix.$type.$postfix);

           $info = $this->_fixed->info();
           $this->_fixedschema = $info['cols'];

           if (null !== $id)
                $this->load($id);

           return true;
        }

        public function data()
        {
        	foreach ($this->_dnodes as $key => $fn)
                $this->_getDValue($key);

        	return $this->_data;
        }

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

        public function where ($key, $selector, $value = null)
        {
            switch ($selector)
            {
                case '=':
                        $data = $this->_fixed->fetchRow(
                                        $this->_fixed->select()->where($key.' = ?', $value)
                                                       );
                break;

                default:
                    throw new Exception('Unknown selector '.$selector);
                break;
            }



            if (empty($data))
                return null;
            else
            {
                $data = $data->toArray();
                return $this->_id = $data['id'];
            }

        }

        public function create ($id, $data)
        {
            $this->_id = $id;

            $fixedvalues = array('id' => $id);

            foreach ($data as $key => $value)
                if (in_array($key, $this->_fixedschema))
                    $fixedvalues[$key] =  $value;
                else
                    $this->addNode ($key, $value);

            $this->_fixed->insert($fixedvalues);

            return $this;
        }

        public function erase ()
        {
            return $this;
        }

        public function addDNode ($key, $fn)
        {
            $this->_dnodes[$key] = $fn;
            return $this;
        }

        public function addNode  ($key, $value)
        {
            return $this;
        }

        public function delNode  ($key, $value = null)
        {
            return $this;
        }

        public function setNode  ($key, $value, $oldvalue = null)
        {
            $this->_fixed->update(array($key => $value), array('id = "'.$this->_id.'"'));
            return $this;
        }

        public function incNode  ($key, $increment)
        {
            if (isset($this->_data[$key]))
                return $this->setNode($key, $this->_data[$key]+$increment);
            else
                return $this->addNode($key, $increment);
        }

        private function _getDValue ($key)
        {
        	return $this->_data[$key] = $this->_dnodes[$key]($this->_data);
        }

        public function getValue  ($key, $return = 'var', $default = null)
        {
            if (isset($this->_dnodes[$key]))
                return $this->_getDValue($key);

            if (isset($this->_data[$key]))
                return $this->_data[$key];
            else
                return $default;
        }

        public function load($id = null)
        {
            if ($this->_loaded)
                return true;

            if (null !== $id)
                $this->_id = $id;

            $this->_data = array();

            // Find fixed row, and extract data from

            $data = $this->_fixed->find($this->_id)->toArray();
                        
            if (!empty($data))
            {
                $this->_data = $data[0];
            }
            else
                return false;

            $this->_loaded = true;
            return true;
        }
    }