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

    class Evil_Object_Fixed extends Evil_Object_Base implements Evil_Object_Interface
    {
        /**
         * List of fixed table keys
         * @var <array>
         */
        private   $_fixedschema = array();

        /**
         * @var null|Zend_Db_Table
         */
        private   $_fixed   = null;

        /**
         * @param  $type
         * @param null $id
         * 
         */
        public function __construct ($type, $id = null)
        {
           $this->_type = $type;

           $this->_fixed = new Zend_Db_Table(Evil_DB::scope2table($type));
           
           $info = $this->_fixed->info();
           $this->_fixedschema = $info['cols'];

           if (null !== $id)
                $this->load($id);

           return true;
        }

        /**
         * @throws Exception
         * @param  $key
         * @param  $selector
         * @param null $value
         * @return null
         */
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

        /**
         * @param  $id
         * @param  $data
         * @return Evil_Object_Fixed
         */
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

        /**
         * @return Evil_Object_Fixed
         */
        public function erase ()
        {
            return $this;
        }

        /**
         * @param  $key
         * @param  $value
         * @return Evil_Object_Fixed
         */
        public function addNode  ($key, $value)
        {
            return $this;
        }

        /**
         * @param  $key
         * @param null $value
         * @return Evil_Object_Fixed
         */
        public function delNode  ($key, $value = null)
        {
            return $this;
        }

        /**
         * @param  $key
         * @param  $value
         * @param null $oldvalue
         * @return Evil_Object_Fixed
         */
        public function setNode  ($key, $value, $oldvalue = null)
        {
            $this->_fixed->update(array($key => $value), array('id = "'.$this->_id.'"'));
            return $this;
        }

        /**
         * @param  $key
         * @param  $increment
         * @return Evil_Object_Fixed
         */
        public function incNode  ($key, $increment)
        {
            if (isset($this->_data[$key]))
                return $this->setNode($key, $this->_data[$key]+$increment);
            else
                return $this->addNode($key, $increment);
        }

        /**
         * @param null $id
         * @return bool
         */
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