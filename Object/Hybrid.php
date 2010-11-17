<?php
    /**
     * @author BreathLess
     * @name Evil_Object_H2D
     * @description: 2D implementation of ORM, classical table interface
     * @package Evil
     * @subpackage ORM
     * @version 0.1
     * @date 24.10.10
     * @time 12:43
     */

    class Evil_Object_Hybrid implements Evil_Object_Interface
    {
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
        private   $_data     = array ();

        /**
         * List of fixed table keys
         * @var <array> 
         */
        private   $_fixedschema = array();
        /**
         * List of fluid table keys
         * @var <array>
         */
        private   $_fluidschema = array();
        private   $_dnodes      = array();

        private   $_fixed   = null;
        private   $_fluid   = null;

        public function __construct ($type, $id = null, $data = null)
        {
           $this->type = $type;

           $prefix = Zend_Registry::get('db-prefix');

           if (substr($type, strlen($type)-1) != 's')
               $postfix = 's';
           else
               $postfix = '';

           $this->_fixed = new Zend_Db_Table($prefix.$type.$postfix.'-fixed');
           $this->_fluid = new Zend_Db_Table($prefix.$type.$postfix.'-fluid'); // ?

           $info = $this->_fixed->info();
           $this->_fixedschema = $info['cols'];

           if ($data !== null)
           {
               $this->_data = $data;
               $this->_loaded = true;
           }
           else
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
                    if (in_array($key, $this->_fixedschema))
                        $data = $this->_fixed->fetchRow(
                                        $this->_fixed->select()->where($key.' = ?', $value)
                                                       );
                    else
                        $data = $this->_fluid->fetchRow(
                                        $this->_fluid->select()->where('K = ?', $key)->where('V = ?', $value)
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

        public function addNode  ($key, $value = null)
        {
            if (is_array($key) and ($value === null))
                {
                    foreach ($key as $k => $v)
                        $this->addNode($k, $v);
                }
            else
                if (!in_array($key, $this->_fixedschema))
                {
                    $this->_fluid->insert(
                            array('i'=> $this->_id, 'k'=>$key,'v'=>$value)
                        );
                }

            return $this;
        }

        public function delNode  ($key, $value = null)
        {
            if (in_array($key, $this->_fluidschema) and in_array($value, $this->_data[$key]))
                {
                    if (null !== $value and !empty($value))
                        $this->_fluid->delete(
                            $this->_fluid->getAdapter()->quoteInto(array('i = ?','k = ?','v = ?'), array($this->_id, $key, $value)));
                    else
                        $this->_fluid->delete(
                            $this->_fluid->getAdapter()->quoteInto(array('i = ?','k = ?'), array($this->_id, $key)));
                }

            return $this;
        }

        public function setNode  ($key, $value, $oldvalue = null)
        {
            if (!in_array($key, $this->_fixedschema))
            {
                if (null !== $oldvalue and !empty($oldvalue))
                {
                    if (in_array($oldvalue, $this->_data[$key]))
                        $this->_fluid->update(array('k'=>$key, 'v'=>$value), array('i = "'.$this->_id.'"','k = "'.$key.'"','v = "'.$oldvalue.'"'));
                }
                else
                {
                    if (isset($this->_data[$key]))
                        $this->_fluid->update(array('k'=>$key, 'v'=>$value), array('i = "'.$this->_id.'"','k = "'.$key.'"'));
                    else
                        $this->addNode($key, $value);
                }
            }
            else
                $this->_fixed->update(array($key => $value), array('id = "'.$this->_id.'"'));

            return $this;
        }

        public function incNode  ($key, $increment)
        {
            if (isset($this->_data[$key]))
                return $this->setNode($key, $this->_data[$key][0]+$increment);
            else
                return $this->addNode($key, $increment);
        }

        private function _getDValue ($key)
        {
        	return $this->_data[$key] = $this->_dnodes[$key]($this->_data);
        }

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

                $fluidrows = $this->_fluid->fetchAll('i = "'.$this->_id.'"')->toArray();

                    foreach ($fluidrows as $row)
                    {
                        unset ($row['u']);
                        //$this->_fluidnodes[$row['k']] = $row['k'];
                        $this->_data[$row['k']][] = $row['v'];
                    }
            }
            else
                return false;

            $this->_loaded = true;
            return true;
        }

        // Абстрактный класс?

        public function __get ($name)
        {
            return $this->getValue($name);
        }

        public function __isset ($name)
        {
            return isset($this->_data[$name]);
        }

        public function __set ($name, $value)
        {
            return $this->setNode($name, $value);
        }

        public function __toString ()
        {
            return $this->_type.'::'.$this->_id;
        }
    }