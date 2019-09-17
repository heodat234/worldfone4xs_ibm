<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Log extends WFF_Controller {
	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    	$this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
    }

    public function sms()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->load->view('report/sms_log_view');
	}

	public function email()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->load->view('report/email_log_view');
	}
}