<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Outsoucing_collection_trend_report extends WFF_Controller 
{

    public function __construct() {
        parent::__construct();
        $this->_build_template();
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
    }

    public function index()
    {
        $this->_build_template();
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->load->view('report/loan/outsoucing_collection_trend_view');
    }
}