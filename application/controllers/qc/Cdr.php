<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cdr extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    }

    public function index() {
    	$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->load->view('qc/cdr_view');
    }
}