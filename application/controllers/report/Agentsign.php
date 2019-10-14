<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Agentsign extends WFF_Controller {
	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    }

	public function index()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";;
        $this->output->data["js"][] = STEL_PATH . "js/report/agentsign.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
		$this->load->view('report/agent_sign_view');
	}

	function scheduler()
	{
		$this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
		$this->output->data["js"][] = KENDOUI_PATH . "js/kendo.timezones.min.js";
		$this->load->view('report/agent_sign_scheduler_view');
	}
}