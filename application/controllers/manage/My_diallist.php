<?php defined('BASEPATH') OR exit('No direct script access allowed');

class My_diallist extends WFF_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->_build_template();
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->load->view('manage/my_diallist_view');
    }
}
