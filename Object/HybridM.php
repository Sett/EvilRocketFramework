<?php
class Evil_Object_HybridM extends Evil_Object_Hybrid
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

        private   $_fixed   = null;
        private   $_fluid   = null;

        public function __construct ($type, $id = null, $data = null)
        {
           $this->type = $type;

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
        public function whereAll ($key, $selector, $value = null)
        {
            switch ($selector)
            {
                case '=':
                    if (in_array($key, $this->_fixedschema))
                        $data = $this->_fixed->fetchAll(
                                        $this->_fixed->select()->where($key.' = ?', $value)
                                                       );
                    else
                        $data = $this->_fluid->fetchAll(
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
               return  $data->toArray();
            }

        }
}