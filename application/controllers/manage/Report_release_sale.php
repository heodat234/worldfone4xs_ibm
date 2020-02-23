<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_release_sale extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
    	$only_main_content = (bool) $this->input->get("omc");
        $this->_build_template($only_main_content);
    }

	public function index()
	{
        $this->output->test($this->data);
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/report_release_sale.js";
        $this->load->view('manage/report_release_sale_view');
	}
}