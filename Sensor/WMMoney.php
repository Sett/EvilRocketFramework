<?php

    /**
     * @author BreathLess
     * @type Driver
     * @description: Webmaster profit
     * @package Evil
     * @subpackage Sensor
     * @version 0.1
     * @date 28.11.10
     * @time 16:22
     */

    class Evil_Sensor_WMMoney implements Evil_Sensor_Interface
    {
        public function track($source, $args = null)
        {
            $sales = Evil_Structure::getComposite('transfer');
            $sales->where('src', '=', $source);

            $filteredByType = $sales->data('type');
            $filteredByType = array_keys($filteredByType, 'billSale');

            $filteredByPayed = $sales->data('isPayed');
            $filteredByPayed = array_keys($filteredByPayed, Score_Money_Core::PAYED);

            $sales->load(array_intersect($filteredByPayed, $filteredByType));

            $sum = 0;
            $sales = $sales->data();
            foreach ($sales as $sale)
                $sum+= $sale['sum'];

            return $sum;
        }
    }