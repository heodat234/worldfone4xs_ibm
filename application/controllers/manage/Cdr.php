<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cdr extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    }

    public function index() {
    	$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $data["filter"] = $this->input->get("filter");
        // --> Chia view theo phong ban
        $this->load->view( getCT('manage/cdr_view', '/') , $data);
    }

    public function TS_index() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $data["filter"] = $this->input->get("filter");
        // --> Chia view theo phong ban
        $this->load->view( 'TS/manage/cdr_view' , $data);
    }

    public function LO_index() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $data["filter"] = $this->input->get("filter");
        // --> Chia view theo phong ban
        $this->load->view( 'LO/manage/cdr_view' , $data);
    }
}