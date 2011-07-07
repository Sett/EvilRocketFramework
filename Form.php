<?php
/**
 * @description Form operating
 * @author Se#
 * @version 0.0.3
 */
class Evil_Form extends Zend_Form
{
    /**
     * @description construct a form, if values is passed, call isValid
     * @param array $formConfig
     * @param array $values
     * @author Se#
     * @version 0.0.2
     */
    public function __construct($formConfig, $values = array())
    {
        if(is_array($formConfig) && isset($formConfig['callback']))
        {
            $field = is_array($formConfig['callback']) && isset($formConfig['callback']['field'])
                    ? $formConfig['callback']['field']
                    : 'callback';

            $formConfig = self::callback($formConfig, $field);
        }
        elseif(is_string($formConfig))// form by table
            $formConfig = self::createFormOptionsByTable($formConfig);

        parent::__construct($formConfig);

        if(!empty($values))
            $this->isValid($values);
    }

    /**
     * @description create a table object, if table exists
     * @static
     * @param string $name
     * @return bool|Zend_Db_Table
     * @author Se#
     * @version 0.0.1
     */
    public static function getTable($name)
    {
        if(!is_string($name))
            return false;

        $tables = Zend_Registry::get('db')->listTables();
        $name   = Evil_DB::scope2table($name);
        $table  = false;

        if(in_array($name, $tables))
            $table = new Zend_Db_Table($name);

        return $table;
    }

    /**
     * @description create a form config by table scheme
     * @static
     * @param string|Zend_Db_Table $table
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public static function createFormOptionsByTable($table)
    {
        if(is_string($table) && !($table = self::getTable($table)))
            return array();// todo: fix an error

        $metadata = $table->info('metadata');// get metadata
        $options = array('method' => 'post', 'elements' => array());// basic options

        foreach($metadata as $columnName => $columnScheme)
        {
            if($columnScheme['PRIMARY'])// don't show if primary key
                continue;

            $typeOptions = self::getFieldType($columnScheme['DATA_TYPE']);// return array('type'[, 'options'])
            $attrOptions = array('label' => ucfirst($columnName));
            $options = self::setFormField($options, $columnName, $attrOptions, $typeOptions);
        }

        $options['elements']['submit'] = array('type' => 'submit');// add submit button

        return $options;
    }

    /**
     * @description set form field
     * @param array $options
     * @param string $columnName
     * @param array $attrOptions
     * @param array $typeOptions
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public static function setFormField($options, $columnName, $attrOptions, $typeOptions)
    {
        if(isset($options['elements'][$columnName]) && ('ignore' == $options['elements'][$columnName]))
            unset($options['elements'][$columnName]);
        else
        {
            if(isset($options['elements'][$columnName]))
            {
                $options['elements'][$columnName]['type'] = isset($options['elements'][$columnName]['type']) ?
                        $options['elements'][$columnName]['type'] :
                        $typeOptions[0];

                $options['elements'][$columnName]['options'] = isset($options['elements'][$columnName]['options']) ?
                        $options['elements'][$columnName]['options'] + $attrOptions :
                        $attrOptions;
            }
            else
            {
                $options['elements'][$columnName] = array(
                    'type' => $typeOptions[0],
                    'options' =>  $attrOptions
                );
            }

            if(isset($typeOptions[1]))// if there is some additional options, merge it with the basic options
                $options['elements'][$columnName]['options'] += $typeOptions[1];
        }

        return $options;
    }

    /**
     * @description convert mysql type to the HTML-type and add (if it needs) options for the HTML-type
     * @param string $type
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public static function getFieldType($type)
    {
        switch($type)
        {
            case 'text' : return array('textarea', array('rows' => 5));
            case 'int'  : return array('text');
            default     : return array('text');
        }
    }

    /**
     * @description now it is an alias for array_merge_recursive,
     * todo: more intellectual merging,
     * purpose: merge different form configs, ex. default and personal
     * @static
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public static function merge()
    {
        $args  = func_get_args();
        $count = count($args);

        if($count <= 1)
            return array();

        return call_user_func_array('array_merge_recursive', $args);
    }

    /**
     * @description apply callback for a field. Callback is looking in [elements][field][options][$callBackField]
     * @static
     * @param array $formConfig
     * @param string $callBackField
     * @return array
     * @author Se#
     * @version 0.0.2
     */
    public static function callback(array $formConfig, $callBackField = 'callback')
    {
        $elements = isset($formConfig['elements']) ? $formConfig['elements'] : array();

        foreach($elements as $name => $config)
        {
            if(isset($config['options'][$callBackField]))
            {
                $result = call_user_func_array($config['options']['default'],
                                               array($formConfig['elements'][$name], $formConfig));

                if(is_string($result))
                    $formConfig['elements'][$name]['options']['value'] = $result;
                else
                    $formConfig['elements'][$name] = $result;

                if(isset($formConfig['elements'][$name]['options'][$callBackField]))
                    unset($formConfig['elements'][$name]['options'][$callBackField]);
            }
        }

        return $formConfig;
    }
}