<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Agent_chat_summary extends WFF_Controller {
	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    	$this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
    }

    public function index()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        // $this->output->data["js"][] = STEL_PATH . "js/table.js";
        // $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		// $this->output->data["js"][] = STEL_PATH . "js/manage/cdr.js";
		$this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
		$this->output->data["js"][] = KENDOUI_PATH . "js/custom/kendo.muticheck.js";
		$this->load->view('report/chat/agent_chat_summary_view');
	}
	public function bygroup()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        // $this->output->data["js"][] = STEL_PATH . "js/table.js";
        // $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		// $this->output->data["js"][] = STEL_PATH . "js/manage/cdr.js";
		$this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
		$this->output->data["js"][] = KENDOUI_PATH . "js/custom/kendo.muticheck.js";
		$this->load->view('report/chat/agent_chat_summary_by_group_view');
	}
	public function bydate()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        // $this->output->data["js"][] = STEL_PATH . "js/table.js";
        // $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		// $this->output->data["js"][] = STEL_PATH . "js/manage/cdr.js";
		$this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
		$this->output->data["js"][] = KENDOUI_PATH . "js/custom/kendo.muticheck.js";
		$this->load->view('report/chat/agent_chat_summary_by_date_view');
	}

	function scheduler()
	{
		$this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
		$this->output->data["js"][] = KENDOUI_PATH . "js/kendo.timezones.min.js";
		$this->load->view('report/agent_status_scheduler_view');
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
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->load->view('report/email_log_view');
	}
}