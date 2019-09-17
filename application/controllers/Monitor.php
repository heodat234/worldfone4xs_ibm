<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Monitor extends WFF_Controller {
	function __construct()
    { 
    	parent::__construct();
    	$only_main_content = (bool) $this->input->get("omc");
        $this->_build_template($only_main_content);    
    }

    public function one()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/monitor/one.js";
		$this->load->view('monitor/one_view');
	}

	public function two()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/monitor/two.js";
		$this->load->view('monitor/two_view');
	}
}