<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Browse extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    }

    public function index() {
    	$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
		$this->output->data["js"][] = STEL_PATH . "js/manage/browse.js";
        $this->load->view('manage/browse_view');
    }
}