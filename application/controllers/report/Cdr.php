<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Cdr extends WFF_Controller {
	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    	$this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
    }

    public function index()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->output->data["js"][] = STEL_PATH . "js/manage/cdr.js";
		$this->load->view('report/index_view');
	}

	public function statistic()
	{
		$this->load->view('report/statistic_view');
	}

	public function misscall()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/manage/misscall.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->load->view('report/misscall_view'); 
	}

	public function qc()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->load->view('report/qc_view');
	}

	public function unconnectedcallout()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
		$this->load->view('report/un_connected_call_out_view.php');
	}

	public function unconnectedcallin()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
		$this->load->view('report/un_connected_call_in_view.php');
	}

	function qcByAgent() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view('report/qc_by_agent_view');
    }
}