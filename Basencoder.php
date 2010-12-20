<?php

    /**
     * @author BreathLess
     * @date 20.12.10
     * @time 14:05
     */

    class Evil_Basencoder
    {
        private static $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        
        public static function encode($num, $base)
        {
            $alphabet = substr(self::$alphabet, 0, $base-1);
            $base_count = strlen($alphabet);
            $encoded = '';

            while ($num >= $base_count) {
                $div = $num / $base_count;
                $mod = ($num - ($base_count * intval($div)));
                $encoded = $alphabet[$mod] . $encoded;
                $num = intval($div);
            }

            if ($num) {
                $encoded = $alphabet[$num] . $encoded;
            }

            return $encoded;
        }

        public static function decode($num, $base)
        {
            $alphabet = substr(self::$alphabet, 0, $base-1);
            $len = strlen($num);
            $decoded = 0;
            $multi = 1;

            for ($i = $len - 1; $i >= 0; $i--) {
                $decoded += $multi * strpos($alphabet, $num[$i]);
                $multi = $multi * strlen($alphabet);
            }

            return $decoded;
        }
    }