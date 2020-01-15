<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 * By Son Vu
 * 24/12/2019
 */


class Clear_small_daily_report extends WFF_Controller {

    public function __construct() {
        parent::__construct();
        $this->_build_template();
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
    }

    public function index() {
        
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->load->view('report/loan/clear_small_daily_view');
    }
}
