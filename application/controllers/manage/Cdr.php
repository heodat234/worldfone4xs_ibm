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
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->output->data["js"][] = STEL_PATH . "js/manage/cdr.js";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->load->view('manage/cdr_view');
    }
}