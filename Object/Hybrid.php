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
         * @description Dynamic User-Selectors
         * @author Se#
         * @version 0.0.1
         * @var array
         */
        protected $_selectors = array(
            // 'example' - selector
            'example' => array('Evil_Object_Hybrid',// class where selector realization is placed
                               'where')// method, will get (array func_get_args(), object $fixed, object $fluid)
        );

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

        private   $_fixed   = null;
        private   $_fluid   = null;

        public function __construct ($type, $id = null, $data = null, $selectors = array())
        {
           $this->type = $type;
           $this->_selectors = $selectors;// dynamic personal selectors

           $this->_fixed = new Zend_Db_Table(Evil_DB::scope2table($type,'-fixed'));
           $this->_fluid = new Zend_Db_Table(Evil_DB::scope2table($type,'-fluid'));

           $info = $this->_fixed->info();
           $this->_info = $info;
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
                    // Check for Dynamic User-Selectors
                    $sls = is_array($this->_selectors) ? $this->_selectors : array();// for shorthand
                    if(isset($sls[$selector]) && is_array($sls[$selector]) && (count($sls[$selector]) == 2))
                    {// check correction of the passing information
                        if(method_exists($sls[$selector][0], $sls[$selector][1]))
                        {
                            $data = call_user_func_array($sls[$selector],
                                // we can pass more than 3 parameters
                                                         array(func_get_args(), $this->_fixed, $this->_fluid));
                        }
                    }
                    else
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
            $nodes = array();
            $fixedvalues = array('id' => $id);

            foreach ($data as $key => $value)
                if (in_array($key, $this->_fixedschema))
                    $fixedvalues[$key] =  $value;
                else
                    $nodes[$key] = $value;
                   

            $id = $this->_fixed->insert($fixedvalues);
            $this->setId($id);
            if(count($nodes) > 0)
                $this->addNode ($nodes);
            return $this;
        }

        public function erase ()
        {
        	$this->_fluid->delete($this->_fluid->getAdapter()->quoteInto(array('i = ?'), array($this->_id)));
			$this->_fixed->delete($this->_fluid->getAdapter()->quoteInto(array('id = ?'), array($this->_id)));
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
            {
                if (!in_array($key, $this->_fixedschema))
                {
                    $this->_fluid->insert(
                            array('i'=> $this->_id, 'k'=>$key,'v'=>$value)
                        );
                }
                $this->_data[$key] = $value;
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
				$this->_fluid->delete ( $this->_fluid->getAdapter ()->quoteInto ( array ('i = ?', 'k = ?' ), array ($this->_id, $key ) ) );
		}
		
		return $this;
	}
	
	public function setNode($key, $value, $oldvalue = null) {
		if (! in_array ( $key, $this->_fixedschema )) {
			if (null !== $oldvalue and ! empty ( $oldvalue )) {
				if (in_array ( $oldvalue, $this->_data [$key] ))
					$this->_fluid->update ( array ('k' => $key, 'v' => $value ), array ('i = ? ' => $this->_id, 'k = ? ' => $key, 'v = ?' => $key ) );
			} else {
				if (isset ( $this->_data [$key] ))
					$this->_fluid->update ( array ('k' => $key, 'v' => $value ), array ('i = ?' => $this->_id, 'k = ?' => $key ) );
				else
					$this->addNode ( $key, $value );
			}
		} else
            {
            	if($value == $oldvalue)
            	{
            		return $this;
            	}
                $this->_fixed->update(array($key => $value), array('id = "'.$this->_id.'"'));
            }
            return $this;
        }

        /**
         * 
         * Обновление данных
         * @param array $data
         * @author NuR
         */
        public function update($data)
        {
            foreach ($data as $key => $value)
            {
                $this->setNode($key, $value, $this->getValue($key) );
            }
        }
        public function incNode  ($key, $increment)
        {
            if (isset($this->_data[$key]))
                return $this->setNode($key, $this->_data[$key][0]+$increment);
            else
                return $this->addNode($key, $increment);
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

                $fluidrows = $this->_fluid->fetchAll( array('i = ?' => $this->_id) )->toArray();

                    foreach ($fluidrows as $row)
                    {
                        unset ($row['u']);
                        //$this->_fluidnodes[$row['k']] = $row['k'];
                        $this->_data[$row['k']] = $row['v'];
                    }
            }
            else
                return false;

            $this->_loaded = true;
            return true;
        }
    }