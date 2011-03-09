<?php

    class Evil_Composite_Hybrid extends Evil_Composite_Base implements Evil_Composite_Interface
    {
        private $_fixed;
        private $_ids;

        public function __construct ($type)
        {
            $this->_type = $type;

            $this->_fixed = new Zend_Db_Table(Evil_DB::scope2table($type,'-fixed'));
            $this->_fluid = new Zend_Db_Table(Evil_DB::scope2table($type,'-fluid'));

            $info = $this->_fixed->info ();
            $this->_fixedschema = $info['cols'];
        }

        public function where ($key, $selector, $value = null, $offset = null, $count = null, $mode = 'new')
        {
            switch ($selector)
            {
                case '*':
                        $rows = $this->_fixed->fetchAll(null, null, $count, $offset); //count and offset only for selector==*

                        $ids = $rows->toArray ();

                        foreach ($ids as $id)
                        {
                            $id = $id['id'];
                            $this->_ids[] = $id;
                            $this->_items[$id] = new Evil_Object_Hybrid($this->_type, $id);
                        }

                break;
                
                case '=':
                case '>':
                case '<':
                case '>=':
                case '<=':
                    if (in_array ($key, $this->_fixedschema)) {
                        $rows = $this->_fixed->fetchAll (
                            $this->_fixed
                                ->select ()
                                ->from (
                                $this->_fixed,
                                array('id')
                            )
                                ->where ($key . ' '.$selector.' ?', $value));

                        $ids = $rows->toArray ();

                        foreach ($ids as $id)
                        {
                            $id = $id['id'];
                            $this->_ids[] = $id;
                            $this->_items[$id] = new Evil_Object_Hybrid($this->_type, $id);
                        }
                    }
                    else
                    {
                       
                        $rows = $this->_fluid->fetchAll (
                            $this->_fluid
                                ->select ()
                                ->from (
                                $this->_fluid,
                                array('i')
                            )
                                ->where ('k '. $selector .' ?', $key)
                                ->where ('v '. $selector .' ?', $value));

                        $ids = $rows->toArray ();
                        foreach ($ids as $id)
                        {
                            $id = $id['i'];
                            $this->_ids[] = $id;
                            $this->_items[$id] = new Evil_Object_Hybrid($this->_type, $id);
                        }
                    }

                    break;

                case ':':
                    foreach ($value as &$cvalue)
                        $cvalue = '"' . $cvalue . '"';

                    if (in_array ($key, $this->_fixedschema))
                    {
                        $rows = $this->_fixed->fetchAll (
                            $this->_fixed
                                ->select ()
                                ->from (
                                $this->_fixed,
                                array('id')
                            )->where ($key . ' IN (' . implode (',', $value) . ')'));


                        $ids = $rows->toArray ();

                        foreach ($ids as $id)
                        {
                            $this->_ids[] = $id['id'];
                        }
                    }
                    else
                    {
                        $rows = $this->_fluid->fetchAll (
                            $this->_fluid
                                ->select ()
                                ->from (
                                $this->_fluid,
                                array('i')
                            )
                                ->where ('k = ?', $key)
                                ->where ('v IN ("' . implode (',', $value) . '")'));

                        $ids = $rows->toArray ();

                        foreach ($ids as $id)
                            $this->_ids[] = $id['i'];
                    }

                    break;
                    
                    default:
                        throw new Exception('Unknown selector');
                        break;
            }
            return $this;
        }

        public function data ($key = null)
        {
            $output = array();

            if ($key == null)
                foreach ($this->_items as $id => $item)
                    $output[$id] = $item->data ();
            else
                foreach ($this->_items as $id => $item)
                    $output[$id] = $item->getValue ($key);

            return $output;
        }

        public function load($ids = null)
        {
            $data = array();
            if ($ids !== null)
                $this->_ids = $ids;

            $this->_items = array();
            $this->_data = array();

            $ids = (array) $this->_ids;
            
            foreach($ids as &$id) // Se#: WTF?
                $id = '"'.$id.'"';// old-school
            //  die('`id` IN (' . implode (',', $ids) . ')');  
               
            $fixedRows = $this->_fixed->fetchAll (
                            $this->_fixed
                                ->select ()
                                ->from ($this->_fixed)
                                ->where ('`id` IN (' . implode (',', $ids) . ')'));

            $fluidRows = $this->_fluid->fetchAll (
                            $this->_fluid
                                ->select ()
                                ->from ($this->_fluid)
                                ->where ('`i` IN (' . implode (',', $ids) . ')'));


            $fluidRows = $fluidRows->toArray();
            
            $fixedRows = $fixedRows->toArray();

            foreach ($fluidRows as $row)
                $data[$row['i']][$row['k']] = $row['v'];

            foreach($fixedRows as $row)
                $data[$row['id']] = array_merge($data[$row['id']], $row);

            foreach ($data as $id => $data)
                $this->_items[$id] = Evil_Structure::getObject($this->_type, $id, $data);
            
        }

        public function clear()
        {
            $this->_ids = array();
            $this->_items = array();
        }
    }