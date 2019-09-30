<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 */


class Data_library extends WFF_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->_build_template();
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        // $this->output->data["js"][] = STEL_PATH . "js/manage/telesalelist.js";
        $this->load->view('manage/datalibrary_view');
    }
}
