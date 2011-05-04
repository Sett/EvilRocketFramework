<?php
/**
 * 
 * класс обертка для PDFTable
 * @author nur
 * @example
 * $p = new PDFTable();
    $p->AddPage();
    $p->setfont('times','',12);
    $p->htmltable($table1);
    $p->output('example.pdf','F');
 */

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library/Evil/PDF/'),
    get_include_path(),
)));
define('FPDF_FONTPATH','font/');
require_once('lib/pdftable.inc.php');

class Evil_Pdf extends PDFTable
{
    
}