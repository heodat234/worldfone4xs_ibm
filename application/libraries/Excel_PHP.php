<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');  
 
require_once APPPATH."/libraries/PHPExcel.php";
 
class Excel_PHP extends PHPExcel {
    public function __construct() {
        parent::__construct();
    }
}