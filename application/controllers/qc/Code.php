<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Code extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    }

    public function index() {
    	$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->load->view('qc/code_view');
    }
}