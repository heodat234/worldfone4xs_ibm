<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Ticket extends WFF_Controller {
	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    	$this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
    }

	public function statistic()
	{
		$this->load->view('report/ticket_statistic_view');
	}

	public function supportCustomer()
	{
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view('report/supportCustomer_view.php');
	}
}