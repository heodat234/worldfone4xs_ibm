<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Setting extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    }

    function agent_status_code()
    {
    	$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/setting/agent_status_code.js";
    	$this->load->view("setting/agent_status_code_view");
    }

    function preference()
    {
    	//pre($this->session->all_userdata());
    	$this->load->view("setting/preference_view");
    }

    function config()
	{
		$this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
		$this->load->view("setting/config_view");
	}

    function jsondata()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/jsondata.js";
		$this->load->view('setting/jsondata_view');
	}

	function group()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/setting/group.js";
		$this->load->view("setting/group_view");
	}

	function diallistDetailField()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
		$this->output->data["js"][] = STEL_PATH . "js/setting/diallist_detail_field.js";
		$this->load->view("setting/diallist_detail_field_view");
	}

	function servicelevel()
	{
		$this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
		$this->load->view('setting/servicelevel_view');
	}

	function chat_status_code()
    {
    	$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/setting/agent_status_code.js";
    	$this->load->view("setting/chat_status_code_view");
    }

    function sms_template()
    {
    	$this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
    	$this->load->view("setting/sms_template_view");
    }

    function email_template()
    {
    	$this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
    	$this->load->view("setting/email_template_view");
    }

    function email_blacklist()
    {
    	$this->output->data["css"][] = STEL_PATH . "css/table.css";
    	$this->load->view("setting/email_blacklist_view");
    }

    function phone_blacklist()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->load->view("setting/phone_blacklist_view");
    }

    function trigger() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/admin/trigger.js";
        $this->load->view("setting/trigger_view");
    }
}