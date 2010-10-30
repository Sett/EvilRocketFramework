    <?php

    class Evil_Composite_Hybrid implements Evil_Composite_Interface,
        ArrayAccess, Countable, Iterator
    {
        public $_items = array();
        private $_type;

        private $_fixed;


        public function count ()
        {
           // TODO: Implement count() method
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Return the current element
         * @link http://php.net/manual/en/iterator.current.php
         * @return mixed Can return any type.
         */
        public function current () {
            // TODO: Implement current() method.
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Return the key of the current element
         * @link http://php.net/manual/en/iterator.key.php
         * @return scalar scalar on success, integer
         * 0 on failure.
         */
        public function key () {
            // TODO: Implement key() method.
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Move forward to next element
         * @link http://php.net/manual/en/iterator.next.php
         * @return void Any returned value is ignored.
         */
        public function next () {
            // TODO: Implement next() method.
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Rewind the Iterator to the first element
         * @link http://php.net/manual/en/iterator.rewind.php
         * @return void Any returned value is ignored.
         */
        public function rewind () {
            // TODO: Implement rewind() method.
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Checks if current position is valid
         * @link http://php.net/manual/en/iterator.valid.php
         * @return boolean The return value will be casted to boolean and then evaluated.
         * Returns true on success or false on failure.
         */
        public function valid () {
            // TODO: Implement valid() method.
        }

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
        public function offsetUnset ($offset)
        {
            // TODO: Implement offsetUnset() method.
        }

        public function __construct ($type)
        {
            $this->_type = $type;

            $prefix = Zend_Registry::get ('db-prefix');

            if (substr($type, strlen($type)-1) != 's')
               $postfix = 's';
            else
               $postfix = '';

            $this->_fixed = new Zend_Db_Table($prefix . $type . $postfix. '-fixed');
            $this->_fluid = new Zend_Db_Table($prefix . $type . $postfix. '-fluid'); // ?

            $info = $this->_fixed->info ();
            $this->_fixedschema = $info['cols'];
        }

        public function where ($key, $selector, $value)
        {
            switch ($selector)
            {
                case '=':
                    if (in_array ($key, $this->_fixedschema)) {
                        $rows = $this->_fixed->fetchAll (
                            $this->_fixed
                                ->select ()
                                ->from (
                                $this->_fixed,
                                array('id')
                            )
                                ->where ($key . ' = ?', $value));

                        $ids = $rows->toArray ();

                        foreach ($ids as $id)
                        {
                            $id = $id['id'];
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
                                ->where ('k = ?', $key)
                                ->where ('v = ?', $value));

                        $ids = $rows->toArray ();

                        foreach ($ids as $id)
                        {
                            $id = $id['i'];
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
                            )
                                ->where ($key . ' IN (' . implode (',', $value) . ')'));


                        $ids = $rows->toArray ();

                        foreach ($ids as $id)
                        {
                            $id = $id['id'];
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
                                ->where ('k = ?', $key)
                                ->where ('v IN ("' . implode (',', $value) . '")'));

                        $ids = $rows->toArray ();

                        foreach ($ids as $id)
                        {
                            $id = $id['i'];
                            $this->_items[$id] = new Evil_Object_Hybrid($this->_type, $id);
                        }
                    }

                    break;
            }

            return $this;
        }

        public function data ()
        {
            $output = array();

            foreach ($this->_items as $id => $item)
                $output[$id] = $item->data ();

            return $output;
        }

        public function addDNode ($key, $fn) {
            foreach ($this->_items as $item)
            $item->addDNode ($key, $fn);

            return $this;
        }
    }