<?php
require_once FCPATH . "vendor/autoload.php";

class Pheanstalk extends Pheanstalk\Pheanstalk {
	private $host = "127.0.0.1";
	
	function __construct()
    { 
    	parent::__construct($this->host);
    }
}