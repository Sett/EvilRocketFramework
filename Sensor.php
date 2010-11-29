<?php

    /**
     * @author BreathLess
     * @type Library
     * @description: Sensor class, ported from OX!
     * @package Evil
     * @subpackage Sensor
     * @version 0.1
     * @date 28.11.10
     * @time 16:19
     */

    class Evil_Sensor
    {
        public static function track ($type, $source)
        {
            $sensor = Evil_Structure::getObject('sensor');
            // Получить из конфигов частоту трекинга
            // Проверить время последнего съема значений
            // Снять значение
            $sourceClass = Evil_Factory::make('Evil_Sensor_'.$type);
            $value = $sourceClass->track($source, array());
            // Поместить в БД
            $sensor->create('',
                array('src'=> $source,
                      'type'=> $type,
                      'value'=> $value,
                      'time'=>time())
            );
        }

        public static function get ($type, $source)
        {
            $sensors = Evil_Structure::getComposite('sensor');

            $sensors->where('src','=',$source);
            $srcFiltered = $sensors->data('id');

            $sensors->where('type','=',$type);
            $typeFiltered = $sensors->data('id');

            $sensors->load(array_intersect($srcFiltered, $typeFiltered));

            $Output = array();
            foreach($sensors->_items as $item)
                $Output[] = array((int)$item->getValue('time')*1000, (float)$item->getValue('value'));

            return $Output;
        }
    }