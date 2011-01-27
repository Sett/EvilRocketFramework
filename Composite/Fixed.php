<?php
/**
 * @author BreathLess
 */
    class Evil_Composite_Fixed extends Evil_Composite_Base implements Evil_Composite_Interface
    {
        /**
         * @var Zend_Db_Table
         */
        private $_fixed;

        /**
         * @param  $type
         */
        public function __construct ($type)
        {
            $this->_type = $type;
            $this->_fixed = new Zend_Db_Table(Evil_DB::scope2table($type));
            $info = $this->_fixed->info();
            $this->_fixedschema = $info['cols'];
        }

        /**
         * @param  $key
         * @param  $selector
         * @param null $value
         * @param string $mode
         * @return Evil_Composite_Fixed
         */
        public function where ($key, $selector, $value = null,  $mode = 'new')
        {
            //TODO: dynamic configure selectors (Se#)
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
            }

            $ids = $rows->toArray ();
            foreach ($ids as $id)
            {
                $id = $id['id'];
                $this->_items[$id] = new Evil_Object_Fixed($this->_type, $id);
            }
            
            return $this;
        }

        /**
         * @param  $ids
         * @return void
         */
        public function load($ids)
        {
            foreach ($ids as $id)
            {
                $this->_items[$id] = new Evil_Object_Fixed($this->_type, $id);
            }
        }
    }