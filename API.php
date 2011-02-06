<?php

    /**
     * @author BreathLess
     * @type Library
     * @description: Evil API
     * @package Evil
     * @subpackage API
     * @version 5
     * @date 27.01.11
     * @time 17:22
     */

    class Evil_API
    {
        /**
         * @param  $Args - ассоциативный массив аргументов
         * @return mixed
         */

        public static function Call($Call)
        {
            // Проверка корректности вызова

            $Method = isset($Call['M']) ? $Call['M']: (isset($Call['Method']) ? $Call['Method']: null);
            $Service = isset($Call['S']) ? $Call['S']: (isset($Call['Service']) ? $Call['Service']: null);

            // Локальный / Удалённый?

            

            // Вызов!
            return call_user_func(array($Service, $Method), $Call);
        }
    }
