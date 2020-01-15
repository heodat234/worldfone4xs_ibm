<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Diallist extends WFF_Controller {

    public function __construct() {
        parent::__construct();
        $this->_build_template();
    }

    public function index() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->load->view('manage/diallist_view');
    }
}
