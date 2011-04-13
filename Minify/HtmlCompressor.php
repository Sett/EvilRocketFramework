<?php
include_once 'lib/Minify/HTML.php';
include_once 'lib/JSMinPlus.php';

function minJs ($js)
{
    return JSMinPlus::minify($js);
}
class Minify_HtmlCompressor implements Zend_Filter_Interface
{
    public function filter($value) {
        $opts = array();
        $opts['jsMinifier'] = 'minJs';
      return Minify_HTML::minify($value,$opts);
    }
}