<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Call_out extends WFF_Controller {
   function __construct()
    {
      parent::__construct();
      $this->_build_template();
    }

   public function index()
   {
     $this->output->data["css"][] = STEL_PATH . "css/table.css";
     $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
     $this->output->data["js"][] = STEL_PATH . "js/tools.js";
     $this->load->view('report/telesale/call_out_view');
   }

}