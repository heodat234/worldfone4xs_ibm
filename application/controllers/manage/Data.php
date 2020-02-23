<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * @use for LOAN JACCS
 */

class Data extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    }

    function payment_history() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";

        $data["filter"] = $this->input->get("filter");
        $this->load->view('LO/manage/payment_history_view', $data);
    }

    function field_action() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";

        $data["filter"] = $this->input->get("filter");
        $this->load->view('LO/manage/field_action_view', $data);
    }

    function lawsuit_history() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";

        $data["filter"] = $this->input->get("filter");
        $this->load->view('LO/manage/lawsuit_history_view', $data);
    }

    function cross_sell() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";

        $data["filter"] = $this->input->get("filter");
        $this->load->view('LO/manage/cross_sell_view', $data);
    }

    function note() {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/table.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $data["filter"] = $this->input->get("filter");
        $this->load->view('LO/manage/note_view', $data);
    }

    function import_file() {
    	$this->output->data["css"][] = STEL_PATH . "css/table.css";
    	$this->output->data["js"][] = STEL_PATH . "js/table.js";
    	$this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->load->view('LO/manage/import_file_view');
    }
}