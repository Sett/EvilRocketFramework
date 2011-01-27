<?php
    /**
     * @author BreathLess
     * @name Evil_Object_H2D
     * @description: 2D-3D implementation of ORM, classical table interface + fluid tables
     * @package Evil
     * @subpackage ORM
     * @version 0.1
     * @date 24.10.10
     * @time 12:43
     */

    class Evil_Object_Hybrid extends Evil_Object_Base implements Evil_Object_Interface
    {
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

        /**
         * @var null|Zend_Db_Table
         */
        private   $_fixed   = null;

        /**
         * @var null|Zend_Db_Table
         */
        private   $_fluid   = null;

        /**
         * @param  $type
         * @param null $id
         * @param null $data
         *
         */
        public function __construct ($type, $id = null, $data = null)
        {
           $this->type = $type;

           $this->_fixed = new Zend_Db_Table(Evil_DB::scope2table($type,'-fixed'));
           $this->_fluid = new Zend_Db_Table(Evil_DB::scope2table($type,'-fluid'));

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

        /**
         * @param  $id
         * @param  $data
         * @return Evil_Object_Hybrid
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
         * @return Evil_Object_Hybrid
         */
        public function erase ()
        {
            return $this;
        }

        /**
         * @param  $key
         * @param  $value
         * @return Evil_Object_Hybrid
         */
        public function addNode  ($key, $value)
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

        /**
         * @param  $key
         * @param null $value
         * @return Evil_Object_Hybrid
         */
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

        /**
         * @param  $key
         * @param  $value
         * @param null $oldvalue
         * @return Evil_Object_Hybrid
         */
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

        /**
         * @param  $key
         * @param  $increment
         * @return Evil_Object_Hybrid
         */
        public function incNode  ($key, $increment)
        {
            if (isset($this->_data[$key]))
                return $this->setNode($key, $this->_data[$key][0]+$increment);
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
    }