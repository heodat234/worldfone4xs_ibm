<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 * By Le Thanh Hung
 * 19/02/2020
 */

class Last_past_year_report extends WFF_Controller {

    public function __construct() {
        parent::__construct();
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
    }

    public function index() {
        $this->_build_template();
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->load->view('report/loan/last_past_year_view');
    }
}
