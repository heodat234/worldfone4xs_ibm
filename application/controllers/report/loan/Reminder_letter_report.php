<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 * By Le Thanh Hung
 * 12/12/2019
 */

class Reminder_letter_report extends WFF_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->_build_template();
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->load->view('report/loan/reminder_letter_view');
    }
}
