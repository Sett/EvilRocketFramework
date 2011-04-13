<?php
$min_libPath = dirname(__FILE__) . '/Minify';
set_include_path($min_libPath . PATH_SEPARATOR . get_include_path());
include_once 'Minify/Minify.php';
class Evil_Minify extends Minify
{
    
}
Minify::setCache( APPLICATION_PATH . '/cache/', true);
