<?php

    class Evil_Composite_H2D implements Evil_Composite_Interface,
        ArrayAccess, Countable, Iterator
    {
       private $_items = array();
       private $_type;

       private $_fixed;

       private $_fluid;

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Whether a offset exists
         * @link http://php.net/manual/en/arrayaccess.offsetexists.php
         * @param mixed $offset <p>
         * An offset to check for.
         * </p>
         * @return boolean Returns true on success or false on failure.
         * </p>
         * <p>
         * The return value will be casted to boolean if non-boolean was returned.
         */
        public function offsetExists ($offset) {
            // TODO: Implement offsetExists() method.
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Offset to retrieve
         * @link http://php.net/manual/en/arrayaccess.offsetget.php
         * @param mixed $offset <p>
         * The offset to retrieve.
         * </p>
         * @return mixed Can return all value types.
         */
        public function offsetGet ($offset) {
            // TODO: Implement offsetGet() method.
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Offset to set
         * @link http://php.net/manual/en/arrayaccess.offsetset.php
         * @param mixed $offset <p>
         * The offset to assign the value to.
         * </p>
         * @param mixed $value <p>
         * The value to set.
         * </p>
         * @return void
         */
        public function offsetSet ($offset, $value) {
            // TODO: Implement offsetSet() method.
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Offset to unset
         * @link http://php.net/manual/en/arrayaccess.offsetunset.php
         * @param mixed $offset <p>
         * The offset to unset.
         * </p>
         * @return void
         */
        public function offsetUnset ($offset) {
            // TODO: Implement offsetUnset() method.
        }

       public function __construct ($type)
       {
           $this->_type = $type;

           $prefix = Zend_Registry::get('db-prefix');

           $this->_fixed = new Zend_Db_Table($prefix.$type.'s-fixed');
           $this->_fluid = new Zend_Db_Table($prefix.$type.'s-fluid'); // ?

           $info = $this->_fixed->info();
           $this->_fixedschema = $info['cols'];
       }

       public function where ($key, $selector, $value)
       {
            $mode  = mb_substr($selector,0,1);
            $query = mb_substr($selector,1);

            switch ($mode)
            {
                case '=':
                    list($key, $value) = explode('=', $query);

                    if (in_array($key, $this->_fixedschema))
                    {
                        $rows = $this->_fixed->fetchAll(
                            $this->_fixed
                                ->select()
                                ->from(
                                    $this->_fixed,
                                        array($this->_type.'_id')
                                       )
                                ->where($key.' = ?', $value));

                        $ids = $rows->toArray();

                                        foreach ($ids as $id)
                                        {
                                                $id = $id[$this->_type.'_id'];
                                                $this->_items[$id] = new H2D_Object($this->_type, $id);
                                        }
                    }
                    else
                    {
                        $rows = $this->_fluid->fetchAll(
                            $this->_fluid
                                ->select()
                                ->from(
                                    $this->_fluid,
                                        array('i')
                                       )
                                ->where('k = ?', $key)
                                ->where('v = ?', $value));

                        $ids = $rows->toArray();

                                        foreach ($ids as $id)
                                        {
                                                $id = $id['i'];
                                                $this->_items[$id] = new H2D_Object($this->_type, $id);
                                        }
                    }

                break;

                        case ':':
                    list($key, $values) = explode(':', $query);

                                $values = explode(',',$values);

                                foreach ($values as &$value)
                                    $value = '"'.$value.'"';

                    if (in_array($key, $this->_fixedschema))
                    {

                                        $rows = $this->_fixed->fetchAll(
                            $this->_fixed
                                ->select()
                                ->from(
                                    $this->_fixed,
                                        array($this->_type.'_id')
                                       )
                                ->where($key.' IN ('.implode(',',$values).')'));


                        $ids = $rows->toArray();

                                        foreach ($ids as $id)
                                        {
                                                $id = $id[$this->_type.'_id'];
                                                $this->_items[$id] = new H2D_Object($this->_type, $id);
                                        }
                    }
                    else
                    {
                        $rows = $this->_fluid->fetchAll(
                            $this->_fluid
                                ->select()
                                ->from(
                                    $this->_fluid,
                                        array('i')
                                       )
                                ->where('k = ?', $key)
                                ->where('v IN ("'.implode(',',$values).'")'));

                        $ids = $rows->toArray();

                                        foreach ($ids as $id)
                                        {
                                                $id = $id['i'];
                                                $this->_items[$id] = new H2D_Object($this->_type, $id);
                                        }
                    }

                break;
            }

            return $this;
       }

       public function data()
       {
            $output = array();

            foreach ($this->_items as $id => $item)
                $output[$id] = $item->data();

            return $output;
       }

       public function addDNode ($key, $fn)
       {
            foreach ($this->_items as $item)
                $item->addDNode($key, $fn);

            return $this;
       }
    }