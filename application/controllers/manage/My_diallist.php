<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 */


class My_diallist extends WFF_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->_build_template();
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/manage/my_diallist.js";
        $this->load->view('manage/my_diallist_view');
    }
}
