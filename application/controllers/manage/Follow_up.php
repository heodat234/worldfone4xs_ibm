<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Follow_up extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
        $this->_build_template();
    }

    function index() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/follow_up.js";
        $this->load->view('manage/follow_up_view');
    }

    function TS_index() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/follow_up.js";
        $this->load->view('TS/manage/follow_up_view');
    }

    function LO_index() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/follow_up.js";
        $this->load->view('LO/manage/follow_up_view');
    }
}