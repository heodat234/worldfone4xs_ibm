<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Appointment extends WFF_Controller {

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
        $this->output->data["js"][] = STEL_PATH . "js/manage/appointment.js";
        $this->load->view('manage/appointment_view');
	}

    public function solve()
    {
        $this->output->test($this->data);
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/appointment_solve.js";
        $this->load->view('manage/appointment_solve_view');
    }

	public function log() {
        $this->output->test($this->data);
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/appointment_log.js";
        $this->load->view('manage/appointment_log_view');
    }

	public function log_solve() {
        $this->output->test($this->data);
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/appointment_log.js";
        $this->load->view('manage/appointment_log_solve_view');
    }
}