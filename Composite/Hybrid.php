<?php

    class Evil_Composite_Hybrid extends Evil_Composite_Base implements Evil_Composite_Interface
    {
        private $_fixed;
        private $_ids;
        private $_lastQuery = null;

        public function __construct ($type)
        {
            $this->_type = $type;

            $this->_fixed = new Zend_Db_Table(Evil_DB::scope2table($type,'-fixed'));
            $this->_fluid = new Zend_Db_Table(Evil_DB::scope2table($type,'-fluid'));

            $info = $this->_fixed->info ();
            $this->_fixedschema = $info['cols'];
        }
        /**
         * 
         * return items count from set
         * @author NuR
         * @return int
         */
        public function count()
        {
        	/**
        	 * 
        	 * нутро подсказывает что как то проще должно быть...
        	 * @var unknown_type
        	 */
        	
        	 $query = $this->_lastQuery->
        	 reset(Zend_Db_Select::LIMIT_COUNT)->
        	 reset(Zend_Db_Select::LIMIT_OFFSET)->
        	 reset(Zend_Db_Select::COLUMNS)->columns(new Zend_Db_Expr('count(*) as c'));
        	 $rowData = $this->_lastQuery->getTable()->fetchRow ($query);
        	 $count = $rowData->toArray();
        	 return $count['c'];
        	 
        }

      	public function erase()
        {
        	foreach ($this->_items as $item)
        	{
        		$item->erase();
        	}
        	return $this;
        }
        
        public function update($data)
        {
        	foreach ($this->_items as $item)
        	{
        		$item->update($data);
        	}
        	return $this;
        }
        public function where ($key, $selector, $value = null, $offset = 0, $count = 500, $orderBy = 'id DESC')
        {	
            switch ($selector)
            {
            	case 'multi':
            				$this->_lastQuery = $this->_fixed->select ()->from ( $this->_fixed );	
            				foreach ($value as $fieldName => $fieldParams)
            				{
            					foreach ($fieldParams as $val => $selector)
            					{
            						$this->_lastQuery->where ( $fieldName . ' ' . $selector . ' ?', $val );
            					}
		            			
								
            				}
            				
           					 $rows = $this->_fixed->fetchAll ( $this->_lastQuery );
		                     $ids = $rows->toArray ();
		
		                        foreach ($ids as $data)
		                        {
		                            $id = $data['id'];
		                            $this->_ids[] = $id;
		                            $this->_items[$id] = Evil_Structure::getObject($this->_type, $id, $data);
		                        }
            		break;
                case '*':
                		$this->_lastQuery = $this->_fixed->select()->limitPage($offset, $count)->order($orderBy) ;
                        $rows = $this->_fixed->fetchAll($this->_lastQuery); //count and offset only for selector==*
                        $ids = $rows->toArray ();

                        foreach ($ids as $data)
                        {
                            $id = $data['id'];
                            $this->_ids[] = $id;
                            $this->_items[$id] = Evil_Structure::getObject($this->_type, $id, $data);
                        }

                break;
                
                case '=':
                case '>':
                case '<':
                case '>=':
                case '<=':
                    if (in_array ($key, $this->_fixedschema)) {
                    	
                    	$this->_lastQuery = $this->_fixed->select ()->limitPage($offset, $count)->from ( $this->_fixed)->where ( $key . ' ' . $selector . ' ?', $value );
						$rows = $this->_fixed->fetchAll ( $this->_lastQuery );
                        $ids = $rows->toArray ();

                        foreach ($ids as $data)
                        {
                            $id = $data['id'];
                            $this->_ids[] = $id;
                            $this->_items[$id] = Evil_Structure::getObject($this->_type, $id, $data);
                        }
                    }
                    else
                    {
                       $this->_lastQuery =    $this->_fluid
                                ->select ()
                                ->from (
                                $this->_fluid,
                                array('i')
                            )
                                ->where ('k = ?', $key)
                                ->where ('v '. $selector .' ?', $value);
                        $rows = $this->_fluid->fetchAll ( $this->_lastQuery );

                        $ids = $rows->toArray ();
                        foreach ($ids as $data)
                        {
                            $id = $data['i'];
                            $this->_ids[] = $id;
                            $this->_items[$id] = Evil_Structure::getObject($this->_type, $id, $data);
                        }
                    }

                    break;

                case ':':
                    foreach ($value as &$cvalue)
                        $cvalue = '"' . $cvalue . '"';

                    if (in_array ($key, $this->_fixedschema))
                    {
                    	$this->_lastQuery = $this->_fixed
                                ->select ()
                                ->from (
                                $this->_fixed,
                                array('id')
                            )->where ($key . ' IN (' . implode (',', $value) . ')');
                        $rows = $this->_fixed->fetchAll ($this->_lastQuery );


                        $ids = $rows->toArray ();

                        foreach ($ids as $id)
                        {
                            $this->_ids[] = $id['id'];
                        }
                    }
                    else
                    {
                    	$this->_lastQuery = $this->_fluid
                                ->select ()
                                ->from (
                                $this->_fluid,
                                array('i')
                            )
                                ->where ('k = ?', $key)
                                ->where ('v IN ("' . implode (',', $value) . '")');
                        $rows = $this->_fluid->fetchAll ($this->_lastQuery );

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
        
        public function truncate()
        {
        	$this->_fixed->delete();
        	$this->_fluid->delete();
        	return $this;
        }

        public function clear()
        {
            $this->_ids = array();
            $this->_items = array();
        }
    }