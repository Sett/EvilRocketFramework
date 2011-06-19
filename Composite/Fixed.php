<?php

    class Evil_Composite_Fixed extends Evil_Composite_Base implements Evil_Composite_Interface
    {
        private $_fixed;

        public function __construct ($type)
        {
            $this->_type = $type;
            $this->_fixed = new Zend_Db_Table(Evil_DB::scope2table($type));
            $info = $this->_fixed->info();
            $this->_fixedschema = $info['cols'];
        }
        
        public function truncate()
        {
        	$this->_fixed->delete();
        	return $this;
        }

        public function where ($key, $selector, $value = null,  $mode = 'new')
        {
            switch ($selector)
            {
                case '=':
	            case '<':
	            case '>':
	            case '<=':
	            case '>=':
	            case '!=':
                	
                    if (in_array ($key, $this->_fixedschema)) {
                        $rows = $this->_fixed->fetchAll (
                            $this->_fixed
                                ->select ()
                                ->from (
                                $this->_fixed,
                                array('id')
                            )
                                ->where ($key . ' ' . $selector . ' ?', $value));

                        $ids = $rows->toArray ();
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
                            )
                                ->where ($key . ' IN (' . implode (',', $value) . ')'));
                    }
                    break;

                    case '@':
                        switch ($key)
                        {
                            case 'all':
                                $rows = $this->_fixed->fetchAll();
                            break;
                        }
                    break;
                    
                    default:
                    		throw new Evil_Exception('Unknown selector '  .$selector);
                    	break;
            }

            $ids = $rows->toArray ();
            foreach ($ids as $id)
            {
                $id = $id['id'];
                $this->_items[$id] = new Evil_Object_Fixed($this->_type, $id);
            }
            
            return $this;
        }

        public function load($ids)
        {
            foreach ($ids as $id)
            {
                $this->_items[$id] = new Evil_Object_Fixed($this->_type, $id);
            }
        }
    }