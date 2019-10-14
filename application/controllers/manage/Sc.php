<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sc extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
//        $this->load->model("Ticket_model");
    	$only_main_content = (bool) $this->input->get("omc");
        $this->_build_template($only_main_content);
    }

	public function dealer()
	{
        $this->output->test($this->data);
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/dealer.js";
        $this->load->view('manage/dealer_view');
	}

	public function sc()
	{
        $this->output->test($this->data);
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/sc.js";
        $this->load->view('manage/sc_view');
	}

	public function scSchedule() {
        $this->output->test($this->data);
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/scSchedule.js";
        $this->load->view('manage/scSchedule_view');
    }
}