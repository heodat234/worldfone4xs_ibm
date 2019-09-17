<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Agentstatus extends WFF_Controller {
	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    }

	public function index()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->output->data["js"][] = STEL_PATH . "js/manage/cdr.js";
		$this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
		$this->load->view('report/agent_status_view');
	}

	function scheduler()
	{
		$this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
		$this->output->data["js"][] = KENDOUI_PATH . "js/kendo.timezones.min.js";
		$this->load->view('report/agent_status_scheduler_view');
	}

	public function agentStatusDetail()
	{
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view('report/agent_status_detail_view');
	}
}
